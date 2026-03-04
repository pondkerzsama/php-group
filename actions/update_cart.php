<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $user_id = $_SESSION['user_id'];
    $item_id = $_POST['item_id'];
    $action = $_POST['action'];

    try {
        // ตรวจสอบว่า item นี้เป็นของผู้ใช้คนนี้หรือไม่
        $stmt_check = $pdo->prepare("
            SELECT ci.id, ci.quantity, p.stock 
            FROM cart_items ci
            JOIN carts c ON ci.cart_id = c.id
            JOIN products p ON ci.product_id = p.id
            WHERE ci.id = ? AND c.user_id = ?
        ");
        $stmt_check->execute([$item_id, $user_id]);
        $item = $stmt_check->fetch();

        if ($item) {
            if ($action === 'increase') {
                if ($item['quantity'] < $item['stock']) {
                    $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?")->execute([$item_id]);
                }
                else {
                    $_SESSION['error_msg'] = "จำนวนสินค้าถึงขีดจำกัดสต็อกแล้ว";
                }
            }
            elseif ($action === 'decrease') {
                if ($item['quantity'] > 1) {
                    $pdo->prepare("UPDATE cart_items SET quantity = quantity - 1 WHERE id = ?")->execute([$item_id]);
                }
                else {
                    // ถ้าเหลือ 1 แล้วกดลด ให้ลบออก
                    $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item_id]);
                }
            }
            elseif ($action === 'remove') {
                $pdo->prepare("DELETE FROM cart_items WHERE id = ?")->execute([$item_id]);
                $_SESSION['success_msg'] = "ลบสินค้าออกจากตะกร้าแล้ว";
            }
        }

    }
    catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }
}

// เด้งกลับไปหน้าตะกร้า
header("Location: ../cart.php");
exit();
?>
