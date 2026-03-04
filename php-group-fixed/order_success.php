<?php
session_start();
require_once 'includes/auth_check.php';
require_login();

$success = $_SESSION['success_msg'] ?? '';
$warning = $_SESSION['error_msg']   ?? '';
unset($_SESSION['success_msg'], $_SESSION['error_msg']);
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สั่งซื้อสำเร็จ — Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>
        // ตรวจสอบและใช้ค่าธีมเดิมจาก localStorage
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
    <style>
        @keyframes popIn {
            0%   { transform: scale(0.5) rotate(-10deg); opacity: 0; }
            70%  { transform: scale(1.15) rotate(3deg); }
            100% { transform: scale(1) rotate(0); opacity: 1; }
        }
        .pop-in { animation: popIn 0.6s cubic-bezier(0.175,0.885,0.32,1.275) forwards; }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(16px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .fade-up { animation: fadeUp 0.5s ease forwards; }
        .delay-1 { animation-delay: 0.3s; opacity: 0; }
        .delay-2 { animation-delay: 0.5s; opacity: 0; }
        .delay-3 { animation-delay: 0.7s; opacity: 0; }
    </style>
</head>
<body class="bg-gradient-to-br from-green-50 to-emerald-100 dark:from-gray-900 dark:to-gray-800 min-h-screen flex items-center justify-center p-4 transition-colors duration-300">

<div class="bg-white dark:bg-gray-800 rounded-3xl shadow-2xl max-w-md w-full p-10 text-center border dark:border-gray-700 transition-colors duration-300">

    <div class="text-7xl pop-in inline-block mb-5">🎉</div>

    <h1 class="text-3xl font-extrabold text-gray-800 dark:text-white mb-2 fade-up delay-1">สั่งซื้อสำเร็จ!</h1>
    <p class="text-gray-500 dark:text-gray-400 text-sm mb-6 fade-up delay-1">ขอบคุณที่ช้อปปิ้งกับ Gameproduct Store</p>

    <?php if($success): ?>
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 rounded-xl p-4 text-sm text-left mb-4 fade-up delay-2">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <?php if($warning): ?>
        <div class="bg-amber-50 dark:bg-amber-900/30 border border-amber-200 dark:border-amber-800 text-amber-700 dark:text-amber-400 rounded-xl p-4 text-sm text-left mb-4 fade-up delay-2">
            ⚠️ <?= htmlspecialchars($warning) ?>
        </div>
    <?php endif; ?>

    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-2xl p-5 mb-6 fade-up delay-2">
        <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
            <span class="text-2xl">📦</span>
            <div class="text-left">
                <div class="font-semibold text-gray-800 dark:text-white">สต็อกสินค้าถูกอัปเดตแล้ว</div>
                <div class="text-xs text-gray-400 dark:text-gray-500 mt-0.5">จำนวนสินค้าถูกหักออกจากระบบเรียบร้อย</div>
            </div>
        </div>
    </div>

    <div class="flex gap-3 fade-up delay-3">
        <a href="index.php" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl transition-all active:scale-95 shadow-md">
            🛍️ ช้อปต่อ
        </a>
        <a href="cart.php" class="flex-1 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 font-bold py-3 rounded-xl transition-all shadow-sm">
            🛒 ตะกร้า
        </a>
    </div>
</div>

</body>
</html>