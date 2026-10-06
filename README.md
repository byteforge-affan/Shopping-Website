# Arts Store — E-commerce Management System

> A PHP/MySQL shopping platform with separate customer, employee and administrator workflows.

![PHP](https://img.shields.io/badge/PHP-777BB4?style=flat-square&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=flat-square&logo=mysql&logoColor=white)
![Bootstrap](https://img.shields.io/badge/Bootstrap-7952B3?style=flat-square&logo=bootstrap&logoColor=white)

## Overview

**Arts Store** is a database-driven shopping website that combines a customer storefront with dedicated employee and administrator dashboards.

The project covers the shopping flow from product browsing and cart management through checkout and order tracking, while also providing operational tools for products, stock, customers, employees, orders, feedback and reports.

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
- Checkout using Credit Card, Cheque or Cash on Delivery
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

> These credentials are included for local demonstration/testing of the portfolio project.

## Project Focus

Arts Store demonstrates how a storefront and internal business workflows can share one database-backed application. It combines **customer-facing e-commerce features with operational order, inventory and account management**.

---

**Built by Muhammad Affan · ByteForge Studio**

[GitHub Profile](https://github.com/byteforge-affan) · [Portfolio](https://byteforge-affan-portfolio.netlify.app/)
