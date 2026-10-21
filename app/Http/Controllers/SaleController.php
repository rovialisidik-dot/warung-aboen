<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class SaleController extends Controller
{
    public function index(Request $request)
    {
        $query = Sale::with(['user', 'items'])->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $sales = $query->paginate(15)->withQueryString();

        $summary = [
            'total_amount' => (clone $query)->where('status', 'completed')->sum('total_amount'),
            'total_profit' => (clone $query)->where('status', 'completed')->sum('gross_profit'),
            'total_count' => (clone $query)->where('status', 'completed')->count(),
        ];

        return view('sales.index', compact('sales', 'summary'));
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.product', 'user', 'cancelledBy']);
        return view('sales.show', compact('sale'));
    }

    public function cancel(Request $request, Sale $sale)
    {
        if ($sale->status === 'cancelled') {
            return back()->with('error', 'Transaksi ini sudah pernah dibatalkan sebelumnya.');
        }

        $request->validate([
            'reason' => 'required|string|min:3|max:500',
        ]);

        try {
            DB::transaction(function () use ($request, $sale) {
                $oldStatus = $sale->status;

                // 1. Return stock
                foreach ($sale->items as $item) {
                    if ($item->product) {
                        StockMovement::recordMovement(
                            $item->product,
                            $item->quantity,
                            'in',
                            'cancellation',
                            $sale->id,
                            "Pengembalian stok pembatalan nota {$sale->invoice_number}: {$request->reason}"
                        );
                    }
                }

                // 2. Return cash
                CashTransaction::record(
                    'out',
                    'adjustment',
                    $sale->total_amount,
                    "Pengembalian kas (Refund) pembatalan nota {$sale->invoice_number}: {$request->reason}",
                    $sale->id,
                    auth()->id()
                );

                // 3. Mark sale as cancelled
                $sale->update([
                    'status' => 'cancelled',
                    'cancelled_reason' => $request->reason,
                    'cancelled_at' => now(),
                    'cancelled_by' => auth()->id(),
                ]);

                // 4. Audit Log
                AuditLog::log(
                    'sale_cancelled',
                    "Pembatalan transaksi nota {$sale->invoice_number} senilai " . format_rupiah($sale->total_amount) . ". Alasan: {$request->reason}",
                    'Sale',
                    $sale->id,
                    ['status' => $oldStatus],
                    ['status' => 'cancelled', 'reason' => $request->reason]
                );
            });

            return back()->with('success', "Transaksi nota {$sale->invoice_number} berhasil dibatalkan. Stok dan kas telah disesuaikan secara otomatis.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal membatalkan transaksi: ' . $e->getMessage());
        }
    }
}
