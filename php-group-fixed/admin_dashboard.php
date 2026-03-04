<?php 
require_once 'includes/db.php'; 
require_once 'includes/auth_check.php'; 
require_admin(); // ต้องเป็น admin เท่านั้น

// --- 1. ข้อมูลสถิติ 4 กล่องด้านบน ---
$total_revenue = $pdo->query("SELECT SUM(total_price) FROM orders")->fetchColumn() ?: 0;
$total_orders  = $pdo->query("SELECT COUNT(id) FROM orders")->fetchColumn() ?: 0;
$total_products = $pdo->query("SELECT COUNT(id) FROM products")->fetchColumn() ?: 0;
$low_stock_count = $pdo->query("SELECT COUNT(id) FROM products WHERE stock <= 5")->fetchColumn() ?: 0;

// --- 2. ดึงข้อมูล 5 ออเดอร์ล่าสุด ---
$stmt_recent_orders = $pdo->query("
    SELECT o.id, o.total_price, o.created_at, u.username 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
");
$recent_orders = $stmt_recent_orders->fetchAll();

// --- 3. ดึงรายการสินค้าใกล้หมดสต็อก ---
$stmt_low_stock_items = $pdo->query("
    SELECT id, productname, stock, img, price 
    FROM products 
    WHERE stock <= 5 
    ORDER BY stock ASC 
    LIMIT 5
");
$low_stock_items = $stmt_low_stock_items->fetchAll();
?>

<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <title>📊 Dashboard - Admin Panel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>
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
<body class="bg-[#f0f2f5] dark:bg-gray-900 min-h-screen text-slate-800 dark:text-gray-200 transition-colors duration-200 pb-10">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="max-w-6xl mx-auto p-4">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">📊 ภาพรวมระบบ (Dashboard)</h1>
            <a href="admin_index.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-5 py-2 rounded-xl transition shadow-sm text-sm font-semibold">
                ไปหน้าจัดการสินค้า →
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 rounded-2xl flex items-center justify-center text-3xl">💰</div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">ยอดขายรวม</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white">฿<?= number_format($total_revenue, 2) ?></div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 rounded-2xl flex items-center justify-center text-3xl">📦</div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">คำสั่งซื้อทั้งหมด</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format($total_orders) ?> รายการ</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-purple-100 dark:bg-purple-900/30 text-purple-600 dark:text-purple-400 rounded-2xl flex items-center justify-center text-3xl">🏷️</div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">สินค้าในระบบ</div>
                    <div class="text-2xl font-bold text-gray-800 dark:text-white"><?= number_format($total_products) ?> ชิ้น</div>
                </div>
            </div>
            <div class="bg-white dark:bg-gray-800 p-6 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 flex items-center gap-5 hover:shadow-md transition">
                <div class="w-14 h-14 bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-2xl flex items-center justify-center text-3xl">⚠️</div>
                <div>
                    <div class="text-xs text-gray-400 dark:text-gray-400 font-semibold uppercase tracking-wider mb-1">สินค้าใกล้หมด (≤5)</div>
                    <div class="text-2xl font-bold <?= $low_stock_count > 0 ? 'text-red-500' : 'text-gray-800 dark:text-white' ?>"><?= number_format($low_stock_count) ?> ชิ้น</div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="font-bold text-gray-800 dark:text-white text-lg flex items-center gap-2">
                        <span>🛒</span> ออเดอร์ล่าสุด
                    </h2>
                </div>
                <div class="p-0">
                    <?php if(count($recent_orders) > 0): ?>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <?php foreach($recent_orders as $order): ?>
                                <div class="px-6 py-4 flex justify-between items-center hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div>
                                        <div class="font-semibold text-sm text-gray-800 dark:text-white">ออเดอร์ #<?= $order['id'] ?></div>
                                        <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                            โดย: <span class="text-indigo-600 dark:text-indigo-400 font-medium"><?= htmlspecialchars($order['username']) ?></span> 
                                            • <?= date('d/m/Y H:i', strtotime($order['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-green-600 dark:text-green-400">฿<?= number_format($order['total_price'], 2) ?></div>
                                        <span class="inline-block mt-1 bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-400 text-[10px] px-2 py-0.5 rounded-full font-semibold">สำเร็จ</span>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center text-gray-500 dark:text-gray-400 text-sm">ยังไม่มีคำสั่งซื้อในระบบ</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-700 flex justify-between items-center">
                    <h2 class="font-bold text-gray-800 dark:text-white text-lg flex items-center gap-2">
                        <span>⚠️</span> สินค้าต้องรีบเติมสต็อก
                    </h2>
                </div>
                <div class="p-0">
                    <?php if(count($low_stock_items) > 0): ?>
                        <div class="divide-y divide-gray-100 dark:divide-gray-700">
                            <?php foreach($low_stock_items as $item): ?>
                                <div class="px-6 py-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                                    <div class="flex items-center gap-4">
                                        <?php if($item['img']): ?>
                                            <img src="<?= htmlspecialchars($item['img']) ?>" class="w-12 h-12 rounded-lg object-cover border border-gray-200 dark:border-gray-600">
                                        <?php else: ?>
                                            <div class="w-12 h-12 bg-gray-100 dark:bg-gray-700 rounded-lg flex items-center justify-center text-xl opacity-50">🎮</div>
                                        <?php endif; ?>
                                        
                                        <div>
                                            <div class="font-semibold text-sm text-gray-800 dark:text-white line-clamp-1"><?= htmlspecialchars($item['productname']) ?></div>
                                            <div class="flex items-center gap-2 mt-1">
                                                <span class="text-xs font-bold px-2 py-0.5 rounded-full <?= $item['stock'] == 0 ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400' ?>">
                                                    เหลือ <?= $item['stock'] ?> ชิ้น
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <a href="edit.php?id=<?= $item['id'] ?>" class="text-xs font-semibold bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-indigo-600 hover:text-white px-3 py-1.5 rounded-lg transition">
                                        จัดการ
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="p-8 text-center flex flex-col items-center justify-center">
                            <span class="text-4xl mb-3">✅</span>
                            <div class="text-gray-800 dark:text-white font-semibold">สต็อกสินค้าปลอดภัย</div>
                            <div class="text-gray-500 dark:text-gray-400 text-sm mt-1">ไม่มีสินค้าที่เหลือน้อยกว่า 5 ชิ้น</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>
</body>
</html>