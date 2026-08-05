@echo off
REM ---------------------------------------------------------------------------
REM Menjalankan OpenSID untuk pengembangan lokal.
REM
REM Wajib memakai PHP 8.4 Laragon. Perintah `php` di PATH mengarah ke XAMPP 8.2
REM yang tidak punya ekstensi gd, intl, dan zip, sehingga OpenSID gagal jalan.
REM
REM Pemakaian : .\serve.bat        Hentikan : Ctrl+C
REM Alamat    : http://localhost:8000
REM ---------------------------------------------------------------------------

cd /d "%~dp0"

set PHP_BIN=C:\laragon\bin\php\php-8.4.23-Win32-vs17-x64\php.exe
set PORT=8000

if not exist "%PHP_BIN%" (
    echo [GAGAL] PHP tidak ditemukan di:
    echo         %PHP_BIN%
    echo.
    echo Versi PHP Laragon mungkin sudah berubah. Cek folder berikut,
    echo lalu sesuaikan variabel PHP_BIN di file ini:
    echo         C:\laragon\bin\php\
    exit /b 1
)

echo Menjalankan OpenSID di http://localhost:%PORT%
echo Tekan Ctrl+C untuk berhenti.
echo.

REM Bind ke 0.0.0.0 agar bisa diakses via localhost maupun 127.0.0.1
"%PHP_BIN%" -S 0.0.0.0:%PORT%
