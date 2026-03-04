<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_admin(); // ล็อกการเข้าถึง

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'];
    $name = $_POST['productname'];
    $detail = $_POST['detail'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $p_date = $_POST['production_date'];
    $category_id = $_POST['category_id'] ?? 1; // เพิ่ม category_id

    // 1. กำหนดค่าเริ่มต้นให้ $img_path โดยดึงจากช่อง URL (ซึ่งมีค่ารูปเดิมอยู่แล้ว)
    // วิธีนี้จะทำให้ถ้าไม่อัปโหลดใหม่ ระบบจะใช้ค่ารูปภาพเดิมทันที
    $img_path = $_POST['img'];

    // 2. เช็คว่ามีการเลือกไฟล์ใหม่จากเครื่องไหม (ถ้ามี จะทับค่าจาก URL ด้านบน)
    if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir))
            mkdir($uploadDir, 0777, true);

        $fileName = uniqid('prod_', true) . '.' . pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION);
        $targetPath = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['img_file']['tmp_name'], $targetPath)) {
            $img_path = 'uploads/' . $fileName; // อัปโหลดสำเร็จ ให้เปลี่ยนพาธไปที่ไฟล์ใหม่
        }
    }

    try {
        // ตอนนี้ $img_path จะมีค่าเสมอ (ไม่เป็นค่าเดิม ก็เป็นไฟล์ใหม่ที่เพิ่งอัปโหลด)
        $sql = "UPDATE products SET productname=?, detail=?, price=?, stock=?, production_date=?, img=?, category_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $detail, $price, $stock, $p_date, $img_path, $category_id, $id]);

        header("Location: ../admin_index.php?status=updated");
        exit();
    }
    catch (PDOException $e) {
        die("Update Error: " . $e->getMessage());
    }
}