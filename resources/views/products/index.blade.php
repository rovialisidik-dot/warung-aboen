@extends('layouts.app')

@section('title', 'Katalog Barang & Stok')
@section('page_title', 'Manajemen Barang & Stok Warung')
@section('page_subtitle', 'Kelola daftar barang dagangan, harga beli (HPP), harga jual, dan stok')

@section('content')
<div class="space-y-6" x-data="{ adjustModalOpen: false, activeProduct: null }">

    <!-- KPI & Action Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex flex-wrap items-center gap-3">
            <div class="bg-white px-4 py-2.5 rounded-xl border border-slate-200 shadow-sm text-xs">
                <span class="text-slate-500">Total Nilai Persediaan (HPP):</span>
                <strong class="text-slate-900 ml-1 font-extrabold text-sm">{{ format_rupiah($totalValuation) }}</strong>
            </div>
            @if($lowStockCount > 0)
            <div class="bg-amber-50 px-4 py-2.5 rounded-xl border border-amber-300 shadow-sm text-xs text-amber-900 font-bold flex items-center space-x-1.5">
                <i class="fa-solid fa-triangle-exclamation text-amber-600"></i>
                <span>{{ $lowStockCount }} barang stok tipis</span>
            </div>
            @endif
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('purchases.create') }}" class="py-2.5 px-4 bg-teal-600 hover:bg-teal-700 text-white rounded-xl text-xs font-bold shadow-md shadow-teal-600/20 flex items-center space-x-1.5 transition-colors">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Kulak / Beli Stok</span>
            </a>
            <a href="{{ route('products.create') }}" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition-colors">
                <i class="fa-solid fa-plus"></i>
                <span>Tambah Barang Baru</span>
            </a>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('products.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Cari Barang:</label>
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Nama barang atau barcode..."
                           class="w-full pl-9 pr-3 py-2 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                    <i class="fa-solid fa-magnifying-glass absolute left-3 top-2.5 text-slate-400 text-xs"></i>
                </div>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Kategori:</label>
                <select name="category_id" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                    <option value="">Semua Kategori</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex items-center pb-2">
                <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 cursor-pointer">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }}
                           class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 border-slate-300">
                    <span>Tampilkan Hanya Stok Menipis</span>
                </label>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('products.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Barang & Barcode</th>
                        <th class="py-3.5 px-4 font-bold">Kategori</th>
                        <th class="py-3.5 px-4 font-bold text-center">Satuan</th>
                        @if(auth()->user()->isOwner())
                        <th class="py-3.5 px-4 font-bold text-right">Harga Beli (HPP)</th>
                        @endif
                        <th class="py-3.5 px-4 font-bold text-right">Harga Jual</th>
                        @if(auth()->user()->isOwner())
                        <th class="py-3.5 px-4 font-bold text-center">Margin</th>
                        @endif
                        <th class="py-3.5 px-4 font-bold text-center">Stok</th>
                        <th class="py-3.5 px-4 font-bold text-center">Min. Stok</th>
                        <th class="py-3.5 px-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $product)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $product->stock <= $product->min_stock ? 'bg-amber-50/20' : '' }}">
                        <td class="py-3.5 px-4">
                            <span class="font-bold text-slate-900 block text-xs">{{ $product->name }}</span>
                            @if($product->barcode)
                            <span class="text-[10px] text-slate-400 font-mono"><i class="fa-solid fa-barcode text-slate-300 mr-1"></i>{{ $product->barcode }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 font-medium">
                            {{ $product->category->name ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-center text-slate-500 font-medium">{{ $product->unit }}</td>
                        @if(auth()->user()->isOwner())
                        <td class="py-3.5 px-4 text-right font-medium text-slate-600">{{ format_rupiah($product->purchase_price) }}</td>
                        @endif
                        <td class="py-3.5 px-4 text-right font-extrabold text-slate-900">{{ format_rupiah($product->selling_price) }}</td>
                        @if(auth()->user()->isOwner())
                        <td class="py-3.5 px-4 text-center font-bold text-teal-700">
                            {{ $product->margin }}%
                        </td>
                        @endif
                        <td class="py-3.5 px-4 text-center">
                            @if($product->stock <= 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Habis</span>
                            @elseif($product->stock <= $product->min_stock)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">
                                {{ $product->stock }} (Kritis)
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                {{ $product->stock }}
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center text-slate-400 font-medium">{{ $product->min_stock }}</td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-1">
                                <!-- Stock Opname Button -->
                                <button type="button" @click="activeProduct = { id: {{ $product->id }}, name: '{{ addslashes($product->name) }}', stock: {{ $product->stock }}, unit: '{{ $product->unit }}' }; adjustModalOpen = true"
                                        class="p-1.5 text-slate-500 hover:text-indigo-600 rounded-lg hover:bg-indigo-50" title="Sesuaikan Stok (Stock Opname)">
                                    <i class="fa-solid fa-sliders"></i>
                                </button>
                                
                                <a href="{{ route('products.edit', $product->id) }}" class="p-1.5 text-slate-500 hover:text-emerald-600 rounded-lg hover:bg-emerald-50" title="Ubah Data">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>

                                @if(auth()->user()->isOwner())
                                <form action="{{ route('products.destroy', $product->id) }}" method="POST" onsubmit="return confirm('Yakin ingin menghapus barang ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Hapus Barang">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-boxes-stacked text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Belum ada barang terdaftar</p>
                            <a href="{{ route('products.create') }}" class="text-xs text-emerald-600 font-bold mt-1 inline-block hover:underline">
                                + Tambah barang pertama
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $products->links() }}
        </div>
    </div>

    <!-- Stock Opname Adjustment Modal -->
    <div x-show="adjustModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="adjustModalOpen = false">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center text-lg">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-slate-900">Penyesuaian Stok (Stock Opname)</h4>
                    <p class="text-xs text-slate-500" x-text="activeProduct?.name + ' (Stok sekarang: ' + activeProduct?.stock + ' ' + activeProduct?.unit + ')'"></p>
                </div>
            </div>

            <form :action="'{{ url('/products') }}/' + activeProduct?.id + '/adjust-stock'" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jenis Penyesuaian:</label>
                    <select name="adjustment_type" required class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-indigo-500">
                        <option value="set">Tetapkan Jumlah Fisik Sebenarnya (Set Stok)</option>
                        <option value="add">Tambah Stok (+ Tambahan Fisik)</option>
                        <option value="subtract">Kurangi Stok (&minus; Rusak / Hilang / Kadaluarsa)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Jumlah:</label>
                    <input type="number" name="quantity" min="0" required :placeholder="'Masukkan jumlah ' + activeProduct?.unit"
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500 font-bold">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Penyesuaian (Wajib):</label>
                    <input type="text" name="notes" required placeholder="Contoh: Hasil hitung fisik toko mingguan / Kemasan bocor"
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-indigo-500">
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-xs font-bold shadow-md shadow-indigo-600/20 transition-colors">
                        Simpan Penyesuaian
                    </button>
                    <button type="button" @click="adjustModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
