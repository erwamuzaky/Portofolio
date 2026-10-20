@echo off
title Server Portofolio
cd /d "%~dp0"

echo ==========================================
echo   Menjalankan server portofolio
echo ==========================================
echo.

REM Cek MySQL sudah jalan atau belum (port 3306)
powershell -NoProfile -Command "try { $c = New-Object Net.Sockets.TcpClient; $c.Connect('127.0.0.1',3306); $c.Close(); exit 0 } catch { exit 1 }"
if errorlevel 1 (
    echo [PERINGATAN] MySQL sepertinya belum jalan.
    echo Nyalakan dulu MySQL di XAMPP Control Panel,
    echo lalu jalankan file ini lagi.
    echo.
    pause
    exit /b 1
)

echo MySQL terdeteksi. Membuka browser...
start "" "http://localhost:8000/"
echo.
echo   Halaman publik : http://localhost:8000/
echo   Dashboard admin: http://localhost:8000/admin/
echo   Login admin    : username admin
echo   (password diisi saat setup.sql dijalankan, ganti lewat menu Ganti Password)
echo.
echo Server berjalan. Jangan tutup jendela ini selama memakai situs.
echo Tekan Ctrl+C lalu tutup jendela untuk berhenti.
echo.

php -S localhost:8000
pause
