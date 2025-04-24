-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Εξυπηρετητής: 127.0.0.1
-- Χρόνος δημιουργίας: 15 Απρ 2025 στις 20:24:31
-- Έκδοση διακομιστή: 10.4.32-MariaDB
-- Έκδοση PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Βάση δεδομένων: `supermarket`
--

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `employees`
--

CREATE TABLE `employees` (
  `employee_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `salary` decimal(10,2) DEFAULT NULL,
  `id_number` varchar(50) NOT NULL,
  `store_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `order_date` date NOT NULL,
  `supplier_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `products`
--

CREATE TABLE `products` (
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `photo_source` text DEFAULT NULL,
  `new_prod` tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `products`
--

INSERT INTO `products` (`product_id`, `name`, `type`, `manufacturer`, `price`, `photo_source`, `new_prod`) VALUES
(1, 'Milk', 'Dairy', 'BrandA', 1.99, 'images	humb-Milk.png', 0),
(2, 'Bread', 'Bakery', 'BrandB', 2.49, 'images	humb-Bread.png', 0),
(3, 'Butter', 'Dairy', 'BrandC', 3.79, 'images	humb-Butter.png', 0),
(4, 'Eggs', 'Dairy', 'FarmFresh', 2.99, 'images	humb-Eggs.png', 0),
(5, 'Chicken Breast', 'Meat', 'FreshMeat', 5.99, 'images	humb-Chicken Breast.png', 0),
(6, 'Ground Beef', 'Meat', 'FreshMeat', 4.99, 'images	humb-Ground Beef.png', 0),
(7, 'Apple', 'Fruit', 'LocalFarm', 0.99, 'images	humb-Apple.png', 0),
(8, 'Banana', 'Fruit', 'FreshFarm', 0.59, 'images	humb-Banana.png', 0),
(9, 'Orange Juice', 'Drinks', 'PureO', 3.49, 'images	humb-Orange Juice.png', 0),
(10, 'Cola', 'Drinks', 'SodaCo', 1.49, 'images	humb-Cola.png', 0),
(11, 'Rice', 'Grains', 'BrandD', 1.69, 'images	humb-Rice.png', 0),
(12, 'Pasta', 'Grains', 'BrandE', 1.29, 'images	humb-Pasta.png', 0),
(13, 'Tomato Sauce', 'Sauces', 'SaucyDelight', 2.49, 'images	humb-Tomato Sauce.png', 0),
(14, 'Canned Tuna', 'Canned Goods', 'OceanFresh', 1.89, 'images	humb-Canned Tuna.png', 0),
(15, 'Toilet Paper', 'Household', 'CleanHome', 5.99, 'images	humb-Toilet Paper.png', 0),
(16, 'Shampoo', 'Personal Care', 'FreshLocks', 4.49, 'images	humb-Shampoo.png', 0),
(17, 'Toothpaste', 'Personal Care', 'BrightSmile', 2.99, 'images	humb-Toothpaste.png', 0),
(18, 'Washing Powder', 'Household', 'CleanWiz', 6.99, 'images	humb-Washing Powder.png', 0),
(19, 'Frozen Pizza', 'Frozen Food', 'HotBites', 4.99, 'images/thumb-Frozen Pizza.png', 1),
(20, 'Ice Cream', 'Frozen Food', 'SweetTreats', 3.49, 'images	humb-Ice Cream.png', 0),
(21, 'Potato Chips', 'Snacks', 'SackMaster', 1.79, 'images	humb-Potato Chips.png', 0),
(22, 'Chocolate Bar', 'Snacks', 'ChocoDelights', 1.29, 'images	humb-Chocolate Bar.png', 0),
(23, 'Soda', 'Drinks', 'RefreshCo', 1.19, 'images	humb-Soda.png', 0),
(24, 'Beer', 'Drinks', 'BrewMasters', 4.99, 'images	humb-Beer.png', 0),
(25, 'Wine', 'Drinks', 'FineVines', 9.99, 'images	humb-Wine.png', 0),
(26, 'Soap', 'Household', 'CleanBar', 2.49, 'images	humb-Soap.png', 0),
(27, 'Dishwasher Tablets', 'Household', 'CleanWiz', 7.49, 'images	humb-Dishwasher Tablets.png', 0),
(28, 'Bleach', 'Household', 'PureClean', 3.29, 'images	humb-Bleach.png', 0),
(29, 'Baby Diapers', 'Baby Care', 'BabySoft', 6.99, 'images	humb-Baby Diapers.png', 0),
(30, 'Baby Wipes', 'Baby Care', 'PureBaby', 4.49, 'images	humb-Baby Wipes.png', 0),
(31, 'Hand Sanitizer', 'Personal Care', 'HealthGuard', 2.99, 'images	humb-Hand Sanitizer.png', 0),
(32, 'Face Cream', 'Personal Care', 'SkinGlow', 5.29, 'images	humb-Face Cream.png', 0),
(33, 'Hair Conditioner', 'Personal Care', 'FreshLocks', 4.69, 'images	humb-Hair Conditioner.png', 0),
(34, 'Deodorant', 'Personal Care', 'FreshScent', 3.99, 'images	humb-Deodorant.png', 0),
(35, 'Coffee Beans', 'Drinks', 'BrewPerfect', 5.99, 'images	humb-Coffee Beans.png', 0),
(36, 'Instant Noodles', 'Canned Goods', 'NoodleKing', 0.99, 'images	humb-Instant Noodles.png', 0),
(37, 'Sweet Corn', 'Canned Goods', 'FarmFresh', 1.49, 'images	humb-Sweet Corn.png', 0),
(38, 'Canned Beans', 'Canned Goods', 'BrandF', 1.29, 'images	humb-Canned Beans.png', 0),
(39, 'Frozen Vegetables', 'Frozen Food', 'VeggieDelight', 3.79, 'images	humb-Frozen Vegetables.png', 0),
(40, 'Frozen Fries', 'Frozen Food', 'FryMaster', 2.99, 'images	humb-Frozen Fries.png', 0),
(41, 'Frozen Chicken Wings', 'Frozen Food', 'WingWorld', 5.49, 'images	humb-Frozen Chicken Wings.png', 0),
(42, 'Hot Sauce', 'Sauces', 'SpicyDelight', 1.99, 'images	humb-Hot Sauce.png', 0),
(43, 'Olive Oil', 'Oils & Vinegars', 'OliveGold', 6.99, 'images	humb-Olive Oil.png', 0),
(44, 'Vinegar', 'Oils & Vinegars', 'PureVinegar', 2.49, 'images	humb-Vinegar.png', 0),
(45, 'Salt', 'Spices', 'PureSalt', 0.99, 'images	humb-Salt.png', 0),
(46, 'Pepper', 'Spices', 'SpiceCo', 1.29, 'images	humb-Pepper.png', 0),
(47, 'Garlic Powder', 'Spices', 'SpiceMaster', 2.49, 'images	humb-Garlic Powder.png', 0),
(48, 'Ginger', 'Spices', 'FreshRoots', 3.19, 'images	humb-Ginger.png', 0),
(49, 'Almonds', 'Snacks', 'NutHouse', 4.99, 'images	humb-Almonds.png', 0),
(50, 'Cashews', 'Snacks', 'NutHouse', 5.49, 'images	humb-Cashews.png', 0),
(51, 'Granola Bars', 'Snacks', 'NatureBites', 2.99, 'images	humb-Granola Bars.png', 0),
(52, 'Frozen Shrimp', 'Frozen Food', 'OceanHarvest', 7.49, 'images	humb-Frozen Shrimp.png', 0),
(53, 'Frozen Salmon', 'Frozen Food', 'OceanDelight', 8.99, 'images	humb-Frozen Salmon.png', 0),
(54, 'Frozen Vegetables Mix', 'Frozen Food', 'VeggieDelight', 3.29, 'images	humb-Frozen Vegetables Mix.png', 0),
(55, 'Cereal', 'Breakfast Foods', 'BrandG', 3.99, 'images	humb-Cereal.png', 0),
(56, 'Oatmeal', 'Breakfast Foods', 'HealthyOats', 2.99, 'images	humb-Oatmeal.png', 0),
(57, 'Pancake Mix', 'Breakfast Foods', 'BrandH', 2.49, 'images	humb-Pancake Mix.png', 0),
(58, 'Maple Syrup', 'Breakfast Foods', 'SweetDrizzle', 4.99, 'images	humb-Maple Syrup.png', 0),
(59, 'Honey', 'Sweeteners', 'Nature\'sBest', 5.49, 'images	humb-Honey.png', 0),
(60, 'Peanut Butter', 'Snacks', 'NuttyDelights', 3.69, 'images	humb-Peanut Butter.png', 0),
(61, 'Jam', 'Spreads', 'BerryGood', 2.79, 'images	humb-Jam.png', 0);

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `stores`
--

CREATE TABLE `stores` (
  `store_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL,
  `warehouse_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `store_inventory`
--

CREATE TABLE `store_inventory` (
  `store_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `minimum_stock` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `supplier_product`
--

CREATE TABLE `supplier_product` (
  `supplier_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `sale_price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Άδειασμα δεδομένων του πίνακα `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `created_at`, `updated_at`) VALUES
(1, 'ab', 'chiefpower.go@gmail.com', '$2y$10$2L.dBKXNEIWCq1x7.2aLVu29hGkxwVuOteymYDYH3A3VAStqgIRL2', '2025-04-09 21:01:40', '2025-04-09 21:01:40'),
(3, 'ab2', 'chiefpower.go@gmail.com4', '$2y$10$ZVUXugLJgDL2KCwHWwcUX.cvIJYgGHsJ013uwcvCiCRJZehp27SCa', '2025-04-09 21:05:34', '2025-04-09 21:05:34'),
(7, 'qw', 'chiefpower.go1@gmail.com', '$2y$10$RUR6WFqxLIDsGfNxA4dP5uUVZpI79FMzzUxB9iQw4mHbXpNDoNEiq', '2025-04-12 12:28:34', '2025-04-12 12:28:34'),
(11, 'qwe', 'chiefpower.go2@gmail.com', '$2y$10$yNdilfWLqv9z1zI2Folki.41lgnQTv/S.TcZOz84Gmo1H5T95Er.W', '2025-04-12 12:34:51', '2025-04-12 12:34:51'),
(14, 'qw2', 'chiefpower.go3@gmail.com', '$2y$10$/jemjwALDDN1cC9/yA0BjuumINf9i5ZG6KQY/8ke3u8kkceKpdDay', '2025-04-12 12:37:31', '2025-04-12 12:37:31'),
(18, 'ab5', 'chiefpower.go4@gmail.com', '$2y$10$QDohtmPG8V1b.eaAtrJN..R9vQoPL9LD1SJ/1QD9UNgcZB141yxj6', '2025-04-12 12:59:39', '2025-04-12 12:59:39'),
(23, 'ab4', 'chiefpower.g2o@gmail.com', '$2y$10$Y.XCa3Fshh95YdrDjJ5iou8i5xiBHvgHlT2R/Hvo8QbHniAuWz7NS', '2025-04-12 13:14:32', '2025-04-12 13:14:32');

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `warehouses`
--

CREATE TABLE `warehouses` (
  `warehouse_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Δομή πίνακα για τον πίνακα `warehouse_inventory`
--

CREATE TABLE `warehouse_inventory` (
  `warehouse_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Ευρετήρια για άχρηστους πίνακες
--

--
-- Ευρετήρια για πίνακα `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `id_number` (`id_number`),
  ADD KEY `store_id` (`store_id`);

--
-- Ευρετήρια για πίνακα `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Ευρετήρια για πίνακα `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`);

--
-- Ευρετήρια για πίνακα `stores`
--
ALTER TABLE `stores`
  ADD PRIMARY KEY (`store_id`),
  ADD KEY `warehouse_id` (`warehouse_id`);

--
-- Ευρετήρια για πίνακα `store_inventory`
--
ALTER TABLE `store_inventory`
  ADD PRIMARY KEY (`store_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Ευρετήρια για πίνακα `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`);

--
-- Ευρετήρια για πίνακα `supplier_product`
--
ALTER TABLE `supplier_product`
  ADD PRIMARY KEY (`supplier_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Ευρετήρια για πίνακα `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Ευρετήρια για πίνακα `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`warehouse_id`);

--
-- Ευρετήρια για πίνακα `warehouse_inventory`
--
ALTER TABLE `warehouse_inventory`
  ADD PRIMARY KEY (`warehouse_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT για άχρηστους πίνακες
--

--
-- AUTO_INCREMENT για πίνακα `employees`
--
ALTER TABLE `employees`
  MODIFY `employee_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT για πίνακα `stores`
--
ALTER TABLE `stores`
  MODIFY `store_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT για πίνακα `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT για πίνακα `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `warehouse_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Περιορισμοί για άχρηστους πίνακες
--

--
-- Περιορισμοί για πίνακα `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`);

--
-- Περιορισμοί για πίνακα `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`);

--
-- Περιορισμοί για πίνακα `stores`
--
ALTER TABLE `stores`
  ADD CONSTRAINT `stores_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`);

--
-- Περιορισμοί για πίνακα `store_inventory`
--
ALTER TABLE `store_inventory`
  ADD CONSTRAINT `store_inventory_ibfk_1` FOREIGN KEY (`store_id`) REFERENCES `stores` (`store_id`),
  ADD CONSTRAINT `store_inventory_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Περιορισμοί για πίνακα `supplier_product`
--
ALTER TABLE `supplier_product`
  ADD CONSTRAINT `supplier_product_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `suppliers` (`supplier_id`),
  ADD CONSTRAINT `supplier_product_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Περιορισμοί για πίνακα `warehouse_inventory`
--
ALTER TABLE `warehouse_inventory`
  ADD CONSTRAINT `warehouse_inventory_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`),
  ADD CONSTRAINT `warehouse_inventory_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
