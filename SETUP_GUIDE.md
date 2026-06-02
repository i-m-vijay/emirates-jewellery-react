# Laravel Admin Panel - Setup Guide

## Project Status
✅ Laravel project created
✅ Database migrations prepared
✅ Models created (User, Product)
✅ Controllers created (ProductController, DashboardController, AuthController)
✅ Views created (Blade templates with Tailwind CSS)
✅ Routes configured
✅ Seeders prepared
✅ npm dependencies installed
✅ Frontend assets built

## Next Steps: Complete Database Setup

### Step 1: Start MySQL in XAMPP
1. Open XAMPP Control Panel
2. Click "Start" next to MySQL
3. Wait for MySQL to show "Running" status

### Step 2: Create the Database
Run this command in the project directory:
```bash
php create_db.php
```

Expected output:
```
Database created successfully!
```

### Step 3: Run Migrations
Run all database migrations:
```bash
php artisan migrate
```

This will create the necessary tables (users, products, etc.)

### Step 4: Run Seeders
Seed the database with the default admin user:
```bash
php artisan db:seed
```

Default Admin Credentials:
- Email: admin@admin.com
- Password: password

### Step 5: Create Storage Link
Link the storage directory for image uploads:
```bash
php artisan storage:link
```

### Step 6: Start the Development Server
Start Laravel's built-in development server:
```bash
php artisan serve
```

Server will run at: http://127.0.0.1:8000

## Access the Application

### Login
- URL: http://127.0.0.1:8000/login
- Email: admin@admin.com
- Password: password

### After Login
- Dashboard: http://127.0.0.1:8000/admin/dashboard
- Products: http://127.0.0.1:8000/admin/products

## Features Available

### Authentication
- ✅ Login
- ✅ Registration (create new user accounts)
- ✅ Logout
- ✅ Remember me functionality
- ✅ Session management

### Admin Dashboard
- ✅ Total products count
- ✅ Active products count
- ✅ Out of stock count
- ✅ Recent products table

### Product Management (CRUD)
- ✅ List all products with pagination (10 per page)
- ✅ Search products by name or category
- ✅ Create new products
- ✅ Edit existing products
- ✅ View product details
- ✅ Delete products
- ✅ Upload product images (JPG, PNG, WEBP)
- ✅ Auto slug generation from product name
- ✅ Status management (Active/Inactive)
- ✅ Stock tracking

### Image Management
- Images stored in: storage/app/public/products/
- Accessible via: http://127.0.0.1:8000/storage/products/filename.jpg
- Max file size: 2MB
- Supported formats: JPG, PNG, WEBP

## Database Schema

### Users Table
- id (auto-increment)
- name
- email (unique)
- password (hashed)
- is_admin (0 or 1)
- remember_token
- created_at, updated_at

### Products Table
- id (auto-increment)
- name
- slug (unique, auto-generated)
- description (nullable)
- price (decimal 10,2)
- stock (integer)
- category (nullable)
- image (nullable)
- status (active/inactive)
- created_at, updated_at

## Troubleshooting

### MySQL Connection Error
```
SQLSTATE[HY000] [2002] No connection could be made
```
**Solution:** Ensure MySQL is running in XAMPP Control Panel

### Storage Link Error
If images don't load after upload, run:
```bash
php artisan storage:link
```

### Permission Errors
Ensure these folders have write permissions:
- storage/
- bootstrap/cache/

### Port Already in Use
If port 8000 is in use, run:
```bash
php artisan serve --port 8001
```

## Project Structure

```
laravel-admin/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php
│   │   │   ├── ProductController.php
│   │   │   └── DashboardController.php
│   │   └── Requests/
│   │       └── StoreProductRequest.php
│   └── Models/
│       ├── User.php
│       └── Product.php
├── database/
│   ├── migrations/
│   │   ├── *_create_users_table.php
│   │   └── *_create_products_table.php
│   └── seeders/
│       └── DatabaseSeeder.php
├── resources/
│   └── views/
│       ├── layouts/
│       │   └── admin.blade.php
│       ├── admin/
│       │   ├── dashboard.blade.php
│       │   └── products/
│       │       ├── index.blade.php
│       │       ├── create.blade.php
│       │       ├── edit.blade.php
│       │       └── show.blade.php
│       └── auth/
│           ├── login.blade.php
│           └── register.blade.php
├── routes/
│   ├── web.php
│   └── auth.php
└── storage/
    └── app/
        └── public/
            └── products/  (images stored here)
```

## Development Commands

### Available Commands
```bash
# Start development server
php artisan serve

# Run migrations
php artisan migrate

# Roll back migrations
php artisan migrate:rollback

# Seed database
php artisan db:seed

# Create storage link for public disk
php artisan storage:link

# Clear all caches
php artisan cache:clear

# Optimize application
php artisan optimize
```

### Frontend Development
```bash
# Watch for changes and rebuild automatically
npm run dev

# Build for production
npm run build
```

## Security Notes

⚠️ **Important**: This is a development setup. For production:
1. Change the default admin password
2. Set APP_DEBUG=false in .env
3. Use a strong APP_KEY
4. Enable HTTPS
5. Set proper file permissions
6. Use environment variables for sensitive data
7. Enable CSRF protection (already enabled by default)

## Features Included

✅ **Authentication System**
- Secure password hashing with bcrypt
- CSRF protection on all forms
- Session-based authentication

✅ **Authorization**
- Protected admin routes with auth middleware
- Admin-only access enforcement

✅ **Image Upload**
- Secure file upload handling
- Automatic image deletion on product update/delete
- Image preview in forms

✅ **Form Validation**
- Server-side validation with FormRequest
- Client-side feedback
- Flash messages for success/error

✅ **Search & Pagination**
- Product search by name and category
- Paginated product listings (10 per page)

✅ **Responsive Design**
- Mobile-friendly interface
- Tailwind CSS styling
- Clean admin dashboard layout

## Support

For Laravel documentation: https://laravel.com/docs
For Tailwind CSS: https://tailwindcss.com/docs

---

**Project Created:** May 2026
**Laravel Version:** 12.x
**PHP Version:** 8.2+
**Database:** MySQL 5.7+
