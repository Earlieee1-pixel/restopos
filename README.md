# 🍗 RestoPos — Restaurant POS System

A full-stack Point-of-Sale system inspired by Jollibee's ordering workflow.
Built with **Vue 3** (frontend) + **Laravel 13** (backend) + **MySQL** (database).

---

## Project Structure

```
restopos/
├── frontend/        # Vue 3 + Vite + Pinia + Tailwind CSS
└── backend/         # Laravel 13 + Sanctum + MySQL
```

---

## Tech Stack

| Layer     | Technology                                   |
|-----------|----------------------------------------------|
| Frontend  | Vue 3, Vite, Pinia, Vue Router, Tailwind CSS |
| Backend   | Laravel 13, Laravel Sanctum (API tokens)     |
| Database  | MySQL (InnoDB, utf8mb4)                      |
| Auth      | Token-based via Sanctum                      |

---

## Features

- 🔐 **Auth** — Login/logout with role-based access (admin, manager, cashier). Inactive accounts are blocked.
- 🧾 **Orders** — Create dine-in or takeout orders. Track status (pending → preparing → served). Cancel orders. Receipt shown after each order.
- 🍔 **Menu Management** — Add/edit/delete products by category. Toggle availability. Manager/admin only for mutations.
- 🗂️ **Category Management** — Add/edit/delete menu categories with emoji icons and sort order. Manager/admin only.
- 🪑 **Table Management** — Tables auto-update to occupied when an order is placed, and back to available when served or cancelled.
- 📊 **Dashboard** — Active order count, available tables, today's sales summary (manager/admin). Cashier sees a limited view.
- 📈 **Reports** — Daily and monthly sales reports with top-selling products. Manager/admin only.
- 👥 **User Management** — Create, edit, and activate/deactivate staff accounts. Admin only.

---

## Getting Started

### Backend (Laravel)

```bash
cd backend

# 1. Install dependencies
composer install

# 2. Setup environment
cp .env.example .env
php artisan key:generate

# 3. Configure MySQL in .env — already pre-filled, just set your password if needed
#    DB_DATABASE=restopos
#    DB_USERNAME=root
#    DB_PASSWORD=your_password

# 4. Run migrations + seed sample data
php artisan migrate --seed

# 5. Start the dev server
php artisan serve
# Runs on http://localhost:8000
```

### Frontend (Vue)

```bash
cd frontend

# 1. Install dependencies
npm install

# 2. Setup environment
cp .env.example .env
# VITE_API_URL=http://localhost:8000/api
# VITE_RECEIPT_HEADER="Thank you for your order!"

# 3. Start the dev server
npm run dev
# Runs on http://localhost:5173
```

---

## Default Login Credentials

| Role    | Email                    | Password |
|---------|--------------------------|----------|
| Admin   | admin@restopos.com       | password |
| Manager | manager@restopos.com     | password |
| Cashier | cashier@restopos.com     | password |

> You can change these in `DatabaseSeeder.php` before seeding.

---

## Role Permissions

| Feature              | Cashier | Manager | Admin |
|----------------------|---------|---------|-------|
| Place orders         | ✅      | ✅      | ✅    |
| View active orders   | ✅      | ✅      | ✅    |
| View tables          | ✅      | ✅      | ✅    |
| View menu            | ✅      | ✅      | ✅    |
| Edit/delete products | ❌      | ✅      | ✅    |
| Manage categories    | ❌      | ✅      | ✅    |
| View reports         | ❌      | ✅      | ✅    |
| Manage users         | ❌      | ❌      | ✅    |

---

## Folder Guide

### Backend (`backend/`)

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/        # Login, logout, me
│   │   ├── Category/    # Category CRUD
│   │   ├── Order/       # Order CRUD + status transitions
│   │   ├── Product/     # Menu management
│   │   ├── Report/      # Daily/monthly sales reports
│   │   ├── Table/       # Table management
│   │   └── User/        # User management (admin only)
│   ├── Middleware/
│   │   └── RoleMiddleware.php   # Role-based access control
│   ├── Requests/
│   │   ├── Auth/        # LoginRequest
│   │   ├── Order/       # CreateOrderRequest, UpdateOrderRequest
│   │   └── Product/     # StoreProductRequest, UpdateProductRequest
│   └── Resources/       # UserResource, ProductResource, OrderResource, OrderItemResource
├── Models/              # User, Product, Order, OrderItem, Category, Table
└── Services/            # Business logic (Auth, Order, Product, Report)

