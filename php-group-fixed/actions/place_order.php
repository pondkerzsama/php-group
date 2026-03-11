<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_login();

$user_id = $_SESSION['user_id'];

// --- จัดการอัปโหลดไฟล์สลิป (ก่อนเริ่ม Transaction) ---
$slip_path = null;
if (isset($_FILES['slip_image']) && $_FILES['slip_image']['error'] === UPLOAD_ERR_OK) {
    // กำหนดโฟลเดอร์ที่จะเก็บสลิป
    $upload_dir = '../uploads/slips/';
    
    // ถ้ายังไม่มีโฟลเดอร์ ให้ระบบสร้างให้เลย
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    // ดึงนามสกุลไฟล์ และสร้างชื่อไฟล์ใหม่กันชื่อซ้ำ
    $file_extension = strtolower(pathinfo($_FILES['slip_image']['name'], PATHINFO_EXTENSION));
    $new_filename = 'slip_' . time() . '_' . uniqid() . '.' . $file_extension;
    $target_file = $upload_dir . $new_filename;
    
    // ย้ายไฟล์จาก temp ไปยังโฟลเดอร์ของเรา
    if (move_uploaded_file($_FILES['slip_image']['tmp_name'], $target_file)) {
        // เก็บพาธสำหรับเซฟลงฐานข้อมูล (ไม่เอา ../)
        $slip_path = 'uploads/slips/' . $new_filename; 
    } else {
        $_SESSION['error_msg'] = 'เกิดข้อผิดพลาดในการอัปโหลดรูปสลิป กรุณาลองใหม่';
        header("Location: ../checkout.php");
        exit();
    }
} else {
    $_SESSION['error_msg'] = 'กรุณาแนบรูปสลิปโอนเงินก่อนยืนยันคำสั่งซื้อ';
    header("Location: ../checkout.php");
    exit();
}
// --------------------------------------------------

try {
    $pdo->beginTransaction();

    // 1. ดึงสินค้าทั้งหมดในตะกร้า
    $sql = "SELECT ci.id as item_id, ci.quantity, ci.cart_id,
                   p.id as product_id, p.productname, p.stock, p.price
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE c.user_id = ?
            FOR UPDATE";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$user_id]);
    $items = $stmt->fetchAll();

    if (empty($items)) {
        $pdo->rollBack();
        $_SESSION['error_msg'] = 'ตะกร้าของคุณว่างเปล่า';
        header("Location: ../cart.php");
        exit();
    }

    $order_summary = [];
    $adjusted      = [];
    $removed       = [];
    $total_price   = 0;

    // สร้าง Order เปล่าๆ พร้อมบันทึกพาร์ทของสลิปโอนเงิน
    $stmt_order = $pdo->prepare("INSERT INTO orders (user_id, total_price, slip_image) VALUES (?, 0, ?)");
    $stmt_order->execute([$user_id, $slip_path]);
    $order_id = $pdo->lastInsertId();

    // 2. ตรวจสอบ ตัด stock และบันทึก order_items
    foreach ($items as $item) {
        $available = (int)$item['stock'];
        $wanted    = (int)$item['quantity'];

        if ($available <= 0) {
            $removed[] = $item['productname'];
            $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item['item_id']]);
            continue;
        }

        $actual_qty = min($wanted, $available);
        $item_total = $actual_qty * $item['price'];
        $total_price += $item_total;

        if ($actual_qty < $wanted) {
            $adjusted[] = "{$item['productname']} (ปรับเป็น {$actual_qty} ชิ้น)";
        }

        // ตัด stock
        $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?")
            ->execute([$actual_qty, $item['product_id'], $actual_qty]);

        // บันทึกลง order_items
        $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)")
            ->execute([$order_id, $item['product_id'], $actual_qty, $item['price']]);

        $order_summary[] = "{$item['productname']} x{$actual_qty}";

        // ลบออกจากตะกร้า
        $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item['item_id']]);
    }

    // อัปเดตราคารวมของ Order
    if ($total_price > 0) {
        $pdo->prepare("UPDATE orders SET total_price = ? WHERE id = ?")->execute([$total_price, $order_id]);
    } else {
        // ถ้าไม่มีสินค้าถูกสั่งเลย ให้ลบ Order และลบรูปสลิปทิ้ง
        $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
        if (file_exists('../' . $slip_path)) {
            unlink('../' . $slip_path);
        }
    }

    $pdo->commit();

    // 3. สร้างข้อความแจ้งเตือน
    if (!empty($order_summary)) {
        $msg = '🎉 สั่งซื้อสำเร็จ! รหัสอ้างอิงออเดอร์: #' . $order_id . ' สินค้า: ' . implode(', ', $order_summary);
        if (!empty($adjusted)) {
            $msg .= ' (ปรับจำนวน: ' . implode(', ', $adjusted) . ')';
        }
        $_SESSION['success_msg'] = $msg;
    }

    if (!empty($removed)) {
        $_SESSION['error_msg'] = 'สินค้าต่อไปนี้หมดสต็อกและถูกนำออกจากตะกร้า: ' . implode(', ', $removed);
    }

    if (empty($order_summary) && !empty($removed)) {
        $_SESSION['error_msg'] = 'ขออภัย สินค้าทุกชิ้นในตะกร้าหมดสต็อกแล้ว';
        header("Location: ../cart.php");
        exit();
    }

    header("Location: ../order_success.php");
    exit();

} catch (PDOException $e) {
    $pdo->rollBack();
    $_SESSION['error_msg'] = 'เกิดข้อผิดพลาด: ' . $e->getMessage();
    header("Location: ../checkout.php");
    exit();
}
?>