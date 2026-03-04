<?php
session_start();
require_once 'includes/db.php';

// ดึงหมวดหมู่ทั้งหมดสำหรับทำ Filter
$stmt_cats = $pdo->query("SELECT * FROM categories");
$categories = $stmt_cats->fetchAll();

// กำหนดเงื่อนไขการค้นหา/กรอง
$where_clauses = [];
$params = [];

if (isset($_GET['search']) && trim($_GET['search']) !== '') {
    $where_clauses[] = "p.productname LIKE ?";
    $params[] = "%" . trim($_GET['search']) . "%";
}

if (isset($_GET['category']) && $_GET['category'] !== '') {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}

$where_sql = "";
if (count($where_clauses) > 0) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

// ดึงสินค้า
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        $where_sql 
        ORDER BY p.id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// เช็คจำนวนสินค้าในตะกร้า
$cart_count = 0;
if (isset($_SESSION['user_id'])) {
    $stmt_cart = $pdo->prepare("SELECT SUM(quantity) FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
    $stmt_cart->execute([$_SESSION['user_id']]);
    $cart_count = $stmt_cart->fetchColumn() ?: 0;
}
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gameproduct Store</title>
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
                <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium border-b-2 border-indigo-400">หน้าหลัก</a>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_index.php" class="bg-yellow-500 hover:bg-yellow-600 px-3 py-2 rounded-md transition text-sm font-medium">⚙️ Admin Panel</a>
                    <?php endif; ?>
                    <a href="cart.php" class="flex items-center hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">
                        ตะกร้า <span class="ml-2 bg-red-500 text-white rounded-full px-2 py-0.5 text-xs"><?= $cart_count ?></span>
                    </a>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded-md transition text-sm font-medium">ออกจากระบบ</a>
                <?php else: ?>
                    <a href="login.php" class="bg-indigo-600 hover:bg-indigo-700 px-3 py-2 rounded-md transition text-sm font-medium">เข้าสู่ระบบ</a>
                    <a href="register.php" class="bg-green-600 hover:bg-green-700 px-3 py-2 rounded-md transition text-sm font-medium">สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-6xl mx-auto px-4 pb-12">

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

    <!-- Header & Filter -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-8 gap-4">
        <h1 class="text-3xl font-bold text-gray-800 dark:text-white">สินค้าทั้งหมด</h1>
        <form method="GET" class="flex gap-2 w-full md:w-auto">
            <select name="category" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 dark:bg-gray-800 dark:text-white dark:border-gray-700">
                <option value="">ทั้งหมด (Categories)</option>
                <?php foreach($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <input type="text" name="search" placeholder="ค้นหาสินค้า..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>" class="px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 flex-1 dark:bg-gray-800 dark:text-white dark:border-gray-700">
            <button type="submit" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition">ค้นหา</button>
        </form>
    </div>

    <!-- Product Grid -->
    <?php if(count($products) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <?php foreach($products as $product): ?>
                <div class="bg-white dark:bg-gray-800 rounded-xl shadow-md overflow-hidden hover:shadow-xl transition-shadow duration-300 flex flex-col">
                    <?php if($product['img']): ?>
                        <img src="<?= htmlspecialchars($product['img']) ?>" alt="<?= htmlspecialchars($product['productname']) ?>" class="w-full h-48 object-cover">                                alt="<?= htmlspecialchars($product['productname']) ?>" 
                                class="w-full h-48 object-cover">
                    <?php else: ?>
                        <div class="w-full h-48 bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500 dark:text-gray-400">
                            No Img
                        </div>
                    <?php endif; ?>
                    
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="text-xs text-indigo-600 dark:text-indigo-400 font-bold uppercase mb-1"><?= htmlspecialchars($product['category_name'] ?? 'ไม่มีหมวดหมู่') ?></div>
                        <h2 class="text-lg font-bold text-gray-800 dark:text-white mb-2 line-clamp-2"><?= htmlspecialchars($product['productname']) ?></h2>
                        <div class="text-2xl font-bold text-green-600 mb-4 mt-auto border-t pt-4 dark:border-gray-700">฿<?= number_format($product['price'], 2) ?></div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500 dark:text-gray-400">คงเหลือ: <?= $product['stock'] ?> ชิ้น</span>
                            
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <?php if($product['stock'] > 0): ?>
                                    <form action="actions/add_to_cart.php" method="POST">
                                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-lg hover:bg-indigo-700 transition flex items-center gap-1">
                                            <span>+</span> ตะกร้า
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button disabled class="bg-gray-400 text-white px-4 py-2 rounded-lg cursor-not-allowed">สินค้าหมด</button>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="login.php" class="bg-gray-500 text-white px-4 py-2 rounded-lg hover:bg-gray-600 transition text-sm">ล็อกอินเพื่อซื้อ</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-20 bg-white dark:bg-gray-800 rounded-xl shadow">
            <div class="text-5xl mb-4">😢</div>
            <h2 class="text-2xl font-semibold text-gray-600 dark:text-gray-300">ไม่พบสินค้าที่คุณค้นหา</h2>
            <a href="index.php" class="text-indigo-600 hover:underline mt-2 inline-block">ดูสินค้าทั้งหมด</a>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
