@extends('layouts.app')

@section('title', 'Biaya Operasional Warung')
@section('page_title', 'Pencatatan Biaya & Pengeluaran Operasional')
@section('page_subtitle', 'Catat beban listrik, air, sewa, plastik kemasan, dan pengeluaran harian lainnya')

@section('content')
<div class="space-y-6" x-data="{ addModalOpen: false }">

    <!-- KPI and Action Header -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="bg-white px-5 py-3 rounded-2xl border border-slate-200 shadow-sm text-xs">
            <span class="text-slate-500">Total Beban Operasional Periode Ini:</span>
            <strong class="text-rose-600 ml-1 font-extrabold text-base">{{ format_rupiah($totalExpenses) }}</strong>
        </div>

        <button type="button" @click="addModalOpen = true" class="py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 flex items-center space-x-1.5 transition-colors">
            <i class="fa-solid fa-plus"></i>
            <span>Catat Pengeluaran Baru</span>
        </button>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white p-5 rounded-2xl border border-slate-200 shadow-sm">
        <form method="GET" action="{{ route('expenses.index') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Pilih Bulan:</label>
                <input type="month" name="month" value="{{ request('month', date('Y-m')) }}"
                       class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 bg-white">
            </div>

            <div>
                <label class="block text-xs font-bold text-slate-600 mb-1">Kategori:</label>
                <select name="category" class="w-full py-2 px-3 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 bg-white">
                    <option value="">Semua Kategori Biaya</option>
                    @foreach($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <div class="flex gap-2">
                <button type="submit" class="flex-1 py-2 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold transition-colors">
                    <i class="fa-solid fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('expenses.index') }}" class="py-2 px-3 bg-slate-100 hover:bg-slate-200 text-slate-600 rounded-xl text-xs font-semibold transition-colors">
                    Reset
                </a>
            </div>
        </form>
    </div>

    <!-- Expenses Table -->
    <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3.5 px-4 font-bold">Tanggal</th>
                        <th class="py-3.5 px-4 font-bold">Kategori</th>
                        <th class="py-3.5 px-4 font-bold">Nama Pengeluaran / Keterangan</th>
                        <th class="py-3.5 px-4 font-bold">Dicatat Oleh</th>
                        <th class="py-3.5 px-4 font-bold text-right">Nominal</th>
                        <th class="py-3.5 px-4 font-bold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($expenses as $exp)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-slate-600 whitespace-nowrap">{{ $exp->expense_date->format('d/m/Y') }}</td>
                        <td class="py-3.5 px-4">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">
                                {{ $exp->category }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-slate-900">
                            {{ $exp->title }}
                            @if($exp->notes)
                            <span class="block text-[11px] text-slate-400 font-normal">{{ $exp->notes }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-slate-600">{{ $exp->user->name ?? 'Kasir' }}</td>
                        <td class="py-3.5 px-4 font-extrabold text-rose-600 text-right whitespace-nowrap">
                            -{{ format_rupiah($exp->amount) }}
                        </td>
                        <td class="py-3.5 px-4 text-center whitespace-nowrap">
                            <form action="{{ route('expenses.destroy', $exp->id) }}" method="POST" onsubmit="return confirm('Hapus pengeluaran ini? Saldo kas akan dikembalikan.')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-slate-400 hover:text-rose-600 rounded-lg hover:bg-rose-50" title="Hapus Biaya">
                                    <i class="fa-solid fa-trash-can"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-slate-400">
                            <i class="fa-solid fa-receipt text-3xl mb-2 text-slate-300"></i>
                            <p class="font-semibold">Belum ada catatan pengeluaran pada periode ini</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $expenses->links() }}
        </div>
    </div>

    <!-- Add Expense Modal -->
    <div x-show="addModalOpen" class="fixed inset-0 z-50 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4" x-cloak>
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 text-slate-800 animate-scale-up" @click.away="addModalOpen = false">
            <h4 class="font-bold text-base text-slate-900 mb-4">Catat Biaya Operasional Baru</h4>
            <form action="{{ route('expenses.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Kategori Biaya <span class="text-rose-500">*</span></label>
                    <select name="category" required class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs bg-white focus:ring-2 focus:ring-rose-500 font-semibold">
                        @foreach($categories as $c)
                        <option value="{{ $c }}">{{ $c }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Nama Pengeluaran <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="Contoh: Beli Token Listrik 100rb / Kantong Plastik"
                           class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 font-semibold">
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Nominal (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="amount" min="100" required placeholder="100000"
                               class="w-full py-2.5 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 font-bold">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tanggal <span class="text-rose-500">*</span></label>
                        <input type="date" name="expense_date" value="{{ date('Y-m-d') }}" required
                               class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500 bg-white">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Catatan Tambahan (Opsional):</label>
                    <textarea name="notes" rows="2" placeholder="Keterangan tambahan..."
                              class="w-full py-2 px-3 border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-500"></textarea>
                </div>

                <p class="text-[11px] text-slate-500 bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                    <i class="fa-solid fa-info-circle text-emerald-600 mr-1"></i> Menyimpan pengeluaran akan otomatis memotong saldo kas warung secara real-time.
                </p>

                <div class="flex gap-2.5 pt-2">
                    <button type="submit" class="flex-1 py-2.5 px-4 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-xs font-bold shadow-md shadow-rose-600/20 transition-colors">
                        Simpan Biaya
                    </button>
                    <button type="button" @click="addModalOpen = false" class="py-2.5 px-4 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition-colors">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
