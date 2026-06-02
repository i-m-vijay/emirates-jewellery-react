#!/usr/bin/env bash
# Setup script for Linux/Mac

echo "================================"
echo "Laravel Admin - Quick Setup"
echo "================================"
echo ""

echo "Step 1: Creating Database..."
php create_db.php
if [ $? -ne 0 ]; then
    echo ""
    echo "ERROR: Could not create database. Make sure MySQL is running!"
    echo ""
    exit 1
fi

echo ""
echo "Step 2: Running Migrations..."
php artisan migrate
if [ $? -ne 0 ]; then
    echo "ERROR: Migration failed!"
    exit 1
fi

echo ""
echo "Step 3: Running Seeders..."
php artisan db:seed
if [ $? -ne 0 ]; then
    echo "ERROR: Seeding failed!"
    exit 1
fi

echo ""
echo "Step 4: Creating Storage Link..."
php artisan storage:link
if [ $? -ne 0 ]; then
    echo "ERROR: Storage link failed!"
    exit 1
fi

echo ""
echo "================================"
echo "Setup Complete!"
echo "================================"
echo ""
echo "Admin Credentials:"
echo "Email: admin@admin.com"
echo "Password: password"
echo ""
echo "To start the application, run:"
echo "  php artisan serve"
echo ""
echo "Then visit: http://127.0.0.1:8000"
echo ""
