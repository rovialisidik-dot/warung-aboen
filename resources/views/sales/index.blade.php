@extends('layouts.app')

@section('title', 'Riwayat Penjualan')
@section('page_title', 'Riwayat Penjualan Warung')
@section('page_subtitle', 'Semua catatan faktur kasir dan transaksi penjualan')

@section('content')
<div class="space-y-6">

    <!-- Summary KPI Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Penjualan (Selesai)</span>
            <p class="text-2xl font-extrabold text-emerald-600">{{ format_rupiah($summary['total_amount']) }}</p>
        </div>
        @if(auth()->user()->isOwner())
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Total Laba Kotor</span>
            <p class="text-2xl font-extrabold text-teal-700">{{ format_rupiah($summary['total_profit']) }}</p>
        </div>
        @endif
        <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
            <span class="text-xs font-bold text-slate-500 uppercase tracking-wider block mb-1">Jumlah Transaksi Selesai</span>
            <p class="text-2xl font-extrabold text-slate-800">{{ $summary['total_count'] }} <span class="text-sm font-normal text-slate-500">Nota</span></p>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('sales.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Pencarian:</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="No. Nota / Pelanggan..."
                       class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Dari Tanggal:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
            </div>
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Status:</label>
                <select name="status" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 bg-white">
                    <option value="">Semua Status</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                    <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('sales.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Sales Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden" x-data="{ cancelModalOpen: false, activeSale: null, cancelReason: '' }">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">No. Nota</th>
                        <th class="py-3.5 px-4 font-bold">Waktu</th>
                        <th class="py-3.5 px-4 font-bold">Pelanggan</th>
                        <th class="py-3.5 px-4 font-bold">Kasir</th>
                        <th class="py-3.5 px-4 font-bold text-center">Metode</th>
                        <th class="py-3.5 px-4 font-bold text-right">Total Nota</th>
                        @if(auth()->user()->isOwner())
                        <th class="py-3.5 px-4 font-bold text-right">Laba Kotor</th>
                        @endif
                        <th class="py-3.5 px-4 font-bold text-center">Status</th>
                        <th class="py-3.5 px-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($sales as $sale)
                    <tr class="hover:bg-slate-50/70 transition-colors {{ $sale->status === 'cancelled' ? 'bg-rose-50/30 text-slate-400' : '' }}">
                        <td class="py-3.5 px-4 font-bold">
                            <a href="{{ route('sales.show', $sale->id) }}" class="text-emerald-700 hover:underline">
                                {{ $sale->invoice_number }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $sale->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3.5 px-4 font-medium text-slate-800">{{ $sale->customer_name }}</td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $sale->user->name ?? 'Kasir' }}</td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                {{ $sale->payment_method }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900 text-right whitespace-nowrap">
                            {{ format_rupiah($sale->total_amount) }}
                        </td>
                        @if(auth()->user()->isOwner())
                        <td class="py-3.5 px-4 font-semibold text-teal-700 text-right whitespace-nowrap">
                            {{ format_rupiah($sale->gross_profit) }}
                        </td>
                        @endif
                        <td class="py-3.5 px-4 text-center">
                            @if($sale->status === 'completed')
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Selesai
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800" title="{{ $sale->cancelled_reason }}">
                                Dibatalkan
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center space-x-1">
                                <a href="{{ route('sales.show', $sale->id) }}" class="p-1.5 text-slate-500 hover:text-emerald-600 rounded-lg hover:bg-slate-100" title="Lihat Detail">
                                    <i class="fa-solid fa-eye"></i>
                                </a>
                                <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="p-1.5 text-slate-500 hover:text-teal-600 rounded-lg hover:bg-slate-100" title="Cetak Struk">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                @if($sale->status === 'completed')
                                <button type="button" @click="activeSale = { id: {{ $sale->id }}, invoice: '{{ $sale->invoice_number }}', amount: '{{ format_rupiah($sale->total_amount) }}' }; cancelModalOpen = true"
                                        class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Batalkan Penjualan">
                                    <i class="fa-solid fa-ban"></i>
                                </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Tidak ada riwayat penjualan ditemukan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $sales->links() }}
        </div>

        <!-- Cancel Transaction Modal -->
        <div x-show="cancelModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
            <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="cancelModalOpen = false">
                <div class="flex items-center space-x-3 text-rose-600 mb-4">
                    <div class="w-10 h-10 rounded-full bg-rose-100 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-base text-slate-900">Batalkan Transaksi Penjualan</h4>
                        <p class="text-xs text-slate-500" x-text="'Nota: ' + activeSale?.invoice + ' (' + activeSale?.amount + ')'"></p>
                    </div>
                </div>

                <p class="text-xs text-slate-600 mb-4 bg-amber-50 p-3 rounded-xl border border-amber-200">
                    <strong class="text-amber-900">Perhatian:</strong> Pembatalan akan otomatis mengembalikan stok semua barang dalam nota ini ke persediaan, dan mencatat pengembalian kas (refund) di buku kas.
                </p>

                <form :action="'{{ url('/sales') }}/' + activeSale?.id + '/cancel'" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-xs font-bold text-slate-700 mb-1">Alasan Pembatalan (Wajib):</label>
                        <textarea name="reason" rows="3" required placeholder="Contoh: Barang tertukar / Pelanggan salah beli / Kesalahan kasir"
                                  class="w-full p-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 focus:border-rose-500"></textarea>
                    </div>

                    <div class="flex gap-2.5">
                        <button type="submit" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition-colors">
                            Ya, Batalkan Transaksi
                        </button>
                        <button type="button" @click="cancelModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>

</div>
@endsection
