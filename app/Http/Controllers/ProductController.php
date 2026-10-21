<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Category;
use App\Models\StockMovement;
use App\Models\AuditLog;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('name')->get();

        $query = Product::with('category')->latest();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->boolean('low_stock')) {
            $query->whereColumn('stock', '<=', 'min_stock');
        }

        $products = $query->paginate(15)->withQueryString();

        $totalValuation = Product::where('is_active', true)->sum(DB::raw('stock * purchase_price'));
        $lowStockCount = Product::where('is_active', true)->whereColumn('stock', '<=', 'min_stock')->count();

        return view('products.index', compact('products', 'categories', 'totalValuation', 'lowStockCount'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $product = Product::create($validated);

        if ($product->stock > 0) {
            StockMovement::create([
                'product_id' => $product->id,
                'user_id' => auth()->id(),
                'type' => 'in',
                'reference_type' => 'initial',
                'reference_id' => null,
                'quantity' => $product->stock,
                'stock_before' => 0,
                'stock_after' => $product->stock,
                'notes' => 'Stok awal barang baru didaftarkan',
            ]);
        }

        AuditLog::log('product_created', "Barang baru '{$product->name}' ditambahkan dengan stok {$product->stock} {$product->unit}.", 'Product', $product->id);

        return redirect()->route('products.index')->with('success', "Barang '{$product->name}' berhasil ditambahkan.");
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('name')->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_stock' => 'required|integer|min:0',
            'is_active' => 'boolean',
        ]);

        $oldPrices = [
            'purchase_price' => $product->purchase_price,
            'selling_price' => $product->selling_price,
        ];

        $product->update($validated);

        if ($oldPrices['purchase_price'] != $product->purchase_price || $oldPrices['selling_price'] != $product->selling_price) {
            AuditLog::log(
                'product_price_changed',
                "Perubahan harga barang '{$product->name}': Beli " . format_rupiah($oldPrices['purchase_price']) . " -> " . format_rupiah($product->purchase_price) . ", Jual " . format_rupiah($oldPrices['selling_price']) . " -> " . format_rupiah($product->selling_price),
                'Product',
                $product->id,
                $oldPrices,
                ['purchase_price' => $product->purchase_price, 'selling_price' => $product->selling_price]
            );
        }

        return redirect()->route('products.index')->with('success', "Data barang '{$product->name}' berhasil diperbarui.");
    }

    public function destroy(Product $product)
    {
        $name = $product->name;
        $product->delete();

        AuditLog::log('product_deleted', "Barang '{$name}' telah dihapus dari sistem.", 'Product');

        return redirect()->route('products.index')->with('success', "Barang '{$name}' berhasil dihapus.");
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|integer|min:0',
            'notes' => 'required|string|max:255',
        ]);

        $before = $product->stock;
        $qtyInput = (int) $validated['quantity'];

        if ($validated['adjustment_type'] === 'add') {
            $type = 'in';
            $qty = $qtyInput;
            $after = $before + $qty;
        } elseif ($validated['adjustment_type'] === 'subtract') {
            $type = 'out';
            $qty = $qtyInput;
            $after = max(0, $before - $qty);
        } else {
            // set directly
            $type = 'adjustment';
            $after = $qtyInput;
            $qty = abs($after - $before);
        }

        $product->update(['stock' => $after]);

        StockMovement::create([
            'product_id' => $product->id,
            'user_id' => auth()->id(),
            'type' => $type,
            'reference_type' => 'adjustment',
            'reference_id' => null,
            'quantity' => $qty,
            'stock_before' => $before,
            'stock_after' => $after,
            'notes' => $validated['notes'],
        ]);

        AuditLog::log(
            'stock_adjusted',
            "Penyesuaian stok '{$product->name}' dari {$before} menjadi {$after} {$product->unit}. Catatan: {$validated['notes']}",
            'Product',
            $product->id
        );

        return back()->with('success', "Stok barang '{$product->name}' berhasil disesuaikan menjadi {$after} {$product->unit}.");
    }
}
