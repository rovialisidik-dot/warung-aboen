@echo off
title Unggah Warung Aboen ke GitHub
color 0B

echo =======================================================
echo     MENGUNGGAH APLIKASI WARUNG ABOEN KE GITHUB
echo =======================================================
echo.
echo Tautan Repository:
echo https://github.com/rovialisidik-dot/warung-aboen.git
echo.

set PATH=C:\laragon\bin\git\bin;C:\laragon\bin\git\mingw64\bin;%PATH%

git config credential.helper ""
git config --add credential.helper manager-core
git remote set-url origin https://github.com/rovialisidik-dot/warung-aboen.git

echo Menghubungkan ke GitHub...
echo (Jika muncul jendela login di browser/layar, silakan klik 'Sign in with your browser' atau masukkan akun GitHub Anda)
echo.

git push -u origin main

if "%ERRORLEVEL%"=="0" (
    echo.
    echo =======================================================
    echo  [BERHASIL] KODE WARUNG ABOEN SUDAH MASUK KE GITHUB!
    echo =======================================================
    echo.
    echo Sekarang Anda tinggal membuka Render.com:
    echo 1. Kunjungi https://render.com dan login dengan GitHub
    echo 2. Klik New + -^> Blueprint (atau Web Service)
    echo 3. Pilih repository 'warung-aboen'
    echo 4. Klik Apply / Deploy
    echo.
    echo Aplikasi Anda akan online 24 jam nonstop!
) else (
    echo.
    echo =======================================================
    echo  [PERHATIAN] Pengunggahan belum selesai.
    echo  Pastikan Anda telah mengizinkan login GitHub.
    echo =======================================================
)

echo.
pause
