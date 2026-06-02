@echo off
echo ================================
echo Laravel Admin - Quick Setup
echo ================================
echo.

echo Step 1: Creating Database...
php create_db.php
if %errorlevel% neq 0 (
    echo.
    echo ERROR: Could not create database. Make sure MySQL is running in XAMPP!
    echo.
    pause
    exit /b 1
)

echo.
echo Step 2: Running Migrations...
php artisan migrate
if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)

echo.
echo Step 3: Running Seeders...
php artisan db:seed
if %errorlevel% neq 0 (
    echo ERROR: Seeding failed!
    pause
    exit /b 1
)

echo.
echo Step 4: Creating Storage Link...
php artisan storage:link
if %errorlevel% neq 0 (
    echo ERROR: Storage link failed!
    pause
    exit /b 1
)

echo.
echo ================================
echo Setup Complete!
echo ================================
echo.
echo Admin Credentials:
echo Email: admin@admin.com
echo Password: password
echo.
echo To start the application, run:
echo   php artisan serve
echo.
echo Then visit: http://127.0.0.1:8000
echo.
pause
