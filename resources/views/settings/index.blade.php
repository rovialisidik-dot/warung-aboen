@extends('layouts.app')

@section('title', 'Pengaturan Toko & Cadangan')
@section('page_title', 'Pengaturan Toko Warung Aboen')
@section('page_subtitle', 'Sesuaikan nama aplikasi, alamat, informasi struk, dan cadangkan database')

@section('content')
<div class="max-w-5xl mx-auto space-y-6">

    <!-- Store Profile Settings Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Identitas Toko & Warung</h3>
                <p class="text-xs text-slate-500">Nama warung dan data ini ditampilkan di dashboard, header, dan struk belanja</p>
            </div>
        </div>

        <form action="{{ route('settings.update') }}" method="POST" class="space-y-4">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- App Name / Store Name -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="app_name">
                        Nama Warung / Aplikasi <span class="text-rose-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="text" name="app_name" id="app_name" value="{{ old('app_name', $settings['app_name']) }}" required
                               class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm font-extrabold text-slate-900 focus:ring-2 focus:ring-emerald-500">
                        <i class="fa-solid fa-signature absolute left-3 top-3 text-slate-400"></i>
                    </div>
                    <span class="text-[11px] text-emerald-700 font-semibold mt-1 block">
                        Dapat diganti kapan saja. Nama baru langsung diterapkan ke seluruh sistem.
                    </span>
                </div>

                <!-- Tagline -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="store_tagline">
                        Slogan / Tagline Warung
                    </label>
                    <input type="text" name="store_tagline" id="store_tagline" value="{{ old('store_tagline', $settings['store_tagline']) }}"
                           class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800">
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <!-- Phone -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="store_phone">
                        Nomor HP / WhatsApp
                    </label>
                    <div class="relative">
                        <input type="text" name="store_phone" id="store_phone" value="{{ old('store_phone', $settings['store_phone']) }}"
                               placeholder="Contoh: 0812-3456-7890"
                               class="w-full pl-9 pr-3 py-2.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500">
                        <i class="fa-solid fa-phone absolute left-3 top-3 text-slate-400"></i>
                    </div>
                </div>

                <!-- Receipt Footer Note -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="receipt_footer">
                        Pesan di Bagian Bawah Struk Belanja
                    </label>
                    <input type="text" name="receipt_footer" id="receipt_footer" value="{{ old('receipt_footer', $settings['receipt_footer']) }}"
                           class="w-full py-2.5 px-3.5 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800">
                </div>
            </div>

            <!-- Address -->
            <div>
                <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5" for="store_address">
                    Alamat Lengkap Warung
                </label>
                <textarea name="store_address" id="store_address" rows="2"
                          class="w-full py-2 px-3 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-500 text-slate-800">{{ old('store_address', $settings['store_address']) }}</textarea>
            </div>

            <!-- Submit -->
            <div class="pt-3 flex justify-end">
                <button type="submit" class="py-2.5 px-6 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white rounded-xl text-xs font-extrabold shadow-md shadow-emerald-600/20 transition-all">
                    Simpan Perubahan Pengaturan
                </button>
            </div>
        </form>
    </div>

    <!-- Multi-Device Connection & Online Access Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-lg">
                <i class="fa-solid fa-mobile-screen-button"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Koneksi Multi-Device, Akses HP & Mode Online</h3>
                <p class="text-xs text-slate-500">Gunakan aplikasi Warung Aboen di HP kasir, tablet, atau laptop lain secara bersamaan</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left: QR Code & IP -->
            <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200 text-center flex flex-col items-center justify-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode(app_mobile_url()) }}"
                     alt="QR Code HP"
                     class="w-36 h-36 rounded-2xl border-2 border-slate-300 bg-white p-2 shadow-sm mb-3">
                <span class="text-xs font-black text-slate-800">Scan QR via Kamera HP</span>
                <span class="text-[11px] text-slate-500 mt-1">Pastikan HP terhubung ke Wi-Fi yang sama</span>
            </div>

            <!-- Right: Instructions & Online Mode (2 cols) -->
            <div class="lg:col-span-2 space-y-4 text-xs">
                <!-- LAN Access Info -->
                <div class="p-4 rounded-2xl bg-emerald-50/70 border border-emerald-200 text-slate-700" x-data="{ copied: false }">
                    <span class="font-bold text-emerald-800 block text-sm mb-1">
                        <i class="fa-solid fa-wifi mr-1"></i> 1. Akses Lokal (Satu Wi-Fi / Hotspot HP)
                    </span>
                    <p class="text-slate-600 mb-2">Buka tautan ini di browser Chrome / Safari pada HP atau laptop lain:</p>
                    <div class="flex items-center space-x-2">
                        <code class="px-3 py-1.5 rounded-xl bg-white border border-emerald-300 font-mono font-bold text-emerald-800 text-sm">
                            {{ app_mobile_url() }}
                        </code>
                        <button type="button" @click="navigator.clipboard.writeText('{{ app_mobile_url() }}'); copied = true; setTimeout(() => copied = false, 2500)"
                                class="py-1.5 px-3 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl font-bold transition-colors">
                            <span x-text="copied ? 'Tersalin!' : 'Salin'"></span>
                        </button>
                    </div>
                </div>

                <!-- PWA / APK Tip -->
                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-200 text-slate-700">
                    <span class="font-bold text-indigo-900 block text-sm mb-1">
                        <i class="fa-solid fa-circle-down mr-1"></i> 2. Jadikan Aplikasi Native di HP (PWA / APK)
                    </span>
                    <p class="text-slate-600 leading-relaxed">
                        Saat membuka di Chrome HP, ketuk menu titik tiga (⋮) di pojok kanan atas, lalu pilih <strong>'Tambahkan ke Layar Utama' (Add to Home screen)</strong>. Aplikasi akan langsung muncul di beranda HP dengan ikon dan layar penuh tanpa bilah alamat browser.
                    </p>
                </div>

                <!-- Online via Internet / Data Seluler -->
                <div class="p-4 rounded-2xl bg-amber-50/70 border border-amber-200 text-slate-700">
                    <span class="font-bold text-amber-900 block text-sm mb-1">
                        <i class="fa-solid fa-globe mr-1"></i> 3. Akses Online dari Mana Saja (Paket Data / Internet Luar)
                    </span>
                    <p class="text-slate-600 leading-relaxed">
                        Untuk membuka kasir dari luar rumah tanpa satu Wi-Fi, jalankan file <strong>buka_online_warung_aboen.bat</strong> di folder aplikasi. Sistem akan memberikan tautan publik HTTPS aman (Cloudflare / Localtunnel) yang bisa dibuka dari mana pun di seluruh dunia.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Backup & Maintenance Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center text-lg">
                <i class="fa-solid fa-database"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Pencadangan Database Warung (Backup)</h3>
                <p class="text-xs text-slate-500">Unduh seluruh data penjualan, stok barang, pembukuan, dan akun pengguna dalam format file .SQL</p>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 p-5 rounded-2xl bg-slate-50 border border-slate-200">
            <div class="space-y-1">
                <h4 class="font-bold text-sm text-slate-800">Unduh File SQL Cadangan</h4>
                <p class="text-xs text-slate-500">
                    File ini dapat disimpan di flashdisk atau Google Drive Anda dan dipulihkan kapan pun melalui Laragon / phpMyAdmin.
                </p>
            </div>
            <a href="{{ route('settings.backup') }}" class="py-3 px-5 bg-slate-900 hover:bg-slate-800 text-white rounded-xl text-xs font-extrabold shadow-md flex items-center space-x-2 transition-colors shrink-0">
                <i class="fa-solid fa-download"></i>
                <span>Unduh Cadangan (.SQL)</span>
            </a>
        </div>
    </div>

    <!-- Audit Logs Card -->
    <div class="bg-white rounded-3xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <div class="flex items-center space-x-3 mb-6 pb-4 border-b border-slate-100">
            <div class="w-10 h-10 rounded-xl bg-purple-100 text-purple-700 flex items-center justify-center text-lg">
                <i class="fa-solid fa-clock-rotate-left"></i>
            </div>
            <div>
                <h3 class="font-extrabold text-base text-slate-900">Riwayat Perubahan & Audit Log</h3>
                <p class="text-xs text-slate-500">Catatan aktivitas penting: pembatalan nota, perubahan harga, penyesuaian stok, dll.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead class="bg-slate-50 text-slate-600 border-b border-slate-100 uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4 font-bold">Waktu</th>
                        <th class="py-3 px-4 font-bold">Aktivitas</th>
                        <th class="py-3 px-4 font-bold">Deskripsi</th>
                        <th class="py-3 px-4 font-bold">Pengguna</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($auditLogs as $log)
                    <tr class="hover:bg-slate-50/70">
                        <td class="py-3 px-4 text-slate-500 whitespace-nowrap">{{ $log->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4">
                            <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-100 text-slate-700 uppercase">
                                {{ str_replace('_', ' ', $log->action) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-medium text-slate-800">{{ $log->description }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $log->user->name ?? 'Sistem' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-slate-400">Belum ada catatan log aktivitas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="p-4 border-t border-slate-100">
            {{ $auditLogs->links() }}
        </div>
    </div>

</div>
@endsection
