@extends('layouts.app')

@section('title', 'Laporan Laba Rugi - ' . $periodTitle)
@section('page_title', 'Laporan Laba Rugi Enterprise')
@section('page_subtitle', 'Laporan resmi kinerja profitabilitas: Penjualan, HPP, Laba Kotor, Biaya, dan Laba Bersih')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Enterprise Filter & Period Switcher Bar (Hidden on print) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm no-print space-y-4">
        
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <!-- Period Tabs -->
            <div class="flex items-center space-x-1.5 p-1 bg-slate-100 rounded-2xl text-xs font-bold">
                <a href="{{ route('reports.profit_loss', ['period' => 'daily', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'daily' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-day text-xs"></i>
                    <span>Harian</span>
                </a>

                <a href="{{ route('reports.profit_loss', ['period' => 'monthly', 'month' => $selectedMonth]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'monthly' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                    <span>Bulanan</span>
                </a>

                <a href="{{ route('reports.profit_loss', ['period' => 'yearly', 'year' => $selectedYear]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'yearly' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar text-xs"></i>
                    <span>Tahunan</span>
                </a>
            </div>

            <!-- Export & Print Actions -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.profit_loss.export', array_merge(request()->query(), ['period' => $period, 'date' => $selectedDate, 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                   class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow flex items-center space-x-2 transition-all"
                   title="Unduh laporan laba rugi format Spreadsheet Excel (.csv)">
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

        <!-- Filter Form -->
        <form method="GET" action="{{ route('reports.profit_loss') }}" class="flex flex-wrap items-end gap-3 text-xs">
            <input type="hidden" name="period" value="{{ $period }}">

            @if($period === 'daily')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Tanggal:</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            @elseif($period === 'yearly')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Tahun Pembukuan:</label>
                <select name="year" onchange="this.form.submit()" class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-bold focus:ring-2 focus:ring-emerald-500 bg-white">
                    @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ $selectedYear == $yr ? 'selected' : '' }}>Tahun {{ $yr }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Bulan & Tahun:</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            @endif
        </form>

    </div>

    <!-- Official Corporate Print Header -->
    <div class="hidden print-only text-center mb-8 pb-4 border-b-2 border-slate-900">
        <h2 class="text-2xl font-black uppercase tracking-tight">{{ app_name() }}</h2>
        <p class="text-xs text-slate-600">{{ app_setting('store_address') }} &bull; Telp: {{ app_setting('store_phone') }}</p>
        <div class="mt-4 pt-2 border-t border-slate-300 flex justify-between items-center text-xs">
            <span class="font-bold text-sm uppercase">LAPORAN LABA RUGI OPERASIONAL RESMI</span>
            <span class="font-semibold">{{ $periodTitle }}</span>
            <span class="text-slate-500">Dicetak: {{ date('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Executive Financial Statement Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-10 space-y-6">
        
        <!-- Header in Card -->
        <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row justify-between sm:items-end gap-2">
            <div>
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-600">Enterprise Financial Report</span>
                <h3 class="text-xl font-black text-slate-900 mt-0.5">Laporan Laba Rugi (Income Statement)</h3>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                {{ $periodTitle }}
            </span>
        </div>

        <!-- 1. Pendapatan Penjualan -->
        <div class="space-y-3">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">1. Pendapatan Penjualan Usaha</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Penjualan Bruto (Gross Sales)</span>
                    <span class="font-semibold">{{ format_rupiah($grossSales) }}</span>
                </div>
                @if($discounts > 0)
                <div class="flex justify-between py-1 text-rose-600">
                    <span>Potongan / Diskon Penjualan</span>
                    <span class="font-semibold">-{{ format_rupiah($discounts) }}</span>
                </div>
                @endif
                <div class="flex justify-between py-2 border-t border-slate-200 font-bold text-slate-900">
                    <span>Penjualan Bersih (Net Revenue)</span>
                    <span class="text-emerald-700 font-extrabold">{{ format_rupiah($netSales) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Beban Pokok Penjualan (HPP) -->
        <div class="space-y-3">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">2. Beban Pokok Penjualan (HPP)</h4>
            <div class="space-y-2 text-sm">
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Harga Pokok Penjualan (COGS) Barang Terjual</span>
                    <span class="font-semibold text-rose-600">-{{ format_rupiah($cogs) }}</span>
                </div>
                <div class="flex justify-between py-3 px-4 bg-teal-50/80 rounded-2xl border border-teal-100 font-black text-teal-900 text-base">
                    <span>LABA KOTOR USAHA (GROSS PROFIT)</span>
                    <span>{{ format_rupiah($grossProfit) }}</span>
                </div>
                <div class="text-right text-xs font-bold text-teal-700">
                    Gross Profit Margin: {{ round($grossMarginPercent, 1) }}%
                </div>
            </div>
        </div>

        <!-- 3. Beban Operasional -->
        <div class="space-y-3">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-400">3. Beban & Biaya Operasional</h4>
            <div class="space-y-2 text-sm">
                @forelse($expensesByCategory as $exp)
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Biaya {{ $exp->category }}</span>
                    <span class="font-semibold">{{ format_rupiah($exp->total_amount) }}</span>
                </div>
                @empty
                <div class="py-2 text-xs text-slate-400 italic">Tidak ada catatan biaya operasional di periode ini</div>
                @endforelse

                <div class="flex justify-between py-2 border-t border-slate-200 font-bold text-rose-600">
                    <span>Total Beban Operasional</span>
                    <span class="font-extrabold">-{{ format_rupiah($totalOperatingExpenses) }}</span>
                </div>
            </div>
        </div>

        <!-- 4. Laba Bersih Akhir -->
        <div class="p-6 rounded-3xl border-2 {{ $netProfit >= 0 ? 'bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white border-emerald-500 shadow-glow-emerald' : 'bg-rose-50 text-rose-900 border-rose-300' }} shadow-xl">
            <div class="flex flex-col sm:flex-row justify-between sm:items-center gap-3">
                <div>
                    <span class="text-xs font-extrabold uppercase tracking-widest {{ $netProfit >= 0 ? 'text-emerald-200' : 'text-rose-700' }} block">
                        Kinerja Keuntungan Bersih
                    </span>
                    <h3 class="text-2xl font-black mt-1">
                        {{ $netProfit >= 0 ? 'LABA BERSIH (NET OPERATING PROFIT)' : 'RUGI OPERASIONAL' }}
                    </h3>
                    <p class="text-xs {{ $netProfit >= 0 ? 'text-emerald-100' : 'text-rose-600' }} mt-0.5">
                        Laba Kotor &minus; Beban Operasional
                    </p>
                </div>
                <div class="text-left sm:text-right">
                    <p class="text-3xl sm:text-4xl font-black tracking-tight">{{ format_rupiah($netProfit) }}</p>
                    <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-xs font-black {{ $netProfit >= 0 ? 'bg-white/20 text-white' : 'bg-rose-200 text-rose-900' }}">
                        Net Margin: {{ round($netMarginPercent, 1) }}%
                    </span>
                </div>
            </div>
        </div>

    </div>

    <!-- Yearly 12-Month Matrix Table (Visible when period === 'yearly') -->
    @if($period === 'yearly')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4">
        <div>
            <h3 class="font-extrabold text-slate-900 text-base">Matriks Laba Rugi Komparatif 12 Bulan (Tahun {{ $selectedYear }})</h3>
            <p class="text-xs text-slate-500">Performa keuangan lengkap bulan demi bulan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-bold">Bulan</th>
                        <th class="py-3 px-4 font-bold text-right">Penjualan</th>
                        <th class="py-3 px-4 font-bold text-right">HPP</th>
                        <th class="py-3 px-4 font-bold text-right">Laba Kotor</th>
                        <th class="py-3 px-4 font-bold text-right">Beban Operasional</th>
                        <th class="py-3 px-4 font-bold text-right">Laba Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($yearlyMatrix as $ym)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $ym['month_name'] }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-slate-900">{{ format_rupiah($ym['sales']) }}</td>
                        <td class="py-3 px-4 text-right text-slate-500">{{ format_rupiah($ym['cogs']) }}</td>
                        <td class="py-3 px-4 text-right font-bold text-teal-700">{{ format_rupiah($ym['gross']) }}</td>
                        <td class="py-3 px-4 text-right text-rose-600">{{ format_rupiah($ym['expenses']) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold {{ $ym['net'] >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                            {{ format_rupiah($ym['net']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-black border-t-2 border-slate-300 text-slate-900">
                    <tr>
                        <td class="py-3 px-4">TOTAL TAHUN {{ $selectedYear }}</td>
                        <td class="py-3 px-4 text-right text-emerald-800">{{ format_rupiah($netSales) }}</td>
                        <td class="py-3 px-4 text-right text-slate-700">{{ format_rupiah($cogs) }}</td>
                        <td class="py-3 px-4 text-right text-teal-800">{{ format_rupiah($grossProfit) }}</td>
                        <td class="py-3 px-4 text-right text-rose-700">{{ format_rupiah($totalOperatingExpenses) }}</td>
                        <td class="py-3 px-4 text-right text-emerald-800 text-sm">{{ format_rupiah($netProfit) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    <!-- Official Signature Area (Print only) -->
    <div class="hidden print-only mt-12 pt-6 border-t border-slate-300">
        <div class="grid grid-cols-2 text-center text-xs">
            <div>
                <p class="text-slate-500">Diverifikasi Oleh:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500">Penyusun Laporan Keuangan</p>
            </div>
            <div>
                <p class="text-slate-500">Disahkan Oleh:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">Pemilik Warung Aboen</p>
                <p class="text-[10px] text-slate-500">Owner & Penanggung Jawab</p>
            </div>
        </div>
    </div>

</div>
@endsection
