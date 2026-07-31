@echo off
REM ---------------------------------------------------------------------------
REM Menjalankan OpenSID Nagari Simpang untuk pengembangan lokal.
REM
REM Wajib memakai PHP 8.4 Laragon. Perintah `php` di PATH mengarah ke XAMPP 8.2
REM yang tidak punya ekstensi gd, intl, dan zip, sehingga OpenSID gagal jalan.
REM
REM Port 8080 harus cocok dengan APP_URL di file .env
REM
REM Pemakaian : .\serve.bat        Hentikan : Ctrl+C
REM Alamat    : http://localhost:8080
REM ---------------------------------------------------------------------------

cd /d "%~dp0"

set PHP_BIN=C:\laragon\bin\php\php-8.4.1\php.exe

if not exist "%PHP_BIN%" (
    echo [GAGAL] PHP tidak ditemukan di:
    echo         %PHP_BIN%
    echo.
    echo Versi PHP Laragon mungkin sudah berubah. Cek folder berikut,
    echo lalu sesuaikan variabel PHP_BIN di file ini:
    echo         C:\laragon\bin\php\
    exit /b 1
)

echo Menjalankan OpenSID di http://localhost:8080
echo Tekan Ctrl+C untuk berhenti.
echo.

REM Bind ke 0.0.0.0, bukan "localhost". Di Windows, "localhost" bisa resolve
REM hanya ke IPv6 [::1] sehingga 127.0.0.1:8080 mati — Next.js (undici) mencoba
REM kedua alamat dan gagal dengan AggregateError/ETIMEDOUT.
"%PHP_BIN%" -S 0.0.0.0:8080
