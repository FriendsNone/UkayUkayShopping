-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: May 05, 2023 at 12:24 AM
-- Server version: 10.11.2-MariaDB
-- PHP Version: 7.4.33

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ukayukayshopping`
--

-- --------------------------------------------------------

--
-- Table structure for table `customer`
--

CREATE TABLE `customer` (
  `customer_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) NOT NULL,
  `address` varchar(255) NOT NULL,
  `email_address` varchar(255) NOT NULL,
  `phone_number` varchar(20) NOT NULL,
  `password` varchar(255) NOT NULL,
  `customer_picture` varchar(255) DEFAULT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `randomness` varchar(32) DEFAULT NULL,
  `date_registered` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `feedback`
--

CREATE TABLE `feedback` (
  `feedback_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `sales_id` int(11) NOT NULL,
  `experience` int(11) DEFAULT NULL,
  `loved` text DEFAULT NULL,
  `improve` text DEFAULT NULL,
  `comment` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product`
--

CREATE TABLE `product` (
  `product_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `category` int(11) NOT NULL DEFAULT 1,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `product_picture` varchar(255) DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product`
--

INSERT INTO `product` (`product_id`, `name`, `price`, `description`, `category`, `quantity`, `product_picture`, `date_added`, `date_updated`) VALUES
(1, 'T-Shirt', 316.62, 'a man holding a nintendo wii game controller\r\na man is playing a video game in a living room\r\na man standing in a room holding a wii remote\r\na man standing in a living room holding a wii remote\r\na man standing in a living room holding a remote\r\na man standing in a room holding a remote\r\na man standing in a living room holding a wii controller\r\na man standing in a living room with a remote\r\na man standing in a living room holding a nintendo wii controller\r\na man holding a nintendo wii controller in his hands', 1, 33, '64285add9b47c.jpg', '2023-04-01 16:25:01', '2023-04-02 14:49:24'),
(2, 'T-Shirt 2', 441.40, 'a man wearing a hat and a tie\r\na man wearing a red shirt and tie\r\na man wearing a suit and tie standing next to a woman\r\na man in a suit and tie standing next to a woman\r\na man wearing a suit and tie standing next to a man\r\na man in a suit and tie standing next to a man\r\na man wearing a suit and tie standing in the snow\r\na man in a suit and tie standing in the snow\r\na man in a suit and tie standing in front of a building\r\na man in a suit and tie standing in front of a wall', 1, 21, '64285b607ac63.jpg', '2023-04-01 16:27:12', '2023-04-02 14:49:27'),
(3, 'T-Shirt 3', 5.29, 'a couple of men standing next to each other\r\na couple of people that are standing in the snow\r\na man and a woman standing next to each other\r\na couple of people that are standing in the street\r\na couple of men standing next to each other in a room\r\na couple of men standing next to each other on a street\r\na couple of men standing next to each other on a tennis court\r\na couple of men standing next to each other in front of a building\r\na man in a suit and tie standing in front of a building\r\na man in a suit and tie standing next to a man', 1, 11, '64285ba43e0a5.jpg', '2023-04-01 16:28:20', '2023-04-02 14:49:30'),
(4, 'T-Shirt 4', 290.67, 'a man sitting on a bench in a park\r\na man holding a frisbee in his hands\r\na man and a woman sitting on a bench\r\na man sitting on a bench with a cell phone\r\na man sitting on a bench with a laptop\r\na man sitting on a bench looking at a cell phone\r\na man sitting on a bench with a dog\r\na man sitting on a bench in front of a building\r\na man sitting on a bench looking at his cell phone\r\na man sitting on a bench next to a woman', 1, 6, '64285bbb976d0.jpg', '2023-04-01 16:28:43', '2023-04-02 14:49:31'),
(5, 'T-Shirt 5', 352.20, 'a close up of a person holding a cell phone\r\na close up of a person wearing a tie\r\na close up of a person wearing a suit and tie\r\na man wearing a white shirt and tie\r\na man wearing a hat and a tie\r\na man wearing a white shirt and a tie\r\na close up of a person wearing a suit\r\na close up of a man wearing a tie\r\na close up of a person holding a remote\r\na close up of a person wearing a tie and a tie', 1, 2, '64285bd01e182.jpg', '2023-04-01 16:29:04', '2023-04-02 14:49:35'),
(6, 'T-Shirt 6', 402.30, 'a baseball player holding a bat on a field\r\na man holding a baseball bat on a field\r\na baseball player holding a bat on top of a field\r\na man holding a baseball bat in his hands\r\na baseball player holding a baseball bat on a field\r\na man holding a frisbee in his hands\r\na man in a baseball uniform holding a bat\r\na man standing on a field holding a baseball bat\r\na man standing on a lush green field holding a frisbee\r\na man standing on a lush green field holding a baseball bat\r\n', 1, 0, '64285be429b44.jpg', '2023-04-01 16:29:24', '2023-04-01 16:29:24'),
(7, 'T-Shirt 7', 166.95, 'a man holding a tennis racquet on a tennis court\r\na man holding a frisbee in his hands\r\na man standing in a field with a frisbee\r\na man standing on top of a sandy beach\r\na man standing on top of a lush green field\r\na man holding a tennis racket on a tennis court\r\na man standing in a field holding a frisbee\r\na man and a woman standing next to each other\r\na man standing on top of a sandy beach holding a surfboard\r\na man and a woman standing next to each other on a field', 1, 9, '64285bfed076b.jpg', '2023-04-01 16:29:50', '2023-04-02 14:49:39'),
(8, 'T-Shirt 8', 256.43, 'a man standing on a beach holding a surfboard\r\na man standing on a beach flying a kite\r\na man standing on a beach holding a frisbee\r\na man standing on a beach holding a surf board\r\na man standing in the sand holding a surfboard\r\na man holding a surfboard in the sand\r\na man holding a surfboard on top of a beach\r\na man standing on a beach with a surfboard\r\na man standing in the sand with a surfboard\r\na man holding a surfboard on top of a sandy beach', 1, 39, '64285c1e7f61f.jpg', '2023-04-01 16:30:22', '2023-04-02 14:49:43'),
(9, 'T-Shirt 9', 399.60, 'a man and a woman standing next to each other\r\na man in a suit standing in front of a building\r\na man in a suit and tie standing in front of a building\r\na man standing next to a woman in a suit\r\na man in a suit and tie standing next to a woman\r\na man wearing a suit and tie standing in front of a building\r\na man in a suit and tie standing next to a man\r\na man and a woman standing next to each other in a room\r\na man and a woman standing next to each other on a street\r\na man and a woman standing next to each other in front of a building', 1, 22, '64285c32202d2.jpg', '2023-04-01 16:30:42', '2023-04-02 14:49:46'),
(10, 'T-Shirt 10', 72.73, 'a man holding a tennis racquet on a tennis court\r\na man standing on a tennis court holding a racquet\r\na man holding a tennis racket on a tennis court\r\na woman holding a tennis racquet on a tennis court\r\na woman holding a tennis racket on a tennis court\r\na man holding a tennis racket in his hands\r\na man holding a tennis racquet on top of a tennis court\r\na man holding a tennis racket in his hand\r\na man standing on a tennis court holding a racket\r\na man standing on a tennis court holding a tennis racquet', 1, 6, '64285c43c1d83.jpg', '2023-04-01 16:30:59', '2023-04-02 14:49:49');

-- --------------------------------------------------------

--
-- Table structure for table `product_category`
--

CREATE TABLE `product_category` (
  `category_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_category`
--

INSERT INTO `product_category` (`category_id`, `name`) VALUES
(1, 'Uncategorized');

-- --------------------------------------------------------

--
-- Table structure for table `sales_order`
--

CREATE TABLE `sales_order` (
  `sales_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `items` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `status` varchar(15) NOT NULL DEFAULT 'PENDING',
  `date_ordered` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_order_item`
--

CREATE TABLE `sales_order_item` (
  `sales_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `customer`
--
ALTER TABLE `customer`
  ADD PRIMARY KEY (`customer_id`);

--
-- Indexes for table `feedback`
--
ALTER TABLE `feedback`
  ADD PRIMARY KEY (`feedback_id`),
  ADD KEY `customer_id` (`customer_id`),
  ADD KEY `sales_id` (`sales_id`);

--
-- Indexes for table `product`
--
ALTER TABLE `product`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `category` (`category`);

--
-- Indexes for table `product_category`
--
ALTER TABLE `product_category`
  ADD PRIMARY KEY (`category_id`);

--
-- Indexes for table `sales_order`
--
ALTER TABLE `sales_order`
  ADD PRIMARY KEY (`sales_id`),
  ADD KEY `FK_customer_id` (`customer_id`);

--
-- Indexes for table `sales_order_item`
--
ALTER TABLE `sales_order_item`
  ADD PRIMARY KEY (`sales_id`,`product_id`),
  ADD KEY `FK_product_id` (`product_id`),
  ADD KEY `FK_sales_id` (`sales_id`) USING BTREE;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `customer`
--
ALTER TABLE `customer`
  MODIFY `customer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `feedback`
--
ALTER TABLE `feedback`
  MODIFY `feedback_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product`
--
ALTER TABLE `product`
  MODIFY `product_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `product_category`
--
ALTER TABLE `product_category`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sales_order`
--
ALTER TABLE `sales_order`
  MODIFY `sales_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `feedback`
--
ALTER TABLE `feedback`
  ADD CONSTRAINT `CONST_customer` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  ADD CONSTRAINT `CONST_sales_feedback` FOREIGN KEY (`sales_id`) REFERENCES `sales_order` (`sales_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Constraints for table `product`
--
ALTER TABLE `product`
  ADD CONSTRAINT `CONST_category` FOREIGN KEY (`category`) REFERENCES `product_category` (`category_id`);

--
-- Constraints for table `sales_order_item`
--
ALTER TABLE `sales_order_item`
  ADD CONSTRAINT `CONST_product` FOREIGN KEY (`product_id`) REFERENCES `product` (`product_id`),
  ADD CONSTRAINT `CONST_sales` FOREIGN KEY (`sales_id`) REFERENCES `sales_order` (`sales_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
