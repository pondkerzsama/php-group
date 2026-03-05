<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();

$user_id = $_SESSION['user_id'];

// ดึงประวัติคำสั่งซื้อทั้งหมดของ User นี้
$stmt_orders = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
$stmt_orders->execute([$user_id]);
$orders = $stmt_orders->fetchAll();
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ประวัติการสั่งซื้อ - Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>
        // ตรวจสอบค่าธีม
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
        function toggleTheme() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('color-theme', 'light');
            } else {
                document.documentElement.classList.add('dark');
                localStorage.setItem('color-theme', 'dark');
            }
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200 min-h-screen flex flex-col">

    <?php include 'includes/navbar.php'; ?>

    <div class="max-w-5xl mx-auto px-4 py-8 flex-1 w-full">
        <div class="flex items-center justify-between mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white flex items-center gap-3">
                <span>📜</span> ประวัติการสั่งซื้อของคุณ
            </h1>
            <a href="index.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2.5 rounded-xl font-semibold transition shadow-sm text-sm">
                เลือกซื้อสินค้าต่อ
            </a>
        </div>

        <?php if(count($orders) > 0): ?>
            <div class="space-y-6">
                <?php foreach($orders as $order): ?>
                    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                        <div class="bg-gray-50 dark:bg-gray-700/50 px-6 py-4 border-b border-gray-100 dark:border-gray-700 flex flex-wrap justify-between items-center gap-4">
                            <div>
                                <span class="text-sm text-gray-500 dark:text-gray-400">คำสั่งซื้อ <b class="text-gray-800 dark:text-white">#<?= $order['id'] ?></b></span>
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-1">สั่งเมื่อ: <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></div>
                            </div>
                            <div class="text-right">
                                <div class="text-sm text-gray-500 dark:text-gray-400">ยอดรวมสุทธิ</div>
                                <div class="text-lg font-bold text-green-600 dark:text-green-400">฿<?= number_format($order['total_price'], 2) ?></div>
                            </div>
                        </div>

                        <div class="p-6">
                            <?php
                                // ดึงรายการสินค้าของออเดอร์นี้
                                $stmt_items = $pdo->prepare("
                                    SELECT oi.*, p.productname, p.img 
                                    FROM order_items oi 
                                    JOIN products p ON oi.product_id = p.id 
                                    WHERE oi.order_id = ?
                                ");
                                $stmt_items->execute([$order['id']]);
                                $items = $stmt_items->fetchAll();
                            ?>
                            <ul class="divide-y divide-gray-100 dark:divide-gray-700">
                                <?php foreach($items as $item): ?>
                                    <li class="py-3 flex items-center justify-between gap-4">
                                        <div class="flex items-center gap-4">
                                            <?php if($item['img']): ?>
                                                <img src="<?= htmlspecialchars($item['img']) ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                            <?php else: ?>
                                                <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-xl opacity-50">🎮</div>
                                            <?php endif; ?>
                                            <div>
                                                <div class="font-semibold text-gray-800 dark:text-white text-sm line-clamp-1"><?= htmlspecialchars($item['productname']) ?></div>
                                                <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">฿<?= number_format($item['price'], 2) ?> x <?= $item['quantity'] ?> ชิ้น</div>
                                            </div>
                                        </div>
                                        <div class="font-bold text-gray-700 dark:text-gray-300 text-sm">
                                            ฿<?= number_format($item['price'] * $item['quantity'], 2) ?>
                                        </div>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-12 text-center">
                <div class="text-6xl mb-4 opacity-50">🧾</div>
                <h2 class="text-xl font-bold text-gray-600 dark:text-gray-300 mb-2">คุณยังไม่มีประวัติการสั่งซื้อ</h2>
                <p class="text-gray-400 text-sm mb-6">ลองแวะไปดูสินค้าในร้านของเราก่อนสิ!</p>
                <a href="index.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">ไปช้อปปิ้งเลย</a>
            </div>
        <?php endif; ?>
    </div>

</body>
</html>