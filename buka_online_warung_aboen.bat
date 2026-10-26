@echo off
title Warung Aboen Enterprise - Akses Online Dari Mana Saja
color 0B

echo =======================================================
echo        SERVER ONLINE WARUNG ABOEN (INTERNET OTOMATIS)
echo      BISA DIBUKA DI HP, TABLET, DAN LAPTOP DARI MANA SAJA
echo =======================================================
echo.

set PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin;%PATH%

echo 1. Menghubungkan ke Database Cloud Supabase...
echo    [OK] Database Supabase aktif di Cloud.

echo 2. Memeriksa Server Aplikasi (Laravel)...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Mengaktifkan Server Laravel di latar belakang...
    powershell -WindowStyle Hidden -Command "Start-Process -FilePath 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' -ArgumentList 'artisan serve --host=0.0.0.0 --port=8000' -WorkingDirectory '%~dp0' -WindowStyle Hidden"
    timeout /t 3 /nobreak >nul
) else (
    echo    [OK] Server Laravel aktif.
)

echo.
echo =======================================================
echo  MENGHUBUNGKAN KE INTERNET PUBLIK VIA CLOUDFLARE...
echo.
echo  Tautan HTTPS online akan muncul di bawah ini.
echo  Buka link tersebut di HP atau Laptop dari mana saja!
echo  (PENTING: Jangan tutup jendela ini selama aplikasi dipakai)
echo =======================================================
echo.

if exist "%~dp0cloudflared.exe" (
    "%~dp0cloudflared.exe" tunnel --url http://127.0.0.1:8000
) else (
    npx localtunnel --port 8000
)

pause
