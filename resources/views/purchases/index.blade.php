@extends('layouts.app')

@section('title', 'Pembelian Barang (Kulak)')
@section('page_title', 'Riwayat Pembelian & Kulak Stok')
@section('page_subtitle', 'Catatan pembelian persediaan dari supplier/distributor dan penambahan stok masuk')

@section('content')
<div class="space-y-6">

    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="bg-white px-5 py-3 rounded-2xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-500">Total Pembelian Periode Ini:</span>
            <strong class="text-slate-900 ml-1 font-extrabold text-base">{{ format_rupiah($totalPurchases) }}</strong>
        </div>

        <a href="{{ route('purchases.create') }}" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition-colors">
            <i class="fa-solid fa-plus"></i>
            <span>Catat Pembelian / Kulak Baru</span>
        </a>
    </div>

    <!-- Purchases Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">No. Pembelian</th>
                        <th class="py-3.5 px-4 font-bold">Tanggal</th>
                        <th class="py-3.5 px-4 font-bold">Supplier</th>
                        <th class="py-3.5 px-4 font-bold">Pencatat</th>
                        <th class="py-3.5 px-4 font-bold text-center">Metode Bayar</th>
                        <th class="py-3.5 px-4 font-bold text-right">Total Tagihan</th>
                        <th class="py-3.5 px-4 font-bold text-center">Status</th>
                        <th class="py-3.5 px-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($purchases as $p)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 font-bold">
                            <a href="{{ route('purchases.show', $p->id) }}" class="text-emerald-700 hover:underline">
                                {{ $p->purchase_number }}
                            </a>
                            <span class="block text-[10px] text-slate-400 font-normal">{{ $p->items->count() }} jenis barang</span>
                        </td>
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3.5 px-4 font-semibold text-slate-800">{{ $p->supplier_name }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $p->user->name ?? 'Kasir' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                {{ $p->payment_method }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900 text-right whitespace-nowrap">
                            {{ format_rupiah($p->total_amount) }}
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Diterima
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <a href="{{ route('purchases.show', $p->id) }}" class="p-1.5 text-slate-500 hover:text-emerald-600 rounded-lg hover:bg-slate-100" title="Lihat Faktur Pembelian">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-truck-ramp-box text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Belum ada riwayat pembelian barang</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $purchases->links() }}
        </div>
    </div>

</div>
@endsection
