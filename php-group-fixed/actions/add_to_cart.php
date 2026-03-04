<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_login(); // ต้องล็อกอินก่อน

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = $_POST['product_id'];
    $quantity = 1;

    try {
        // ตรวจสอบว่าสินค้ามีสต็อกหรือไม่
        $stmt_check_stock = $pdo->prepare("SELECT stock FROM products WHERE id = ?");
        $stmt_check_stock->execute([$product_id]);
        $stock = $stmt_check_stock->fetchColumn();

        if ($stock <= 0) {
            $_SESSION['error_msg'] = "ขออภัย สินค้านี้หมดสต็อกแล้ว";
            header("Location: ../index.php");
            exit;
        }

        // ค้นหาตะกร้าของ user
        $stmt_cart = $pdo->prepare("SELECT id FROM carts WHERE user_id = ?");
        $stmt_cart->execute([$user_id]);
        $cart = $stmt_cart->fetch();

        // ถ้ายังไม่มีตะกร้า ให้สร้างใหม่
        if (!$cart) {
            $stmt_new_cart = $pdo->prepare("INSERT INTO carts (user_id) VALUES (?)");
            $stmt_new_cart->execute([$user_id]);
            $cart_id = $pdo->lastInsertId();
        }
        else {
            $cart_id = $cart['id'];
        }

        // เช็คว่ามีสินค้านี้ในตะกร้าหรือยัง
        $stmt_check_item = $pdo->prepare("SELECT id, quantity FROM cart_items WHERE cart_id = ? AND product_id = ?");
        $stmt_check_item->execute([$cart_id, $product_id]);
        $item = $stmt_check_item->fetch();

        if ($item) {
            // ถ้ามีแล้ว เพิ่มจำนวน (+1) แต่ต้องไม่เกินสต็อก
            if ($item['quantity'] < $stock) {
                $stmt_update = $pdo->prepare("UPDATE cart_items SET quantity = quantity + 1 WHERE id = ?");
                $stmt_update->execute([$item['id']]);
                $_SESSION['success_msg'] = "เพิ่มจำนวนสินค้าลงในตะกร้าแล้ว";
            }
            else {
                $_SESSION['error_msg'] = "ไม่สามารถเพิ่มสินค้าได้ (เกินจำนวนสต็อก)";
            }
        }
        else {
            // ถ้ายืนยันว่าไม่มีในตะกร้า ให้เพิ่มเข้าไปใหม่
            $stmt_add = $pdo->prepare("INSERT INTO cart_items (cart_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt_add->execute([$cart_id, $product_id, $quantity]);
            $_SESSION['success_msg'] = "เพิ่มสินค้าลงตะกร้าเรียบร้อยแล้ว! 🛒";
        }

    }
    catch (PDOException $e) {
        $_SESSION['error_msg'] = "เกิดข้อผิดพลาด: " . $e->getMessage();
    }

    // เด้งกลับไปหน้าเดิม (หน้าร้าน)
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../index.php'));
    exit();
}
else {
    header("Location: ../index.php");
    exit();
}
?>
