<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// เช็คว่าล็อกอินหรือยัง
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: auth.php");
        exit();
    }
}

// เช็คว่าเป็นแอดมินหรือไม่ (ต้องล็อกอินก่อนด้วย)
function require_admin()
{
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // ถ้าไม่ใช่แอดมิน ให้เด้งกลับไปหน้าร้าน
        header("Location: index.php");
        exit();
    }
}
?>