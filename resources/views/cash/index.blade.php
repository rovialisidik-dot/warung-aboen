@extends('layouts.app')

@section('title', 'Buku Kas & Arus Kas')
@section('page_title', 'Buku Kas & Mutasi Saldo Kas Warung')
@section('page_subtitle', 'Pantau arus kas masuk, kas keluar, setoran modal, dan penarikan prive pemilik')

@section('content')
<div class="space-y-6" x-data="{ capitalModalOpen: false, modalType: 'modal_masuk' }">

    <!-- KPI Summary Bar -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <!-- Saldo Kas Real-time -->
        <div class="bg-gradient-to-br from-emerald-600 to-teal-700 text-white p-6 rounded-2xl shadow-lg shadow-emerald-600/20">
            <span class="text-xs font-bold text-emerald-100 uppercase tracking-wider block mb-1">Saldo Kas Warung Real-Time</span>
            <p class="text-3xl font-extrabold">{{ format_rupiah($currentBalance) }}</p>
            <p class="text-xs text-emerald-100 mt-2 flex items-center space-x-1">
                <i class="fa-solid fa-vault"></i>
                <span>Saldo kas gabungan (Tunai laci & transfer)</span>
            </p>
        </div>

        <!-- Total Kas Masuk -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Kas Masuk (Filter)</span>
                <span class="w-7 h-7 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-down"></i>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-emerald-600">+{{ format_rupiah($totalIn) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Penjualan kasir & modal masuk</p>
        </div>

        <!-- Total Kas Keluar -->
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Kas Keluar (Filter)</span>
                <span class="w-7 h-7 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                    <i class="fa-solid fa-arrow-up"></i>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-rose-600">-{{ format_rupiah($totalOut) }}</p>
            <p class="text-[11px] text-slate-400 mt-1">Kulak persediaan, beban & prive</p>
        </div>
    </div>

    <!-- Quick Action: Modal / Prive -->
    @if(auth()->user()->isOwner())
    <div class="flex items-center space-x-2">
        <button type="button" @click="modalType = 'modal_masuk'; capitalModalOpen = true"
                class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm flex items-center space-x-1.5 transition-colors">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>+ Setoran Modal Tambahan</span>
        </button>
        <button type="button" @click="modalType = 'penarikan_pribadi'; capitalModalOpen = true"
                class="py-2.5 px-4 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-xs font-bold shadow-sm flex items-center space-x-1.5 transition-colors">
            <i class="fa-solid fa-arrow-up-from-bracket"></i>
            <span>&minus; Penarikan Pribadi (Prive Pemilik)</span>
        </button>
    </div>
    @endif

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('cash.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Arus Kas:</label>
                <select name="type" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500">
                    <option value="">Semua (Masuk & Keluar)</option>
                    <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Kas Masuk (+)</option>
                    <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Kas Keluar (&minus;)</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Dari Tanggal:</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                       class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Sampai Tanggal:</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                       class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500">
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('cash.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Mutasi Kas Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div>
                <h3 class="font-bold text-slate-900 text-sm">Buku Mutasi Kas (Running Balance Ledger)</h3>
                <p class="text-xs text-slate-500">Riwayat setiap rupiah uang masuk dan keluar</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Waktu</th>
                        <th class="py-3.5 px-4 font-bold">Sumber / Keterangan</th>
                        <th class="py-3.5 px-4 font-bold text-center">Jenis</th>
                        <th class="py-3.5 px-4 font-bold">User</th>
                        <th class="py-3.5 px-4 font-bold text-right">Kas Masuk (+)</th>
                        <th class="py-3.5 px-4 font-bold text-right">Kas Keluar (&minus;)</th>
                        <th class="py-3.5 px-4 font-bold text-right">Saldo Kas Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $t)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-slate-500 whitespace-nowrap">{{ $t->transaction_date->format('d/m/Y H:i') }}</td>
                        <td class="py-3.5 px-4 font-bold text-slate-800">
                            {{ $t->description }}
                            <span class="block text-[10px] text-slate-400 font-normal uppercase">Sumber: {{ $t->source }}</span>
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            @if($t->type === 'in')
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">
                                Kas Masuk
                            </span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">
                                Kas Keluar
                            </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $t->user->name ?? 'Sistem' }}</td>
                        <td class="py-3.5 px-4 text-right font-bold text-emerald-600 whitespace-nowrap">
                            {{ $t->type === 'in' ? '+' . format_rupiah($t->amount) : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-bold text-rose-600 whitespace-nowrap">
                            {{ $t->type === 'out' ? '-' . format_rupiah($t->amount) : '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-right font-extrabold text-slate-900 whitespace-nowrap">
                            {{ format_rupiah($t->balance_after) }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-wallet text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Belum ada mutasi arus kas pada filter ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $transactions->links() }}
        </div>
    </div>

    <!-- Modal Setoran Modal & Penarikan Prive -->
    <div x-show="capitalModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="capitalModalOpen = false">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 rounded-xl flex items-center justify-center text-lg"
                     :class="modalType === 'modal_masuk' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'">
                    <i class="fa-solid" :class="modalType === 'modal_masuk' ? 'fa-hand-holding-dollar' : 'fa-arrow-up-from-bracket'"></i>
                </div>
                <div>
                    <h4 class="font-bold text-base text-slate-900" x-text="modalType === 'modal_masuk' ? 'Setoran Modal Tambahan' : 'Penarikan Pribadi (Prive Pemilik)'"></h4>
                    <p class="text-xs text-slate-500">Pencatatan modal dan penarikan yang terpisah dari operasional</p>
                </div>
            </div>

            <form action="{{ route('cash.capital') }}" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="type" :value="modalType">

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                    <input type="number" name="amount" min="1000" required placeholder="500000"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs font-bold text-slate-900 focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal Transaksi <span class="text-rose-500">*</span></label>
                    <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required
                           class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-emerald-500">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Keterangan (Wajib):</label>
                    <input type="text" name="notes" required :placeholder="modalType === 'modal_masuk' ? 'Contoh: Tambahan modal dari simpanan pribadi' : 'Contoh: Keperluan pribadi pemilik'"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-emerald-500">
                </div>

                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 text-white rounded-xl text-xs font-bold shadow-md transition-colors"
                            :class="modalType === 'modal_masuk' ? 'bg-emerald-600 hover:bg-emerald-700 shadow-emerald-600/20' : 'bg-amber-600 hover:bg-amber-700 shadow-amber-600/20'">
                        <span x-text="modalType === 'modal_masuk' ? 'Simpan Setoran Modal' : 'Simpan Penarikan Prive'"></span>
                    </button>
                    <button type="button" @click="capitalModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
