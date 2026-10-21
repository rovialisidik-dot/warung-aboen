@extends('layouts.app')

@section('title', 'Detail Nota ' . $sale->invoice_number)
@section('page_title', 'Detail Faktur Penjualan')
@section('page_subtitle', 'Informasi lengkap transaksi penjualan nota ' . $sale->invoice_number)

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Cancellation Banner -->
    @if($sale->status === 'cancelled')
    <div class="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-900 flex items-start space-x-3">
        <i class="fa-solid fa-ban text-rose-600 text-xl mt-0.5"></i>
        <div>
            <h4 class="font-bold text-sm">Transaksi Ini Telah Dibatalkan</h4>
            <p class="text-xs text-rose-700 mt-0.5">Alasan: <strong>{{ $sale->cancelled_reason }}</strong></p>
            <p class="text-[11px] text-rose-600 mt-1">
                Dibatalkan oleh {{ $sale->cancelledBy->name ?? 'Pengguna' }} pada {{ $sale->cancelled_at ? $sale->cancelled_at->format('d/m/Y H:i') : '-' }}. Stok barang dan kas telah dikembalikan.
            </p>
        </div>
    </div>
    @endif

    <!-- Invoice Header Card -->
    <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div>
            <div class="flex items-center space-x-3 mb-1">
                <h2 class="text-xl font-extrabold text-slate-900">{{ $sale->invoice_number }}</h2>
                @if($sale->status === 'completed')
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                @else
                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-800">Dibatalkan</span>
                @endif
            </div>
            <p class="text-xs text-slate-500">Waktu Transaksi: {{ $sale->created_at->format('d F Y, H:i:s') }} WIB</p>
        </div>

        <div class="flex items-center space-x-2">
            <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-md shadow-emerald-600/20 flex items-center space-x-1.5 transition-colors">
                <i class="fa-solid fa-print"></i>
                <span>Cetak Struk Thermal</span>
            </a>
            <a href="{{ route('sales.index') }}" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                Kembali
            </a>
        </div>
    </div>

    <!-- Metadata Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Pelanggan</span>
            <p class="font-bold text-slate-800 text-sm mt-1">{{ $sale->customer_name ?? 'Pelanggan Umum' }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Kasir</span>
            <p class="font-bold text-slate-800 text-sm mt-1">{{ $sale->user->name ?? 'Kasir' }}</p>
        </div>
        <div class="bg-white p-4 rounded-xl border border-slate-200">
            <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider block">Metode Pembayaran</span>
            <p class="font-bold text-slate-800 text-sm mt-1 uppercase">{{ $sale->payment_method }}</p>
        </div>
    </div>

    <!-- Line Items Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="font-bold text-slate-900 text-sm">Daftar Barang yang Dibeli</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 font-bold">No</th>
                        <th class="py-3 px-4 font-bold">Nama Barang</th>
                        <th class="py-3 px-4 font-bold text-center">Satuan</th>
                        <th class="py-3 px-4 font-bold text-right">Harga Jual</th>
                        @if(auth()->user()->isOwner())
                        <th class="py-3 px-4 font-bold text-right">HPP</th>
                        @endif
                        <th class="py-3 px-4 font-bold text-center">Qty</th>
                        <th class="py-3 px-4 font-bold text-right">Subtotal</th>
                        @if(auth()->user()->isOwner())
                        <th class="py-3 px-4 font-bold text-right">Laba</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($sale->items as $idx => $item)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3 px-4 text-slate-400">{{ $idx + 1 }}</td>
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $item->product_name }}</td>
                        <td class="py-3 px-4 text-center text-slate-500">{{ $item->unit }}</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ format_rupiah($item->unit_price) }}</td>
                        @if(auth()->user()->isOwner())
                        <td class="py-3 px-4 text-right text-slate-500">{{ format_rupiah($item->unit_cost) }}</td>
                        @endif
                        <td class="py-3 px-4 text-center font-bold text-slate-900">{{ $item->quantity }}</td>
                        <td class="py-3 px-4 text-right font-bold text-slate-900">{{ format_rupiah($item->subtotal) }}</td>
                        @if(auth()->user()->isOwner())
                        <td class="py-3 px-4 text-right font-semibold text-teal-700">{{ format_rupiah($item->profit) }}</td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Totals Breakdown -->
        <div class="p-6 border-t border-slate-100 bg-slate-50/50 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="text-xs text-slate-500">
                @if($sale->notes)
                <p><strong>Catatan:</strong> {{ $sale->notes }}</p>
                @endif
            </div>

            <div class="w-full sm:w-72 space-y-2 text-xs">
                <div class="flex justify-between text-slate-600">
                    <span>Subtotal Barang:</span>
                    <span class="font-semibold text-slate-800">{{ format_rupiah($sale->subtotal) }}</span>
                </div>
                @if($sale->discount > 0)
                <div class="flex justify-between text-rose-600">
                    <span>Diskon:</span>
                    <span class="font-semibold">-{{ format_rupiah($sale->discount) }}</span>
                </div>
                @endif
                <div class="flex justify-between text-sm font-extrabold text-slate-900 pt-2 border-t border-slate-200">
                    <span>Total Pembayaran:</span>
                    <span class="text-emerald-700 text-base">{{ format_rupiah($sale->total_amount) }}</span>
                </div>
                <div class="flex justify-between text-slate-600 pt-1">
                    <span>Uang Diterima:</span>
                    <span class="font-semibold text-slate-800">{{ format_rupiah($sale->payment_amount) }}</span>
                </div>
                <div class="flex justify-between text-slate-800 font-bold">
                    <span>Kembalian:</span>
                    <span class="text-teal-700">{{ format_rupiah($sale->change_amount) }}</span>
                </div>
                @if(auth()->user()->isOwner())
                <div class="flex justify-between text-xs text-teal-800 font-bold pt-2 border-t border-slate-200">
                    <span>Laba Kotor Nota Ini:</span>
                    <span>{{ format_rupiah($sale->gross_profit) }}</span>
                </div>
                @endif
            </div>
        </div>
    </div>

</div>
@endsection
