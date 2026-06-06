-- database.sql for Dulha Collection Rental Management System
CREATE DATABASE IF NOT EXISTS dulha_collection_db;
USE dulha_collection_db;

-- 1. Login Table
CREATE TABLE IF NOT EXISTS login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Products Table (Luxury Groom Apparel Inventory)
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('Sherwani', 'Indo-Western', 'Suit & Tuxedo', 'Jodhpuri Suit', 'Kurta Pajama', 'Safa & Turban', 'Mojari & Shoes', 'Accessories') NOT NULL,
    size VARCHAR(20) NOT NULL,
    color VARCHAR(100) NOT NULL,
    rental_price DECIMAL(10,2) NOT NULL,
    security_deposit DECIMAL(10,2) NOT NULL,
    status ENUM('Available', 'Rented', 'Maintenance') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Customers Table (Wedding Grooms/Clients)
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150),
    phone VARCHAR(20) NOT NULL,
    alt_phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 4. Rentals Table (Wedding Apparel Bookings)
CREATE TABLE IF NOT EXISTS rentals (
    id INT AUTO_INCREMENT PRIMARY KEY,
    product_id INT NOT NULL,
    customer_id INT NOT NULL,
    trial_date DATE NOT NULL,
    event_date DATE NOT NULL,
    pickup_date DATE NOT NULL,
    return_date DATE NOT NULL,
    rental_price DECIMAL(10,2) NOT NULL,
    security_deposit_paid DECIMAL(10,2) NOT NULL,
    advance_paid DECIMAL(10,2) NOT NULL,
    balance_pending DECIMAL(10,2) NOT NULL,
    alteration_notes TEXT,
    status ENUM('Booked', 'Picked Up', 'Returned', 'Cancelled') DEFAULT 'Booked',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE RESTRICT,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE RESTRICT
);

-- Insert premium seed products
INSERT INTO products (name, category, size, color, rental_price, security_deposit, status) VALUES 
('Royal Maharaja Ivory Sherwani Set', 'Sherwani', '40', 'Ivory White with Gold Zardozi', 4500.00, 3000.00, 'Available'),
('Velvet Crimson Embroidered Indo-Western', 'Indo-Western', '42', 'Velvet Deep Crimson', 5200.00, 4000.00, 'Available'),
('Midnight Blue Luxury Italian Tuxedo', 'Suit & Tuxedo', '44', 'Midnight Blue', 3800.00, 3000.00, 'Available'),
('Emerald Green Brocade Jodhpuri Suit', 'Jodhpuri Suit', '38', 'Emerald Green', 4200.00, 3000.00, 'Available'),
('Banarasi Silk Hand-Tied Turban with Kalgi', 'Safa & Turban', 'Free Size', 'Pastel Pink & Gold', 1200.00, 1000.00, 'Available'),
('Handcrafted Golden Mojari with Pearl Embellishments', 'Mojari & Shoes', '9', 'Champagne Gold', 800.00, 500.00, 'Available');
