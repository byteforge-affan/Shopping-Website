# Arts Store — E-commerce Management System

> A four-member Aptech academic team project: a PHP/MySQL shopping platform with customer, employee and administrator workflows.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat-square&logo=bootstrap&logoColor=white)

## Overview

**Arts Store** is a database-driven shopping website that combines a customer storefront with dedicated employee and administrator dashboards.

The project includes code for product browsing, cart management, checkout, order tracking, and operational tools for products, stock, customers, employees, orders, feedback and reports. These workflows have not all been independently runtime-tested.

## Three-Sided Workflow

| Experience | Core responsibilities |
| --- | --- |
| **Customer** | Browse products, register/login, manage cart, checkout, track orders, manage account and use wishlist |
| **Employee** | Review orders, update order status, handle dispatch/delivery and manage own password |
| **Admin** | Manage products, categories, stock, orders, employees, customers, feedback and reports |

## Customer Experience

- Browse products without signing in
- Register and log in
- Add products to cart
- Checkout interface with Credit Card, Cheque or Cash on Delivery options (not verified as production payment processing)
- Receive a unique 16-digit order number
- View orders through My Account
- Track orders
- Update profile and password
- Save products to a wishlist
- Cancel eligible orders
- Return/replace flow with a 7-day policy

## Administration

The administrator dashboard provides:

- Order, revenue, product and customer statistics
- Low-stock alerts
- Order filtering and status updates
- Product CRUD
- Category CRUD
- Bulk stock updates
- Employee account management
- Customer list and order history
- Customer feedback
- Sales, top-product and payment reports

## Employee Workflow

Employees have a more focused operational dashboard:

- Order overview
- Order-status management
- Dispatch and delivery confirmation
- Personal password management

## Checkout and Inventory Limitations (main branch)

The `main` branch contains checkout code for creating orders and updating product stock. **Atomic completion of order creation and stock changes, concurrent inventory reliability, and end-to-end payment processing have not been verified.** Do not treat these flows as production-ready without additional testing and remediation.

[Pull request #1](https://github.com/byteforge-affan/arts-store/pull/1) for atomic checkout and inventory integrity was **closed without merge**. Its preserved `phase-3a1-atomic-checkout-inventory` branch contains unfinished, runtime-unverified remediation work; those changes are **not claimed as implemented on `main`**.

## Project Structure

```text
ArtsStore/
├── admin/                 Administration dashboard
├── employee/              Employee dashboard
├── includes/              Shared PHP / database components
├── css/                   Stylesheets
├── js/                    Frontend scripts
├── images/                Project images
├── uploads/               Uploaded assets
├── index.php              Storefront
├── products.php           Product browsing
├── product-detail.php     Product details
├── cart.php               Shopping cart
├── checkout.php           Checkout flow
├── my-account.php         Customer account
├── track-order.php        Order tracking
├── wishlist.php           Saved products
├── cancel-order.php       Order cancellation
├── replace-confirm.php    Return / replacement flow
└── database.sql           Database schema / seed data
```

## Tech Stack

| Layer | Technology |
| --- | --- |
| Backend | PHP |
| Database | MySQL |
| Frontend | HTML · CSS · JavaScript |
| UI | Bootstrap |
| Local environment | XAMPP / WAMP |

## Run Locally

1. Import `database.sql` through phpMyAdmin.
2. Open `includes/db.php` and configure your database password if required.
3. Copy the `ArtsStore` project folder into `htdocs/` (XAMPP) or `www/` (WAMP).
4. Open `http://localhost/ArtsStore/`.

## Demo Accounts

### Administrator

- URL: `http://localhost/ArtsStore/admin/`
- Username: `admin`
- Password: `admin123`

### Employee

- URL: `http://localhost/ArtsStore/employee/`
- Username: `employee1`
- Password: `emp123`

### Customer

- URL: `http://localhost/ArtsStore/login.php`
- Demo accounts: `ahmed@gmail.com`, `saad@gmail.com`, `sara@gmail.com`

> **Local demonstration only:** These seeded credentials are publicly documented and must not be used for a public or production deployment. Change or disable demo accounts before any public hosting.

## Academic Team and Contributions

Arts Store was developed as a **four-member Aptech academic team project**. **Muhammad Affan contributed frontend development and PHP backend implementation**. This README does not attribute the entire project or database design to one contributor.

## Project Focus

Arts Store demonstrates how a storefront and internal business workflows can share one database-backed application. It combines **customer-facing e-commerce features with operational order, inventory and account management**.

---

**Academic team project · Muhammad Affan (frontend and PHP backend contributions)**

[GitHub Profile](https://github.com/byteforge-affan) · [Portfolio](https://byteforge-affan-portfolio.netlify.app/)
