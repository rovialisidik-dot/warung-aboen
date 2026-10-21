@extends('layouts.app')

@section('title', 'Laporan Arus Kas - ' . $periodTitle)
@section('page_title', 'Laporan Arus Kas Enterprise')
@section('page_subtitle', 'Analisis likuiditas dan pergerakan kas masuk serta keluar secara akurat')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Enterprise Filter & Period Switcher Bar (Hidden on print) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm no-print space-y-4">
        
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <!-- Period Tabs -->
            <div class="flex items-center space-x-1.5 p-1 bg-slate-100 rounded-2xl text-xs font-bold">
                <a href="{{ route('reports.cash_flow', ['period' => 'daily', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'daily' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-day text-xs"></i>
                    <span>Harian</span>
                </a>

                <a href="{{ route('reports.cash_flow', ['period' => 'monthly', 'month' => $selectedMonth]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'monthly' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                    <span>Bulanan</span>
                </a>

                <a href="{{ route('reports.cash_flow', ['period' => 'yearly', 'year' => $selectedYear]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'yearly' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar text-xs"></i>
                    <span>Tahunan</span>
                </a>
            </div>

            <!-- Export & Print Actions -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.cash_flow.export', array_merge(request()->query(), ['period' => $period, 'date' => $selectedDate, 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                   class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow flex items-center space-x-2 transition-all"
                   title="Unduh laporan arus kas format Spreadsheet Excel (.csv)">
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
        <form method="GET" action="{{ route('reports.cash_flow') }}" class="flex flex-wrap items-end gap-3 text-xs">
            <input type="hidden" name="period" value="{{ $period }}">

            @if($period === 'daily')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Tanggal:</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            @elseif($period === 'yearly')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Tahun:</label>
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
            <span class="font-bold text-sm uppercase">LAPORAN ARUS KAS USAHA RESMI</span>
            <span class="font-semibold">{{ $periodTitle }}</span>
            <span class="text-slate-500">Dicetak: {{ date('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- Executive Statement Card -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-10 space-y-6">
        
        <div class="border-b border-slate-100 pb-4 flex flex-col sm:flex-row justify-between sm:items-end gap-2">
            <div>
                <span class="text-[11px] font-black uppercase tracking-widest text-emerald-600">Enterprise Cash Flow Statement</span>
                <h3 class="text-xl font-black text-slate-900 mt-0.5">Laporan Arus Kas (Cash Flow)</h3>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-800 border border-slate-200">
                {{ $periodTitle }}
            </span>
        </div>

        <!-- 1. Arus Kas Masuk -->
        <div class="space-y-3">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-emerald-700 flex items-center space-x-1.5">
                <span class="w-5 h-5 rounded-md bg-emerald-100 text-emerald-700 flex items-center justify-center text-[10px]">
                    <i class="fa-solid fa-arrow-down"></i>
                </span>
                <span>Arus Kas Masuk (+)</span>
            </h4>

            <div class="p-4 rounded-2xl bg-emerald-50/40 border border-emerald-100 space-y-2 text-sm">
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Kas Masuk dari Penjualan Kasir (POS)</span>
                    <span class="font-bold text-emerald-700">+{{ format_rupiah($cashFromSales) }}</span>
                </div>
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Setoran Modal Tambahan Pemilik</span>
                    <span class="font-bold text-emerald-700">+{{ format_rupiah($cashFromCapital) }}</span>
                </div>
                @if($cashOtherIn > 0)
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Penerimaan Kas Lainnya</span>
                    <span class="font-bold text-emerald-700">+{{ format_rupiah($cashOtherIn) }}</span>
                </div>
                @endif
                <div class="flex justify-between py-2 border-t border-emerald-200 font-extrabold text-emerald-900 text-base">
                    <span>Total Seluruh Kas Masuk</span>
                    <span>+{{ format_rupiah($totalCashIn) }}</span>
                </div>
            </div>
        </div>

        <!-- 2. Arus Kas Keluar -->
        <div class="space-y-3">
            <h4 class="text-xs font-extrabold uppercase tracking-wider text-rose-700 flex items-center space-x-1.5">
                <span class="w-5 h-5 rounded-md bg-rose-100 text-rose-700 flex items-center justify-center text-[10px]">
                    <i class="fa-solid fa-arrow-up"></i>
                </span>
                <span>Arus Kas Keluar (&minus;)</span>
            </h4>

            <div class="p-4 rounded-2xl bg-rose-50/40 border border-rose-100 space-y-2 text-sm">
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Pembelian Stok Persediaan (Kulak Dagangan)</span>
                    <span class="font-bold text-rose-600">-{{ format_rupiah($cashForPurchases) }}</span>
                </div>
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Beban & Biaya Operasional Toko</span>
                    <span class="font-bold text-rose-600">-{{ format_rupiah($cashForExpenses) }}</span>
                </div>
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Penarikan Pribadi Pemilik (Prive)</span>
                    <span class="font-bold text-rose-600">-{{ format_rupiah($cashForPrive) }}</span>
                </div>
                @if($cashOtherOut > 0)
                <div class="flex justify-between py-1 text-slate-700">
                    <span>Pengeluaran Kas Lainnya</span>
                    <span class="font-bold text-rose-600">-{{ format_rupiah($cashOtherOut) }}</span>
                </div>
                @endif
                <div class="flex justify-between py-2 border-t border-rose-200 font-extrabold text-rose-900 text-base">
                    <span>Total Seluruh Kas Keluar</span>
                    <span>-{{ format_rupiah($totalCashOut) }}</span>
                </div>
            </div>
        </div>

        <!-- 3. Hasil Bersih Arus Kas & Saldo Real-Time -->
        <div class="space-y-3 pt-2">
            <div class="p-4 rounded-2xl bg-slate-100 border border-slate-200 flex justify-between items-center text-sm font-bold text-slate-800">
                <span>Perubahan Bersih Kas Periode Ini:</span>
                <span class="text-lg font-black {{ $netCashChange >= 0 ? 'text-emerald-700' : 'text-rose-600' }}">
                    {{ $netCashChange >= 0 ? '+' : '' }}{{ format_rupiah($netCashChange) }}
                </span>
            </div>

            <div class="p-6 rounded-3xl bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white shadow-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                <div>
                    <span class="text-xs font-black uppercase tracking-widest text-emerald-100 block">Posisi Likuiditas Saat Ini</span>
                    <h3 class="text-xl font-black mt-0.5">Saldo Kas Warung Real-Time</h3>
                </div>
                <span class="text-3xl font-black tracking-tight">{{ format_rupiah($currentBalance) }}</span>
            </div>
        </div>

    </div>

    <!-- Yearly 12-Month Cash Table (Visible when period === 'yearly') -->
    @if($period === 'yearly')
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm p-6 sm:p-8 space-y-4">
        <div>
            <h3 class="font-extrabold text-slate-900 text-base">Perbandingan Arus Kas 12 Bulan (Tahun {{ $selectedYear }})</h3>
            <p class="text-xs text-slate-500">Rekap kas masuk dan kas keluar setiap bulan</p>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-200 uppercase tracking-wider text-[10px]">
                    <tr>
                        <th class="py-3 px-4 font-bold">Bulan</th>
                        <th class="py-3 px-4 font-bold text-right">Kas Masuk (+)</th>
                        <th class="py-3 px-4 font-bold text-right">Kas Keluar (&minus;)</th>
                        <th class="py-3 px-4 font-bold text-right">Arus Kas Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($yearlyCashMatrix as $ycm)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3 px-4 font-bold text-slate-800">{{ $ycm['month_name'] }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-emerald-700">+{{ format_rupiah($ycm['cash_in']) }}</td>
                        <td class="py-3 px-4 text-right font-semibold text-rose-600">-{{ format_rupiah($ycm['cash_out']) }}</td>
                        <td class="py-3 px-4 text-right font-extrabold {{ $ycm['net'] >= 0 ? 'text-emerald-800' : 'text-rose-700' }}">
                            {{ $ycm['net'] >= 0 ? '+' : '' }}{{ format_rupiah($ycm['net']) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50 font-black border-t-2 border-slate-300 text-slate-900">
                    <tr>
                        <td class="py-3 px-4">TOTAL TAHUN {{ $selectedYear }}</td>
                        <td class="py-3 px-4 text-right text-emerald-800">+{{ format_rupiah($totalCashIn) }}</td>
                        <td class="py-3 px-4 text-right text-rose-700">-{{ format_rupiah($totalCashOut) }}</td>
                        <td class="py-3 px-4 text-right text-base {{ $netCashChange >= 0 ? 'text-emerald-800' : 'text-rose-700' }}">
                            {{ $netCashChange >= 0 ? '+' : '' }}{{ format_rupiah($netCashChange) }}
                        </td>
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
                <p class="text-[10px] text-slate-500">Admin Kas Warung</p>
            </div>
            <div>
                <p class="text-slate-500">Disahkan Oleh:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">Pemilik Warung Aboen</p>
                <p class="text-[10px] text-slate-500">Owner Warung Aboen</p>
            </div>
        </div>
    </div>

</div>
@endsection
