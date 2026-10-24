# Panduan Deploy Warung Aboen 24 Jam Non-Stop (100% Gratis di Render.com)

Aplikasi Warung Aboen telah disiapkan agar dapat berjalan **24 jam non-stop** di cloud server gratis tanpa perlu menyalakan laptop Anda sama sekali.

---

## Mengapa Server Cloud (Render.com) Lebih Unggul Dibanding Laptop?
1. **Laptop Bebas Dimatikan**: Anda tidak perlu membiarkan laptop menyala terus-menerus. HP Anda bisa mengakses kasir dan laporan 24 jam non-stop dari mana saja.
2. **Alamat Tautan Permanen**: Anda akan memiliki link resmi sendiri (misal: `https://warung-aboen.onrender.com`) yang tidak akan pernah berubah atau kedaluwarsa.
3. **Bebas Gangguan DNS / Error 1033**: Server berjalan di data center cloud profesional dengan koneksi internet berkecepatan tinggi.
4. **100% Gratis**: Menggunakan paket Free Plan Render tanpa biaya bulanan dan tanpa memerlukan kartu kredit.

---

## Langkah 1: Buat Repository di GitHub
1. Buka browser dan kunjungi [https://github.com](https://github.com).
2. Masuk (Login) ke akun GitHub Anda, atau daftar gratis jika belum memiliki akun.
3. Di pojok kanan atas, klik tanda **+** lalu pilih **"New repository"**.
4. Beri nama repository, misalnya: `warung-aboen`.
5. Pilih opsi **Private** (agar kode warung Anda aman) atau **Public**.
6. Klik tombol hijau **"Create repository"**.
7. Salin URL repository Anda, contoh: `https://github.com/nama-anda/warung-aboen.git`.

---

## Langkah 2: Unggah (Push) Kode dari Laptop ke GitHub
Buka Terminal / Command Prompt di folder Warung Aboen, lalu jalankan dua perintah berikut:
```bash
git remote add origin https://github.com/nama-anda/warung-aboen.git
git push -u origin main
```
*(Atau cukup beri tahu saya URL repository GitHub Anda, saya dapat bantu menyambungkannya).*

---

## Langkah 3: Deploy di Render.com (1 Klik Selesai)
1. Kunjungi [https://render.com](https://render.com) lalu klik **"Sign In"** atau **"Get Started for Free"**.
2. Pilih login menggunakan akun **GitHub** Anda.
3. Setelah masuk ke Dashboard Render, klik tombol **"New +"** di pojok kanan atas, lalu pilih **"Blueprint"** (atau **"Web Service"**).
4. Pilih repository `warung-aboen` yang baru saja Anda buat.
5. Render akan secara otomatis membaca berkas `render.yaml` yang sudah kami rancang:
   - Sistem operasi: Docker Alpine
   - Region: Singapore (sangat cepat untuk Indonesia)
   - Database: Sudah terintegrasi dengan 23 produk awal sembako, kasir, dan pengaturan toko.
6. Klik tombol **"Apply"** / **"Create Web Service"**.
7. Tunggu sekitar 2-3 menit sampai proses deploy selesai berstatus **"Live"**.

---

## Selesai!
Anda akan mendapatkan tautan permanen toko Anda:
👉 **`https://warung-aboen.onrender.com`**

Buka tautan tersebut di HP Anda, lalu pasang ke Layar Utama (**Add to Home Screen**). Sekarang seluruh keluarga dan kasir Anda bisa menggunakan aplikasi Warung Aboen 24 jam non-stop dari mana saja!
