-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3307
-- Generation Time: Mar 10, 2026 at 08:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `food_delivery_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `Admin_Approve_Restaurant` (IN `p_request_id` INT, IN `p_restaurant_id` INT, IN `p_admin_id` INT)   BEGIN
    UPDATE restaurant_request 
    SET status = 'Approved', decision_date = NOW(), admin_id = p_admin_id 
    WHERE request_id = p_request_id;

    UPDATE RESTAURANT 
    SET status = 'Open' 
    WHERE restaurant_id = p_restaurant_id;

    INSERT INTO action_log (admin_id, action, remark, action_date)
    VALUES (p_admin_id, 'APPROVE_RESTAURANT', CONCAT('Approved Restaurant ID: ', p_restaurant_id), NOW());
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Safe_Customer_Cancel` (IN `p_order_id` INT, IN `p_user_id` INT)   BEGIN
    DECLARE current_status VARCHAR(50);

    -- getscurrent status of the order 
    SELECT status INTO current_status 
    FROM orders 
    WHERE order_id = p_order_id AND user_id = p_user_id;

    -- cancel only if its placed
    IF current_status = 'Placed' THEN
        -- 1. Cancel the Order
        UPDATE orders SET status = 'Cancelled' WHERE order_id = p_order_id;
        
        -- stop delivery
        UPDATE delivery SET delivery_status = 'Cancelled' WHERE order_id = p_order_id;
        
        -- triger refund
        UPDATE payment SET payment_status = 'Refunded' WHERE order_id = p_order_id;
    ELSE
        -- no cancelation if restaurant accept order
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Order cannot be cancelled as it is already being prepared or dispatched.';
    END IF;

END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `Update_Order_Status` (IN `p_order_id` INT, IN `p_new_status` VARCHAR(50))   BEGIN
    -- master order
    UPDATE orders SET status = p_new_status WHERE order_id = p_order_id;
    
    -- cascading logic
    IF p_new_status = 'Preparing' THEN
        UPDATE delivery SET delivery_status = 'Out for Delivery' WHERE order_id = p_order_id;
    ELSEIF p_new_status = 'Delivered' THEN
        UPDATE delivery SET delivery_status = 'Delivered', delivered_at = NOW() WHERE order_id = p_order_id;
    ELSEIF p_new_status = 'Cancelled' THEN
        UPDATE delivery SET delivery_status = 'Cancelled' WHERE order_id = p_order_id;
        UPDATE payment SET payment_status = 'Refunded' WHERE order_id = p_order_id;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `accounts`
--

