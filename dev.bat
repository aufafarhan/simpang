@echo off
REM ---------------------------------------------------------------------------
REM Menjalankan backend + frontend sekaligus, masing-masing di jendela sendiri.
REM
REM Pemakaian : .\dev.bat        Hentikan : tutup kedua jendela, atau Ctrl+C
REM Backend   : http://localhost:8000
REM Frontend  : http://localhost:3000
REM ---------------------------------------------------------------------------

start "OpenSID :8000" cmd /k "cd /d %~dp0 && serve.bat"
start "Next.js :3000" cmd /k "cd /d %~dp0frontend && npm run dev"

echo Dua jendela dibuka:
echo   backend  http://localhost:8000
echo   frontend http://localhost:3000
