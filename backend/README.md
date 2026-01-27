# ShopSwift Backend API

## Overview
ShopSwift is a production-grade E-Commerce backend built with Laravel 11. It provides a robust REST API for managing products, inventory, orders, customers, and discounts.

## Requirements
- PHP 8.2+
- Composer
- MySQL

## Setup Instructions

1.  **Clone the repository** (if you haven't already).
2.  **Install dependencies**:
    ```bash
    cd backend
    composer install
    ```
3.  **Environment Configuration**:
    - Copy `.env.example` to `.env`.
    - Configure database credentials in `.env`.
    ```ini
    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=shopswift
    DB_USERNAME=root
    DB_PASSWORD=
    ```
4.  **Database Setup**:
    - Create the database `shopswift` if it doesn't exist.
    - Run migrations:
    ```bash
    php artisan migrate
    ```
5.  **Seed Data**:
    ```bash
    php artisan db:seed
    ```
6.  **Run Server**:
    ```bash
    php artisan serve
    ```

## API Documentation

### Authentication
- `POST /api/v1/register`: Register a new user.
- `POST /api/v1/login`: Login to get a Bearer token.
- `POST /api/v1/logout`: Logout (requires Auth).
- `GET /api/v1/me`: Get current user profile (requires Auth).

### Products
- `GET /api/v1/products`: List products (with filters).
- `GET /api/v1/products/{id}`: Get product details (with variants/images).
- `POST /api/v1/products`: Create product (Admin only logic not strictly enforced yet, but structure is there).
    - Payload includes `variants[]` and `images[]`.

### Orders
- `POST /api/v1/orders`: Place an order.
    - Requires `items` array with `variant_id` and `quantity`.
    - Handle inventory deduction automatically.
- `GET /api/v1/orders`: List orders (User sees own, Admin sees all).

### Categories & Collections
- `GET /api/v1/categories`
- `GET /api/v1/collections`

### Customers
- `GET /api/v1/customer/addresses`
- `POST /api/v1/customer/addresses`

## Demo Credentials
- **Admin**: `admin@shopswift.com` / `password`
- **Customer**: `customer@shopswift.com` / `password`

## Architecture
- **Service Layer**: Complex logic (Ordering, Inventory, Discounts) is handled in dedicated Services.
- **API Resources**: Responses are transformed using standard API Resources.
- **Database**: Normalized schema with soft deletes and foreign keys.
