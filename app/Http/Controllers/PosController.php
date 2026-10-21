<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\StockMovement;
use App\Models\CashTransaction;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;
use Exception;

class PosController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        $products = Product::where('is_active', true)
            ->with('category')
            ->orderBy('name')
            ->get();

        return view('pos.index', compact('categories', 'products'));
    }

    public function checkout(Request $request)
    {
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'customer_name' => 'nullable|string|max:100',
            'payment_method' => 'required|in:cash,transfer,qris',
            'payment_amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $sale = DB::transaction(function () use ($validated, $request) {
                $discount = (float) ($validated['discount'] ?? 0);
                $paymentAmount = (float) $validated['payment_amount'];
                $customerName = !empty($validated['customer_name']) ? $validated['customer_name'] : 'Pelanggan Umum';

                $subtotal = 0;
                $totalCost = 0;
                $itemsData = [];

                foreach ($validated['items'] as $itemInput) {
                    $product = Product::lockForUpdate()->findOrFail($itemInput['product_id']);

                    if ($product->stock < $itemInput['quantity']) {
                        throw new Exception("Stok untuk barang '{$product->name}' tidak mencukupi (sisa stok: {$product->stock}).");
                    }

                    $qty = (int) $itemInput['quantity'];
                    $itemSubtotal = $product->selling_price * $qty;
                    $itemCost = $product->purchase_price * $qty;
                    $itemProfit = $itemSubtotal - $itemCost;

                    $subtotal += $itemSubtotal;
                    $totalCost += $itemCost;

                    $itemsData[] = [
                        'product' => $product,
                        'product_name' => $product->name,
                        'unit' => $product->unit,
                        'unit_cost' => $product->purchase_price,
                        'unit_price' => $product->selling_price,
                        'quantity' => $qty,
                        'subtotal' => $itemSubtotal,
                        'profit' => $itemProfit,
                    ];
                }

                $totalAmount = max(0, $subtotal - $discount);

                if ($paymentAmount < $totalAmount) {
                    throw new Exception("Uang pembayaran kurang dari total tagihan.");
                }

                $changeAmount = $paymentAmount - $totalAmount;
                $grossProfit = $totalAmount - $totalCost;

                $invoiceNumber = Sale::generateInvoiceNumber();

                $sale = Sale::create([
                    'invoice_number' => $invoiceNumber,
                    'user_id' => auth()->id(),
                    'customer_name' => $customerName,
                    'subtotal' => $subtotal,
                    'discount' => $discount,
                    'total_amount' => $totalAmount,
                    'total_cost' => $totalCost,
                    'gross_profit' => $grossProfit,
                    'payment_method' => $validated['payment_method'],
                    'payment_amount' => $paymentAmount,
                    'change_amount' => $changeAmount,
                    'status' => 'completed',
                    'notes' => $validated['notes'] ?? null,
                ]);

                foreach ($itemsData as $item) {
                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'product_id' => $item['product']->id,
                        'product_name' => $item['product_name'],
                        'unit' => $item['unit'],
                        'unit_cost' => $item['unit_cost'],
                        'unit_price' => $item['unit_price'],
                        'quantity' => $item['quantity'],
                        'subtotal' => $item['subtotal'],
                        'profit' => $item['profit'],
                    ]);

                    StockMovement::recordMovement(
                        $item['product'],
                        $item['quantity'],
                        'out',
                        'sale',
                        $sale->id,
                        'Penjualan Nota ' . $sale->invoice_number
                    );
                }

                // Catat transaksi kas jika pembayaran tunai / masuk ke saldo kas warung
                CashTransaction::record(
                    'in',
                    'sale',
                    $totalAmount,
                    "Penjualan Nota {$sale->invoice_number} ({$sale->customer_name})",
                    $sale->id,
                    auth()->id()
                );

                AuditLog::log(
                    'sale_created',
                    "Transaksi penjualan {$sale->invoice_number} senilai " . format_rupiah($sale->total_amount) . " berhasil diproses.",
                    'Sale',
                    $sale->id
                );

                return $sale;
            });

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Transaksi berhasil disimpan!',
                    'sale' => $sale->load('items'),
                    'print_url' => route('pos.receipt', $sale->id),
                ]);
            }

            return redirect()->route('pos.index')->with([
                'success' => 'Transaksi berhasil disimpan! Nota: ' . $sale->invoice_number,
                'printed_sale_id' => $sale->id,
            ]);
        } catch (Exception $e) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 422);
            }

            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    public function receipt(Sale $sale)
    {
        $sale->load(['items', 'user']);
        return view('pos.receipt', compact('sale'));
    }
}
