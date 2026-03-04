<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_login();

$user_id = $_SESSION['user_id'];

$sql = "SELECT ci.id as item_id, ci.quantity, p.id as product_id,
               p.productname, p.price, p.img, p.stock
        FROM cart_items ci
        JOIN carts c ON ci.cart_id = c.id
        JOIN products p ON ci.product_id = p.id
        WHERE c.user_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll();

if (empty($cart_items)) {
    $_SESSION['error_msg'] = 'ตะกร้าของคุณว่างเปล่า';
    header("Location: cart.php");
    exit();
}

$total = array_sum(array_map(fn($i) => $i['price'] * $i['quantity'], $cart_items));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยืนยันคำสั่งซื้อ — Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900 min-h-screen">

<nav class="bg-[#1e293b] text-white shadow-md mb-8">
    <div class="max-w-4xl mx-auto px-4 flex justify-between items-center h-16">
        <a href="index.php" class="flex items-center gap-2 font-bold text-xl">🛒 Gameproduct Store</a>
        <a href="cart.php" class="text-sm hover:bg-gray-700 px-3 py-2 rounded-md transition">← กลับไปตะกร้า</a>
    </div>
</nav>

<div class="max-w-2xl mx-auto px-4 pb-16">
    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-6">📋 ยืนยันคำสั่งซื้อ</h1>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden mb-5">
        <?php foreach($cart_items as $item): ?>
            <div class="flex items-center gap-4 p-4 border-b border-gray-100 dark:border-gray-700 last:border-0">
                <?php if($item['img']): ?>
                    <img src="<?= htmlspecialchars($item['img']) ?>" class="w-14 h-14 object-cover rounded-xl shrink-0" alt="">
                <?php else: ?>
                    <div class="w-14 h-14 bg-gray-100 dark:bg-gray-700 rounded-xl flex items-center justify-center text-2xl shrink-0">🎮</div>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-800 dark:text-white text-sm truncate"><?= htmlspecialchars($item['productname']) ?></div>
                    <div class="text-xs text-gray-400 mt-0.5">฿<?= number_format($item['price'], 2) ?> &times; <?= $item['quantity'] ?> ชิ้น</div>
                    <?php if($item['quantity'] > $item['stock']): ?>
                        <div class="text-xs text-red-500 mt-0.5 font-semibold">⚠️ สต็อกเหลือเพียง <?= $item['stock'] ?> ชิ้น (จะปรับจำนวนให้อัตโนมัติ)</div>
                    <?php endif; ?>
                </div>
                <div class="font-bold text-green-600 dark:text-green-400 text-sm whitespace-nowrap">
                    ฿<?= number_format($item['price'] * $item['quantity'], 2) ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-2">
            <span>ราคาสินค้ารวม</span><span>฿<?= number_format($total, 2) ?></span>
        </div>
        <div class="flex justify-between text-sm text-gray-500 dark:text-gray-400 mb-4 pb-4 border-b border-gray-100 dark:border-gray-700">
            <span>ค่าจัดส่ง</span><span class="text-green-600 font-semibold">ฟรี 🎉</span>
        </div>
        <div class="flex justify-between text-xl font-bold text-gray-800 dark:text-white">
            <span>ยอดสุทธิ</span>
            <span class="text-green-600 dark:text-green-400">฿<?= number_format($total, 2) ?></span>
        </div>
    </div>

    <form action="actions/place_order.php" method="POST">
        <button type="submit"
            class="w-full bg-green-600 hover:bg-green-700 active:scale-[0.99] text-white font-bold py-4 rounded-2xl text-lg shadow-lg transition-all">
            ✅ ยืนยันคำสั่งซื้อ
        </button>
    </form>
    <a href="cart.php" class="block text-center text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 text-sm mt-4 transition">← ยังไม่พร้อม กลับไปแก้ตะกร้า</a>
</div>
</body>
</html>
