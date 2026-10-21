@extends('layouts.app')

@section('title', 'Faktur Pembelian ' . $purchase->purchase_number)
@section('page_title', 'Detail Faktur Pembelian Stok')
@section('page_subtitle', 'Bukti transaksi pembelian persediaan ' . $purchase->purchase_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Invoice Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <h2 class="text-xl font-extrabold text-slate-900">{{ $purchase->purchase_number }}</h2>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Diterima</span>
            </div>
            <p class="text-xs text-slate-500">Waktu Pembelian: {{ $purchase->created_at->format('d F Y, H:i') }} WIB</p>
        </div>

        <a href="{{ route('purchases.index') }}" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
            Kembali ke Daftar
        </a>
    </div>

    <!-- Metadata Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Supplier / Toko Grosir</span>
            <p class="font-bold text-slate-800 text-sm mt-1">{{ $purchase->supplier_name }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Dicatat Oleh</span>
            <p class="font-bold text-slate-800 text-sm mt-1">{{ $purchase->user->name ?? 'Kasir' }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Metode Pembayaran</span>
            <p class="font-bold text-slate-800 text-sm mt-1 uppercase">{{ $purchase->payment_method }}</p>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm">Barang Persediaan yang Diterima</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 font-bold">No</th>
                        <th class="py-3 px-4 font-bold">Nama Barang</th>
                        <th class="py-3 px-4 font-bold text-center">Satuan</th>
                        <th class="py-3 px-4 font-bold text-center">Jumlah Diterima</th>
                        <th class="py-3 px-4 font-bold text-right">Harga Beli Satuan</th>
                        <th class="py-3 px-4 font-bold text-right">Subtotal</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($purchase->items as $idx => $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3 px-4 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $item->product_name }}</td>
                        <td class="py-3 px-4 text-center text-slate-500">{{ $item->unit }}</td>
                        <td class="py-3 px-4 text-center font-bold text-slate-900">+{{ $item->quantity }}</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ format_rupiah($item->unit_cost) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">{{ format_rupiah($item->subtotal) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex justify-between items-center">
            <p class="text-xs text-slate-500">{{ $purchase->notes ? 'Catatan: ' . $purchase->notes : '' }}</p>
            <div class="text-right">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block">Total Tagihan Pembelian:</span>
                <span class="text-xl font-extrabold text-slate-900">{{ format_rupiah($purchase->total_amount) }}</span>
            </div>
        </div>
    </div>

</div>
@endsection
