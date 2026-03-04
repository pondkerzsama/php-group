<?php
session_start();
require_once 'includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: auth.php");
    exit();
}

$categories = [];
try {
    $stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
    $categories = $stmt_cats->fetchAll();
} catch (PDOException $e) {}

$search      = trim($_GET['search']    ?? '');
$category_id = $_GET['category']       ?? '';
$min_price   = $_GET['min_price']      ?? '';
$max_price   = $_GET['max_price']      ?? '';
$sort        = $_GET['sort']           ?? 'newest';

$where_clauses = [];
$params        = [];

if ($search !== '') {
    $where_clauses[] = "(p.productname LIKE ? OR p.detail LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}
if ($category_id !== '') {
    $where_clauses[] = "p.category_id = ?";
    $params[] = $category_id;
}
if ($min_price !== '' && is_numeric($min_price)) {
    $where_clauses[] = "p.price >= ?";
    $params[] = (float)$min_price;
}
if ($max_price !== '' && is_numeric($max_price)) {
    $where_clauses[] = "p.price <= ?";
    $params[] = (float)$max_price;
}

$where_sql = count($where_clauses) ? "WHERE " . implode(" AND ", $where_clauses) : "";

$order_map = [
    'price_asc'  => "ORDER BY p.price ASC",
    'price_desc' => "ORDER BY p.price DESC",
    'name'       => "ORDER BY p.productname ASC",
    'newest'     => "ORDER BY p.id DESC",
];
$order_sql = $order_map[$sort] ?? "ORDER BY p.id DESC";

$sql = "SELECT p.*, c.name as category_name
        FROM products p
        LEFT JOIN categories c ON p.category_id = c.id
        $where_sql $order_sql";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$stmt_cart = $pdo->prepare("SELECT SUM(quantity) FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
$stmt_cart->execute([$_SESSION['user_id']]);
$cart_count = $stmt_cart->fetchColumn() ?: 0;

$price_range = $pdo->query("SELECT MIN(price) as mn, MAX(price) as mx FROM products")->fetch();
$is_filtered = $search !== '' || $category_id !== '' || $min_price !== '' || $max_price !== '';
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🛒 Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <style>
        .product-card { transition: transform 0.2s ease, box-shadow 0.2s ease; }
        .product-card:hover { transform: translateY(-4px); box-shadow: 0 12px 32px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-gray-50 dark:bg-gray-900 transition-colors">

<nav class="bg-[#1e293b] text-white shadow-md sticky top-0 z-50">
    <div class="max-w-7xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <a href="index.php" class="flex items-center gap-2 font-bold text-xl tracking-tight">
                <span class="text-2xl">🛒</span> Gameproduct Store
            </a>
            <div class="flex items-center gap-3">
                <button onclick="document.documentElement.classList.toggle('dark')" class="p-2 rounded-md hover:bg-gray-700 transition">🌙</button>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_index.php" class="bg-yellow-500 hover:bg-yellow-600 px-3 py-2 rounded-md text-sm font-medium transition">⚙️ Admin</a>
                <?php endif; ?>
                <a href="cart.php" class="flex items-center gap-1.5 hover:bg-indigo-500 px-3 py-2 rounded-md text-sm font-medium transition">
                    🛒
                    <?php if($cart_count > 0): ?>
                        <span class="bg-red-500 text-white rounded-full px-2 py-0.5 text-xs font-bold"><?= $cart_count ?></span>
                    <?php endif; ?>
                </a>
                <span class="text-gray-300 text-sm hidden md:block">สวัสดี, <b><?= htmlspecialchars($_SESSION['username']) ?></b></span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-2 rounded-md text-sm font-medium transition">ออกระบบ</a>
            </div>
        </div>
    </div>
</nav>

<div class="max-w-7xl mx-auto px-4 py-8">

    <?php if(isset($_SESSION['success_msg'])): ?>
        <div class="bg-green-100 text-green-800 p-4 rounded-xl mb-5 border border-green-200">✅ <?= htmlspecialchars($_SESSION['success_msg']); unset($_SESSION['success_msg']); ?></div>
    <?php endif; ?>
    <?php if(isset($_SESSION['error_msg'])): ?>
        <div class="bg-red-100 text-red-800 p-4 rounded-xl mb-5 border border-red-200">⚠️ <?= htmlspecialchars($_SESSION['error_msg']); unset($_SESSION['error_msg']); ?></div>
    <?php endif; ?>

    <!-- Filter Bar -->
    <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 p-5 mb-6">
        <form method="GET">
            <div class="grid grid-cols-2 md:grid-cols-6 gap-3 items-end">

                <div class="col-span-2 md:col-span-2">
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">ค้นหาสินค้า</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm">🔍</span>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            placeholder="ชื่อหรือรายละเอียด..."
                            class="w-full pl-8 pr-3 py-2.5 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">หมวดหมู่</label>
                    <select name="category" class="w-full py-2.5 px-3 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="">ทั้งหมด</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $category_id == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">ราคาต่ำสุด (฿)</label>
                    <input type="number" name="min_price" value="<?= htmlspecialchars($min_price) ?>"
                        placeholder="0" min="0" step="1"
                        class="w-full py-2.5 px-3 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">ราคาสูงสุด (฿)</label>
                    <input type="number" name="max_price" value="<?= htmlspecialchars($max_price) ?>"
                        placeholder="ไม่จำกัด" min="0" step="1"
                        class="w-full py-2.5 px-3 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-400 uppercase tracking-wider mb-1">เรียงโดย</label>
                    <select name="sort" class="w-full py-2.5 px-3 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:bg-gray-700 dark:text-white focus:ring-2 focus:ring-indigo-500 outline-none">
                        <option value="newest"     <?= $sort==='newest'     ? 'selected':'' ?>>ล่าสุด</option>
                        <option value="price_asc"  <?= $sort==='price_asc'  ? 'selected':'' ?>>ราคา ต่ำ→สูง</option>
                        <option value="price_desc" <?= $sort==='price_desc' ? 'selected':'' ?>>ราคา สูง→ต่ำ</option>
                        <option value="name"       <?= $sort==='name'       ? 'selected':'' ?>>ชื่อ A-Z</option>
                    </select>
                </div>
            </div>

            <div class="flex gap-3 mt-4">
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2.5 rounded-xl font-semibold text-sm shadow transition">🔍 ค้นหา</button>
                <?php if($is_filtered): ?>
                    <a href="index.php" class="px-5 py-2.5 rounded-xl border border-gray-200 dark:border-gray-600 text-gray-500 dark:text-gray-400 hover:bg-gray-50 dark:hover:bg-gray-700 transition text-sm font-medium">✕ ล้างค่า</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Header + active filters -->
    <div class="flex flex-wrap items-center gap-3 mb-5">
        <h1 class="text-xl font-bold text-gray-800 dark:text-white">
            <?= $is_filtered ? 'ผลการค้นหา' : 'สินค้าทั้งหมด' ?>
            <span class="text-sm font-normal text-gray-400">(<?= count($products) ?> รายการ)</span>
        </h1>
        <?php if($search !== ''): ?>
            <span class="bg-indigo-100 text-indigo-700 text-xs px-3 py-1 rounded-full">🔍 "<?= htmlspecialchars($search) ?>"</span>
        <?php endif; ?>
        <?php if($min_price !== '' || $max_price !== ''): ?>
            <span class="bg-emerald-100 text-emerald-700 text-xs px-3 py-1 rounded-full">
                💰 ฿<?= $min_price !== '' ? number_format((float)$min_price) : '0' ?> – ฿<?= $max_price !== '' ? number_format((float)$max_price) : '∞' ?>
            </span>
        <?php endif; ?>
    </div>

    <!-- Product Grid -->
    <?php if(count($products) > 0): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            <?php foreach($products as $p): ?>
                <?php
                    $stock = (int)$p['stock'];
                    if($stock <= 0)     { $badge_cls = 'bg-red-100 text-red-700';    $badge_txt = 'หมดสต็อก'; }
                    elseif($stock <= 5) { $badge_cls = 'bg-amber-100 text-amber-700'; $badge_txt = "เหลือ $stock ชิ้น"; }
                    else                { $badge_cls = 'bg-green-100 text-green-700'; $badge_txt = "$stock ชิ้น"; }
                ?>
                <div class="product-card bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700 overflow-hidden flex flex-col shadow-sm">
                    <div class="relative h-48 bg-gray-100 dark:bg-gray-700 overflow-hidden">
                        <?php if(!empty($p['img'])): ?>
                            <img src="<?= htmlspecialchars($p['img']) ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-5xl opacity-30">🎮</div>
                        <?php endif; ?>
                        <span class="absolute top-2 right-2 text-xs font-bold px-2.5 py-1 rounded-full <?= $badge_cls ?>"><?= $badge_txt ?></span>
                    </div>

                    <div class="p-4 flex-1 flex flex-col">
                        <div class="text-xs text-indigo-500 font-semibold uppercase mb-1"><?= htmlspecialchars($p['category_name'] ?? 'ไม่มีหมวดหมู่') ?></div>
                        <h2 class="font-bold text-gray-800 dark:text-white text-sm mb-2 line-clamp-2 leading-snug"><?= htmlspecialchars($p['productname']) ?></h2>

                        <div class="mt-auto pt-3 border-t border-gray-100 dark:border-gray-700 flex items-center justify-between gap-2">
                            <span class="text-lg font-extrabold text-green-600 dark:text-green-400 whitespace-nowrap">฿<?= number_format($p['price'], 2) ?></span>

                            <?php if($stock > 0): ?>
                                <form action="actions/add_to_cart.php" method="POST" class="shrink-0">
                                    <input type="hidden" name="product_id" value="<?= $p['id'] ?>">
                                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 active:scale-95 text-white text-xs font-bold px-3 py-2 rounded-xl transition-all whitespace-nowrap">
                                        + ตะกร้า
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="text-xs text-gray-400 dark:text-gray-500">สินค้าหมด</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="text-center py-24 bg-white dark:bg-gray-800 rounded-2xl border border-gray-100 dark:border-gray-700">
            <div class="text-6xl mb-4">🔍</div>
            <h2 class="text-xl font-bold text-gray-600 dark:text-gray-300 mb-2">ไม่พบสินค้าที่ตรงกับเงื่อนไข</h2>
            <p class="text-gray-400 text-sm mb-6">ลองปรับช่วงราคาหรือคำค้นหาใหม่</p>
            <a href="index.php" class="bg-indigo-600 text-white px-6 py-2.5 rounded-xl hover:bg-indigo-700 transition font-semibold text-sm">ดูสินค้าทั้งหมด</a>
        </div>
    <?php endif; ?>

</div>
</body>
</html>
