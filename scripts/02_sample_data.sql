-- Sample data for testing

-- Insert sample users
INSERT INTO users (name, email, password, role) VALUES 
('Cashier User', 'cashier@tialo.com', '$2y$10$YourHashedPasswordHere', 'Cashier');

-- Insert sample shipments
INSERT INTO shipments (date_received, time_received, supplier, driver_name, total_boxes) VALUES 
('2025-01-15', '09:00:00', 'Japan Import Co.', 'John Doe', 5),
('2025-01-16', '10:30:00', 'Asia Trading Ltd.', 'Maria Santos', 3);

-- Insert sample products
INSERT INTO products (shipment_id, name, category, quantity, price, status) VALUES 
(1, 'Rice Cooker', 'Appliances', 10, 2500.00, 'Available'),
(1, 'Microwave Oven', 'Appliances', 5, 4500.00, 'Available'),
(1, 'Dining Table Set', 'Furniture', 3, 8500.00, 'Available'),
(1, 'Office Chair', 'Furniture', 8, 3200.00, 'Available'),
(1, 'Kitchen Knife Set', 'Kitchenware', 12, 1200.00, 'Available'),
(2, 'Ceramic Plates Set', 'Kitchenware', 15, 800.00, 'Available'),
(2, 'Vacuum Cleaner', 'Appliances', 4, 3800.00, 'Available'),
(2, 'Bookshelf', 'Furniture', 6, 2800.00, 'Available'),
(2, 'Electric Fan', 'Appliances', 10, 1800.00, 'Available'),
(2, 'Coffee Table', 'Furniture', 4, 2200.00, 'Available');
