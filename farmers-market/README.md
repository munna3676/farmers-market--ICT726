# Aussie Farmers Market — Dynamic Website
ICT726 Web Development — Assignment 4 (Dynamic Website, Group Project)

A PHP + MySQL web application connecting Australian farmers directly with
customers who want fresh, local produce delivered to their door.

## 1. Tech Stack
- HTML5, CSS3 (custom, responsive, no framework)
- Vanilla JavaScript (client-side validation)
- PHP 8+ (PDO for MySQL access)
- MySQL / MariaDB

## 2. Setup Instructions
1. Install a local server stack (XAMPP / WAMP / MAMP) or use your hosting provider's PHP/MySQL environment.
2. Create the database by importing `database.sql` (via phpMyAdmin or `mysql -u root -p < database.sql`).
3. Open `config/db.php` and update `DB_HOST`, `DB_NAME`, `DB_USER`, `DB_PASS` to match your environment.
4. Place the whole `farmers-market` folder inside your server's web root (e.g. `htdocs/farmers-market`).
5. Visit `setup_admin_password.php` in your browser once to set a real admin password, then **delete that file**.
6. Visit `index.php` to load the site. Register a new Farmer and Customer account to test the full flow, or use the sample farmer/customer accounts in `database.sql` after resetting their passwords the same way.
7. Ensure the `uploads/products/` folder is writable (for product image uploads).

## 3. Folder Structure
```
farmers-market/
├── admin/              Admin-only pages (users, products, orders, messages)
├── farmer/             Farmer-only pages (dashboard, add/edit/delete product, orders)
├── assets/             CSS, JS, static images (hero banner, default product art)
├── config/db.php       Database connection (PDO)
├── includes/           Shared PHP: auth/session/RBAC, functions, header, footer
├── uploads/products/   Farmer-uploaded product images (+ generated sample images)
├── dev-tools/          Python scripts used to generate placeholder illustration
│                       images (not required to run the site — reference only)
├── database.sql        Full schema + sample data
├── index.php           Homepage (hero, category browser, featured products)
├── products.php        Public product browsing/search
├── product.php         Product detail + add to cart
├── farmers.php         Public "Meet Our Farmers" directory
├── farmer_profile.php  Individual farmer's public profile + their products
├── faq.php             FAQ accordion page
├── cart.php            Session-based shopping cart
├── checkout.php        Places order (transaction-safe stock deduction)
├── orders.php          Customer order history
├── register.php / login.php / logout.php
├── about.php / contact.php / privacy.php
├── robots.txt / sitemap.xml   SEO
└── .htaccess           Security headers, custom 404
```

## 4. Database Schema (ER Overview)
- **users** (user_id PK) — role ENUM(admin, farmer, customer), hashed password, address fields.
- **categories** (category_id PK) — product categories (Vegetables, Fruits, etc.)
- **products** (product_id PK) — FK farmer_id → users, FK category_id → categories.
- **orders** (order_id PK) — FK customer_id → users.
- **order_items** (order_item_id PK) — FK order_id → orders, FK product_id → products, FK farmer_id → users (junction table linking orders and products; a many-to-many relationship).
- **contact_messages** (message_id PK) — stores Contact Us submissions.

Relationships: One farmer → many products. One customer → many orders. One order → many order_items. One product → many order_items (so sales history is preserved even if a product is later edited/deleted).

## 5. Key Functionality Checklist (maps to assignment rubric)
- [x] Register / Login / Logout with hashed passwords (`password_hash`/`password_verify`)
- [x] Role-based access control: admin / farmer / customer (`includes/auth.php` → `require_role()`)
- [x] CRUD: Farmers create/read/update/delete their own products; Admin can moderate all
- [x] Two+ validated forms: Registration, Add Product, Checkout, Contact (server-side + client-side JS)
- [x] Session-based shopping cart + transactional checkout (stock decremented safely)
- [x] Semantic HTML5, ARIA labels, skip link, alt text on images (accessibility)
- [x] Responsive CSS (mobile-friendly, tested breakpoints)
- [x] SEO: unique meta titles/descriptions per page, semantic headings, robots.txt, sitemap.xml
- [x] Privacy notice page + CSRF protection + input sanitization (privacy & security)

## 6. Suggested Task Split for 3 Team Members
(Edit this to reflect what you actually each build — the assignment requires
individual contribution reports, so keep your git commits granular.)

| Member | Focus Area | Files |
|---|---|---|
| Member 1 | Authentication & Access Control | `register.php`, `login.php`, `logout.php`, `includes/auth.php`, `admin/manage_users.php` |
| Member 2 | Product Catalogue & Farmer CRUD | `products.php`, `product.php`, `farmer/*.php`, image upload handling |
| Member 3 | Cart, Checkout, Orders, SEO & Accessibility polish | `cart.php`, `checkout.php`, `orders.php`, `admin/manage_orders.php`, meta tags, `robots.txt`, `sitemap.xml`, CSS responsiveness |

## 7. Git Workflow Reminder
Per the assignment (mandatory): create a shared git repository, and each
member should commit their own work incrementally under their own account so
individual contribution is visible in the commit history.

## 8. Notes
- This is a **student project**; checkout is "Cash on Delivery" only — no real payment gateway is integrated.
- Default/sample password hashes in `database.sql` are placeholders — use `setup_admin_password.php` (then delete it) or the `register.php` form to set real, working passwords.
