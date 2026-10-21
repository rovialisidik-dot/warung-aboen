@extends('layouts.app')

@section('title', 'Ubah Barang - ' . $product->name)
@section('page_title', 'Ubah Data Barang Dagangan')
@section('page_subtitle', 'Edit data produk ' . $product->name)

@section('content')
<div class="max-w-2xl mx-auto" x-data="productEditForm()">
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        
        <form action="{{ route('products.update', $product->id) }}" method="POST" class="space-y-5">
            @csrf
            @method('PUT')

            <!-- Name -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="name">
                    Nama Barang <span class="text-rose-500">*</span>
                </label>
                <input type="text" name="name" id="name" value="{{ old('name', $product->name) }}" required
                       class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-slate-900 font-semibold">
            </div>

            <!-- Barcode & Category -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="barcode">
                        Kode Barcode / SKU
                    </label>
                    <div class="relative">
                        <input type="text" name="barcode" id="barcode" value="{{ old('barcode', $product->barcode) }}"
                               class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-mono">
                        <i class="fa-solid fa-barcode absolute left-3 top-3 text-slate-400"></i>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="category_id">
                        Kategori Barang
                    </label>
                    <select name="category_id" id="category_id" class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        <option value="">Pilih Kategori...</option>
                        @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Unit with quick presets -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="unit">
                    Satuan Barang <span class="text-rose-500">*</span>
                </label>
                <div class="flex items-center space-x-2">
                    <input type="text" name="unit" id="unit" x-model="unit" required
                           class="w-40 py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 font-medium">
                    <div class="flex flex-wrap gap-1.5 text-xs">
                        <template x-for="preset in ['pcs', 'bungkus', 'renceng', 'botol', 'kg', 'kotak', 'pouch', 'kaleng']">
                            <button type="button" @click="unit = preset" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-semibold transition-colors" x-text="preset"></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Prices: Purchase Price (HPP) & Selling Price with Live Margin Calc -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 space-y-4">
                <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Perhitungan Harga & Margin</h4>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" for="purchase_price">
                            Harga Beli Pokok (HPP) <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="purchase_price" id="purchase_price" x-model.number="cost" min="0" required
                                   class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1" for="selling_price">
                            Harga Jual Warung <span class="text-rose-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-xs font-bold text-slate-400">Rp</span>
                            <input type="number" name="selling_price" id="selling_price" x-model.number="price" min="0" required
                                   class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                        </div>
                    </div>
                </div>

                <!-- Margin Display Pill -->
                <div class="flex items-center justify-between text-xs p-3 rounded-xl bg-white border border-slate-200">
                    <div>
                        <span class="text-slate-500">Estimasi Keuntungan Bersih per Satuan:</span>
                        <strong class="ml-1 text-slate-800 font-extrabold" x-text="formatRupiah(profitPerUnit())"></strong>
                    </div>
                    <span class="px-2.5 py-1 rounded-full font-bold text-xs"
                          :class="marginPercent() > 0 ? 'bg-teal-100 text-teal-800' : 'bg-rose-100 text-rose-800'"
                          x-text="'Margin: ' + marginPercent() + '%'">
                    </span>
                </div>
            </div>

            <!-- Stocks Info -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-slate-100 border border-slate-200">
                    <span class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Fisik Saat Ini</span>
                    <p class="text-xl font-extrabold text-slate-900 mt-1">{{ $product->stock }} {{ $product->unit }}</p>
                    <span class="text-[11px] text-slate-500 mt-0.5 block">Gunakan fitur Stock Opname di tabel untuk mengubah stok fisik</span>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="min_stock">
                        Batas Minimum Stok (Peringatan) <span class="text-rose-500">*</span>
                    </label>
                    <input type="number" name="min_stock" id="min_stock" value="{{ old('min_stock', $product->min_stock) }}" min="0" required
                           class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="pt-4 border-t border-slate-200 flex items-center justify-end space-x-3">
                <a href="{{ route('products.index') }}" class="py-2.5 px-5 rounded-xl border border-slate-300 text-slate-700 hover:bg-slate-100 text-xs font-bold transition-colors">
                    Batal
                </a>
                <button type="submit" class="py-2.5 px-6 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-extrabold shadow-md shadow-emerald-600/20 transition-all">
                    Simpan Perubahan
                </button>
            </div>

        </form>

    </div>
</div>
@endsection

@push('scripts')
<script>
function productEditForm() {
    return {
        unit: '{{ old('unit', $product->unit) }}',
        cost: {{ old('purchase_price', $product->purchase_price) }},
        price: {{ old('selling_price', $product->selling_price) }},

        profitPerUnit() {
            return Math.max(0, (this.price || 0) - (this.cost || 0));
        },

        marginPercent() {
            if (!this.price || this.price <= 0) return 0;
            return (((this.price - this.cost) / this.price) * 100).toFixed(1);
        },

        formatRupiah(num) {
            return 'Rp ' + Number(num || 0).toLocaleString('id-ID');
        }
    }
}
</script>
@endpush
