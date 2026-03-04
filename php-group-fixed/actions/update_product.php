<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_admin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id          = (int)$_POST['id'];
    $name        = trim($_POST['productname'] ?? '');
    $detail      = trim($_POST['detail']      ?? '');
    $price       = (float)($_POST['price']    ?? 0);
    $stock       = (int)($_POST['stock']      ?? 0);
    $p_date      = trim($_POST['production_date'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 1);

    // production_date: ถ้าว่างให้เป็น NULL
    $p_date = ($p_date === '') ? null : $p_date;

    // img: เริ่มต้นจาก field URL/path ที่ส่งมา
    $img_path = trim($_POST['img'] ?? '');

    // ถ้า img ว่าง ให้ดึงรูปเดิมจาก DB มาใช้
    if ($img_path === '') {
        $stmt_old = $pdo->prepare("SELECT img FROM products WHERE id = ?");
        $stmt_old->execute([$id]);
        $img_path = $stmt_old->fetchColumn() ?: '';
    }

    // ถ้ามีอัปโหลดไฟล์ใหม่ ให้ทับ
    if (isset($_FILES['img_file']) && $_FILES['img_file']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = '../uploads/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
        $ext      = pathinfo($_FILES['img_file']['name'], PATHINFO_EXTENSION);
        $fileName = uniqid('prod_', true) . '.' . $ext;
        if (move_uploaded_file($_FILES['img_file']['tmp_name'], $uploadDir . $fileName)) {
            $img_path = 'uploads/' . $fileName;
        }
    }

    try {
        $sql = "UPDATE products SET productname=?, detail=?, price=?, stock=?, production_date=?, img=?, category_id=? WHERE id=?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $detail, $price, $stock, $p_date, $img_path, $category_id, $id]);

        $_SESSION['success_msg'] = '✅ อัปเดตสินค้าสำเร็จ';
        header("Location: ../admin_index.php?status=updated");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_msg'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
        header("Location: ../edit.php?id=$id");
        exit();
    }
} else {
    header("Location: ../admin_index.php");
    exit();
}
