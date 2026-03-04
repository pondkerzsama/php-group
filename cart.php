<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();

$user_id = $_SESSION['user_id'];

// ดึงข้อมูลสินค้าในตะกร้า
$sql = "
    SELECT ci.id as item_id, ci.quantity, p.id as product_id, p.name, p.price, p.image, p.stock
    FROM cart_items ci
    JOIN carts c ON ci.cart_id = c.id
    JOIN products p ON ci.product_id = p.id
    WHERE c.user_id = ?
";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

// คำนวณราคารวม
$total_price = 0;
foreach ($cart_items as $item) {
    $total_price += $item['price'] * $item['quantity'];
}
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ตะกร้าสินค้า - Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
        }
        function toggleDarkMode() {
            document.documentElement.classList.toggle('dark');
        }
    </script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors duration-200">

<!-- Navbar -->
<nav class="bg-[#1e293b] text-white shadow-md mb-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🛒</span>
                <span class="font-bold text-xl tracking-tight">Gameproduct Store</span>
            </div>
            <div class="flex items-center space-x-4">
                <button onclick="toggleDarkMode()" class="p-2 rounded-md hover:bg-gray-700 transition" title="Toggle Dark Mode">🌙/☀️</button>
                <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">หน้าหลัก</a>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_index.php" class="bg-yellow-500 hover:bg-yellow-600 px-3 py-2 rounded-md transition text-sm font-medium">⚙️ Admin Panel</a>
                <?php endif; ?>
                <a href="cart.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium border-b-2 border-indigo-400">ตะกร้า</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded-md transition text-sm font-medium">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-4xl mx-auto px-4 pb-12">
    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-8">ตะกร้าสินค้าของคุณ</h1>

    <!-- Messages -->
    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 shadow-sm border border-green-200">
            <?= $_SESSION['success_msg']; unset($_SESSION['success_msg']); ?>
        </div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 shadow-sm border border-red-200">
            <?= $_SESSION['error_msg']; unset($_SESSION['error_msg']); ?>
        </div>
    <?php endif; ?>

    <?php if(count($cart_items) > 0): ?>
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden">
            <table class="w-full text-left">
                <thead class="bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">
                    <tr>
                        <th class="p-4">สินค้า</th>
                        <th class="p-4 text-center">ราคาต่อชิ้น</th>
                        <th class="p-4 text-center">จำนวน</th>
                        <th class="p-4 text-center">รวม</th>
                        <th class="p-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php foreach($cart_items as $item): ?>
                        <tr class="text-gray-800 dark:text-white">
                            <td class="p-4 flex items-center gap-4">
                                <?php if($item['image']): ?>
                                    <img src="uploads/<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-16 h-16 object-cover rounded-md">
                                <?php else: ?>
                                    <div class="w-16 h-16 bg-gray-200 dark:bg-gray-700 rounded-md flex items-center justify-center text-xs text-gray-500">No Image</div>
                                <?php endif; ?>
                                <div>
                                    <div class="font-bold line-clamp-2"><?= htmlspecialchars($item['name']) ?></div>
                                    <div class="text-xs text-green-600 dark:text-green-400">คงเหลือ <?= $item['stock'] ?> ชิ้น</div>
                                </div>
                            </td>
                            <td class="p-4 text-center">฿<?= number_format($item['price'], 2) ?></td>
                            <td class="p-4 text-center">
                                <form action="actions/update_cart.php" method="POST" class="inline-flex items-center gap-2 border dark:border-gray-600 rounded-lg px-2 py-1">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                    <button type="submit" name="action" value="decrease" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 font-bold px-2" <?= $item['quantity'] <= 1 ? 'disabled class="opacity-50 cursor-not-allowed"' : '' ?>>-</button>
                                    <span class="w-8 text-center font-semibold"><?= $item['quantity'] ?></span>
                                    <button type="submit" name="action" value="increase" class="text-gray-500 dark:text-gray-400 hover:text-indigo-600 font-bold px-2" <?= $item['quantity'] >= $item['stock'] ? 'disabled class="opacity-50 cursor-not-allowed"' : '' ?>>+</button>
                                </form>
                            </td>
                            <td class="p-4 text-center font-bold text-green-600 dark:text-green-400">
                                ฿<?= number_format($item['price'] * $item['quantity'], 2) ?>
                            </td>
                            <td class="p-4 text-right">
                                <form action="actions/update_cart.php" method="POST" onsubmit="return confirm('คุณต้องการลบสินค้านี้ออกจากตะกร้าใช่หรือไม่?');">
                                    <input type="hidden" name="item_id" value="<?= $item['item_id'] ?>">
                                    <button type="submit" name="action" value="remove" class="text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 dark:bg-gray-700 dark:hover:bg-gray-600 p-2 rounded-lg transition" title="ลบสินค้า">
                                        🗑️
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <div class="p-6 bg-gray-50 dark:bg-gray-800 border-t dark:border-gray-700 flex flex-col sm:flex-row justify-between items-center gap-4">
                <a href="index.php" class="text-indigo-600 hover:text-indigo-800 dark:text-indigo-400 font-medium">&larr; เลือกซื้อสินค้าต่อ</a>
                <div class="flex items-center gap-6">
                    <span class="text-gray-600 dark:text-gray-300">ราคาสุทธิ:</span>
                    <span class="text-3xl font-bold text-green-600 dark:text-green-400">฿<?= number_format($total_price, 2) ?></span>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-8 py-3 rounded-xl font-bold transition shadow-lg whitespace-nowrap">ดำเนินการชำระเงิน</button>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl shadow-md border dark:border-gray-700">
            <div class="text-6xl mb-6">🛒</div>
            <h2 class="text-2xl font-bold text-gray-700 dark:text-gray-200 mb-2">ตะกร้าสินค้าของคุณยังว่างเปล่า</h2>
            <p class="text-gray-500 dark:text-gray-400 mb-8">ลองไปดูสินค้าในร้านของเราก่อนสิ!</p>
            <a href="index.php" class="bg-indigo-600 text-white px-8 py-3 rounded-xl font-bold hover:bg-indigo-700 transition shadow-md inline-block">ไปเลือกซื้อสินค้า</a>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
