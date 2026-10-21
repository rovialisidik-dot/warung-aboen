@extends('layouts.app')

@section('title', 'Executive Dashboard')
@section('page_title', 'Ringkasan Eksekutif Warung')
@section('page_subtitle', 'Sistem pemantauan penjualan, laba bersih, persediaan, dan arus kas tingkat enterprise')

@section('content')
<div class="space-y-6">

    <!-- Executive Welcome & Quick Metric Bar -->
    <div class="bg-gradient-to-r from-slate-900 via-enterprise-900 to-emerald-950 rounded-3xl p-6 text-white shadow-xl border border-slate-800 flex flex-col md:flex-row justify-between items-start md:items-center gap-4 relative overflow-hidden">
        <!-- Subtle Background Glow -->
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

        <div class="z-10">
            <div class="flex items-center space-x-2 text-emerald-400 text-xs font-bold uppercase tracking-wider mb-1">
                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                <span>Warung Aboen &bull; Enterprise POS Online</span>
            </div>
            <h2 class="text-xl sm:text-2xl font-black tracking-tight">
                Selamat Datang, {{ auth()->user()->name }}
            </h2>
            <p class="text-xs text-slate-300 mt-1 max-w-xl">
                Sistem pembukuan dan kasir berjalan normal. Anda login sebagai <strong class="text-white">{{ auth()->user()->isOwner() ? 'Pemilik (Owner)' : 'Kasir / Keluarga' }}</strong>.
            </p>
        </div>

        <div class="z-10 flex items-center space-x-2.5">
            <a href="{{ route('pos.index') }}" 
               class="py-3 px-5 rounded-2xl bg-gradient-to-r from-emerald-500 to-teal-500 hover:from-emerald-400 hover:to-teal-400 text-white font-extrabold text-xs shadow-lg shadow-emerald-500/25 flex items-center space-x-2 transition-all transform hover:-translate-y-0.5 active:translate-y-0">
                <i class="fa-solid fa-cash-register text-sm"></i>
                <span>Buka Kasir Penjualan</span>
            </a>
            <a href="{{ route('reports.sales', ['period' => 'daily']) }}" 
               class="py-3 px-4 rounded-2xl bg-white/10 hover:bg-white/20 text-white font-bold text-xs backdrop-blur-md border border-white/10 transition-colors">
                <span>Laporan Harian</span>
            </a>
        </div>
    </div>

    <!-- Low Stock Alert Banner (If Any) -->
    @if($lowStockCount > 0)
    <div class="p-4 rounded-2xl bg-gradient-to-r from-amber-500/15 via-amber-500/5 to-transparent border border-amber-300/80 text-amber-950 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
        <div class="flex items-center space-x-3.5">
            <div class="w-10 h-10 rounded-xl bg-amber-500 text-white flex items-center justify-center shrink-0 shadow-md shadow-amber-500/20">
                <i class="fa-solid fa-triangle-exclamation text-lg"></i>
            </div>
            <div>
                <h4 class="font-extrabold text-sm">Peringatan: Ada {{ $lowStockCount }} barang yang stoknya menipis!</h4>
                <p class="text-xs text-amber-800/90">Persediaan berada pada atau di bawah batas minimum. Segera kulak stok agar penjualan tidak terhambat.</p>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <a href="{{ route('purchases.create') }}" class="px-3.5 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl shadow-sm transition-colors flex items-center space-x-1.5">
                <i class="fa-solid fa-truck-ramp-box"></i>
                <span>Kulak Stok Sekarang</span>
            </a>
            <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="px-3.5 py-2 bg-white hover:bg-amber-50 text-amber-900 border border-amber-300 text-xs font-bold rounded-xl transition-colors">
                Lihat Barang Tipis
            </a>
        </div>
    </div>
    @endif

    <!-- Multi-Device Connection & HP Access Bar -->
    <div class="bg-white rounded-3xl p-5 border border-slate-200/80 shadow-card flex flex-col lg:flex-row items-center justify-between gap-5 relative overflow-hidden">
        <div class="flex items-start sm:items-center space-x-4">
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 text-xl font-bold shadow-sm border border-emerald-100">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <div>
                <div class="flex items-center space-x-2">
                    <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 uppercase tracking-wider">Akses Multi-Device</span>
                    <span class="text-xs text-slate-400">&bull; HP, Tablet & Laptop Lain</span>
                </div>
                <h3 class="font-black text-sm sm:text-base text-slate-900 mt-0.5">Bisa Digunakan di HP dan Laptop Lain</h3>
                <p class="text-xs text-slate-500 mt-0.5">
                    Hubungkan HP ke Wi-Fi yang sama, lalu buka link di bawah ini di browser HP:
                </p>
                <div class="mt-2.5 flex flex-wrap items-center gap-2" x-data="{ copied: false }">
                    <code class="px-3 py-1.5 rounded-xl bg-slate-100 border border-slate-200 text-xs font-mono font-bold text-emerald-700 select-all">
                        {{ app_mobile_url() }}
                    </code>
                    <button type="button" @click="navigator.clipboard.writeText('{{ app_mobile_url() }}'); copied = true; setTimeout(() => copied = false, 2500)"
                            class="px-3 py-1.5 rounded-xl bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all flex items-center space-x-1.5 shadow-sm">
                        <i :class="copied ? 'fa-solid fa-check text-emerald-400' : 'fa-regular fa-copy'"></i>
                        <span x-text="copied ? 'Tersalin!' : 'Salin Link HP'"></span>
                    </button>
                    <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data={{ urlencode(app_mobile_url()) }}" target="_blank"
                       class="px-3 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-700 border border-emerald-200 text-xs font-bold transition-colors flex items-center space-x-1.5">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>Lihat QR Code HP</span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick PWA Tip Box with QR code -->
        <div class="w-full lg:w-auto p-3.5 rounded-2xl bg-slate-50 border border-slate-200/80 text-xs text-slate-600 flex items-center space-x-3 shrink-0">
            <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data={{ urlencode(app_mobile_url()) }}"
                 alt="Scan QR HP"
                 class="w-16 h-16 rounded-xl border border-slate-300 bg-white p-1 shrink-0 shadow-xs">
            <div class="space-y-0.5">
                <span class="font-extrabold text-slate-800 block text-[11px]">Scan QR di HP Anda</span>
                <p class="text-[10px] text-slate-500 leading-tight">Arahkan kamera HP ke kode QR ini untuk buka.</p>
                <span class="inline-block text-[10px] text-emerald-700 font-bold">Menu ⋮ &rarr; Tambahkan ke Layar Utama (PWA)</span>
            </div>
        </div>
    </div>

    <!-- 6 Executive KPI Metric Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        
        <!-- 1. Penjualan Hari Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-all group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Penjualan Hari Ini</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 group-hover:scale-110 flex items-center justify-center text-xs font-bold transition-transform">
                    <i class="fa-solid fa-receipt"></i>
                </span>
            </div>
            <p class="text-xl font-black text-slate-900 tracking-tight">{{ format_rupiah($salesToday) }}</p>
            <div class="mt-2 flex items-center text-xs text-slate-500 space-x-1">
                <span class="font-bold text-emerald-600">{{ $salesCountToday }}</span>
                <span>transaksi selesai</span>
            </div>
        </div>

        <!-- 2. Omzet Bulan Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-all group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Omzet Bulan Ini</span>
                <span class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 group-hover:scale-110 flex items-center justify-center text-xs font-bold transition-transform">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
            </div>
            <p class="text-xl font-black text-slate-900 tracking-tight">{{ format_rupiah($salesMonth) }}</p>
            <p class="mt-2 text-xs text-slate-400">Bulan {{ Carbon\Carbon::now()->translatedFormat('F Y') }}</p>
        </div>

        <!-- 3. Laba Kotor Bulan Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-all group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Laba Kotor</span>
                <span class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 group-hover:scale-110 flex items-center justify-center text-xs font-bold transition-transform">
                    <i class="fa-solid fa-coins"></i>
                </span>
            </div>
            <p class="text-xl font-black text-teal-700 tracking-tight">{{ format_rupiah($grossProfitMonth) }}</p>
            <p class="mt-2 text-xs text-teal-600 font-semibold">Penjualan &minus; HPP</p>
        </div>

        <!-- 4. Beban Operasional Bulan Ini -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-all group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Beban Operasional</span>
                <span class="w-8 h-8 rounded-xl bg-rose-50 text-rose-600 group-hover:scale-110 flex items-center justify-center text-xs font-bold transition-transform">
                    <i class="fa-solid fa-money-bill-transfer"></i>
                </span>
            </div>
            <p class="text-xl font-black text-rose-600 tracking-tight">{{ format_rupiah($expensesMonth) }}</p>
            <p class="mt-2 text-xs text-slate-400">Listrik, sewa, plastik, dll</p>
        </div>

        <!-- 5. Laba Bersih Operasional -->
        <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-card hover:shadow-card-hover transition-all group">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Laba Bersih Toko</span>
                <span class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-700 group-hover:scale-110 flex items-center justify-center text-xs font-bold transition-transform">
                    <i class="fa-solid fa-scale-balanced"></i>
                </span>
            </div>
            <p class="text-xl font-black {{ $netProfitMonth >= 0 ? 'text-emerald-700' : 'text-rose-600' }} tracking-tight">
                {{ format_rupiah($netProfitMonth) }}
            </p>
            <p class="mt-2 text-[11px] text-slate-400">Laba Kotor &minus; Biaya</p>
        </div>

        <!-- 6. Saldo Kas Real-Time -->
        <div class="bg-gradient-to-br from-emerald-600 via-emerald-500 to-teal-700 text-white p-5 rounded-3xl shadow-lg shadow-emerald-600/20 hover:shadow-xl transition-all">
            <div class="flex items-center justify-between mb-2">
                <span class="text-[11px] font-bold text-emerald-100 uppercase tracking-wider">Kas Warung Real-Time</span>
                <span class="w-8 h-8 rounded-xl bg-white/20 text-white flex items-center justify-center text-xs">
                    <i class="fa-solid fa-wallet"></i>
                </span>
            </div>
            <p class="text-xl font-black tracking-tight">{{ format_rupiah($cashBalance) }}</p>
            <p class="mt-2 text-[11px] text-emerald-100 font-semibold flex items-center space-x-1">
                <i class="fa-solid fa-vault text-[10px]"></i>
                <span>Kas fisik & rekening</span>
            </p>
        </div>

    </div>

    <!-- Chart & Corporate Summary Station -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Main Chart Card (2 cols) -->
        <div class="lg:col-span-2 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-card">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-6">
                <div>
                    <h3 class="font-extrabold text-base text-slate-900">Performa Tren 7 Hari Terakhir</h3>
                    <p class="text-xs text-slate-500">Omzet penjualan vs perolehan laba kotor harian</p>
                </div>
                <div class="flex items-center space-x-3 text-xs font-semibold">
                    <span class="flex items-center space-x-1.5 text-slate-700">
                        <span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span>
                        <span>Omzet Penjualan</span>
                    </span>
                    <span class="flex items-center space-x-1.5 text-slate-700">
                        <span class="w-3 h-3 rounded-full bg-teal-400 inline-block"></span>
                        <span>Laba Kotor</span>
                    </span>
                    <a href="{{ route('reports.sales') }}" class="text-emerald-700 font-bold hover:underline text-xs ml-2">
                        Laporan Detail &rarr;
                    </a>
                </div>
            </div>

            <div class="h-64 sm:h-72">
                <canvas id="dashboardSalesChart"></canvas>
            </div>
        </div>

        <!-- Corporate Station & Quick Controls (1 col) -->
        <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-card flex flex-col justify-between">
            <div>
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div>
                        <span class="text-[10px] font-black uppercase tracking-widest text-emerald-600 block">Sistem Terhubung</span>
                        <h3 class="font-extrabold text-base text-slate-900">{{ app_name() }}</h3>
                    </div>
                    <span class="px-2.5 py-1 rounded-full text-[10px] font-black bg-emerald-100 text-emerald-800 border border-emerald-200">
                        Online
                    </span>
                </div>

                <div class="py-4 space-y-3 text-xs">
                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500">Katalog Barang Aktif</span>
                        <strong class="font-black text-slate-800">{{ $totalProducts }} Produk</strong>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500">Peringatan Stok Tipis</span>
                        <strong class="font-black {{ $lowStockCount > 0 ? 'text-amber-600' : 'text-emerald-600' }}">{{ $lowStockCount }} Produk</strong>
                    </div>

                    <div class="flex items-center justify-between p-2.5 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-500">Alamat Toko</span>
                        <span class="font-semibold text-slate-700 truncate max-w-[140px]" title="{{ app_setting('store_address') }}">{{ app_setting('store_address', 'Jl. Merdeka') }}</span>
                    </div>
                </div>
            </div>

            <!-- Fast Action Buttons -->
            <div class="pt-4 border-t border-slate-100 space-y-2">
                <a href="{{ route('pos.index') }}" 
                   class="w-full py-3 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-extrabold text-xs shadow-md shadow-emerald-600/20 flex items-center justify-center space-x-2 transition-all">
                    <i class="fa-solid fa-cash-register"></i>
                    <span>Buka Kasir Penjualan (POS)</span>
                </a>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('purchases.create') }}" class="py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold text-center transition-colors">
                        + Kulak Stok
                    </a>
                    <a href="{{ route('expenses.index') }}" class="py-2 px-3 rounded-xl border border-slate-200 hover:bg-slate-50 text-slate-700 text-xs font-bold text-center transition-colors">
                        + Catat Biaya
                    </a>
                </div>
            </div>
        </div>

    </div>

    <!-- Recent Sales & Low Stock Products Tables -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Recent Invoices Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Transaksi Penjualan Terkini</h3>
                    <p class="text-xs text-slate-500">Catatan faktur nota kasir paling baru</p>
                </div>
                <a href="{{ route('sales.index') }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center space-x-1">
                    <span>Semua Nota</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 font-bold">No. Nota</th>
                            <th class="py-3 px-4 font-bold">Pelanggan</th>
                            <th class="py-3 px-4 font-bold text-right">Total Tagihan</th>
                            <th class="py-3 px-4 font-bold text-center">Status</th>
                            <th class="py-3 px-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($recentSales as $sale)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                <a href="{{ route('sales.show', $sale->id) }}" class="text-emerald-700 hover:underline">
                                    {{ $sale->invoice_number }}
                                </a>
                                <span class="block text-[10px] text-slate-400 font-mono">{{ $sale->created_at->format('d/m H:i') }}</span>
                            </td>
                            <td class="py-3 px-4 font-semibold text-slate-700">{{ $sale->customer_name }}</td>
                            <td class="py-3 px-4 font-extrabold text-slate-900 text-right whitespace-nowrap">
                                {{ format_rupiah($sale->total_amount) }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($sale->status === 'completed')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800">Selesai</span>
                                @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800">Dibatalkan</span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('pos.receipt', $sale->id) }}" target="_blank" class="p-1.5 text-slate-400 hover:text-emerald-700 rounded-lg hover:bg-slate-100" title="Cetak Ulang Struk">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Belum ada transaksi penjualan</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Items List Table -->
        <div class="bg-white rounded-3xl border border-slate-200/80 shadow-card overflow-hidden">
            <div class="p-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <h3 class="font-extrabold text-slate-900 text-sm">Persediaan Stok Kritis</h3>
                    <p class="text-xs text-slate-500">Barang dagangan dengan stok &le; batas minimum</p>
                </div>
                <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="text-xs font-bold text-emerald-700 hover:text-emerald-800 flex items-center space-x-1">
                    <span>Lihat Semua</span>
                    <i class="fa-solid fa-arrow-right text-[10px]"></i>
                </a>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3 px-4 font-bold">Nama Barang</th>
                            <th class="py-3 px-4 font-bold text-center">Sisa Stok</th>
                            <th class="py-3 px-4 font-bold text-center">Batas Min</th>
                            <th class="py-3 px-4 font-bold text-right">Harga Jual</th>
                            <th class="py-3 px-4 font-bold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($lowStockProducts->take(6) as $item)
                        <tr class="hover:bg-slate-50/70 transition-colors">
                            <td class="py-3 px-4 font-bold text-slate-900">
                                {{ $item->name }}
                                <span class="block text-[10px] text-slate-400 font-normal">{{ $item->unit }}</span>
                            </td>
                            <td class="py-3 px-4 text-center">
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-rose-100 text-rose-700">
                                    {{ $item->stock }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-center text-slate-500 font-semibold">{{ $item->min_stock }}</td>
                            <td class="py-3 px-4 font-extrabold text-slate-900 text-right whitespace-nowrap">{{ format_rupiah($item->selling_price) }}</td>
                            <td class="py-3 px-4 text-center whitespace-nowrap">
                                <a href="{{ route('purchases.create') }}" class="px-2.5 py-1 bg-amber-50 hover:bg-amber-100 text-amber-800 border border-amber-300 rounded-xl text-[11px] font-bold transition-colors">
                                    Kulak Stok
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-emerald-600 font-bold">
                                <i class="fa-solid fa-check-circle mr-1"></i> Seluruh persediaan dalam kondisi aman
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const ctx = document.getElementById('dashboardSalesChart');
    if (!ctx) return;

    const labels = @json($chartLabels);
    const salesData = @json($chartSales);
    const profitData = @json($chartProfits);

    new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Omzet Penjualan',
                    data: salesData,
                    backgroundColor: 'rgba(16, 185, 129, 0.85)',
                    borderColor: '#059669',
                    borderWidth: 1.5,
                    borderRadius: 8,
                },
                {
                    label: 'Laba Kotor',
                    data: profitData,
                    backgroundColor: 'rgba(20, 184, 166, 0.45)',
                    borderColor: '#0d9488',
                    borderWidth: 1.5,
                    borderRadius: 8,
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
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
