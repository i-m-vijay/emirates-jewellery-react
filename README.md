# Laravel Admin Panel - Quick Start

## 🚀 Quick Setup (5 minutes)

### Prerequisites
- ✅ XAMPP/WAMP installed with MySQL
- ✅ PHP 8.2+ 
- ✅ Composer
- ✅ Node.js & npm

### Step-by-Step Setup

#### 1. Start MySQL in XAMPP
```
1. Open XAMPP Control Panel
2. Click START next to MySQL
3. Wait until it shows "Running"
```

#### 2. Auto Setup (Windows)
Simply double-click the `setup.bat` file:
```
setup.bat
```

This will automatically:
- Create the database
- Run migrations
- Seed the default admin user
- Create storage link for images

#### 3. Start the Application
```bash
php artisan serve
```

#### 4. Login
Open your browser and go to:
```
http://127.0.0.1:8000
```

**Login Credentials:**
- Email: `admin@admin.com`
- Password: `password`

---

## 📋 Manual Setup (if setup.bat doesn't work)

```bash
# Create database
php create_db.php

# Run migrations
php artisan migrate

# Seed database
php artisan db:seed

# Create storage link for images
php artisan storage:link

# Start server
php artisan serve
```

---

## ✨ Features

### ✅ Authentication
- Login / Logout
- User Registration
- Password Hashing
- Session Management

### ✅ Admin Dashboard
- Total Products Count
- Active Products Count
- Out of Stock Count
- Recent Products List

### ✅ Product Management
- Create Products
- Edit Products
- Delete Products
- View Product Details
- Search Products
- Paginate Results
- Upload Images
- Set Status (Active/Inactive)
- Track Stock Levels

### ✅ User Interface
- Responsive Design
- Tailwind CSS Styling
- Clean Admin Layout
- Flash Messages

---

## 📁 Project Location
```
C:\Users\praful\laravel-admin
```

---

## 🔑 Admin User
```
Email: admin@admin.com
Password: password
```

---

## 📖 For More Details
See: `SETUP_GUIDE.md` in the project directory

---

## 🆘 Troubleshooting

**Problem:** "Can't connect to MySQL server"
**Solution:** Start MySQL in XAMPP Control Panel first

**Problem:** Images not showing after upload
**Solution:** Run `php artisan storage:link`

**Problem:** Port 8000 already in use
**Solution:** Run `php artisan serve --port 8001`

---

**Ready to go!** 🎉
