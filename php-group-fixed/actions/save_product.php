<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_admin(); // ล็อกการเข้าถึงให้เฉพาะ admin

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. รับค่าจากฟอร์ม (ต้องชื่อเดียวกับ name ใน <input>)
    $name = $_POST['productname'] ?? null;
    $detail = $_POST['detail'] ?? null;
    $price = $_POST['price'] ?? 0;
    $stock = $_POST['stock'] ?? 0;
    $p_date = $_POST['production_date'] ?? null;
    $category_id = $_POST['category_id'] ?? 1; // เพิ่ม category_id

    // 2. จัดการรูปภาพ (URL หรือ File)
    $img_path = $_POST['img'] ?? '';

    if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $fileName = uniqid('prod_', true) . '.' . pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['img_file']['tmp_name'], $targetPath)) {
            $img_path = 'uploads/' . $fileName;
        }
    }

    // 3. บันทึกลงฐานข้อมูล (เพิ่ม stock, production_date และ category_id เข้าไป)
    try {
        $sql = "INSERT INTO products (productname, detail, price, stock, production_date, img, category_id) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        // ส่งตัวแปรเข้าไปในอาเรย์ให้ครบตามจำนวนเครื่องหมาย ?
        $stmt->execute([$name, $detail, $price, $stock, $p_date, $img_path, $category_id]);

        header("Location: ../admin_index.php?status=success");
        exit();
    }
    catch (PDOException $e) {
        die("เกิดข้อผิดพลาดในการบันทึก: " . $e->getMessage());
    }
}