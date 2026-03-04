<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// เช็คว่าล็อกอินหรือยัง
function require_login()
{
    if (!isset($_SESSION['user_id'])) {
        // เช็คว่าไฟล์ที่เรียกใช้งานฟังก์ชันนี้ อยู่ในโฟลเดอร์ actions หรือไม่
        if (basename(dirname($_SERVER['PHP_SELF'])) === 'actions') {
            header("Location: ../auth.php"); // ถ้าใช่ ให้ถอยออกมา 1 โฟลเดอร์
        } else {
            header("Location: auth.php");    // ถ้าไม่ใช่ (อยู่ข้างนอก) ก็เข้า auth.php ปกติ
        }
        exit();
    }
}

// เช็คว่าเป็นแอดมินหรือไม่ (ต้องล็อกอินก่อนด้วย)
function require_admin()
{
    require_login();
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        // ถ้าไม่ใช่แอดมิน ให้เด้งกลับไปหน้าร้าน
        if (basename(dirname($_SERVER['PHP_SELF'])) === 'actions') {
            header("Location: ../index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    }
}
?>