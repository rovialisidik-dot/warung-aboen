@extends('layouts.app')

@section('title', 'Laporan Penjualan - ' . $periodTitle)
@section('page_title', 'Laporan Penjualan Enterprise')
@section('page_subtitle', 'Analisis komprehensif performa penjualan harian, bulanan, dan tahunan')

@section('content')
<div class="space-y-6">

    <!-- Enterprise Filter & Period Switcher Bar (Hidden on print) -->
    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm no-print space-y-4">
        
        <!-- Period Tabs: Harian | Bulanan | Tahunan | Kustom -->
        <div class="flex flex-wrap items-center justify-between gap-3 pb-3 border-b border-slate-100">
            <div class="flex items-center space-x-1.5 p-1 bg-slate-100 rounded-2xl text-xs font-bold">
                <!-- Tab Harian -->
                <a href="{{ route('reports.sales', ['period' => 'daily', 'date' => $selectedDate]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'daily' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-day text-xs"></i>
                    <span>Harian</span>
                </a>

                <!-- Tab Bulanan -->
                <a href="{{ route('reports.sales', ['period' => 'monthly', 'month' => $selectedMonth]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'monthly' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar-days text-xs"></i>
                    <span>Bulanan</span>
                </a>

                <!-- Tab Tahunan -->
                <a href="{{ route('reports.sales', ['period' => 'yearly', 'year' => $selectedYear]) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'yearly' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-calendar text-xs"></i>
                    <span>Tahunan</span>
                </a>

                <!-- Tab Kustom -->
                <a href="{{ route('reports.sales', ['period' => 'custom']) }}"
                   class="px-3.5 py-2 rounded-xl transition-all flex items-center space-x-1.5 {{ $period === 'custom' ? 'bg-white text-emerald-700 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}">
                    <i class="fa-solid fa-sliders text-xs"></i>
                    <span>Rentang Kustom</span>
                </a>
            </div>

            <!-- Export & Print Actions -->
            <div class="flex items-center space-x-2">
                <a href="{{ route('reports.sales.export', array_merge(request()->query(), ['period' => $period, 'date' => $selectedDate, 'month' => $selectedMonth, 'year' => $selectedYear])) }}"
                   class="py-2.5 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow flex items-center space-x-2 transition-all"
                   title="Unduh laporan dalam format Spreadsheet Microsoft Excel (.csv)">
                    <i class="fa-solid fa-file-excel text-emerald-200 text-sm"></i>
                    <span>Ekspor Excel (.csv)</span>
                </a>
                <button type="button" onclick="window.print()"
                        class="py-2.5 px-4 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-bold shadow-sm hover:shadow flex items-center space-x-2 transition-all"
                        title="Cetak atau Simpan sebagai dokumen PDF resmi">
                    <i class="fa-solid fa-file-pdf text-rose-400 text-sm"></i>
                    <span>Cetak / Unduh PDF</span>
                </button>
            </div>
        </div>

        <!-- Dynamic Filter Form Depending on Chosen Period -->
        <form method="GET" action="{{ route('reports.sales') }}" class="flex flex-wrap items-end gap-3 text-xs">
            <input type="hidden" name="period" value="{{ $period }}">

            @if($period === 'daily')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Tanggal:</label>
                <input type="date" name="date" value="{{ $selectedDate }}" onchange="this.form.submit()"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            <div class="flex items-center space-x-2 pb-1 text-slate-500 text-xs">
                <span>(Menampilkan performa penjualan per jam dan seluruh nota pada hari tersebut)</span>
            </div>

            @elseif($period === 'monthly')
            <div>
                <label class="block font-bold text-slate-700 mb-1">Pilih Bulan & Tahun:</label>
                <input type="month" name="month" value="{{ $selectedMonth }}" onchange="this.form.submit()"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            <div class="flex items-center space-x-2 pb-1 text-slate-500 text-xs">
                <span>(Menampilkan rekap harian dari tanggal 1 hingga akhir bulan)</span>
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
            <div class="flex items-center space-x-2 pb-1 text-slate-500 text-xs">
                <span>(Menampilkan perbandingan 12 bulan penuh dari Januari hingga Desember)</span>
            </div>

            @else
            <!-- Custom Date Range -->
            <div>
                <label class="block font-bold text-slate-700 mb-1">Dari Tanggal:</label>
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            <div>
                <label class="block font-bold text-slate-700 mb-1">Sampai Tanggal:</label>
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                       class="py-2 px-3 border border-slate-300 rounded-xl text-xs font-semibold focus:ring-2 focus:ring-emerald-500 bg-white">
            </div>
            <button type="submit" class="py-2 px-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-bold transition-colors">
                Terapkan Filter
            </button>
            @endif
        </form>

    </div>

    <!-- Official Corporate Print Header (Visible ONLY when printing) -->
    <div class="hidden print-only text-center mb-8 pb-4 border-b-2 border-slate-900">
        <h2 class="text-2xl font-black uppercase tracking-tight">{{ app_name() }}</h2>
        <p class="text-xs text-slate-600">{{ app_setting('store_address') }} &bull; Telp: {{ app_setting('store_phone') }}</p>
        <div class="mt-4 pt-2 border-t border-slate-300 flex justify-between items-center text-xs">
            <span class="font-bold text-sm uppercase">LAPORAN PENJUALAN RESMI</span>
            <span class="font-semibold">{{ $periodTitle }}</span>
            <span class="text-slate-500">Dicetak: {{ date('d/m/Y H:i') }}</span>
        </div>
    </div>

    <!-- KPI Summary Row -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        
        <!-- Total Omzet Penjualan -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Penjualan Kotor</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-slate-900 tracking-tight">{{ format_rupiah($totalSales) }}</p>
            <div class="mt-2 flex items-center text-xs text-slate-500 space-x-1.5">
                <span class="font-bold text-emerald-600">{{ $totalOrders }}</span>
                <span>transaksi nota selesai</span>
            </div>
        </div>

        @if(auth()->user()->isOwner())
        <!-- HPP Modal Barang Terjual -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total HPP Barang (Modal)</span>
                <span class="w-8 h-8 rounded-xl bg-slate-100 text-slate-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-slate-700 tracking-tight">{{ format_rupiah($totalCost) }}</p>
            <div class="mt-2 text-xs text-slate-400">
                Modal pokok persediaan terjual
            </div>
        </div>

        <!-- Laba Kotor (Gross Profit) -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Laba Kotor</span>
                <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-teal-700 tracking-tight">{{ format_rupiah($grossProfit) }}</p>
            <div class="mt-2 flex items-center space-x-1.5">
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-teal-100 text-teal-800">
                    Margin {{ round($profitMargin, 1) }}%
                </span>
                <span class="text-[11px] text-slate-400">Penjualan &minus; HPP</span>
            </div>
        </div>
        @endif

        <!-- Rata-rata Nilai Transaksi (Basket Size) -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm hover:shadow-md transition-shadow">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Rata-Rata per Nota</span>
                <span class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-sm font-bold">
                    <i class="fa-solid fa-basket-shopping"></i>
                </span>
            </div>
            <p class="text-2xl font-extrabold text-indigo-900 tracking-tight">{{ format_rupiah($averageOrder) }}</p>
            <div class="mt-2 text-xs text-slate-400">
                Nilai belanja rata-rata per pembeli
            </div>
        </div>

    </div>

    <!-- Chart & Payment Methods Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Visual Graph (2 cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-4">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900">
                        @if($period === 'daily')
                            Grafik Distribusi Jam Penjualan (06:00 - 22:00)
                        @elseif($period === 'yearly')
                            Grafik Perbandingan 12 Bulan (Tahun {{ $selectedYear }})
                        @else
                            Grafik Penjualan Harian
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500">Visualisasi omzet penjualan dan margin keuntungan</p>
                </div>
                <div class="flex items-center space-x-3 text-xs font-semibold">
                    <span class="flex items-center space-x-1.5 text-slate-700">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span>Penjualan</span>
                    </span>
                    @if(auth()->user()->isOwner())
                    <span class="flex items-center space-x-1.5 text-slate-700">
                        <span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span>
                        <span>Laba Kotor</span>
                    </span>
                    @endif
                </div>
            </div>
            <div class="h-64 sm:h-72">
                <canvas id="periodSalesChart"></canvas>
            </div>
        </div>

        <!-- Payment Breakdown Cards (1 col) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="font-extrabold text-base text-slate-900 mb-1">Metode Pembayaran</h3>
                <p class="text-xs text-slate-500 mb-4">Pembagian cara bayar pelanggan pada periode ini</p>

                <div class="space-y-3">
                    @forelse($paymentMethods as $pm)
                    <div class="p-3 rounded-2xl bg-slate-50 border border-slate-100 text-xs flex items-center justify-between">
                        <div class="flex items-center space-x-2.5">
                            <div class="w-7 h-7 rounded-lg bg-white border border-slate-200 flex items-center justify-center font-bold text-slate-700">
                                @if($pm->payment_method === 'cash')
                                    <i class="fa-solid fa-money-bill-wave text-emerald-600"></i>
                                @elseif($pm->payment_method === 'qris')
                                    <i class="fa-solid fa-qrcode text-indigo-600"></i>
                                @else
                                    <i class="fa-solid fa-building-columns text-blue-600"></i>
                                @endif
                            </div>
                            <div>
                                <span class="font-bold text-slate-800 uppercase block">{{ $pm->payment_method }}</span>
                                <span class="text-[10px] text-slate-400">{{ $pm->count }} transaksi</span>
                            </div>
                        </div>
                        <div class="text-right">
                            <span class="font-extrabold text-slate-900 block">{{ format_rupiah($pm->total) }}</span>
                            <span class="text-[10px] text-emerald-600 font-bold">
                                {{ $totalSales > 0 ? round(($pm->total / $totalSales) * 100, 1) : 0 }}%
                            </span>
                        </div>
                    </div>
                    @empty
                    <p class="text-center text-xs text-slate-400 py-6">Belum ada transaksi</p>
                    @endforelse
                </div>
            </div>

            <!-- Total period indicator -->
            <div class="pt-4 mt-4 border-t border-slate-100 flex items-center justify-between text-xs font-bold text-slate-700">
                <span>Total Periode Terpilih:</span>
                <span class="text-emerald-700 font-extrabold text-sm">{{ format_rupiah($totalSales) }}</span>
            </div>
        </div>

    </div>

    <!-- Data Tables & Top Products Row -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Detailed Table Breakdown (2 cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">
                        @if($period === 'daily')
                            Daftar Seluruh Nota Transaksi Hari Ini
                        @elseif($period === 'yearly')
                            Rincian Performa Penjualan 12 Bulan
                        @else
                            Rincian Penjualan Harian
                        @endif
                    </h3>
                    <p class="text-xs text-slate-500">Tabel data rincian finansial penjualan</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                @if($period === 'daily')
                <!-- Daily Invoices List -->
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 font-bold">No. Nota</th>
                            <th class="py-3 px-4 font-bold">Waktu</th>
                            <th class="py-3 px-4 font-bold">Pelanggan</th>
                            <th class="py-3 px-4 font-bold text-center">Metode</th>
                            <th class="py-3 px-4 font-bold text-right">Total Nota</th>
                            @if(auth()->user()->isOwner())
                            <th class="py-3 px-4 font-bold text-right">Laba Kotor</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($salesList as $s)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-bold text-emerald-700">
                                <a href="{{ route('sales.show', $s->id) }}" class="hover:underline">
                                    {{ $s->invoice_number }}
                                </a>
                            </td>
                            <td class="py-3 px-4 text-slate-500 font-mono">{{ $s->created_at->format('H:i:s') }}</td>
                            <td class="py-3 px-4 font-semibold text-slate-800">{{ $s->customer_name }}</td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2 py-0.5 rounded text-[10px] font-bold uppercase bg-slate-100 text-slate-700">
                                    {{ $s->payment_method }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right font-extrabold text-slate-900">{{ format_rupiah($s->total_amount) }}</td>
                            @if(auth()->user()->isOwner())
                            <td class="py-3 px-4 text-right font-bold text-teal-700">{{ format_rupiah($s->gross_profit) }}</td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada transaksi pada tanggal ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>

                @else
                <!-- Breakdown Table (Daily or Monthly or Yearly) -->
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 font-bold">{{ $period === 'yearly' ? 'Bulan' : 'Tanggal' }}</th>
                            <th class="py-3 px-4 font-bold text-center">Jumlah Nota</th>
                            <th class="py-3 px-4 font-bold text-right">Total Penjualan</th>
                            @if(auth()->user()->isOwner())
                            <th class="py-3 px-4 font-bold text-right">HPP</th>
                            <th class="py-3 px-4 font-bold text-right">Laba Kotor</th>
                            <th class="py-3 px-4 font-bold text-center">Margin</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($breakdownData as $b)
                        <tr class="hover:bg-slate-50/70">
                            <td class="py-3 px-4 font-bold text-slate-900">{{ $b['label'] }}</td>
                            <td class="py-3 px-4 text-center font-semibold text-slate-600">{{ $b['count'] }} Nota</td>
                            <td class="py-3 px-4 text-right font-extrabold text-slate-900">{{ format_rupiah($b['sales']) }}</td>
                            @if(auth()->user()->isOwner())
                            <td class="py-3 px-4 text-right text-slate-500">{{ format_rupiah($b['cost']) }}</td>
                            <td class="py-3 px-4 text-right font-bold text-teal-700">{{ format_rupiah($b['profit']) }}</td>
                            <td class="py-3 px-4 text-center font-bold text-xs {{ $b['margin'] > 0 ? 'text-teal-700' : 'text-slate-400' }}">
                                {{ $b['margin'] }}%
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="py-8 text-center text-slate-400">Tidak ada data untuk periode ini</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        <!-- Top 10 Selling Products (1 col) -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
            <div class="p-5 border-b border-slate-100">
                <h3 class="font-extrabold text-slate-900 text-sm">Produk Terlaris Periode Ini</h3>
                <p class="text-xs text-slate-500">Ranking barang berdasarkan kuantiti terjual</p>
            </div>

            <div class="p-4 space-y-2.5 max-h-[460px] overflow-y-auto">
                @forelse($topProducts as $idx => $prod)
                <div class="p-2.5 rounded-2xl bg-slate-50 border border-slate-100 text-xs flex items-center justify-between gap-2 hover:bg-slate-100/60 transition-colors">
                    <div class="flex items-center space-x-2.5 min-w-0">
                        <span class="w-6 h-6 rounded-lg font-black text-[11px] flex items-center justify-center shrink-0 {{ $idx < 3 ? 'bg-amber-100 text-amber-800' : 'bg-slate-200 text-slate-600' }}">
                            {{ $idx + 1 }}
                        </span>
                        <div class="truncate">
                            <strong class="text-slate-900 block truncate text-xs">{{ $prod->product_name }}</strong>
                            <span class="text-[10px] text-slate-400">{{ format_rupiah($prod->total_sales) }}</span>
                        </div>
                    </div>
                    <div class="text-right shrink-0">
                        <span class="font-extrabold text-emerald-700 text-sm">{{ $prod->total_qty }}</span>
                        <span class="text-[10px] text-slate-500 block">{{ $prod->unit }}</span>
                    </div>
                </div>
                @empty
                <p class="text-center text-xs text-slate-400 py-8">Belum ada barang terjual pada periode ini</p>
                @endforelse
            </div>
        </div>

    </div>

    <!-- Official Signature Area for Corporate Reports (Visible only on print) -->
    <div class="hidden print-only mt-12 pt-6 border-t border-slate-300">
        <div class="grid grid-cols-2 text-center text-xs">
            <div>
                <p class="text-slate-500">Dibuat Oleh:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-slate-500">Kasir / Admin Warung Aboen</p>
            </div>
            <div>
                <p class="text-slate-500">Mengetahui & Menyetujui:</p>
                <div class="h-20"></div>
                <p class="font-bold border-t border-slate-400 inline-block px-8 pt-1">Pemilik Warung Aboen</p>
                <p class="text-[10px] text-slate-500">Owner Warung Aboen</p>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('periodSalesChart');
    if (!ctx) return;

    const labels = @json($chartLabels);
    const salesData = @json($chartSales);
    const profitData = @json($chartProfits);
    const isOwner = {{ auth()->user()->isOwner() ? 'true' : 'false' }};

    const datasets = [
        {
            label: 'Penjualan (Omzet)',
            data: salesData,
            backgroundColor: 'rgba(16, 185, 129, 0.85)',
            borderColor: '#059669',
            borderWidth: 1.5,
            borderRadius: 6,
        }
    ];

    if (isOwner) {
        datasets.push({
            label: 'Laba Kotor',
            data: profitData,
            backgroundColor: 'rgba(20, 184, 166, 0.45)',
            borderColor: '#0d9488',
            borderWidth: 1.5,
            borderRadius: 6,
        });
    }

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: datasets
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return (context.dataset.label || '') + ': Rp ' + context.parsed.y.toLocaleString('id-ID');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            if (value >= 1000000) return (value / 1000000) + ' Jt';
                            if (value >= 1000) return (value / 1000) + ' Rb';
                            return value;
                        },
                        font: { size: 10 }
                    },
                    grid: { color: '#f1f5f9' }
                },
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 10 } }
                }
            }
        }
    });
});
</script>
@endpush
