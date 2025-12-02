-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Nov 17, 2025 at 05:49 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tialo_posdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `installments`
--

CREATE TABLE `installments` (
  `installment_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `due_date` date NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `balance_remaining` decimal(10,2) NOT NULL,
  `status` enum('Paid','Unpaid') DEFAULT 'Unpaid',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `installments`
--

INSERT INTO `installments` (`installment_id`, `transaction_id`, `due_date`, `amount_due`, `balance_remaining`, `status`, `created_at`) VALUES
(1, 2, '2025-12-12', 600.00, 1800.00, 'Unpaid', '2025-11-12 15:45:41'),
(2, 2, '2026-01-12', 600.00, 1200.00, 'Unpaid', '2025-11-12 15:45:41'),
(3, 2, '2026-02-12', 600.00, 600.00, 'Unpaid', '2025-11-12 15:45:41');

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `shipment_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `status` enum('Available','Sold','Out of Stock') DEFAULT 'Available',
  `image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `shipment_id`, `name`, `category`, `quantity`, `price`, `status`, `image`, `created_at`) VALUES
(1, 1, 'Rice Cooker', 'Appliances', 10, 2500.00, 'Available', NULL, '2025-10-23 03:01:01'),
(2, 1, 'Microwave Oven', 'Appliances', 5, 4500.00, 'Available', NULL, '2025-10-23 03:01:01'),
(3, 1, 'Dining Table Set', 'Furniture', 3, 8500.00, 'Available', NULL, '2025-10-23 03:01:01'),
(4, 1, 'Office Chair', 'Furniture', 8, 3200.00, 'Available', NULL, '2025-10-23 03:01:01'),
(5, 1, 'Kitchen Knife Set', 'Kitchenware', 12, 1200.00, 'Available', NULL, '2025-10-23 03:01:01'),
(6, 2, 'Ceramic Plates Set', 'Kitchenware', 15, 800.00, 'Available', NULL, '2025-10-23 03:01:01'),
(7, 2, 'Vacuum Cleaner', 'Appliances', 4, 3800.00, 'Available', NULL, '2025-10-23 03:01:01'),
(8, 2, 'Bookshelf', 'Furniture', 4, 2800.00, 'Available', 'products/product_6919920d498d23.07876977.jpg', '2025-10-23 03:01:01'),
(9, 2, 'Electric Fan', 'Appliances', 7, 1800.00, 'Available', NULL, '2025-10-23 03:01:01'),
(10, 2, 'Coffee Table', 'Furniture', 3, 2200.00, 'Available', 'products/product_69199072e51444.04265576.jpg', '2025-10-23 03:01:01'),
(11, NULL, 'Speaker', 'Appliances', 17, 500.00, 'Available', 'products/product_691997da7a0434.49273608.jpg', '2025-11-16 08:52:54'),
(12, 1, 'Mug', 'Kitchenware', 20, 100.00, 'Available', 'products/product_69199421168bf4.55710717.png', '2025-11-16 08:56:46');

-- --------------------------------------------------------

--
-- Table structure for table `shipments`
--

CREATE TABLE `shipments` (
  `shipment_id` int(11) NOT NULL,
  `date_received` date NOT NULL,
  `time_received` time NOT NULL,
  `supplier` varchar(100) NOT NULL,
  `driver_name` varchar(100) DEFAULT NULL,
  `total_boxes` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipments`
--

INSERT INTO `shipments` (`shipment_id`, `date_received`, `time_received`, `supplier`, `driver_name`, `total_boxes`, `created_at`) VALUES
(1, '2025-01-15', '09:00:00', 'Japan Import Co.', 'John Doe', 5, '2025-10-23 03:01:01'),
(2, '2025-01-16', '10:30:00', 'Asia Trading Ltd.', 'Maria Santos', 3, '2025-10-23 03:01:01'),
(3, '2025-10-23', '11:05:00', 'Supplier1', 'driver1', 50, '2025-10-23 03:05:51');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `transaction_date` datetime DEFAULT current_timestamp(),
  `payment_type` enum('Cash','GCash','Installment') NOT NULL,
  `total_amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `transactions` ADD `discount_amount` DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER `total_amount`;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `transaction_date`, `payment_type`, `total_amount`) VALUES
(1, 1, '2025-11-12 09:50:43', 'Cash', 1800.00),
(2, 3, '2025-11-12 23:45:41', 'Installment', 1800.00),
(3, 1, '2025-11-13 22:05:16', 'Cash', 2800.00),
(4, 1, '2025-11-16 16:06:27', 'Cash', 1800.00),
(5, 1, '2025-11-17 15:40:43', 'Cash', 500.00),
(6, 1, '2025-11-17 16:05:49', 'GCash', 3300.00),
(7, 1, '2025-11-17 16:20:03', 'Cash', 2200.00),
(8, 1, '2025-11-17 16:47:51', 'Cash', 400.00);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_items`
--

CREATE TABLE `transaction_items` (
  `item_id` int(11) NOT NULL,
  `transaction_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_items`
--

INSERT INTO `transaction_items` (`item_id`, `transaction_id`, `product_id`, `quantity`, `subtotal`) VALUES
(1, 1, 9, 1, 1800.00),
(2, 2, 9, 1, 1800.00),
(3, 3, 8, 1, 2800.00),
(4, 4, 9, 1, 1800.00),
(5, 5, 11, 1, 500.00),
(6, 6, 11, 1, 500.00),
(7, 6, 8, 1, 2800.00),
(8, 7, 10, 1, 2200.00),
(9, 8, 11, 1, 500.00);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('Admin','Cashier') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Admin User', 'admin@tialo.com', '$2y$10$80IhiHI6SWz8BiZ6r0dmQu0T1y6xIhykpdnT.rcBmWq0uKF4kT8M6', 'Admin', '2025-10-23 03:00:43'),
(2, 'Cashier User', 'cashier@tialo.com', '$2y$10$7qbChjbDPNDZAubC/8v8z.RV6NOUvJ2yXKu6Bv067Jlwg.qGn95Lq', 'Cashier', '2025-10-23 03:01:01'),
(3, 'Anastacia Reiza Almodiel', 'reiza@tialo.com', '$2y$10$OR4mPt/Y56sZqt8wkGkUxOeTiiy1rW/G7cJBb58APzdHjbfoKEqyK', 'Admin', '2025-11-12 14:40:27');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `installments`
--
ALTER TABLE `installments`
  ADD PRIMARY KEY (`installment_id`),
  ADD KEY `transaction_id` (`transaction_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `shipment_id` (`shipment_id`);

--
-- Indexes for table `shipments`
--
ALTER TABLE `shipments`
  ADD PRIMARY KEY (`shipment_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `installments`
--
ALTER TABLE `installments`
  MODIFY `installment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `shipments`
--
ALTER TABLE `shipments`
  MODIFY `shipment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `transaction_items`
--
ALTER TABLE `transaction_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `installments`
--
ALTER TABLE `installments`
  ADD CONSTRAINT `installments_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`shipment_id`) REFERENCES `shipments` (`shipment_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `transaction_items`
--
ALTER TABLE `transaction_items`
  ADD CONSTRAINT `transaction_items_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`transaction_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `transaction_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

 D E L I M I T E R   
 
 C R E A T E   T R I G G E R   a f t e r _ t r a n s a c t i o n _ i t e m _ i n s e r t 
 A F T E R   I N S E R T   O N   t r a n s a c t i o n _ i t e m s 
 F O R   E A C H   R O W 
 B E G I N 
         D E C L A R E   p r o d u c t _ q u a n t i t y   I N T ; 
 
         - -   G e t   t h e   c u r r e n t   q u a n t i t y   o f   t h e   p r o d u c t 
         S E L E C T   q u a n t i t y   I N T O   p r o d u c t _ q u a n t i t y 
         F R O M   p r o d u c t s 
         W H E R E   p r o d u c t _ i d   =   N E W . p r o d u c t _ i d ; 
 
         - -   I f   t h e   q u a n t i t y   i s   0 ,   u p d a t e   t h e   s t a t u s   t o   ' O u t   o f   S t o c k ' 
         I F   p r o d u c t _ q u a n t i t y   =   0   T H E N 
                 U P D A T E   p r o d u c t s 
                 S E T   s t a t u s   =   ' O u t   o f   S t o c k ' 
                 W H E R E   p r o d u c t _ i d   =   N E W . p r o d u c t _ i d ; 
         E N D   I F ; 
 E N D 
 
 D E L I M I T E R   ; 
  
 