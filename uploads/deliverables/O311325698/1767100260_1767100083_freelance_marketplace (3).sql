-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 30, 2025 at 12:30 AM
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
-- Database: `freelance_marketplace`
--

-- --------------------------------------------------------

--
-- Table structure for table `categories`
--

CREATE TABLE `categories` (
  `category_id` int(11) NOT NULL,
  `category_name` varchar(100) NOT NULL,
  `Programming` varchar(100) DEFAULT NULL,
  `Design` varchar(100) DEFAULT NULL,
  `Writing` varchar(100) DEFAULT NULL,
  `Marketing` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `categories`
--

INSERT INTO `categories` (`category_id`, `category_name`, `Programming`, `Design`, `Writing`, `Marketing`) VALUES
(1, 'Web Development', NULL, NULL, NULL, NULL),
(2, 'Graphic Design', NULL, NULL, NULL, NULL),
(3, 'Writing & Translation', NULL, NULL, NULL, NULL),
(4, 'Digital Marketing', NULL, NULL, NULL, NULL),
(5, 'Video & Animation', NULL, NULL, NULL, NULL),
(6, 'Data & Analytics', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `file_attachments`
--

CREATE TABLE `file_attachments` (
  `file_id` int(11) NOT NULL,
  `order_id` varchar(10) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `original_filename` varchar(255) NOT NULL,
  `file_size` int(11) NOT NULL,
  `file_type` enum('requirement','deliverable','revision') NOT NULL,
  `upload_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(10) NOT NULL,
  `client_id` varchar(10) NOT NULL,
  `freelancer_id` varchar(10) NOT NULL,
  `service_id` varchar(10) NOT NULL,
  `service_title` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `delivery_time` int(11) NOT NULL,
  `revisions_included` int(11) NOT NULL,
  `requirements` text NOT NULL,
  `deliverable_notes` text DEFAULT NULL,
  `status` enum('Pending','In Progress','Delivered','Completed','Revision Requested','Cancelled') DEFAULT 'Pending',
  `payment_method` varchar(50) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `expected_delivery` date NOT NULL,
  `completion_date` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `client_id`, `freelancer_id`, `service_id`, `service_title`, `price`, `delivery_time`, `revisions_included`, `requirements`, `deliverable_notes`, `status`, `payment_method`, `order_date`, `expected_delivery`, `completion_date`) VALUES
('3000000001', '1000000001', '1000000002', '2000000001', 'Professional Logo Design', 150.00, 5, 3, 'I need a logo for my startup.', NULL, 'Pending', 'Credit Card', '2025-12-24 20:41:10', '2025-01-15', '2025-12-28 13:31:49'),
('3000000002', '1000000001', '4004477041', '2000000001', 'Professional Logo Design', 150.00, 5, 3, 'I need a logo for my startup.', NULL, 'Completed', 'Credit Card', '2025-12-24 20:44:45', '2025-01-15', '2025-12-28 13:31:49'),
('O761391622', '7747622430', '1000000002', '2000000004', 'Business Card Design', 60.00, 2, 2, 'Client requirements will be added later', NULL, 'Pending', 'Credit Card', '2025-12-29 16:29:25', '2025-12-31', NULL),
('O791476332', '7747622430', '1000000003', '2000000005', 'Full Stack Web Application', 900.00, 15, 3, 'Client requirements will be added later', NULL, 'Pending', 'Credit Card', '2025-12-29 16:29:25', '2026-01-13', NULL),
('ORD6952918', '7747622430', '1000000002', '2000000003', 'Social Media Post Design', 80.00, 3, 2, 'Client requirements will be added later', NULL, 'Pending', 'Credit Card', '2025-12-29 14:34:42', '2026-01-01', '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `revision_requests`
--

CREATE TABLE `revision_requests` (
  `revision_id` int(11) NOT NULL,
  `order_id` varchar(10) NOT NULL,
  `revision_notes` text NOT NULL,
  `revision_file` varchar(255) DEFAULT NULL,
  `request_status` enum('Pending','Accepted','Rejected') DEFAULT 'Pending',
  `request_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `response_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `freelancer_response` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `service_id` varchar(10) NOT NULL,
  `freelancer_id` varchar(10) NOT NULL,
  `title` varchar(200) NOT NULL,
  `category` varchar(100) NOT NULL,
  `subcategory` varchar(100) NOT NULL,
  `description` text NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `delivery_time` int(11) NOT NULL,
  `revisions_included` int(11) NOT NULL,
  `image_1` varchar(255) NOT NULL,
  `image_2` varchar(255) DEFAULT NULL,
  `image_3` varchar(255) DEFAULT NULL,
  `service_image` varchar(255) NOT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `featured_status` enum('Yes','No') DEFAULT 'No',
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`service_id`, `freelancer_id`, `title`, `category`, `subcategory`, `description`, `price`, `delivery_time`, `revisions_included`, `image_1`, `image_2`, `image_3`, `service_image`, `status`, `featured_status`, `created_date`) VALUES
('2000000001', '1000000002', 'Professional Logo Design', 'Graphic Design', 'Logo Design', 'I will design a modern and professional logo for your business.', 150.00, 5, 3, '', '', '', 'images/logo_design.jpg', 'Active', 'No', '2025-12-22 18:26:49'),
('2000000002', '1000000002', 'Responsive Website Development', 'Web Development', 'Frontend Development', 'I will build a responsive and fast website using HTML and CSS.', 500.00, 10, 5, '', '', '', 'images/website_creator.jpg', 'Active', 'No', '2025-12-22 18:26:49'),
('2000000003', '1000000002', 'Social Media Post Design', 'Graphic Design', 'Social Media Design', 'I will design eye-catching social media posts for Instagram and Facebook.', 80.00, 3, 2, 'images/SM_Post_Design_sara1.jpg', 'images/SM_Post_Design_sara2.jpg', 'images/SM_Post_Design_sara3.jpg', 'images/social_media_design.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000004', '1000000002', 'Business Card Design', 'Graphic Design', 'Branding', 'I will design a professional business card that matches your brand.', 60.00, 2, 2, 'images/Business_Card_Design_sara1.jpg', 'images/Business_Card_Design_sara2.jpg', 'images/Business_Card_Design_sara3.jpg', 'images/business_card.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000005', '1000000003', 'Full Stack Web Application', 'Web Development', 'Full Stack Development', 'I will build a complete web application using PHP and MySQL.', 900.00, 15, 3, 'images/Full_Stack_Web_Application_omar1.jpg', 'images/Full_Stack_Web_Application_omar2.jpg', 'images/Full_Stack_Web_Application_omar3.jpg', 'images/fullstack_web.jpg', 'Active', 'Yes', '2025-12-25 17:35:17'),
('2000000007', '1000000004', 'SEO Blog Article Writing', 'Writing & Translation', 'Content Writing', 'I will write a high-quality SEO-friendly blog article.', 70.00, 3, 1, '', '', '', 'images/blog_writing.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000008', '1000000004', 'Website Content Writing', 'Writing & Translation', 'Website Copy', 'I will write professional content for your website pages.', 120.00, 5, 2, '', '', '', 'images/website_content.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000009', '1000000005', 'Instagram Marketing Strategy', 'Digital Marketing', 'Social Media Marketing', 'I will create a complete Instagram marketing strategy for your brand.', 150.00, 4, 2, '', '', '', 'images/instagram_marketing.jpg', 'Active', 'Yes', '2025-12-25 17:35:17'),
('2000000010', '1000000005', 'Google Ads Campaign Setup', 'Digital Marketing', 'Paid Advertising', 'I will set up and optimize your Google Ads campaign.', 200.00, 5, 1, '', '', '', 'images/google_ads.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000011', '1000000006', 'Professional Video Editing', 'Video & Animation', 'Video Editing', 'I will edit your video professionally with effects and transitions.', 180.00, 6, 2, '', '', '', 'images/video_editing.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000012', '1000000006', 'Animated Explainer Video', 'Video & Animation', 'Animation', 'I will create a short animated explainer video for your business.', 350.00, 8, 2, '', '', '', 'images/explainer_video.jpg', 'Active', 'Yes', '2025-12-25 17:35:17'),
('2000000013', '1000000007', 'Data Analysis Using Python', 'Data & Analytics', 'Data Analysis', 'I will analyze your data and provide clear visual insights using Python.', 220.00, 7, 2, '', '', '', 'images/data_analysis.jpg', 'Active', 'No', '2025-12-25 17:35:17'),
('2000000014', '1000000007', 'Database Design & Optimization', 'Data & Analytics', 'Database Management', 'I will design and optimize your MySQL database for performance.', 250.00, 6, 2, '', '', '', 'images/database_design.jpg', 'Active', 'No', '2025-12-25 17:35:17');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` varchar(10) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(10) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `country` varchar(50) NOT NULL,
  `city` varchar(50) NOT NULL,
  `bio` text DEFAULT NULL,
  `role` enum('Client','Freelancer') NOT NULL,
  `status` enum('Active','Inactive') NOT NULL DEFAULT 'Active',
  `profile_photo` varchar(255) DEFAULT NULL,
  `registration_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `failed_attempts` int(11) DEFAULT 0,
  `last_failed_attempt` datetime DEFAULT NULL,
  `lock_until` datetime DEFAULT NULL,
  `professional_title` varchar(150) DEFAULT NULL,
  `professional_bio` text DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `experience_years` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `age`, `country`, `city`, `bio`, `role`, `status`, `profile_photo`, `registration_date`, `failed_attempts`, `last_failed_attempt`, `lock_until`, `professional_title`, `professional_bio`, `skills`, `experience_years`) VALUES
('1000000001', 'Ali', 'Hassan', 'ali.client@example.com', '$2y$10$examplehashclient', '0591234567', NULL, 'Palestine', 'Ramallah', '', 'Client', 'Active', NULL, '2025-12-22 18:26:15', 0, NULL, NULL, NULL, NULL, NULL, NULL),
('1000000002', 'Sara', 'Khaled', 'sara.freelancer@example.com', '$2y$10$examplehashfreelancer', '0597654321', NULL, 'Palestine', 'Nablus', '', 'Freelancer', 'Active', NULL, '2025-12-22 18:26:15', 0, NULL, NULL, NULL, NULL, NULL, NULL),
('1000000003', 'Omar', 'Abu Saleh', 'omar.freelancer@example.com', '$2y$10$examplehashfreelancer3', '0591122334', 25, 'Palestine', 'Gaza', 'Creative web developer.', 'Freelancer', 'Active', NULL, '2025-12-25 17:34:31', 0, NULL, NULL, 'Full Stack Developer', 'I build scalable web applications using modern technologies.', 'HTML, CSS, JavaScript, PHP, MySQL', 4),
('1000000004', 'Lina', 'Jamal', 'lina.freelancer@example.com', '$2y$10$examplehashfreelancer4', '0592233445', 27, 'Palestine', 'Ramallah', 'Passionate content writer.', 'Freelancer', 'Active', NULL, '2025-12-25 17:34:31', 0, NULL, NULL, 'Content Writer', 'I create SEO-friendly articles and website copy.', 'SEO, Blogging, Copywriting', 5),
('1000000005', 'Tariq', 'Hussein', 'tariq.freelancer@example.com', '$2y$10$examplehashfreelancer5', '0593344556', 30, 'Palestine', 'Nablus', 'Digital marketing expert.', 'Freelancer', 'Active', NULL, '2025-12-25 17:34:31', 0, NULL, NULL, 'Digital Marketing Specialist', 'I manage social media and paid advertising campaigns.', 'Instagram Ads, Google Ads, Social Media Marketing', 6),
('1000000006', 'Rana', 'Khalil', 'rana.freelancer@example.com', '$2y$10$examplehashfreelancer6', '0594455667', 24, 'Palestine', 'Bethlehem', 'Video and animation designer.', 'Freelancer', 'Active', NULL, '2025-12-25 17:34:31', 0, NULL, NULL, 'Video Editor & Animator', 'I produce engaging videos and animations for businesses.', 'Video Editing, After Effects, Premiere Pro', 3),
('1000000007', 'Fadi', 'Nasser', 'fadi.freelancer@example.com', '$2y$10$examplehashfreelancer7', '0595566778', 28, 'Palestine', 'Hebron', 'Data analyst and tech enthusiast.', 'Freelancer', 'Active', NULL, '2025-12-25 17:34:31', 0, NULL, NULL, 'Data Analyst', 'I analyze data and provide actionable insights.', 'Python, SQL, Data Visualization, Excel', 5),
('4004477041', 'Bailasan', 'Qa\'dan', 'bailasan@gmail.com', '$2y$10$aE26i2PLEmXhIYr3n2DFbeOemInDZLKhadLHta.ofV5ot9KSh2rEu', '0599731534', 22, 'Palestine', 'Ramallah', 'dfghjkl', 'Freelancer', 'Active', NULL, '2025-12-24 19:01:52', 0, NULL, NULL, 'open doooooor', 'hello you have the most funny and creative person ever', 'i can do everything', 100),
('7747622430', 'Bailasan', 'Qa\'dan', 'bailasanqadan5@gmail.com', '$2y$10$tRwDYI.0wV5o4bvVaz8GVOJ33QMc5ZQQoMHDhXGd/PHcYx/D9/xwa', '0599731534', 20, 'Palestine', 'Ramallah', 'ertyuiop', 'Client', 'Active', 'uploads/profiles/7747622430/profile_photo.jpg', '2025-12-25 11:17:41', 0, NULL, NULL, NULL, NULL, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categories`
--
ALTER TABLE `categories`
  ADD PRIMARY KEY (`category_id`),
  ADD UNIQUE KEY `category_name` (`category_name`);

--
-- Indexes for table `file_attachments`
--
ALTER TABLE `file_attachments`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `fk_file_order` (`order_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `fk_order_client` (`client_id`),
  ADD KEY `fk_order_freelancer` (`freelancer_id`),
  ADD KEY `fk_order_service` (`service_id`);

--
-- Indexes for table `revision_requests`
--
ALTER TABLE `revision_requests`
  ADD PRIMARY KEY (`revision_id`),
  ADD KEY `fk_revision_order` (`order_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`service_id`),
  ADD KEY `fk_service_user` (`freelancer_id`);

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
-- AUTO_INCREMENT for table `categories`
--
ALTER TABLE `categories`
  MODIFY `category_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `file_attachments`
--
ALTER TABLE `file_attachments`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `revision_requests`
--
ALTER TABLE `revision_requests`
  MODIFY `revision_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `file_attachments`
--
ALTER TABLE `file_attachments`
  ADD CONSTRAINT `fk_file_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_freelancer` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_service` FOREIGN KEY (`service_id`) REFERENCES `services` (`service_id`);

--
-- Constraints for table `revision_requests`
--
ALTER TABLE `revision_requests`
  ADD CONSTRAINT `fk_revision_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_service_user` FOREIGN KEY (`freelancer_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
