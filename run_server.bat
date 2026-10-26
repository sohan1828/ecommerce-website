@echo off
title Gizmo E-Commerce Server
echo ========================================================
echo   Gizmo E-Commerce Website is starting...
echo ========================================================
echo   URL:         http://localhost:8000
echo   Admin Panel: http://localhost:8000/admin/admin_login.php
echo   Database:    MySQL (ecom)
echo.
echo   Press Ctrl+C to stop the server anytime.
echo ========================================================
echo.

set "PHP_BIN=%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.2_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"

if exist "%PHP_BIN%" (
    "%PHP_BIN%" -S localhost:8000
) else (
    php -S localhost:8000
)
pause

