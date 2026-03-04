<?php
session_start();
require_once 'includes/db.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    
    if (empty($username) || empty($password)) {
        $error = "กรุณากรอกข้อมูลให้ครบถ้วน";
    } else {
        // ตรวจสอบว่า username ซ้ำหรือไม่
        $stmt_check = $pdo->prepare("SELECT id FROM users WHERE username = ?");
        $stmt_check->execute([$username]);
        if ($stmt_check->fetch()) {
            $error = "ชื่อผู้ใช้นี้มีคนใช้แล้ว";
        } else {
            // สมัครสมาชิก (เพิ่ม role เป็น 'user' ตาม default)
            $stmt_insert = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'user')");
            if ($stmt_insert->execute([$username, $password])) {
                $success = "สมัครสมาชิกสำเร็จ! กรุณาเข้าสู่ระบบ";
            } else {
                $error = "เกิดข้อผิดพลาดในการสมัครสมาชิก";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>Register - 🛒Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f0f2f5] min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-xl shadow-md w-96">
        <h1 class="text-2xl font-bold mb-6 text-center text-gray-800">สมัครสมาชิก</h1>
        
        <?php if($error): ?>
            <div class="bg-red-100 text-red-600 p-3 rounded mb-4 text-center text-sm"><?= $error ?></div>
        <?php endif; ?>
        <?php if($success): ?>
            <div class="bg-green-100 text-green-600 p-3 rounded mb-4 text-center text-sm"><?= $success ?></div>
            <div class="text-center mb-4">
                <a href="login.php" class="text-indigo-600 hover:underline">ไปหน้าเข้าสู่ระบบ</a>
            </div>
        <?php else: ?>
            <form method="POST" class="space-y-4">
                <input type="text" name="username" placeholder="ชื่อผู้ใช้" required class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <input type="password" name="password" placeholder="รหัสผ่าน" required class="w-full p-2 border rounded-lg focus:ring-2 focus:ring-indigo-500">
                <button type="submit" class="w-full bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition">สมัครสมาชิก</button>
            </form>
            <div class="mt-4 text-center text-sm text-gray-600">
                มีบัญชีแล้วใช่ไหม? <a href="login.php" class="text-indigo-600 hover:underline">เข้าสู่ระบบ</a>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>
