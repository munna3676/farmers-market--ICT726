-- =====================================================
-- Aussie Farmers Market - Database Schema
-- ICT726 Assignment 4 - Dynamic Website
-- =====================================================

-- NOTE: CREATE DATABASE / USE removed for shared hosts (e.g. InfinityFree)
-- where you must import directly into your pre-created database.

-- -----------------------------------------------------
-- Table: users
-- Stores all users: admin, farmer, customer
-- -----------------------------------------------------
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,           -- stored using PHP password_hash()
    role ENUM('admin','farmer','customer') NOT NULL DEFAULT 'customer',
    phone VARCHAR(20),
    address VARCHAR(255),
    suburb VARCHAR(100),
    state VARCHAR(50),
    postcode VARCHAR(10),
    bio TEXT NULL,                             -- optional farm story, shown on public farmer profile
    status ENUM('active','suspended') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: categories
-- -----------------------------------------------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(80) NOT NULL UNIQUE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: products
-- Each product belongs to one farmer and one category
-- -----------------------------------------------------
CREATE TABLE products (
    product_id INT AUTO_INCREMENT PRIMARY KEY,
    farmer_id INT NOT NULL,
    category_id INT,
    product_name VARCHAR(150) NOT NULL,
    description TEXT,
    price DECIMAL(8,2) NOT NULL,
    unit VARCHAR(30) NOT NULL DEFAULT 'kg',   -- e.g. kg, dozen, bunch, punnet
    stock_quantity INT NOT NULL DEFAULT 0,
    is_organic TINYINT(1) NOT NULL DEFAULT 0,
    image VARCHAR(255) DEFAULT 'default-product.jpg',
    in_season_months VARCHAR(50) NOT NULL DEFAULT '1,2,3,4,5,6,7,8,9,10,11,12', -- comma-separated month numbers this product is in season
    status ENUM('active','inactive') NOT NULL DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (farmer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: orders
-- One order belongs to one customer, can contain items
-- from multiple farmers
-- -----------------------------------------------------
CREATE TABLE orders (
    order_id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    delivery_address VARCHAR(255) NOT NULL,
    delivery_suburb VARCHAR(100) NOT NULL,
    delivery_postcode VARCHAR(10) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL DEFAULT 0,
    status ENUM('pending','confirmed','delivered','cancelled') NOT NULL DEFAULT 'pending',
    FOREIGN KEY (customer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: order_items
-- Line items for each order (many-to-many between orders & products)
-- -----------------------------------------------------
CREATE TABLE order_items (
    order_item_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    product_id INT NOT NULL,
    farmer_id INT NOT NULL,
    quantity INT NOT NULL,
    price_at_purchase DECIMAL(8,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE CASCADE,
    FOREIGN KEY (farmer_id) REFERENCES users(user_id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- -----------------------------------------------------
-- Table: contact_messages
-- Stores messages submitted via the Contact Us form
-- -----------------------------------------------------
CREATE TABLE contact_messages (
    message_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- =====================================================
-- Sample data
-- =====================================================

-- Default admin account (password: Admin@123)
INSERT INTO users (full_name, email, password, role, phone, address, suburb, state, postcode)
VALUES ('Site Admin', 'admin@aussiefarmers.com.au',
'$2y$10$92IX9zaFqXH1EqSHBqM8s.PezWvC.a5gTsCbTB1O5R5j5F8/6y2N6', 'admin',
'0400000000','1 Admin St','Sydney','NSW','2000');
-- NOTE: The hash above is a placeholder length-correct bcrypt hash used only to
-- illustrate storage format. Run reset_admin_password.php (included) once after
-- setup to set a real password securely, OR register a new admin manually.

-- Sample categories
INSERT INTO categories (category_name) VALUES
('Vegetables'), ('Fruits'), ('Dairy & Eggs'), ('Honey & Preserves'), ('Herbs');

-- Sample farmer (password: Farmer@123 - set via registration form in practice)
INSERT INTO users (full_name, email, password, role, phone, address, suburb, state, postcode, bio)
VALUES ('Jack Wilson', 'jack@bluehillsfarm.com.au',
'$2y$10$92IX9zaFqXH1EqSHBqM8s.PezWvC.a5gTsCbTB1O5R5j5F8/6y2N6','farmer',
'0411111111','22 Blue Hills Rd','Orange','NSW','2800',
'Third-generation farmer growing heirloom vegetables, orchard fruit and raw honey in the Central West of NSW. We have been supplying our local community since 1987 and believe in pesticide-free, sustainable growing practices.');

-- Sample customer
INSERT INTO users (full_name, email, password, role, phone, address, suburb, state, postcode)
VALUES ('Emma Brown', 'emma@example.com',
'$2y$10$92IX9zaFqXH1EqSHBqM8s.PezWvC.a5gTsCbTB1O5R5j5F8/6y2N6','customer',
'0422222222','5 King St','Parramatta','NSW','2150');

-- Sample products (using generated illustration placeholders — farmers can
-- replace these with real photos via the "Edit Product" image upload)
-- in_season_months reflects typical Australian growing seasons for each item.
INSERT INTO products (farmer_id, category_id, product_name, description, price, unit, stock_quantity, is_organic, image, in_season_months)
VALUES
(2, 1, 'Heirloom Tomatoes', 'Vine-ripened heirloom tomatoes grown without pesticides.', 6.50, 'kg', 40, 1, 'sample-tomatoes.jpg', '12,1,2,3'),
(2, 2, 'Pink Lady Apples', 'Crisp and sweet apples freshly picked from the orchard.', 4.20, 'kg', 60, 0, 'sample-apples.jpg', '3,4,5,6,7,8'),
(2, 3, 'Free-range Eggs', 'Dozen free-range eggs from pasture-raised hens.', 7.00, 'dozen', 30, 0, 'sample-eggs.jpg', '1,2,3,4,5,6,7,8,9,10,11,12'),
(2, 4, 'Raw Wildflower Honey', 'Pure, unfiltered honey harvested locally.', 12.00, '500g jar', 25, 1, 'sample-honey.jpg', '1,2,3,4,5,6,7,8,9,10,11,12'),
(2, 5, 'Fresh Basil', 'Fragrant fresh basil grown in our greenhouse, perfect for pesto and salads.', 3.50, 'bunch', 20, 1, 'sample-basil.jpg', '11,12,1,2,3');

-- -----------------------------------------------------
-- Table: newsletter_subscribers
-- Stores emails collected from the homepage newsletter signup form
-- -----------------------------------------------------
CREATE TABLE newsletter_subscribers (
    subscriber_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    subscribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
