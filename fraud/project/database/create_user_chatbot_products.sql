-- Run this in phpMyAdmin or MySQL if the table doesn't exist
-- Database: web (or your app database name)

CREATE TABLE IF NOT EXISTS `user_chatbot_products` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint unsigned NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text,
  `price` decimal(12,2) NOT NULL DEFAULT '0.00',
  `image` varchar(255) DEFAULT NULL,
  `product_url` varchar(255) DEFAULT NULL COMMENT 'Link to product page',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `status` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_chatbot_products_user_id_index` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
