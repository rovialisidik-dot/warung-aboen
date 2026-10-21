<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class PurchaseController extends Controller
{
    public function index(Request $request)
    {
        $query = Purchase::with(['user', 'items'])->latest();

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $purchases = $query->paginate(15)->withQueryString();
        $totalPurchases = (clone $query)->sum('total_amount');

        return view('purchases.index', compact('purchases', 'totalPurchases'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->orderBy('name')->get();
        return view('purchases.create', compact('products'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'supplier_name' => 'nullable|string|max:150',
            'payment_method' => 'required|in:cash,transfer',
            'notes' => 'nullable|string|max:500',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
        ]);

        try {
            $purchase = DB::transaction(function () use ($validated) {
                $totalAmount = 0;
                $itemsData = [];

                foreach ($validated['items'] as $item) {
                    $product = Product::lockForUpdate()->findOrFail($item['product_id']);
                    $qty = (int) $item['quantity'];
                    $cost = (float) $item['unit_cost'];
                    $subtotal = $qty * $cost;
                    $totalAmount += $subtotal;

                    $itemsData[] = [
                        'product' => $product,
                        'product_name' => $product->name,
                        'unit' => $product->unit,
                        'unit_cost' => $cost,
                        'quantity' => $qty,
                        'subtotal' => $subtotal,
                    ];
                }

                $purchaseNumber = Purchase::generatePurchaseNumber();

                $purchase = Purchase::create([
                    'purchase_number' => $purchaseNumber,
                    'user_id' => auth()->id(),
                    'supplier_name' => $validated['supplier_name'] ?? 'Supplier Umum',
                    'total_amount' => $totalAmount,
                    'payment_method' => $validated['payment_method'],
                    'status' => 'received',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($itemsData as $item) {
                    PurchaseItem::create([
                        'purchase_id' => $purchase->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product_name'],
                        'unit' => $item['unit'],
                        'unit_cost' => $item['unit_cost'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                    ]);

                    // Update stock and HPP if updated
                    $item['product']->purchase_price = $item['unit_cost'];
                    $item['product']->save();

                    StockMovement::recordMovement(
                        $item['product'],
                        $item['quantity'],
                        'in',
                        'purchase',
                        $purchase->id,
                        "Pembelian Stok Nota {$purchase->purchase_number} dari {$purchase->supplier_name}"
                    );
                }

                // Record cash outflow for inventory purchase
                CashTransaction::record(
                    'out',
                    'purchase',
                    $totalAmount,
                    "Pembelian Barang Nota {$purchase->purchase_number} ({$purchase->supplier_name})",
                    $purchase->id,
                    auth()->id()
                );

                AuditLog::log(
                    'purchase_created',
                    "Pembelian barang nota {$purchase->purchase_number} senilai " . format_rupiah($totalAmount) . " berhasil dicatat.",
                    'Purchase',
                    $purchase->id
                );

                return $purchase;
            });

            return redirect()->route('purchases.index')->with('success', "Pembelian {$purchase->purchase_number} berhasil dicatat dan stok telah bertambah.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal mencatat pembelian: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Purchase $purchase)
    {
        $purchase->load(['items.product', 'user']);
        return view('purchases.show', compact('purchase'));
    }
}
