CREATE DATABASE IF NOT EXISTS shop_db;
USE shop_db;

-- Users table
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    user_type ENUM('admin', 'customer') DEFAULT 'customer',
    image VARCHAR(255) DEFAULT NULL
);

-- Products table
CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category VARCHAR(50) NOT NULL,
    details TEXT,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255) DEFAULT NULL,
    quantity INT NOT NULL,
    barcode VARCHAR(50) UNIQUE NOT NULL
);

-- Cart table
CREATE TABLE cart (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pid INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    quantity INT NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pid) REFERENCES products(id) ON DELETE CASCADE
);

-- Coupon table
CREATE TABLE coupon (
    couponID INT AUTO_INCREMENT PRIMARY KEY,
    couponCode VARCHAR(50) UNIQUE NOT NULL,
    productID INT NOT NULL,
    couponAmount DECIMAL(5, 2) CHECK (couponAmount >= 0 AND couponAmount <= 100),
    FOREIGN KEY (productID) REFERENCES products(id) ON DELETE CASCADE
);

-- Wishlist table
CREATE TABLE wishlist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    pid INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    image VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (pid) REFERENCES products(id) ON DELETE CASCADE
);

-- Orders table
CREATE TABLE orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    number VARCHAR(15) NOT NULL,
    email VARCHAR(100) NOT NULL,
    method ENUM('cash on delivery', 'credit card', 'paytm', 'paypal') NOT NULL,
    address TEXT NOT NULL,
    total_products TEXT NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    discount_applied DECIMAL(10, 2) DEFAULT 0,
    placed_on DATE NOT NULL DEFAULT CURRENT_DATE(),
    payment_status ENUM('pending', 'completed') DEFAULT 'pending',
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Message table
CREATE TABLE message (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    number VARCHAR(15) NOT NULL,
    message TEXT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

