@extends('layouts.app')

@section('title', 'Laporan Persediaan & Nilai Aset')
@section('page_title', 'Laporan Persediaan & Valuasi Stok')
@section('page_subtitle', 'Analisis komprehensif aset barang dagangan, modal tertahan, dan potensi margin')

@section('content')
<div class="space-y-6">

    <!-- KPI Cards & Print Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 no-print">
        <form method="GET" action="{{ route('reports.stock') }}" class="flex items-center space-x-2">
            <label class="flex items-center space-x-2 text-xs font-bold text-slate-700 bg-white px-4 py-2.5 rounded-2xl border border-slate-200 cursor-pointer shadow-sm hover:bg-slate-50 transition-colors">
                <input type="checkbox" name="low_stock_only" value="1" {{ request('low_stock_only') ? 'checked' : '' }} onchange="this.form.submit()"
                       class="rounded text-emerald-600 focus:ring-emerald-500 w-4 h-4 border-slate-300">
                <span>Hanya Tampilkan Stok Menipis</span>
            </label>
            @if(request('low_stock_only'))
            <a href="{{ route('reports.stock') }}" class="text-xs text-rose-600 font-bold hover:underline ml-2">Reset Filter</a>
            @endif
        </form>

        <div class="flex items-center space-x-2">
            <a href="{{ route('reports.stock.export', request()->query()) }}"
               class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow flex items-center space-x-2 transition-all"
               title="Unduh laporan persediaan format Spreadsheet Excel (.csv)">
                <i class="fa-solid fa-file-excel text-emerald-200 text-sm"></i>
                <span>Ekspor Excel (.csv)</span>
            </a>
            <button type="button" onclick="window.print()"
                    class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow flex items-center space-x-2 transition-all"
                    title="Cetak atau Simpan sebagai PDF">
                <i class="fa-solid fa-file-pdf text-rose-400 text-sm"></i>
                <span>Cetak / Unduh PDF</span>
            </button>
        </div>
    </div>

    <!-- Official Corporate Print Header -->
    <div class="hidden print-only text-center mb-8 pb-4 border-b-2 border-slate-900">
        <h2 class="text-2xl font-black uppercase tracking-tight">{{ app_name() }}</h2>
        <p class="text-xs text-slate-600">{{ app_setting('store_address') }} &bull; Telp: {{ app_setting('store_phone') }}</p>
        <div class="mt-4 pt-2 border-t border-slate-300 flex justify-between items-center text-xs">
            <span class="font-bold text-sm uppercase">LAPORAN VALUASI STOK DAN ASET RESMI</span>
            <span class="text-slate-500">Tanggal: {{ date('d F Y, H:i') }} WIB</span>
        </div>
    </div>

    <!-- Valuations Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Fisik Barang</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-700 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-slate-900 tracking-tight">{{ number_format($totalStockItems, 0, ',', '.') }} <span class="text-xs font-semibold text-slate-500">Unit</span></p>
            <p class="mt-2 text-xs text-slate-400">Total kuantiti fisik di toko</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Nilai Modal Aset (HPP)</span>
                <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-700 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-vault"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-teal-700 tracking-tight">{{ format_rupiah($totalAssetCost) }}</p>
            <p class="mt-2 text-xs text-slate-400">Modal tertanam dalam persediaan</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Nilai Jual</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-emerald-600 tracking-tight">{{ format_rupiah($totalAssetSelling) }}</p>
            <p class="mt-2 text-xs text-slate-400">Jika seluruh persediaan terjual</p>
        </div>

        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Potensi Keuntungan</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center text-xs font-bold">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </span>
            </div>
            <p class="text-2xl font-black text-indigo-700 tracking-tight">{{ format_rupiah($potentialProfit) }}</p>
            <p class="mt-2 text-xs text-indigo-600 font-bold">
                Margin Rata-rata: {{ $totalAssetSelling > 0 ? round(($potentialProfit / $totalAssetSelling) * 100, 1) : 0 }}%
            </p>
        </div>

    </div>

    <!-- Stock Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-card overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-extrabold text-slate-900 text-sm">Rincian Persediaan & Valuasi per Barang</h3>
                <p class="text-xs text-slate-500">Nilai modal HPP vs harga jual setiap item barang dagangan</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Nama Barang</th>
                        <th class="py-3.5 px-4 font-bold text-center">Satuan</th>
                        <th class="py-3.5 px-4 font-bold text-center">Stok</th>
                        <th class="py-3.5 px-4 font-bold text-center">Batas Min</th>
                        <th class="py-3.5 px-4 font-bold text-right">Harga Beli (HPP)</th>
                        <th class="py-3.5 px-4 font-bold text-right">Total Nilai HPP</th>
                        <th class="py-3.5 px-4 font-bold text-right">Harga Jual</th>
                        <th class="py-3.5 px-4 font-bold text-right">Total Nilai Jual</th>
                        <th class="py-3.5 px-4 font-bold text-center">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($products as $p)
                    <tr class="hover:bg-slate-50/70 {{ $p->stock <= $p->min_stock ? 'bg-amber-50/20' : '' }}">
                        <td class="py-3 px-4 font-bold text-slate-900">
                            {{ $p->name }}
                            <span class="block text-[10px] text-slate-400 font-normal">{{ $p->category->name ?? 'Umum' }}</span>
                        </td>
                        <td class="py-3 px-4 text-center text-slate-500 font-semibold">{{ $p->unit }}</td>
                        <td class="py-3 px-4 text-center font-black text-slate-900">{{ $p->stock }}</td>
                        <td class="py-3 px-4 text-center text-slate-400 font-semibold">{{ $p->min_stock }}</td>
                        <td class="py-3 px-4 text-right text-slate-600 font-mono">{{ format_rupiah($p->purchase_price) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-teal-800 font-mono">{{ format_rupiah($p->stock * $p->purchase_price) }}</td>
                        <td class="py-3 px-4 text-right text-slate-600 font-mono">{{ format_rupiah($p->selling_price) }}</td>
                        <td class="py-3 px-4 text-right font-black text-emerald-700 font-mono">{{ format_rupiah($p->stock * $p->selling_price) }}</td>
                        <td class="py-3 px-4 text-center">
                            @if($p->stock <= 0)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Habis</span>
                            @elseif($p->stock <= $p->min_stock)
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800">Menipis</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Cukup</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-8 text-center text-slate-400">Tidak ada barang persediaan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Official Signature Block (Print only) -->
    <div class="hidden print-only mt-12 pt-6 border-t border-slate-300">
        <div class="grid grid-cols-2 text-center text-xs">
            <div>
                <p class="text-slate-500">Penanggung Jawab Gudang & Stok:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500">Staf Persediaan Warung Aboen</p>
            </div>
            <div>
                <p class="text-slate-500">Menyetujui & Mengesahkan:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">Pemilik Warung Aboen</p>
                <p class="text-[10px] text-slate-500">Owner Warung Aboen</p>
            </div>
        </div>
    </div>

</div>
@endsection
