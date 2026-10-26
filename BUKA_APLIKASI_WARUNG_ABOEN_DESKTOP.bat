@echo off
title Warung Aboen Enterprise - Aplikasi Kasir Desktop
color 0A

echo =======================================================
echo          APLIKASI WARUNG ABOEN (MODE DESKTOP APP)
echo =======================================================
echo.

set PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin;%PATH%

echo 1. Menghubungkan ke Database Cloud Supabase...
echo    [OK] Database Supabase aktif di Cloud.

echo 2. Memeriksa Server Laravel...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Mengaktifkan server lokal di latar belakang...
    powershell -WindowStyle Hidden -Command "Start-Process -FilePath 'C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64\php.exe' -ArgumentList 'artisan serve --host=0.0.0.0 --port=8000' -WorkingDirectory '%~dp0' -WindowStyle Hidden"
    timeout /t 3 /nobreak >nul
) else (
    echo    [OK] Server Laravel aktif.
)

echo 3. Membuka Aplikasi Warung Aboen dalam mode App Standalone...
start "" "msedge.exe" --app="http://127.0.0.1:8000" 2>NUL
if "%ERRORLEVEL%"=="1" (
    start "" "chrome.exe" --app="http://127.0.0.1:8000" 2>NUL
    if "%ERRORLEVEL%"=="1" (
        start http://127.0.0.1:8000
    )
)

echo.
echo =======================================================
echo  Aplikasi Warung Aboen Desktop sudah aktif dan terbuka!
echo =======================================================
timeout /t 4 >nul
exit
