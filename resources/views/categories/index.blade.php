@extends('layouts.app')

@section('title', 'Kategori Barang')
@section('page_title', 'Kelola Kategori Barang')
@section('page_subtitle', 'Pengelompokan barang untuk memudahkan pencarian di kasir dan laporan')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false, editModalOpen: false, activeCat: null }">

    <div class="flex justify-between items-center">
        <div>
            <h3 class="text-sm font-bold text-slate-800">Daftar Kategori Terdaftar</h3>
            <p class="text-xs text-slate-500">Total {{ $categories->count() }} kategori barang</p>
        </div>
        <button type="button" @click="addModalOpen = true" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition-colors">
            <i class="fa-solid fa-plus"></i>
            <span>Tambah Kategori Baru</span>
        </button>
    </div>

    <!-- Category Grid Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach($categories as $category)
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:border-emerald-500 transition-colors flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                        <i class="fa-solid fa-tag"></i>
                    </span>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-700">
                        {{ $category->products_count }} Barang
                    </span>
                </div>
                <h4 class="font-bold text-slate-900 text-base">{{ $category->name }}</h4>
                <p class="text-xs text-slate-500 mt-1">{{ $category->description ?? 'Tidak ada keterangan' }}</p>
            </div>

            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between">
                <a href="{{ route('products.index', ['category_id' => $category->id]) }}" class="text-xs font-bold text-emerald-600 hover:underline">
                    Lihat {{ $category->products_count }} Barang &rarr;
                </a>
                <div class="flex items-center space-x-1">
                    <button type="button" @click="activeCat = { id: {{ $category->id }}, name: '{{ addslashes($category->name) }}', description: '{{ addslashes($category->description) }}' }; editModalOpen = true"
                            class="p-1.5 text-slate-400 hover:text-emerald-600 rounded-lg hover:bg-slate-100" title="Ubah Kategori">
                        <i class="fa-solid fa-pen-to-square"></i>
                    </button>
                    @if($category->products_count === 0)
                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-slate-100" title="Hapus Kategori">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Add Category Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="addModalOpen = false">
            <h4 class="font-bold text-base text-slate-900 mb-4">Tambah Kategori Baru</h4>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" required placeholder="Contoh: Biskuit & Roti"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Keterangan</label>
                    <textarea name="description" rows="2" placeholder="Keterangan singkat kategori..."
                              class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Simpan Kategori
                    </button>
                    <button type="button" @click="addModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <div x-show="editModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="editModalOpen = false">
            <h4 class="font-bold text-base text-slate-900 mb-4">Ubah Kategori</h4>
            <form :action="'{{ url('/categories') }}/' + activeCat?.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" :value="activeCat?.name" required
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Deskripsi / Keterangan</label>
                    <textarea name="description" rows="2" :value="activeCat?.description"
                              class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500"></textarea>
                </div>
                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold transition-colors">
                        Simpan Perubahan
                    </button>
                    <button type="button" @click="editModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