CREATE TABLE `accounts` (
  `account_id` int(11) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `accounts`
--

INSERT INTO `accounts` (`account_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `status`, `created_at`) VALUES
(1, 'Shreyank', 'Desai', 'shreyankdessai6@gmail.com', '$2y$10$XfXRVZY30GDmgkJrkC0Wq.0Xgf5emPdCnDC56.XzQd0xkKqoNIVD2', '8208623759', 'Active', '2026-02-10 12:53:22'),
(2, 'Neeraj', 'Aroskar', 'neeraj@gmail.com', '$2y$10$86CO4vrC2Z.kKHom.liJqOCzypEcO/f3LrXCc0q/tYmkTNn6F3SIK', '9889122145', 'Active', '2026-02-11 11:12:47'),
(3, 'Vighnesh', 'Raikar', 'vighnesh@gmail.com', '$2y$10$caDjJTeOFJ3/UWn8d3SeROwuK8XULxxCpBK7GhlkxjJiHKJQPkYme', NULL, 'Active', '2026-02-12 13:00:24'),
(20, 'Aryan', 'Naik', 'aryan@fishermans.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9011002233', 'Active', '2026-02-20 19:35:07'),
(21, 'Deepa', 'Sardesai', 'deepa@viva.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9011445566', 'Active', '2026-02-20 19:35:07'),
(22, 'Kevin', 'Dsilva', 'kevin@pizzahub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9011778899', 'Active', '2026-02-20 19:35:07'),
(23, 'Sana', 'Khan', 'sana@biryani.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9022112233', 'Active', '2026-02-20 19:35:07'),
(24, 'Rahul', 'Verma', 'rahul@tandoor.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9022445566', '', '2026-02-20 19:35:07'),
(25, 'Maria', 'Gomes', 'maria@bakery.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '9022778899', 'Active', '2026-02-20 19:35:07'),
(26, 'Roslin', '', 'roslin69@gmail.com', '$2y$10$uXAGk1McorE66I5P0ikIUeA0pCUuvPP2LXl8FQL3tEV44qiE.ILrK', '9090909090', 'Active', '2026-03-10 05:47:20'),
(27, 'Shreyank', 'Desai', 'shreyankds22@gmail.com', '$2y$10$Hqh1U0mwiw.ivi14LAGsIejGQhS55zYz1Qi5aWq8juiodMow9qrXq', '9604935847', 'Active', '2026-03-10 07:17:40');

-- --------------------------------------------------------

--
-- Table structure for table `action_log`
--

CREATE TABLE `action_log` (
  `log_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` varchar(100) NOT NULL,
  `remark` varchar(255) DEFAULT NULL,
  `action_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `action_log`
--

INSERT INTO `action_log` (`log_id`, `admin_id`, `action`, `remark`, `action_date`) VALUES
(1, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 2', '2026-02-12 09:47:36'),
(2, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 101', '2026-02-21 15:59:47'),
(3, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 102', '2026-02-21 15:59:47'),
(4, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 103', '2026-02-21 15:59:47'),
(5, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 104', '2026-02-21 15:59:47'),
(6, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 105', '2026-02-21 15:59:47'),
(7, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 106', '2026-02-21 15:59:47'),
(8, 1, 'APPROVE_RESTAURANT', 'Approved Restaurant ID: 107', '2026-03-10 06:41:00');

-- --------------------------------------------------------

--
-- Stand-in structure for view `active_delivery_tracking`
-- (See below for the actual view)
--
CREATE TABLE `active_delivery_tracking` (
`order_id` int(11)
,`user_id` int(11)
,`restaurant_id` int(11)
,`address_id` int(11)
,`order_date` datetime
,`total_amount` decimal(10,2)
,`status` enum('Placed','Preparing','Delivered','Cancelled')
,`delivery_status` enum('Assigned','Out for Delivery','Delivered','Cancelled')
,`estimated_time` datetime
);

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `role` enum('Super_Admin','Regional_Manager') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `role`) VALUES
(1, 'Regional_Manager');

-- --------------------------------------------------------

--
-- Table structure for table `cuisine`
--

CREATE TABLE `cuisine` (
  `cuisine_id` int(11) NOT NULL,
  `cuisine_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cuisine`
--

INSERT INTO `cuisine` (`cuisine_id`, `cuisine_name`) VALUES
(6, 'Bakery & Desserts'),
(4, 'Chinese'),
(8, 'Fast Food'),
(1, 'Goan'),
(5, 'Italian'),
(7, 'Mughlai'),
(2, 'North Indian'),
(3, 'South Indian');

-- --------------------------------------------------------

--
-- Table structure for table `delivery`
--

CREATE TABLE `delivery` (
  `delivery_id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `delivery_status` enum('Assigned','Out for Delivery','Delivered','Cancelled') NOT NULL,
  `estimated_time` datetime DEFAULT NULL,
  `delivered_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `delivery`
--

INSERT INTO `delivery` (`delivery_id`, `order_id`, `delivery_status`, `estimated_time`, `delivered_at`) VALUES
(1, 22, 'Delivered', '2026-02-19 16:31:57', '2026-02-19 15:57:31'),
(2, 23, 'Delivered', '2026-02-19 20:35:07', '2026-02-20 12:00:34'),
(3, 24, 'Cancelled', '2026-02-20 13:02:44', NULL),
(4, 25, 'Out for Delivery', '2026-02-20 14:33:39', NULL),
(5, 26, 'Delivered', '2026-02-20 22:25:35', '2026-02-20 21:44:40'),
(6, 27, 'Assigned', '2026-02-21 02:07:42', NULL),
(7, 28, 'Assigned', '2026-02-23 10:51:22', NULL),
(8, 29, 'Assigned', '2026-02-25 13:18:20', NULL),
(9, 30, 'Assigned', '2026-03-09 19:06:17', NULL),
(10, 31, 'Assigned', '2026-03-09 19:07:53', NULL),
(11, 32, 'Assigned', '2026-03-09 19:17:01', NULL),
(12, 33, 'Delivered', '2026-03-09 21:57:39', '2026-03-10 00:33:32');

-- --------------------------------------------------------

--
-- Table structure for table `failed_jobs`
--

CREATE TABLE `failed_jobs` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `uuid` varchar(255) NOT NULL,
  `connection` text NOT NULL,
  `queue` text NOT NULL,
  `payload` longtext NOT NULL,
  `exception` longtext NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `food_item`
--

CREATE TABLE `food_item` (
  `food_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `food_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `availability` tinyint(1) DEFAULT 1,
  `is_veg` tinyint(1) NOT NULL,
  `price` decimal(8,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `food_item`
--

INSERT INTO `food_item` (`food_id`, `restaurant_id`, `food_name`, `description`, `availability`, `is_veg`, `price`) VALUES
(1, 2, 'Chicken', 'good', 1, 0, 230.00),
(2, 2, 'Paneer Tikka', 'vvv', 1, 1, 250.00),
(3, 101, 'Goan Fish Thali', 'Traditional thali with fish curry, fried fish, kismur, and rice.', 1, 0, 250.00),
(4, 101, 'Kingfish Tawa Fry', 'Fresh Surmai marinated in Goan recheado masala and pan-fried.', 1, 0, 350.00),
(5, 101, 'Prawn Curry Rice', 'Authentic Goan coconut-based prawn curry served with steamed rice.', 1, 0, 280.00),
(6, 101, 'Squid Butter Garlic', 'Tender calamari tossed in rich butter and burnt garlic sauce.', 1, 0, 320.00),
(7, 101, 'Crab Xacuti', 'Fresh mud crabs cooked in roasted coconut and Goan spices.', 1, 0, 450.00),
(8, 101, 'Pomfret Recheado', 'Whole silver pomfret stuffed with spicy red Goan masala.', 1, 0, 500.00),
(9, 101, 'Bombay Duck Fry', 'Crispy fried bombil coated with semolina.', 1, 0, 220.00),
(10, 101, 'Mackerel Uddamethi', 'Traditional Goan fish curry made with fenugreek and urad dal.', 1, 0, 240.00),
(11, 101, 'Shark Ambot Tik', 'Spicy and sour shark meat curry.', 1, 0, 290.00),
(12, 101, 'Clam Sukha', 'Tisreo (clams) dry preparation with grated coconut.', 1, 0, 260.00),
(13, 101, 'Prawn Balchao', 'Spicy, pickled Goan prawn preparation.', 1, 0, 340.00),
(14, 101, 'Goan Chilli Beef', 'Stir-fried beef chunks with capsicum and green chillies.', 1, 0, 270.00),
(15, 101, 'Fish Fingers', 'Crumb-fried fish strips served with tartar sauce.', 1, 0, 210.00),
(16, 101, 'Modso Rava Fry', 'Lemonfish coated in rava and fried to perfection.', 1, 0, 310.00),
(17, 101, 'Mussels Rava Fry', 'Fresh mussels coated in semolina and shallow fried.', 1, 0, 280.00),
(18, 101, 'Rawas Fish Curry', 'Indian salmon cooked in a mild coconut gravy.', 1, 0, 330.00),
(19, 101, 'Goan Prawn Pulao', 'Fragrant basmati rice cooked with fresh prawns and spices.', 1, 0, 290.00),
(20, 101, 'Chonak Tawa Fry', 'Giant sea perch marinated in spicy red masala.', 1, 0, 360.00),
(21, 101, 'Ladyfish Fry', 'Crispy Kane (ladyfish) fried whole.', 1, 0, 250.00),
(22, 101, 'Calamari Rings', 'Golden fried squid rings.', 1, 0, 280.00),
(23, 101, 'Steamed Rice', 'Portion of plain steamed basmati rice.', 1, 1, 80.00),
(24, 101, 'Goan Poi (Bread)', 'Traditional Goan wheat bread.', 1, 1, 15.00),
(25, 101, 'Sol Kadhi', 'Digestive drink made from kokum and coconut milk.', 1, 1, 50.00),
(26, 101, 'Serradura', 'Portuguese sawdust pudding made with cream and biscuits.', 1, 1, 120.00),
(27, 101, 'Caramel Pudding', 'Classic Goan style egg caramel custard.', 1, 1, 100.00),
(28, 102, 'Pork Vindaloo', 'World-famous Goan pork dish cooked in garlic and vinegar.', 1, 0, 320.00),
(29, 102, 'Chicken Cafreal', 'Chicken marinated in coriander, green chillies, and spices.', 1, 0, 290.00),
(30, 102, 'Goan Pork Sorpotel', 'Rich, spicy, and tangy pork stew, a Goan festive specialty.', 1, 0, 340.00),
(31, 102, 'Beef Croquettes', 'Spiced minced beef rolls, crumbed and deep-fried.', 1, 0, 220.00),
(32, 102, 'Prawn Rissois', 'Crescent-shaped pastry stuffed with creamy prawn filling.', 1, 0, 240.00),
(33, 102, 'Mutton Xacuti', 'Tender mutton cooked in a roasted spice and coconut gravy.', 1, 0, 380.00),
(34, 102, 'Vegetable Caldine', 'Mixed vegetables in a mild yellow coconut milk stew.', 1, 1, 210.00),
(35, 102, 'Mushroom Green Curry', 'Local mushrooms cooked in a spicy green cafreal gravy.', 1, 1, 230.00),
(36, 102, 'Goan Sausage Pulao', 'Spicy Choris (Goan sausages) cooked with fragrant rice.', 1, 0, 310.00),
(37, 102, 'Chicken Xacuti', 'Chicken on the bone cooked in authentic roasted masala.', 1, 0, 280.00),
(38, 102, 'Sannas (3 pcs)', 'Spongy steamed rice cakes, perfect with Sorpotel.', 1, 1, 90.00),
(39, 102, 'Beef Roast', 'Slow-roasted beef sliced and served with savory pan gravy.', 1, 0, 330.00),
(40, 102, 'Feijoada', 'Portuguese-inspired bean and pork stew.', 1, 0, 300.00),
(41, 102, 'Pork Assado', 'Pan-roasted pork slices with a hint of cinnamon and clove.', 1, 0, 310.00),
(42, 102, 'Goan Dal fry', 'Tempered yellow lentils Goan style.', 1, 1, 150.00),
(43, 102, 'Bhindi Sukha', 'Dry okra preparation with coconut and spices.', 1, 1, 160.00),
(44, 102, 'Khatkhatem', 'Traditional Goan mixed vegetable and local lentil stew.', 1, 1, 190.00),
(45, 102, 'Prawn Dangar', 'Goan style prawn and coconut cutlets.', 1, 0, 250.00),
(46, 102, 'Beef Potato Chops', 'Mashed potato stuffed with spiced minced beef and fried.', 1, 0, 230.00),
(47, 102, 'Chicken Pan Roll', 'Crepes stuffed with savory chicken, crumbed and fried.', 1, 0, 210.00),
(48, 102, 'Bebinca', 'Traditional 7-layered Goan dessert.', 1, 1, 150.00),
(49, 102, 'Dodol', 'Sweet Goan pudding made with palm jaggery and coconut milk.', 1, 1, 140.00),
(50, 102, 'Alle Belle', 'Goan pancakes stuffed with coconut and jaggery.', 1, 1, 110.00),
(51, 102, 'Fresh Lime Soda', 'Refreshing lime soda (Sweet/Salted).', 1, 1, 60.00),
(52, 102, 'Kokum Juice', 'Sweet and tangy local kokum fruit juice.', 1, 1, 70.00),
(53, 103, 'Margherita Pizza (8\")', 'Classic cheese and tomato sauce base.', 1, 1, 199.00),
(54, 103, 'Pepperoni Pizza (8\")', 'Loaded with premium beef pepperoni and mozzarella.', 1, 0, 349.00),
(55, 103, 'Veggie Supreme (8\")', 'Onions, capsicum, mushrooms, olives, and jalapenos.', 1, 1, 259.00),
(56, 103, 'BBQ Chicken Pizza (8\")', 'Smoked BBQ chicken, onions, and extra cheese.', 1, 0, 299.00),
(57, 103, 'Hawaiian Pizza (8\")', 'Pineapple chunks, ham, and mozzarella cheese.', 1, 0, 279.00),
(58, 103, 'Mushroom Delight (8\")', 'Double mushrooms with a garlic butter base.', 1, 1, 249.00),
(59, 103, 'Paneer Tikka Pizza (8\")', 'Desi fusion with spiced paneer and mint mayo.', 1, 1, 269.00),
(60, 103, 'Meat Lovers Pizza (12\")', 'Pepperoni, sausage, ham, and grilled chicken.', 1, 0, 599.00),
(61, 103, 'Garlic Breadsticks', 'Freshly baked breadsticks with garlic butter.', 1, 1, 120.00),
(62, 103, 'Cheese Burst Garlic Bread', 'Garlic bread stuffed with gooey mozzarella.', 1, 1, 160.00),
(63, 103, 'Pasta Alfredo Veg', 'Penne pasta in a rich, creamy white cheese sauce.', 1, 1, 220.00),
(64, 103, 'Chicken Arrabbiata Pasta', 'Spicy tomato basil sauce with grilled chicken chunks.', 1, 0, 260.00),
(65, 103, 'Spaghetti Bolognese', 'Classic spaghetti tossed in a rich minced meat sauce.', 1, 0, 290.00),
(66, 103, 'Baked Lasagna', 'Layers of pasta, meat sauce, and cheese baked to perfection.', 1, 0, 320.00),
(67, 103, 'Pesto Penne', 'Pasta tossed in fresh basil and pine nut pesto.', 1, 1, 240.00),
(68, 103, 'Crispy Chicken Wings (6 pcs)', 'Deep-fried wings tossed in hot buffalo sauce.', 1, 0, 210.00),
(69, 103, 'BBQ Wings (6 pcs)', 'Chicken wings glazed in sweet and smoky BBQ sauce.', 1, 0, 210.00),
(70, 103, 'French Fries', 'Classic salted potato fries.', 1, 1, 110.00),
(71, 103, 'Peri Peri Fries', 'Fries tossed in spicy African peri-peri seasoning.', 1, 1, 130.00),
(72, 103, 'Cheesy Jalapeno Poppers', 'Crispy bites stuffed with cheese and jalapenos.', 1, 1, 180.00),
(73, 103, 'Chicken Nuggets (8 pcs)', 'Golden fried chicken nuggets with dip.', 1, 0, 160.00),
(74, 103, 'Choco Lava Cake', 'Warm chocolate cake with a gooey molten center.', 1, 1, 130.00),
(75, 103, 'Tiramisu', 'Coffee-flavored Italian dessert.', 1, 1, 180.00),
(76, 103, 'Coke (500ml)', 'Chilled Coca-Cola.', 1, 1, 50.00),
(77, 103, 'Cold Coffee', 'Thick blended iced coffee.', 1, 1, 120.00),
(78, 104, 'Chicken Dum Biryani', 'Classic Hyderabadi style slow-cooked chicken biryani.', 1, 0, 280.00),
(79, 104, 'Mutton Lucknowi Biryani', 'Mildly spiced Awadhi style mutton biryani.', 1, 0, 360.00),
(80, 104, 'Veg Shahi Biryani', 'Mixed vegetables and paneer cooked with fragrant rice.', 1, 1, 220.00),
(81, 104, 'Paneer Tikka Biryani', 'Smoky paneer tikka layered with biryani rice.', 1, 1, 250.00),
(82, 104, 'Egg Dum Biryani', 'Spiced boiled eggs roasted and layered with rice.', 1, 0, 200.00),
(83, 104, 'Prawn Biryani', 'Coastal fusion biryani made with fresh prawns.', 1, 0, 340.00),
(84, 104, 'Fish Tikka Biryani', 'Boneless fish tikka pieces cooked with aromatic rice.', 1, 0, 320.00),
(85, 104, 'Keema Biryani', 'Minced mutton cooked with spices and layered with basmati.', 1, 0, 350.00),
(86, 104, 'Special Chicken 65 Biryani', 'Spicy fried chicken 65 chunks served over biryani rice.', 1, 0, 300.00),
(87, 104, 'Chicken 65 (Dry)', 'Spicy, deep-fried chicken starter from South India.', 1, 0, 220.00),
(88, 104, 'Mutton Seekh Kebab', 'Minced mutton skewers grilled over charcoal.', 1, 0, 290.00),
(89, 104, 'Chicken Tangdi Kebab (2 pcs)', 'Chicken drumsticks marinated and roasted in tandoor.', 1, 0, 240.00),
(90, 104, 'Paneer 65', 'Spicy and tangy deep-fried paneer chunks.', 1, 1, 210.00),
(91, 104, 'Mirchi Ka Salan', 'Spicy peanut and chilli gravy, perfect with biryani.', 1, 1, 80.00),
(92, 104, 'Burhani Raita', 'Garlic and roasted cumin flavored yogurt.', 1, 1, 60.00),
(93, 104, 'Murg Musallam', 'Whole roasted chicken in a rich, creamy Mughlai gravy.', 1, 0, 650.00),
(94, 104, 'Mutton Haleem', 'Slow-cooked stew of meat, lentils, and wheat (Weekend Special).', 1, 0, 280.00),
(95, 104, 'Chicken Reshmi Kebab', 'Silky, creamy chicken skewers melted in the mouth.', 1, 0, 270.00),
(96, 104, 'Veg Galouti Kebab', 'Melt-in-mouth vegetable and lentil patties.', 1, 1, 230.00),
(97, 104, 'Shahi Tukda', 'Fried bread soaked in saffron milk and topped with nuts.', 1, 1, 120.00),
(98, 104, 'Phirni', 'Traditional ground rice pudding flavored with cardamom.', 1, 1, 100.00),
(99, 104, 'Gulab Jamun (2 pcs)', 'Deep-fried milk dumplings soaked in sugar syrup.', 1, 1, 60.00),
(100, 104, 'Double Ka Meetha', 'Hyderabadi style bread pudding dessert.', 1, 1, 130.00),
(101, 104, 'Sweet Lassi', 'Thick, churned yogurt drink.', 1, 1, 80.00),
(102, 104, 'Masala Chaas', 'Spiced buttermilk with mint and coriander.', 1, 1, 50.00),
(103, 105, 'Butter Chicken', 'Tandoori chicken simmered in a rich tomato and butter gravy.', 1, 0, 320.00),
(104, 105, 'Dal Makhani', 'Black lentils slow-cooked overnight with butter and cream.', 1, 1, 220.00),
(105, 105, 'Paneer Butter Masala', 'Cottage cheese cubes in a creamy tomato gravy.', 1, 1, 260.00),
(106, 105, 'Mutton Rogan Josh', 'Aromatic Kashmiri style mutton curry.', 1, 0, 380.00),
(107, 105, 'Kadai Chicken', 'Chicken cooked with bell peppers and freshly ground spices.', 1, 0, 300.00),
(108, 105, 'Palak Paneer', 'Fresh spinach puree cooked with paneer and garlic.', 1, 1, 250.00),
(109, 105, 'Chana Masala', 'Spicy and tangy chickpea curry.', 1, 1, 200.00),
(110, 105, 'Malai Kofta', 'Potato and paneer dumplings in a sweet, rich cashew gravy.', 1, 1, 270.00),
(111, 105, 'Aloo Gobi Adraki', 'Cauliflower and potatoes tossed with ginger and spices.', 1, 1, 190.00),
(112, 105, 'Chicken Tikka Masala', 'Roasted chicken chunks in a spicy, thick gravy.', 1, 0, 310.00),
(113, 105, 'Tandoori Chicken (Half)', 'Half chicken marinated in yogurt and spices, roasted in a clay oven.', 1, 0, 280.00),
(114, 105, 'Chicken Malai Tikka', 'Creamy, mild chicken kebabs with cheese and cardamom.', 1, 0, 290.00),
(115, 105, 'Paneer Tikka', 'Marinated paneer cubes grilled with onions and capsicum.', 1, 1, 240.00),
(116, 105, 'Mushroom Tikka', 'Button mushrooms marinated in tandoori spices and grilled.', 1, 1, 230.00),
(117, 105, 'Garlic Naan', 'Soft Indian flatbread topped with minced garlic and butter.', 1, 1, 60.00),
(118, 105, 'Butter Naan', 'Classic refined flour flatbread brushed with butter.', 1, 1, 50.00),
(119, 105, 'Tandoori Roti', 'Whole wheat flatbread baked in the tandoor.', 1, 1, 30.00),
(120, 105, 'Lachha Paratha', 'Multi-layered flaky whole wheat bread.', 1, 1, 55.00),
(121, 105, 'Jeera Rice', 'Basmati rice tossed with cumin seeds.', 1, 1, 140.00),
(122, 105, 'Peas Pulao', 'Fragrant rice cooked with green peas.', 1, 1, 160.00),
(123, 105, 'Papad Roasted', 'Crispy lentil wafer.', 1, 1, 20.00),
(124, 105, 'Masala Papad', 'Fried papad topped with spicy onion-tomato mixture.', 1, 1, 40.00),
(125, 105, 'Gajar Ka Halwa', 'Traditional carrot pudding cooked with ghee and milk.', 1, 1, 120.00),
(126, 105, 'Rasmalai (2 pcs)', 'Cottage cheese discs soaked in thickened, sweetened milk.', 1, 1, 100.00),
(127, 105, 'Jal Jeera', 'Refreshing cumin and mint flavored digestive drink.', 1, 1, 60.00),
(128, 106, 'Chicken Patties', 'Flaky puff pastry filled with spiced minced chicken.', 1, 0, 45.00),
(129, 106, 'Veg Puff', 'Puff pastry filled with curried mixed vegetables.', 1, 1, 30.00),
(130, 106, 'Beef Samosa', 'Crispy pastry triangle filled with spicy minced beef.', 1, 0, 35.00),
(131, 106, 'Mushroom Quiche', 'Savory tart filled with creamy mushrooms and cheese.', 1, 1, 80.00),
(132, 106, 'Chicken Sausage Roll', 'Chicken sausage wrapped in buttery puff pastry.', 1, 0, 50.00),
(133, 106, 'Goan Baath Cake (Slice)', 'Traditional coconut and semolina cake.', 1, 1, 60.00),
(134, 106, 'Plum Cake (Slice)', 'Rich fruit cake soaked in rum and spices.', 1, 1, 70.00),
(135, 106, 'Chocolate Eclair', 'Choux pastry filled with cream and topped with chocolate.', 1, 1, 65.00),
(136, 106, 'Black Forest Pastry', 'Classic chocolate sponge layered with cream and cherries.', 1, 1, 80.00),
(137, 106, 'Pineapple Pastry', 'Vanilla sponge layered with fresh pineapple chunks and cream.', 1, 1, 70.00),
(138, 106, 'Red Velvet Cupcake', 'Soft red velvet cake topped with cream cheese frosting.', 1, 1, 60.00),
(139, 106, 'Chocolate Truffle Pastry', 'Dense, rich, and gooey chocolate pastry.', 1, 1, 90.00),
(140, 106, 'Butter Croissant', 'Authentic French-style flaky butter croissant.', 1, 1, 75.00),
(141, 106, 'Cheese Croissant', 'Croissant baked with a rich cheddar cheese center.', 1, 1, 95.00),
(142, 106, 'Chocolate Donut', 'Soft donut dipped in dark chocolate glaze.', 1, 1, 50.00),
(143, 106, 'Cinnamon Roll', 'Sweet roll spiraled with cinnamon sugar and icing.', 1, 1, 80.00),
(144, 106, 'Macarons (Box of 3)', 'Assorted French almond meringue cookies.', 1, 1, 150.00),
(145, 106, 'Brownie with Walnuts', 'Fudgy chocolate brownie loaded with toasted walnuts.', 1, 1, 85.00),
(146, 106, 'Apple Pie (Slice)', 'Classic shortcrust pastry filled with spiced apples.', 1, 1, 110.00),
(147, 106, 'Lemon Tart', 'Sweet tart shell filled with tangy lemon curd.', 1, 1, 70.00),
(148, 106, 'Chicken Mayo Sandwich', 'Soft white bread filled with creamy chicken and herbs.', 1, 0, 60.00),
(149, 106, 'Veg Coleslaw Sandwich', 'Cabbage and carrot coleslaw in soft bakery bread.', 1, 1, 45.00),
(150, 106, 'Garlic Bread Loaf', 'Freshly baked loaf infused with garlic and herbs.', 1, 1, 80.00),
(151, 106, 'Cold Chocolate Milk', 'Thick, sweet, and chilled chocolate milk.', 1, 1, 70.00),
(152, 106, 'Iced Tea (Peach)', 'Refreshing peach flavored iced black tea.', 1, 1, 80.00);

--
-- Triggers `food_item`
--
DELIMITER $$
CREATE TRIGGER `Prevent_Invalid_Food_Price` BEFORE UPDATE ON `food_item` FOR EACH ROW BEGIN
    IF NEW.price <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Error: Food price must be greater than zero.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `migrations`
--

CREATE TABLE `migrations` (
  `id` int(10) UNSIGNED NOT NULL,
  `migration` varchar(255) NOT NULL,
  `batch` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `migrations`
--

INSERT INTO `migrations` (`id`, `migration`, `batch`) VALUES
(1, '2019_08_19_000000_create_failed_jobs_table', 1),
(2, '2019_12_14_000001_create_personal_access_tokens_table', 1),
(3, '2026_02_10_152949_create_food_delivery_tables', 1);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `restaurant_id` int(11) DEFAULT NULL,
  `address_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` enum('Placed','Preparing','Delivered','Cancelled') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `restaurant_id`, `address_id`, `order_date`, `total_amount`, `status`) VALUES
(1, 2, 2, NULL, '2026-02-12 20:28:47', 580.00, 'Delivered'),
(2, 2, 2, NULL, '2026-02-12 20:54:01', 580.00, 'Delivered'),
(3, 2, 2, NULL, '2026-02-12 20:57:57', 580.00, 'Delivered'),
(4, 2, 2, NULL, '2026-02-12 20:59:10', 2320.00, 'Preparing'),
(5, 2, 2, NULL, '2026-02-13 13:13:48', 290.00, 'Delivered'),
(6, 2, 2, NULL, '2026-02-13 13:49:16', 290.00, 'Delivered'),
(9, 2, 2, NULL, '2026-02-13 14:06:22', 290.00, 'Delivered'),
(10, 2, 2, NULL, '2026-02-13 14:22:17', 580.00, 'Delivered'),
(11, 2, 2, NULL, '2026-02-13 14:31:51', 290.00, 'Placed'),
(12, 2, 2, NULL, '2026-02-13 15:43:47', 580.00, 'Cancelled'),
(13, 2, 2, NULL, '2026-02-13 15:44:21', 870.00, 'Delivered'),
(14, 2, 2, NULL, '2026-02-13 18:05:51', 250.00, 'Placed'),
(15, 2, 2, NULL, '2026-02-13 19:42:09', 250.00, 'Placed'),
(16, 2, 2, 2, '2026-02-13 19:45:36', 500.00, 'Placed'),
(17, 2, 2, 2, '2026-02-13 19:52:19', 250.00, 'Placed'),
(18, 2, 2, 2, '2026-02-13 19:53:15', 290.00, 'Placed'),
(19, 2, 2, 3, '2026-02-13 19:58:44', 500.00, 'Placed'),
(20, 2, 2, 3, '2026-02-17 05:15:10', 500.00, 'Placed'),
(22, 2, 2, 3, '2026-02-19 15:46:57', 580.00, 'Delivered'),
(23, 2, 2, 2, '2026-02-19 20:20:07', 230.00, 'Delivered'),
(24, 2, 2, 2, '2026-02-20 06:47:44', 270.00, 'Cancelled'),
(25, 2, 2, 2, '2026-02-20 08:18:39', 500.00, 'Preparing'),
(26, 2, 2, 2, '2026-02-20 16:10:35', 500.00, 'Delivered'),
(27, 2, 104, 3, '2026-02-20 19:52:42', 980.00, 'Placed'),
(28, 2, 101, 3, '2026-02-23 04:36:22', 540.00, 'Placed'),
(29, 2, 102, 3, '2026-02-25 07:03:20', 970.00, 'Placed'),
(30, 2, 101, 3, '2026-03-09 12:51:17', 360.00, 'Placed'),
(31, 2, 102, 3, '2026-03-09 12:52:53', 410.00, 'Placed'),
(32, 2, 101, 3, '2026-03-09 18:32:01', 800.00, 'Placed'),
(33, 2, 2, 2, '2026-03-09 21:12:39', 770.00, 'Delivered');

--
-- Triggers `orders`
--
DELIMITER $$
CREATE TRIGGER `After_Order_Placed` AFTER INSERT ON `orders` FOR EACH ROW BEGIN
    INSERT INTO delivery (order_id, delivery_status, estimated_time)
    VALUES (NEW.order_id, 'Assigned', DATE_ADD(NOW(), INTERVAL 45 MINUTE));
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `Before_Order_Placed` BEFORE INSERT ON `orders` FOR EACH ROW BEGIN
    DECLARE res_status VARCHAR(50);
    

    SELECT status INTO res_status FROM RESTAURANT WHERE restaurant_id = NEW.restaurant_id;


    IF res_status != 'Open' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Database Error: Cannot place order. This restaurant is currently closed.';
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `order_item`
--

CREATE TABLE `order_item` (
  `order_item_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `food_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(8,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_item`
--

INSERT INTO `order_item` (`order_item_id`, `order_id`, `food_id`, `quantity`, `price`) VALUES
(1, 1, 1, 2, 290.00),
(2, 2, 1, 2, 290.00),
(3, 3, 1, 2, 290.00),
(4, 4, 1, 8, 290.00),
(5, 5, 1, 1, 290.00),
(6, 6, 1, 1, 290.00),
(7, 9, 1, 1, 290.00),
(8, 10, 1, 2, 290.00),
(9, 11, 1, 1, 290.00),
(10, 12, 1, 2, 290.00),
(11, 13, 1, 3, 290.00),
(12, 14, 2, 1, 250.00),
(13, 15, 2, 1, 250.00),
(14, 16, 2, 2, 250.00),
(15, 17, 2, 1, 250.00),
(16, 18, 1, 1, 290.00),
(17, 19, 2, 2, 250.00),
(18, 20, 2, 2, 250.00),
(19, 22, 1, 2, 290.00),
(20, 23, 1, 1, 230.00),
(21, 24, 1, 1, 230.00),
(22, 25, 1, 2, 230.00),
(23, 26, 1, 2, 230.00),
(24, 27, 79, 2, 360.00),
(25, 27, 80, 1, 220.00),
(26, 28, 3, 2, 250.00),
(27, 29, 28, 2, 320.00),
(28, 29, 29, 1, 290.00),
(29, 30, 27, 2, 100.00),
(30, 30, 26, 1, 120.00),
(31, 31, 35, 1, 230.00),
(32, 31, 52, 2, 70.00),
(33, 32, 18, 2, 330.00),
(34, 32, 27, 1, 100.00),
(35, 33, 2, 2, 250.00),
(36, 33, 1, 1, 230.00);

-- --------------------------------------------------------

--
-- Table structure for table `payment`
--

CREATE TABLE `payment` (
  `payment_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_mode` enum('UPI','Card','Cash','NetBanking') DEFAULT NULL,
  `payment_status` enum('Pending','Success','Failed','Refunded') DEFAULT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `payment_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment`
--

INSERT INTO `payment` (`payment_id`, `order_id`, `amount`, `payment_mode`, `payment_status`, `transaction_id`, `payment_date`) VALUES
(1, 3, 580.00, 'UPI', 'Success', 'TXN-21D9CD8A0F2D', '2026-02-12 20:57:57'),
(2, 4, 2320.00, 'Card', 'Success', 'TXN-E257D666EED4', '2026-02-12 20:59:10'),
(3, 5, 290.00, 'UPI', 'Success', 'TXN-68FDAD4F4CB8', '2026-02-13 13:13:48'),
(4, 6, 290.00, 'UPI', 'Success', 'TXN-D4D9B580AB89', '2026-02-13 13:49:16'),
(5, 9, 290.00, 'UPI', 'Success', 'TXN-95552FA01CA1', '2026-02-13 14:06:22'),
(6, 10, 580.00, 'UPI', 'Success', 'TXN-43AF4D06057A', '2026-02-13 14:22:17'),
(7, 11, 290.00, 'UPI', 'Success', 'TXN-ED38111A714E', '2026-02-13 14:31:51'),
(8, 12, 580.00, 'UPI', 'Refunded', 'TXN-BD28EE2101BD', '2026-02-13 15:43:47'),
(9, 13, 870.00, 'UPI', 'Success', 'TXN-A0DB8AC3A89C', '2026-02-13 15:44:21'),
(10, 14, 250.00, 'UPI', 'Success', 'TXN-E1D5BDF2FBE7', '2026-02-13 18:05:51'),
(11, 15, 250.00, 'UPI', 'Success', 'TXN-0F8CC18F8CF0', '2026-02-13 19:42:09'),
(12, 16, 500.00, 'UPI', 'Success', 'TXN-ED467E23F1BE', '2026-02-13 19:45:36'),
(13, 17, 250.00, 'UPI', 'Success', 'TXN-4396B168789B', '2026-02-13 19:52:19'),
(14, 18, 290.00, 'UPI', 'Success', 'TXN-78DC9748834F', '2026-02-13 19:53:15'),
(15, 19, 500.00, 'UPI', 'Success', 'TXN-21A9F509AD2A', '2026-02-13 19:58:44'),
(16, 20, 500.00, 'UPI', 'Success', 'TXN-89E1E6424ADA', '2026-02-17 05:15:10'),
(17, 22, 580.00, 'UPI', 'Success', 'TXN-2BE3FB7CDFBE', '2026-02-19 15:46:57'),
(18, 23, 230.00, 'UPI', 'Success', 'TXN-1216EA5130D4', '2026-02-19 20:20:07'),
(19, 24, 270.00, 'UPI', 'Refunded', 'TXN-6B8F9A526A66', '2026-02-20 06:47:44'),
(20, 25, 500.00, 'UPI', 'Success', 'TXN-B766E954280D', '2026-02-20 08:18:39'),
(21, 26, 500.00, 'UPI', 'Success', 'TXN-B2F37818C843', '2026-02-20 16:10:35'),
(22, 27, 980.00, 'UPI', 'Success', 'TXN-1D494AF9F53F', '2026-02-20 19:52:42'),
(23, 28, 540.00, 'UPI', 'Success', 'TXN-6653826A787C', '2026-02-23 04:36:22'),
(24, 29, 970.00, 'Card', 'Success', 'TXN-D9C5F480BAEB', '2026-02-25 07:03:20'),
(25, 30, 360.00, 'Card', 'Success', 'TXN-CAF09D2866A0', '2026-03-09 12:51:18'),
(26, 31, 410.00, 'Card', 'Success', 'TXN-EBD7169F9AC3', '2026-03-09 12:52:53'),
(27, 32, 800.00, 'UPI', 'Success', 'TXN-17C129314E35', '2026-03-09 18:32:01'),
(28, 33, 770.00, 'UPI', 'Success', 'TXN-67B60BA2E339', '2026-03-09 21:12:39');

-- --------------------------------------------------------

--
-- Table structure for table `personal_access_tokens`
--

CREATE TABLE `personal_access_tokens` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `tokenable_type` varchar(255) NOT NULL,
  `tokenable_id` bigint(20) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `token` varchar(64) NOT NULL,
  `abilities` text DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `postcode`
--

CREATE TABLE `postcode` (
  `pincode` varchar(10) NOT NULL,
  `city` varchar(50) DEFAULT NULL,
  `state` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `postcode`
--

INSERT INTO `postcode` (`pincode`, `city`, `state`) VALUES
('403001', 'Panaji', 'Goa'),
('403110', 'Bicholim', 'Goa'),
('403401', 'Ponda', 'Goa'),
('403501', 'Calangute', 'Goa'),
('403507', 'Mapusa', 'Goa'),
('403601', 'Madgaon', 'Goa'),
('403706', 'Vasco da Gama', 'Goa');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant`
--

CREATE TABLE `restaurant` (
  `restaurant_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `owner_id` int(11) DEFAULT NULL,
  `status` enum('Open','Closed') DEFAULT 'Open',
  `preparation_time` int(11) NOT NULL DEFAULT 15,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant`
--

INSERT INTO `restaurant` (`restaurant_id`, `name`, `description`, `phone`, `owner_id`, `status`, `preparation_time`, `image_url`) VALUES
(2, 'Manju Restaurant', NULL, '8658329743', 3, 'Open', 20, NULL),
(101, 'Fishermans Cove', 'Authentic Goan seafood and coastal delicacies.', '0832240001', 20, 'Open', 40, NULL),
(102, 'Viva Panjim', 'Award-winning heritage Goan recipes in a cozy setting.', '0832240002', 21, 'Open', 35, NULL),
(103, 'The Pizza Hub', 'Wood-fired Italian pizzas and quick fast food.', '0832240003', 22, 'Open', 20, NULL),
(104, 'Biryani Palace', 'Rich Mughlai flavors and aromatic dum biryanis.', '0832240004', 23, 'Open', 30, NULL),
(105, 'Tandoor Nights', 'Premium North Indian curries and tandoori specials.', '0832240005', 24, 'Closed', 45, NULL),
(106, 'Maria Bakery', 'Freshly baked Goan sweets, cakes, and savories.', '0832240006', 25, 'Open', 15, NULL),
(107, 'Roslin\'s Corner', NULL, '0909090909', 26, 'Open', 20, 'https://res.cloudinary.com/daepwe5ei/image/upload/v1773121642/vddgy5zw0erkuhbjrepq.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_address`
--

CREATE TABLE `restaurant_address` (
  `restaurant_address_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `building_name` varchar(100) DEFAULT NULL,
  `street` varchar(150) NOT NULL,
  `postcode` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_address`
--

INSERT INTO `restaurant_address` (`restaurant_address_id`, `restaurant_id`, `building_name`, `street`, `postcode`) VALUES
(1, 2, 'Raikars Den', 'Comba', '403601'),
(2, 101, 'Sea Breeze Plaza', 'Colva Beach Road', '403601'),
(3, 102, 'Heritage Mansion', 'Fontainhas', '403601'),
(4, 103, 'Sunshine Tower', 'Abade Faria Road', '403601'),
(5, 104, 'Golden Enclave', 'Station Road', '403601'),
(6, 105, 'Royal Arcade', 'Pajifond', '403601'),
(7, 106, 'Sweet Corner', 'Aquem', '403601'),
(8, 107, '4th ward', 'Fatona, Colva', '403601');

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_cuisine`
--

CREATE TABLE `restaurant_cuisine` (
  `restaurant_id` int(11) NOT NULL,
  `cuisine_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_cuisine`
--

INSERT INTO `restaurant_cuisine` (`restaurant_id`, `cuisine_id`) VALUES
(2, 5),
(101, 1),
(101, 2),
(102, 1),
(103, 5),
(103, 8),
(104, 2),
(104, 7),
(105, 2),
(105, 7),
(106, 6),
(107, 4),
(107, 6);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_owner`
--

CREATE TABLE `restaurant_owner` (
  `owner_id` int(11) NOT NULL,
  `license_no` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_owner`
--

INSERT INTO `restaurant_owner` (`owner_id`, `license_no`) VALUES
(3, 'Pending'),
(20, NULL),
(21, NULL),
(22, NULL),
(23, NULL),
(24, NULL),
(25, NULL),
(26, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `restaurant_request`
--

CREATE TABLE `restaurant_request` (
  `request_id` int(11) NOT NULL,
  `restaurant_id` int(11) NOT NULL,
  `admin_id` int(11) DEFAULT NULL,
  `request_date` datetime DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `decision_date` date DEFAULT NULL,
  `remarks` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `restaurant_request`
--

INSERT INTO `restaurant_request` (`request_id`, `restaurant_id`, `admin_id`, `request_date`, `status`, `decision_date`, `remarks`) VALUES
(1, 2, 1, '2026-02-12 13:00:24', 'Approved', '2026-02-12', NULL),
(2, 101, 1, '2026-02-21 01:10:56', 'Approved', '2026-02-21', NULL),
(3, 102, 1, '2026-02-21 01:10:56', 'Approved', '2026-02-21', NULL),
(4, 103, 1, '2026-02-21 01:10:56', 'Approved', '2026-02-21', NULL),
(5, 104, 1, '2026-02-21 01:10:56', 'Approved', '2026-02-21', NULL),
(6, 105, 1, '2026-02-21 01:10:56', 'Approved', '2026-02-21', NULL),
(7, 106, 1, '2026-02-21 01:10:56', 'Approved', '2026-02-21', NULL),
(8, 107, 1, '2026-03-10 11:17:20', 'Approved', '2026-03-10', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `review`
--

CREATE TABLE `review` (
  `review_id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `review_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review`
--

INSERT INTO `review` (`review_id`, `order_id`, `review_date`) VALUES
(2, 13, '2026-02-19');

-- --------------------------------------------------------

--
-- Table structure for table `review_item`
--

CREATE TABLE `review_item` (
  `review_item_id` int(11) NOT NULL,
  `review_id` int(11) NOT NULL,
  `item_type` enum('RESTAURANT','FOOD') NOT NULL,
  `item_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `review_item`
--

INSERT INTO `review_item` (`review_item_id`, `review_id`, `item_type`, `item_id`, `rating`, `comment`) VALUES
(1, 2, 'RESTAURANT', 2, 5, 'fine'),
(2, 2, 'FOOD', 1, 5, 'Good');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `dob` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `dob`) VALUES
(2, '2004-06-12'),
(27, '2004-07-22');

-- --------------------------------------------------------

--
-- Table structure for table `user_address`
--

CREATE TABLE `user_address` (
  `address_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `building_name` varchar(100) DEFAULT NULL,
  `street` varchar(150) NOT NULL,
  `postcode` varchar(10) NOT NULL,
  `address_type` enum('Home','Work','Other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_address`
--

INSERT INTO `user_address` (`address_id`, `user_id`, `building_name`, `street`, `postcode`, `address_type`) VALUES
(2, 2, 'Damodar College', 'Comba', '403601', 'Other'),
(3, 2, 'Chowgule College', 'Gogol', '403601', 'Home');

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_available_restaurants`
-- (See below for the actual view)
--
CREATE TABLE `view_available_restaurants` (
`restaurant_id` int(11)
,`name` varchar(100)
,`phone` varchar(15)
,`preparation_time` int(11)
,`status` enum('Open','Closed')
,`building_name` varchar(100)
,`street` varchar(150)
,`postcode` varchar(10)
,`avg_rating` decimal(14,4)
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_user_monthly_stats`
-- (See below for the actual view)
--
CREATE TABLE `view_user_monthly_stats` (
`user_id` int(11)
,`month_num` int(2)
,`year_num` int(4)
,`total_orders` bigint(21)
,`total_spent` decimal(32,2)
);

-- --------------------------------------------------------

--
-- Structure for view `active_delivery_tracking`
--
DROP TABLE IF EXISTS `active_delivery_tracking`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `active_delivery_tracking`  AS SELECT `o`.`order_id` AS `order_id`, `o`.`user_id` AS `user_id`, `o`.`restaurant_id` AS `restaurant_id`, `o`.`address_id` AS `address_id`, `o`.`order_date` AS `order_date`, `o`.`total_amount` AS `total_amount`, `o`.`status` AS `status`, `d`.`delivery_status` AS `delivery_status`, `d`.`estimated_time` AS `estimated_time` FROM (`orders` `o` join `delivery` `d` on(`o`.`order_id` = `d`.`order_id`)) WHERE `o`.`status` in ('Placed','Preparing','Out for Delivery') ;

-- --------------------------------------------------------

--
-- Structure for view `view_available_restaurants`
--
DROP TABLE IF EXISTS `view_available_restaurants`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_available_restaurants`  AS SELECT `r`.`restaurant_id` AS `restaurant_id`, `r`.`name` AS `name`, `r`.`phone` AS `phone`, `r`.`preparation_time` AS `preparation_time`, `r`.`status` AS `status`, `ra`.`building_name` AS `building_name`, `ra`.`street` AS `street`, `ra`.`postcode` AS `postcode`, ifnull(`ratings`.`avg_rating`,0) AS `avg_rating` FROM (((`restaurant` `r` join `restaurant_request` `rr` on(`r`.`restaurant_id` = `rr`.`restaurant_id`)) join `restaurant_address` `ra` on(`r`.`restaurant_id` = `ra`.`restaurant_id`)) left join (select `review_item`.`item_id` AS `item_id`,avg(`review_item`.`rating`) AS `avg_rating` from `review_item` where `review_item`.`item_type` = 'Restaurant' group by `review_item`.`item_id`) `ratings` on(`r`.`restaurant_id` = `ratings`.`item_id`)) WHERE `rr`.`status` = 'Approved' ;

-- --------------------------------------------------------

--
-- Structure for view `view_user_monthly_stats`
--
DROP TABLE IF EXISTS `view_user_monthly_stats`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_user_monthly_stats`  AS SELECT `orders`.`user_id` AS `user_id`, month(`orders`.`order_date`) AS `month_num`, year(`orders`.`order_date`) AS `year_num`, count(`orders`.`order_id`) AS `total_orders`, sum(case when `orders`.`status` <> 'Cancelled' then `orders`.`total_amount` else 0 end) AS `total_spent` FROM `orders` GROUP BY `orders`.`user_id`, year(`orders`.`order_date`), month(`orders`.`order_date`) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accounts`
--
ALTER TABLE `accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `action_log`
--
ALTER TABLE `action_log`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `cuisine`
--
ALTER TABLE `cuisine`
  ADD PRIMARY KEY (`cuisine_id`),
  ADD UNIQUE KEY `cuisine_name` (`cuisine_name`);

--
-- Indexes for table `delivery`
--
ALTER TABLE `delivery`
  ADD PRIMARY KEY (`delivery_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`);

--
-- Indexes for table `food_item`
--
ALTER TABLE `food_item`
  ADD PRIMARY KEY (`food_id`),
  ADD KEY `restaurant_id` (`restaurant_id`);

--
-- Indexes for table `migrations`
--
ALTER TABLE `migrations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `fk_order_address` (`address_id`);

--
-- Indexes for table `order_item`
--
ALTER TABLE `order_item`
  ADD PRIMARY KEY (`order_item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `food_id` (`food_id`);

--
-- Indexes for table `payment`
--
ALTER TABLE `payment`
  ADD PRIMARY KEY (`payment_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  ADD KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`);

--
-- Indexes for table `postcode`
--
ALTER TABLE `postcode`
  ADD PRIMARY KEY (`pincode`);

--
-- Indexes for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD PRIMARY KEY (`restaurant_id`),
  ADD KEY `fk_res_to_owner` (`owner_id`);

--
-- Indexes for table `restaurant_address`
--
ALTER TABLE `restaurant_address`
  ADD PRIMARY KEY (`restaurant_address_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `postcode` (`postcode`);

--
-- Indexes for table `restaurant_cuisine`
--
ALTER TABLE `restaurant_cuisine`
  ADD PRIMARY KEY (`restaurant_id`,`cuisine_id`),
  ADD KEY `cuisine_id` (`cuisine_id`);

--
-- Indexes for table `restaurant_owner`
--
ALTER TABLE `restaurant_owner`
  ADD PRIMARY KEY (`owner_id`);

--
-- Indexes for table `restaurant_request`
--
ALTER TABLE `restaurant_request`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `restaurant_id` (`restaurant_id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `review`
--
ALTER TABLE `review`
  ADD PRIMARY KEY (`review_id`),
  ADD UNIQUE KEY `order_id` (`order_id`);

--
-- Indexes for table `review_item`
--
ALTER TABLE `review_item`
  ADD PRIMARY KEY (`review_item_id`),
  ADD UNIQUE KEY `review_id` (`review_id`,`item_type`,`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `user_address`
--
ALTER TABLE `user_address`
  ADD PRIMARY KEY (`address_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `postcode` (`postcode`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `accounts`
--
ALTER TABLE `accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `action_log`
--
ALTER TABLE `action_log`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `cuisine`
--
ALTER TABLE `cuisine`
  MODIFY `cuisine_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `delivery`
--
ALTER TABLE `delivery`
  MODIFY `delivery_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `failed_jobs`
--
ALTER TABLE `failed_jobs`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `food_item`
--
ALTER TABLE `food_item`
  MODIFY `food_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=153;

--
-- AUTO_INCREMENT for table `migrations`
--
ALTER TABLE `migrations`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `order_item`
--
ALTER TABLE `order_item`
  MODIFY `order_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `payment`
--
ALTER TABLE `payment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `personal_access_tokens`
--
ALTER TABLE `personal_access_tokens`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `restaurant`
--
ALTER TABLE `restaurant`
  MODIFY `restaurant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `restaurant_address`
--
ALTER TABLE `restaurant_address`
  MODIFY `restaurant_address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `restaurant_request`
--
ALTER TABLE `restaurant_request`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `review`
--
ALTER TABLE `review`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `review_item`
--
ALTER TABLE `review_item`
  MODIFY `review_item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `user_address`
--
ALTER TABLE `user_address`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `action_log`
--
ALTER TABLE `action_log`
  ADD CONSTRAINT `action_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `delivery`
--
ALTER TABLE `delivery`
  ADD CONSTRAINT `delivery_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `food_item`
--
ALTER TABLE `food_item`
  ADD CONSTRAINT `food_item_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`restaurant_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_address` FOREIGN KEY (`address_id`) REFERENCES `user_address` (`address_id`) ON DELETE SET NULL,
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`restaurant_id`);

--
-- Constraints for table `order_item`
--
ALTER TABLE `order_item`
  ADD CONSTRAINT `order_item_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`),
  ADD CONSTRAINT `order_item_ibfk_2` FOREIGN KEY (`food_id`) REFERENCES `food_item` (`food_id`);

--
-- Constraints for table `payment`
--
ALTER TABLE `payment`
  ADD CONSTRAINT `payment_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `restaurant`
--
ALTER TABLE `restaurant`
  ADD CONSTRAINT `fk_res_to_owner` FOREIGN KEY (`owner_id`) REFERENCES `restaurant_owner` (`owner_id`);

--
-- Constraints for table `restaurant_address`
--
ALTER TABLE `restaurant_address`
  ADD CONSTRAINT `restaurant_address_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`restaurant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restaurant_address_ibfk_2` FOREIGN KEY (`postcode`) REFERENCES `postcode` (`pincode`);

--
-- Constraints for table `restaurant_cuisine`
--
ALTER TABLE `restaurant_cuisine`
  ADD CONSTRAINT `restaurant_cuisine_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`restaurant_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `restaurant_cuisine_ibfk_2` FOREIGN KEY (`cuisine_id`) REFERENCES `cuisine` (`cuisine_id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_owner`
--
ALTER TABLE `restaurant_owner`
  ADD CONSTRAINT `restaurant_owner_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `restaurant_request`
--
ALTER TABLE `restaurant_request`
  ADD CONSTRAINT `restaurant_request_ibfk_1` FOREIGN KEY (`restaurant_id`) REFERENCES `restaurant` (`restaurant_id`),
  ADD CONSTRAINT `restaurant_request_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `admin` (`admin_id`);

--
-- Constraints for table `review`
--
ALTER TABLE `review`
  ADD CONSTRAINT `review_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);

--
-- Constraints for table `review_item`
--
ALTER TABLE `review_item`
  ADD CONSTRAINT `review_item_ibfk_1` FOREIGN KEY (`review_id`) REFERENCES `review` (`review_id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `user_address`
--
ALTER TABLE `user_address`
  ADD CONSTRAINT `user_address_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_address_ibfk_2` FOREIGN KEY (`postcode`) REFERENCES `postcode` (`pincode`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
