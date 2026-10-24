@echo off
title Warung Aboen Enterprise - Aplikasi Kasir Desktop
color 0A

echo =======================================================
echo          APLIKASI WARUNG ABOEN (MODE DESKTOP APP)
echo =======================================================
echo.

set PATH=C:\laragon\bin\php\php-8.1.10-Win32-vs16-x64;C:\laragon\bin\composer;C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin;%PATH%

echo 1. Memeriksa Database MySQL...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Mengaktifkan database...
    start "" /B "C:\laragon\bin\mysql\mysql-8.0.30-winx64\bin\mysqld.exe" --defaults-file="C:\laragon\bin\mysql\mysql-8.0.30-winx64\my.ini" --console
    timeout /t 3 /nobreak >nul
)

echo 2. Memeriksa Server Laravel...
tasklist /FI "IMAGENAME eq php.exe" 2>NUL | find /I /N "php.exe">NUL
if "%ERRORLEVEL%"=="1" (
    echo    Mengaktifkan server lokal...
    start "" /B php artisan serve --host=0.0.0.0 --port=8000
    timeout /t 3 /nobreak >nul
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
