# Warung Aboen - Sistem Kasir (POS) & Pembukuan Warung Digital

Aplikasi manajemen dan pembukuan warung sembako berbasis web yang dibangun dengan Laravel 10, MySQL, Blade, dan Tailwind CSS. Dirancang untuk menggantikan pencatatan manual menjadi sistem digital yang rapi, akurat, dan mudah digunakan oleh pemilik maupun anggota keluarga.

---

## 🚀 Cara Menjalankan Aplikasi

### Cara 1 (Paling Mudah):
Cukup **klik ganda (double-click)** file:
👉 **`start_warung_aboen.bat`**  
Script ini akan otomatis mengaktifkan database MySQL, menjalankan server, dan membuka browser ke `http://127.0.0.1:8000/login`.

### Cara 2 (Manual lewat Terminal / Laragon):
1. Pastikan **Laragon** (MySQL & PHP) aktif.
2. Buka terminal di folder project `WARUNGABUN`:
   ```bash
   php artisan serve
   ```
3. Buka browser dan akses:
   👉 **`http://127.0.0.1:8000`**

---

## 🔑 Akun Login Bawaan (Demo Cepat)

Di halaman login telah disediakan tombol instan untuk langsung mencoba:

| Peran (Role) | Username | Password | Hak Akses |
| :--- | :--- | :--- | :--- |
| **Pemilik (Owner)** | `admin` | `admin123` | Akses penuh (Laba Rugi, Buku Kas, Pengaturan, Kelola Akun, Ekspor) |
| **Kasir (Keluarga)** | `kasir` | `kasir123` | Transaksi Kasir POS, Katalog Stok, Penjualan, Pembelian Stok Masuk |

---

## ⚙️ Fitur Utama Aplikasi

1. **Dashboard Eksekutif**:
   - Omzet hari ini & bulan ini.
   - Total laba kotor & perkiraan laba bersih.
   - Posisi saldo kas warung real-time.
   - Grafik tren penjualan & margin keuntungan 7 hari.
   - Banner peringatan barang stok menipis.

2. **Kasir (POS - Point of Sale)**:
   - Filter barang per kategori & pencarian cepat nama/barcode.
   - Keranjang belanja interaktif dengan tombol kuantiti (+ / &minus;).
   - Tombol nominal uang cepat (Uang Pas, 20rb, 50rb, 100rb).
   - Perhitungan kembalian otomatis.
   - Metode bayar: Tunai, QRIS, Transfer Bank.
   - **Cetak Struk Belanja**: format struk thermal 58mm/80mm siap cetak.

3. **Manajemen Barang & Stok**:
   - Kode barcode/SKU, nama barang, kategori, satuan, harga beli (HPP), harga jual, dan batas stok minimum.
   - Kalkulator margin keuntungan otomatis saat input harga.
   - **Fitur Stock Opname (Penyesuaian Stok Langsung)** dengan alasan dan log riwayat.

4. **Pembelian Barang (Kulak Persediaan)**:
   - Pencatatan stok masuk dari distributor / agen grosir.
   - Perhitungan otomatis penambahan stok fisik dan mutasi kas keluar persediaan.

5. **Biaya & Pengeluaran Operasional**:
   - Pencatatan beban listrik, sewa tempat, kantong plastik kemasan, upah, dll.
   - Otomatis memotong saldo kas warung.

6. **Buku Kas & Modal (Cash Flow)**:
   - Saldo kas real-time (*Running Balance Ledger*).
   - Pencatatan setoran modal pemilik & penarikan pribadi (Prive) yang terpisah dari biaya operasional.

7. **Laporan Keuangan & Persediaan**:
   - **Laporan Penjualan**: rincian harian, omzet, HPP, laba kotor, dan produk terlaris.
   - **Laporan Laba Rugi**: Penjualan Bersih &minus; HPP = Laba Kotor; Laba Kotor &minus; Beban Operasional = Laba Bersih.
   - **Laporan Arus Kas**: ringkasan arus kas masuk vs keluar.
   - **Laporan Persediaan**: valuasi modal aset tertahan (HPP) vs potensi nilai jual.
   - Semua laporan dilengkapi tombol cetak / simpan PDF.

8. **Pengaturan Toko & Pencadangan**:
   - **Ubah Nama Warung**: Nama aplikasi default **"Warung Aboen"** dapat diganti kapan saja dari menu Pengaturan dan langsung berubah di seluruh sistem.
   - Alamat toko, nomor telepon/WhatsApp, dan pesan kaki struk belanja.
   - **Pencadangan Database 1-Klik**: Unduh cadangan database `.SQL` untuk disimpan di Google Drive atau flashdisk.
   - **Audit Log**: Catatan riwayat aktivitas penting (pembatalan nota, perubahan harga, penyesuaian stok).