routes/
└── api.php              # All API routes

database/
├── migrations/          # MySQL table definitions
└── seeders/             # Sample data (3 users, 5 categories, 14 products, 10 tables)

config/
├── pos.php              # POS-specific settings (restaurant name, currency, receipt header)
└── sanctum.php          # Sanctum stateful domains
```

### Frontend (`frontend/src/`)

```
composables/
└── useToast.js          # Global toast notification composable

services/
├── api.js               # Axios base instance with token + 401 interceptors
├── authService.js       # Auth API calls
├── categoryService.js   # Category API calls
├── orderService.js      # Order API calls
├── productService.js    # Product API calls
├── reportService.js     # Report API calls
├── tableService.js      # Table API calls
└── userService.js       # User management API calls

store/modules/
├── authStore.js         # Auth state (Pinia)
├── cartStore.js         # Cart state (items, discount, amount tendered, change)
├── orderStore.js        # Order state
└── productStore.js      # Product + category state

views/
├── auth/                # LoginView
├── category/            # CategoryView (CRUD)
├── dashboard/           # DashboardView (role-aware)
├── order/               # OrderView (POS screen + active orders tab)
├── product/             # ProductView (menu management)
├── report/              # ReportView (daily + monthly tabs)
├── table/               # TableView (click to start order)
└── users/               # UsersView (admin only)

components/
├── common/              # SummaryCard, ToastContainer
├── order/               # CartPanel, ReceiptModal
└── product/             # ProductCard

layouts/
└── MainLayout.vue       # Sidebar + nav (role-aware links) + content area

auth/
└── useAuth.js           # Auth composable

utils/
├── currency.js          # Peso formatting
└── date.js              # Date formatting
```

---

## API Endpoints

| Method    | Endpoint                            | Description                  | Auth          |
|-----------|-------------------------------------|------------------------------|---------------|
| POST      | `/api/login`                        | Login                        | Public        |
| POST      | `/api/logout`                       | Logout                       | ✓             |
| GET       | `/api/me`                           | Current user info            | ✓             |
| GET       | `/api/categories`                   | All categories               | ✓             |
| POST      | `/api/categories`                   | Create category              | Manager/Admin |
| PUT       | `/api/categories/{id}`              | Update category              | Manager/Admin |
| DELETE    | `/api/categories/{id}`              | Delete category              | Admin         |
| GET       | `/api/products`                     | All products                 | ✓             |
| POST      | `/api/products`                     | Create product               | Manager/Admin |
| PUT       | `/api/products/{id}`                | Update product               | Manager/Admin |
| DELETE    | `/api/products/{id}`                | Delete product (soft)        | Manager/Admin |
| PATCH     | `/api/products/{id}/availability`   | Toggle availability          | ✓             |
| GET       | `/api/tables`                       | All tables                   | ✓             |
| POST      | `/api/tables`                       | Create table                 | ✓             |
| PATCH     | `/api/tables/{id}/status`           | Update table status          | ✓             |
| GET       | `/api/orders`                       | Active orders                | ✓             |
| POST      | `/api/orders`                       | Place order                  | ✓             |
| GET       | `/api/orders/{id}`                  | Get single order             | ✓             |
| PATCH     | `/api/orders/{id}/status/{status}`  | Update order status          | ✓             |
| PATCH     | `/api/orders/{id}/cancel`           | Cancel order                 | ✓             |
| GET       | `/api/reports/daily`                | Daily sales                  | Manager/Admin |
| GET       | `/api/reports/monthly`              | Monthly sales                | Manager/Admin |
| GET       | `/api/reports/top-products`         | Top-selling products         | Manager/Admin |
| GET       | `/api/users`                        | All users                    | Admin         |
| POST      | `/api/users`                        | Create user                  | Admin         |
| PUT       | `/api/users/{id}`                   | Update user                  | Admin         |
| PATCH     | `/api/users/{id}/toggle-active`     | Activate/deactivate user     | Admin         |

---

## Order Status Flow

```
pending → preparing → served
    ↓           ↓
 cancelled   cancelled
```

Tables auto-update: `available` → `occupied` on order create, back to `available` when the last active order on that table is served or cancelled.

---

## Notes

- Comments in code are written in **Bisaya** for easier understanding by the team.
- All other code, UI text, and documentation is in English.
- MySQL is configured with `InnoDB` engine and `utf8mb4_unicode_ci` collation.
- Role hierarchy: `admin` > `manager` > `cashier`.
- Soft deletes are used for products — deleted products remain in order history.
