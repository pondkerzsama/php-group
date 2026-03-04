-- --------------------------------------------------------
-- SQL Script: อัปเดตฐานข้อมูลสำหรับระบบ E-commerce
-- (นำไปรันในเมนู SQL ของ phpMyAdmin)
-- --------------------------------------------------------

-- 1. เพิ่ม Role สิทธิ์การใช้งานในตาราง users
ALTER TABLE `users` ADD COLUMN `role` VARCHAR(20) DEFAULT 'user';

-- เปลี่ยน Role ของ user แรกที่เป็นแอดมินหรือที่มีอยู่แล้ว (ถ้ามี)
-- UPDATE `users` SET `role` = 'admin' WHERE `id` = 1;

-- 2. สร้างตารางหมวดหมู่สินค้า (Categories)
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- เพิ่มข้อมูลหมวดหมู่เริ่มต้น
INSERT INTO `categories` (`name`) VALUES 
('ทั่วไป'),
('แอคชั่น'),
('ผจญภัย'),
('เกมสวมบทบาท (RPG)'),
('กีฬา');

-- 3. เพิ่มคอลัมน์ category_id ในตาราง products
ALTER TABLE `products` ADD COLUMN `category_id` INT DEFAULT 1;

-- 4. สร้างตารางตะกร้าสินค้า (Carts)
CREATE TABLE IF NOT EXISTS `carts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. สร้างตารางสินค้าในตะกร้า (Cart Items)
CREATE TABLE IF NOT EXISTS `cart_items` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `cart_id` INT NOT NULL,
  `product_id` INT NOT NULL,
  `quantity` INT DEFAULT 1,
  FOREIGN KEY (`cart_id`) REFERENCES `carts`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`product_id`) REFERENCES `products`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
