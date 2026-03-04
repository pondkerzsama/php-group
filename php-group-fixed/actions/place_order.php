<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_login();

$user_id = $_SESSION['user_id'];

try {
    $pdo->beginTransaction();

    // 1. ดึงสินค้าทั้งหมดในตะกร้า
    $sql = "SELECT ci.id as item_id, ci.quantity, ci.cart_id,
                   p.id as product_id, p.productname, p.stock, p.price
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE c.user_id = ?
            FOR UPDATE";  // lock rows เพื่อป้องกัน race condition
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
    $adjusted      = [];   // รายการที่ต้องปรับจำนวน
    $removed       = [];   // รายการที่หมดสต็อก

    // 2. ตรวจสอบและตัด stock ทีละรายการ
    foreach ($items as $item) {
        $available = (int)$item['stock'];
        $wanted    = (int)$item['quantity'];

        if ($available <= 0) {
            // หมดแล้ว ข้ามไป
            $removed[] = $item['productname'];
            // ลบออกจากตะกร้า
            $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item['item_id']]);
            continue;
        }

        $actual_qty = min($wanted, $available);

        if ($actual_qty < $wanted) {
            $adjusted[] = "{$item['productname']} (ปรับเป็น {$actual_qty} ชิ้น)";
        }

        // ตัด stock
        $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ? AND stock >= ?")
            ->execute([$actual_qty, $item['product_id'], $actual_qty]);

        $order_summary[] = "{$item['productname']} x{$actual_qty}";

        // ลบออกจากตะกร้า
        $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item['item_id']]);
    }

    $pdo->commit();

    // 3. สร้าง success message
    if (!empty($order_summary)) {
        $msg = '🎉 สั่งซื้อสำเร็จ! สินค้า: ' . implode(', ', $order_summary);
        if (!empty($adjusted)) {
            $msg .= ' (ปรับจำนวน: ' . implode(', ', $adjusted) . ')';
        }
        $_SESSION['success_msg'] = $msg;
    }

    if (!empty($removed)) {
        $_SESSION['error_msg'] = 'สินค้าต่อไปนี้หมดสต็อกและถูกนำออกจากตะกร้า: ' . implode(', ', $removed);
    }

    if (empty($order_summary) && !empty($removed)) {
        // ทุกรายการหมดสต็อก
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
