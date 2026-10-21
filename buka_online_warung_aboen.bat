@echo off
title Akses Online Warung Aboen (Internet Dari Mana Saja)
color 0B

echo =======================================================
echo        AKSES ONLINE WARUNG ABOEN (DARI LUAR JARINGAN)
echo        BISA DIBUKA DI HP VIA INTERNET / PAKET DATA
echo =======================================================
echo.

echo 1. Memeriksa status server lokal...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Server belum menyala! Silakan jalankan 'start_warung_aboen.bat' terlebih dahulu.
    echo.
    pause
    exit /b
)

echo 2. Menghubungkan ke jaringan publik online yang aman...
echo.

if exist "%~dp0cloudflared.exe" (
    echo    Menggunakan Cloudflare Tunnel (Akses Cepat & Gratis)...
    echo.
    echo =======================================================
    echo  PERHATIKAN TULISAN TAUTAN URL HTTPS DI BAWAH INI:
    echo  (Salin tautan https://...trycloudflare.com ke HP Anda)
    echo =======================================================
    echo.
    "%~dp0cloudflared.exe" tunnel --url http://127.0.0.1:8000
) else (
    echo    Menggunakan Localtunnel via npx...
    echo.
    echo =======================================================
    echo  PERHATIKAN TULISAN 'your url is: https://...'
    echo  (Buka alamat tersebut di HP atau laptop mana saja)
    echo =======================================================
    echo.
    npx localtunnel --port 8000
)

pause
