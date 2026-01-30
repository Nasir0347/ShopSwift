# ShopSwift 🚀

A modern, production-ready E-commerce platform built with **Laravel 11** (Backend) and **Vue 3** (Frontend). Designed to mimic Shopify's functionality with a premium user experience.

## 🛠️ Tech Stack

**Backend:**
- **Framework:** Laravel 11
- **Database:** MySQL
- **API:** RESTful API with Sanctum Authentication
- **Features:** Eloquent ORM, Request Validation, Resource Classes, Service Layer Pattern

**Frontend:**
- **Framework:** Vue 3 (Composition API)
- **Styling:** TailwindCSS
- **State Management:** Pinia
- **Build Tool:** Vite
- **Icons:** Heroicons

---

## ✨ Key Features

### 🛍️ Product Management
- **Shopify-like Admin Interface:** Create and edit products with ease.
- **Variant Support:** Manage variants (Size, Color, etc.) with individual prices and inventory.
- **Variant Images:** 
    - Upload specific images for each variant.
    - **Smart Cleanup:** Automatically deletes unused image files when replaced or removed.
    - **Persistence:** Images stay linked correctly even after complex edits.
- **Inventory Tracking:** Automatic "Out of Stock" status on storefront.

### 🏪 Storefront
- **Dynamic Catalog:** Filters active products automatically.
- **Responsive Design:** Optimized for Mobile and Desktop.
- **Cart System:** Real-time cart updates with stock validation (prevents adding out-of-stock items).
- **Checkout Flow:** Seamless shipping and payment conceptual flow.

### 📦 Order Management
- Track orders from placement to fulfillment.
- Integrated database schema for scalable order processing.

---

## 🚀 Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- Node.js & npm
- MySQL (XAMPP recommended for Windows)

### 1. Backend Setup (Laravel)
```bash
cd backend
cp .env.example .env
# Configure your database credentials in .env

composer install
php artisan key:generate
php artisan migrate --seed  # Seeds demo data
php artisan storage:link    # Important for image uploads
php artisan serve
```

### 2. Frontend Setup (Vue)
```bash
cd frontend
cp .env.example .env
# Ensure VITE_API_BASE_URL=http://localhost:8000/api

npm install
npm run dev
```

### 3. Access the App
- **Storefront:** `http://localhost:5173`
- **Admin Panel:** `http://localhost:5173/admin`
- **API Docs:** `http://localhost:8000/api/documentation` (if installed)

---

## 📂 Project Structure

```
ShopSwift/
├── backend/            # Laravel API
│   ├── app/Models/     # Eloquent Models (Product, Variant, Order...)
│   ├── app/Http/       # Controllers & Requests
│   └── routes/api.php  # API Definitions
│
├── frontend/           # Vue 3 App
│   ├── src/views/      # Admin & Shop Pages
│   ├── src/components/ # Reusable UI Components
│   └── src/stores/     # Pinia State Stores
```

## 📜 License
MIT License. Free to use and modify.