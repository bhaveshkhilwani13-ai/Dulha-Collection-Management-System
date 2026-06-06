<?php
// includes/db.php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$username = "root";
$password = "";
$dbname = "dulha_collection_db";

// Connect to MySQL server first
$conn = new mysqli($host, $username, $password);

if ($conn->connect_error) {
    die("MySQL connection failed: " . $conn->connect_error);
}

// 1. Auto-create database if not exists
$conn->query("CREATE DATABASE IF NOT EXISTS $dbname");
$conn->select_db($dbname);

// 2. Auto-generate tables if they don't exist
$conn->query("CREATE TABLE IF NOT EXISTS login (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    category ENUM('Sherwani', 'Indo-Western', 'Suit & Tuxedo', 'Jodhpuri Suit', 'Kurta Pajama', 'Safa & Turban', 'Mojari & Shoes', 'Accessories') NOT NULL,
    size VARCHAR(20) NOT NULL,
    color VARCHAR(100) NOT NULL,
    rental_price DECIMAL(10,2) NOT NULL,
    security_deposit DECIMAL(10,2) NOT NULL,
    status ENUM('Available', 'Rented', 'Maintenance') DEFAULT 'Available',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) UNIQUE,
    password_hash VARCHAR(255),
    phone VARCHAR(20) NOT NULL,
    alt_phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS rentals (
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
)");

// 3. Auto-seed Admin User
$admin_check = $conn->query("SELECT id FROM login LIMIT 1");
if ($admin_check && $admin_check->num_rows === 0) {
    $secure_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO login (username, password_hash) VALUES (?, ?)");
    $default_user = "admin";
    $stmt->bind_param("ss", $default_user, $secure_hash);
    $stmt->execute();
    $stmt->close();
}

// 4. Auto-seed Sample Products if inventory is empty
$prod_check = $conn->query("SELECT id FROM products LIMIT 1");
if ($prod_check && $prod_check->num_rows === 0) {
    $conn->query("INSERT INTO products (name, category, size, color, rental_price, security_deposit, status) VALUES 
    ('Royal Maharaja Ivory Sherwani Set', 'Sherwani', '40', 'Ivory White with Gold Zardozi', 4500.00, 3000.00, 'Available'),
    ('Velvet Crimson Embroidered Indo-Western', 'Indo-Western', '42', 'Velvet Deep Crimson', 5200.00, 4000.00, 'Available'),
    ('Midnight Blue Luxury Italian Tuxedo', 'Suit & Tuxedo', '44', 'Midnight Blue', 3800.00, 3000.00, 'Available'),
    ('Emerald Green Brocade Jodhpuri Suit', 'Jodhpuri Suit', '38', 'Emerald Green', 4200.00, 3000.00, 'Available'),
    ('Banarasi Silk Hand-Tied Turban with Kalgi', 'Safa & Turban', 'Free Size', 'Pastel Pink & Gold', 1200.00, 1000.00, 'Available'),
    ('Handcrafted Golden Mojari with Pearl Embellishments', 'Mojari & Shoes', '9', 'Champagne Gold', 800.00, 500.00, 'Available')");
}

// 5. Dynamic Base URL Auto-detection
$script_name = $_SERVER['SCRIPT_NAME'];
$pos = strpos($script_name, '/dulha_collection');
$base_url = ($pos !== false) ? substr($script_name, 0, $pos) . '/dulha_collection' : '/dulha_collection';

// 6. Auth verification utility
function check_login() {
    global $base_url;
    if(!isset($_SESSION['user_id'])) {
        header("Location: " . $base_url . "/admin/index.php");
        exit();
    }
}
?>
