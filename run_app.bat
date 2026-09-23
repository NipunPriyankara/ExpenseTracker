@echo off
title Personal Expense Tracker
echo ========================================================
echo Starting Personal Expense Tracker...
echo ========================================================

REM Set PHP path to WAMP PHP 8.2
set "PATH=C:\wamp64\bin\php\php8.2.26;C:\ProgramData\ComposerSetup\bin;%PATH%"

echo Checking MySQL status...
tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL | find /I /N "mysqld.exe">NUL
if "%ERRORLEVEL%"=="0" (
    echo [OK] MySQL is already running.
) else (
    echo Starting MySQL in background...
    start /B "" "C:\wamp64\bin\mysql\mysql9.1.0\bin\mysqld.exe" --defaults-file="C:\wamp64\bin\mysql\mysql9.1.0\my.ini" --console
    timeout /t 3 /nobreak >nul
)

echo.
echo Application will be available at:
echo   - Local: http://127.0.0.1:8000
echo   - LAN / Mobile: Available using your local computer IP (e.g. http://192.168.x.x:8000)
echo.
echo Press Ctrl+C to stop the server.
echo ========================================================

php artisan serve --host 0.0.0.0 --port 8000
pause
