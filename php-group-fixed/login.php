<?php
session_start();
require_once 'includes/db.php'; // ใช้ไฟล์เชื่อมต่อที่มีอยู่แล้ว

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // ค้นหาใน DB เดิม
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$user, $pass]);
    $userData = $stmt->fetch();

    if ($userData) {
        $_SESSION['user_id'] = $userData['id'];
        $_SESSION['username'] = $userData['username'];
        $_SESSION['role'] = $userData['role'] ?? 'user';

        // ถ้าเป็น admin ให้ไปหน้า admin
        if ($_SESSION['role'] === 'admin') {
            header("Location: admin_index.php");
        } else {
            header("Location: index.php");
        }
        exit();
    } else {
        $error = "เข้าสู่ระบบไม่สำเร็จ!";
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Login - 🛒Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script> </head>
<body class="bg-[#f0f2f5] min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">เข้าสู่ระบบ</h1>
        <form method="POST" class="space-y-4">
            <input type="text" name="username" placeholder="ชื่อผู้ใช้" required class="w-full p-2 border rounded-lg">
            <input type="password" name="password" placeholder="รหัสผ่าน" required class="w-full p-2 border rounded-lg">
            <button type="submit" class="w-full bg-indigo-600 text-white py-2 rounded-lg hover:bg-indigo-700 transition">เข้าสู่ระบบ</button>
        </form>
        <?php if(isset($error)): ?>
            <p class="text-red-500 mt-4 text-center text-sm"><?= $error ?></p>
        <?php endif; ?>
    </div>
</body>
</html>