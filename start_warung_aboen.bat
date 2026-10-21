@echo off
title Warung Aboen Enterprise - Server Multi-Device & HP
color 0A

echo =======================================================
echo          APLIKASI WARUNG ABOEN (KASIR & PEMBUKUAN)
echo        RESPONSIF UNTUK HP, TABLET, DAN LAPTOP LAIN
echo =======================================================
echo.

echo 1. Menyiapkan environment sistem...
set PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin;%PATH%

echo 2. Memastikan server database MySQL aktif...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Memulai mysqld di latar belakang...
    start "" /B "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\bin\mysql\mysql-8.0.30-winx64\my.ini" --console
    timeout /t 3 /nobreak >nul
) else (
    echo    MySQL sudah aktif!
)

echo 3. Mendeteksi alamat IP jaringan lokal Wi-Fi...
for /f %%i in ('powershell -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.InterfaceAlias -notmatch 'Loopback' -and $_.IPAddress -notmatch '^169\.254\.' } | Select-Object -First 1).IPAddress"') do set LOCAL_IP=%%i

if "%LOCAL_IP%"=="" set LOCAL_IP=192.168.1.116

echo.
echo =======================================================
echo              PANDUAN ALAMAT AKSES APLIKASI
echo =======================================================
echo  [*] Akses di Laptop ini   : http://localhost:8000
echo  [*] Akses di HP / Lainnya : http://%LOCAL_IP%:8000
echo =======================================================
echo  CARA PAKAI DI HP (SMARTPHONE):
echo  1. Pastikan HP terhubung ke Wi-Fi yang sama dengan laptop.
echo  2. Buka browser Chrome / Safari di HP Anda.
echo  3. Masukkan alamat: http://%LOCAL_IP%:8000
echo  4. Ketuk menu titik tiga (⋮) di Chrome lalu pilih
echo     'Tambahkan ke Layar Utama' (Add to Home screen)
echo     agar langsung terpasang seperti aplikasi APK di HP!
echo =======================================================
echo.

echo 4. Membuka browser laptop ke http://localhost:8000 ...
start http://localhost:8000/login

echo 5. Menjalankan server aplikasi (Host 0.0.0.0 siap multi-device)...
echo    (Biarkan jendela hitam ini tetap terbuka selama aplikasi digunakan)
echo.

php artisan serve --host=0.0.0.0 --port=8000
pause
