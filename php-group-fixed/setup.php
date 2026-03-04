<?php
require_once 'includes/db.php';

echo "<h1>🛠️ Database Setup for E-commerce</h1>";

try {
    echo "<ul>";

    // 1. ตาราง users: เพิ่ม role
    try {
        $pdo->exec("ALTER TABLE users ADD COLUMN role VARCHAR(20) DEFAULT 'user'");
        echo "<li>✅ เพิ่มคอลัมน์ `role` ในตาราง `users` สำเร็จ!</li>";
    }
    catch (PDOException $e) {
        if ($e->getCode() == '42S21') {
            echo "<li>⚠️ คอลัมน์ `role` มีอยู่แล้วในตาราง `users`</li>";
        }
        else {
            echo "<li>❌ Error (users.role): " . $e->getMessage() . "</li>";
        }
    }

    // 2. ตาราง categories
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS categories (
            id INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");

        // เพิ่มข้อมูลตัวอย่าง
        $check = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
        if ($check == 0) {
            $pdo->exec("INSERT INTO categories (name) VALUES ('ทั่วไป'), ('แอคชั่น'), ('ผจญภัย'), ('เกมสวมบทบาท (RPG)'), ('กีฬา')");
        }
        echo "<li>✅ สร้างตาราง `categories` สำเร็จ!</li>";
    }
    catch (PDOException $e) {
        echo "<li>❌ Error (categories): " . $e->getMessage() . "</li>";
    }

    // 3. ตาราง products: เพิ่ม category_id
    try {
        $pdo->exec("ALTER TABLE products ADD COLUMN category_id INT DEFAULT 1");
        echo "<li>✅ เพิ่มคอลัมน์ `category_id` ในตาราง `products` สำเร็จ!</li>";
    }
    catch (PDOException $e) {
        if ($e->getCode() == '42S21') {
            echo "<li>⚠️ คอลัมน์ `category_id` มีอยู่แล้วในตาราง `products`</li>";
        }
        else {
            echo "<li>❌ Error (products.category_id): " . $e->getMessage() . "</li>";
        }
    }

    // 4. ตาราง carts
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS carts (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "<li>✅ สร้างตาราง `carts` สำเร็จ!</li>";
    }
    catch (PDOException $e) {
        echo "<li>❌ Error (carts): " . $e->getMessage() . "</li>";
    }

    // 5. ตาราง cart_items
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS cart_items (
            id INT AUTO_INCREMENT PRIMARY KEY,
            cart_id INT NOT NULL,
            product_id INT NOT NULL,
            quantity INT DEFAULT 1,
            FOREIGN KEY (cart_id) REFERENCES carts(id) ON DELETE CASCADE,
            FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
        echo "<li>✅ สร้างตาราง `cart_items` สำเร็จ!</li>";
    }
    catch (PDOException $e) {
        echo "<li>❌ Error (cart_items): " . $e->getMessage() . "</li>";
    }

    echo "</ul>";
    echo "<h2>🎉 อัปเดตฐานข้อมูลเสร็จสมบูรณ์!</h2>";
    echo "<p><a href='index.php'>กลับไปหน้าหลัก</a></p>";

}
catch (Exception $e) {
    echo "<h2>เกิดข้อผิดพลาดรุนแรง: " . $e->getMessage() . "</h2>";
}
?>
