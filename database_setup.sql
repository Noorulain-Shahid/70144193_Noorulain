-- Database creation
CREATE DATABASE IF NOT EXISTS ecommerce_admin;
USE ecommerce_admin;

-- Users table (for admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role VARCHAR(50) DEFAULT 'Administrator',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Categories table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    icon VARCHAR(50) DEFAULT 'fas fa-box'
);

-- Products table
CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL,
    stock INT DEFAULT 0,
    category_id INT,
    image VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Customers table
CREATE TABLE IF NOT EXISTS customers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    phone VARCHAR(20),
    city VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    customer_id INT,
    customer_name VARCHAR(100), -- Storing name for easier display if customer is deleted
    order_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(50) DEFAULT 'Pending',
    total_amount DECIMAL(10, 2) NOT NULL,
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE SET NULL
);

-- Feedback table
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    user_name VARCHAR(100),
    email VARCHAR(100),
    message TEXT,
    rating INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin user
-- Password is 'admin123' hashed with BCRYPT
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@ecommerce.com', '$2y$10$K7lJ416rf4PQEO9bC40uWOsD46lfB.Bdmr2sy8jx1SG35rBW4537K', 'Administrator');

-- Insert sample categories
INSERT INTO categories (name, description, icon) VALUES 
('Jackets', 'Winter jackets and coats', 'fas fa-tshirt'),
('Hoodies', 'Warm hoodies and sweatshirts', 'fas fa-vest'),
('Sweaters', 'Cozy sweaters and pullovers', 'fas fa-user-tie'),
('Pants', 'Winter pants and cargo', 'fas fa-person');

-- Insert sample customers
INSERT INTO customers (name, email, phone, city) VALUES
('John Doe', 'john@example.com', '+1234567890', 'New York'),
('Jane Smith', 'jane@example.com', '+0987654321', 'London');

-- Insert sample products
INSERT INTO products (name, description, price, stock, category_id) VALUES
('Winter Jacket', 'Warm winter jacket', 99.99, 50, 1),
('Cotton Hoodie', 'Comfortable cotton hoodie', 49.99, 100, 2);

-- Insert sample orders
INSERT INTO orders (customer_id, customer_name, status, total_amount) VALUES
(1, 'John Doe', 'Delivered', 149.98),
(2, 'Jane Smith', 'Processing', 49.99);
