-- Create database 
CREATE DATABASE IF NOT EXISTS tialo_posdb; 
USE tialo_posdb; 

-- USERS TABLE 
CREATE TABLE users ( 
  user_id INT AUTO_INCREMENT PRIMARY KEY, 
  name VARCHAR(100) NOT NULL, 
  email VARCHAR(100) UNIQUE NOT NULL, 
  password VARCHAR(255) NOT NULL, 
  role ENUM('Admin', 'Cashier') NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

-- SHIPMENTS TABLE 
CREATE TABLE shipments ( 
  shipment_id INT AUTO_INCREMENT PRIMARY KEY, 
  date_received DATE NOT NULL, 
  time_received TIME NOT NULL, 
  supplier VARCHAR(100) NOT NULL, 
  driver_name VARCHAR(100), 
  total_boxes INT DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
); 

-- PRODUCTS TABLE 
CREATE TABLE products ( 
  product_id INT AUTO_INCREMENT PRIMARY KEY, 
  shipment_id INT, 
  name VARCHAR(150) NOT NULL, 
  category VARCHAR(100), 
  quantity INT DEFAULT 0, 
  price DECIMAL(10,2) NOT NULL, 
  status ENUM('Available', 'Sold', 'Out of Stock') DEFAULT 'Available',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (shipment_id) REFERENCES shipments(shipment_id) 
    ON DELETE SET NULL ON UPDATE CASCADE 
); 

-- TRANSACTIONS TABLE 
CREATE TABLE transactions ( 
  transaction_id INT AUTO_INCREMENT PRIMARY KEY, 
  user_id INT, 
  transaction_date DATETIME DEFAULT CURRENT_TIMESTAMP, 
  payment_type ENUM('Cash', 'GCash', 'Installment') NOT NULL, 
  total_amount DECIMAL(10,2) NOT NULL, 
  FOREIGN KEY (user_id) REFERENCES users(user_id) 
    ON DELETE SET NULL ON UPDATE CASCADE 
); 

-- TRANSACTION ITEMS TABLE 
CREATE TABLE transaction_items ( 
  item_id INT AUTO_INCREMENT PRIMARY KEY, 
  transaction_id INT, 
  product_id INT, 
  quantity INT NOT NULL, 
  subtotal DECIMAL(10,2) NOT NULL, 
  FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) 
    ON DELETE CASCADE ON UPDATE CASCADE, 
  FOREIGN KEY (product_id) REFERENCES products(product_id) 
    ON DELETE SET NULL ON UPDATE CASCADE 
); 

-- INSTALLMENTS TABLE 
CREATE TABLE installments ( 
  installment_id INT AUTO_INCREMENT PRIMARY KEY, 
  transaction_id INT, 
  due_date DATE NOT NULL, 
  amount_due DECIMAL(10,2) NOT NULL, 
  balance_remaining DECIMAL(10,2) NOT NULL, 
  status ENUM('Paid', 'Unpaid') DEFAULT 'Unpaid',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (transaction_id) REFERENCES transactions(transaction_id) 
    ON DELETE CASCADE ON UPDATE CASCADE 
);

-- Insert sample admin user (password: admin123)
INSERT INTO users (name, email, password, role) VALUES 
('Admin User', 'admin@tialo.com', '$2y$10$YourHashedPasswordHere', 'Admin');
