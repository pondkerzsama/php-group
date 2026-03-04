<?php 
require_once 'includes/db.php'; 
require_once 'includes/auth_check.php'; 
require_admin(); // ต้องเป็น admin เท่านั้น

$search = $_GET['search'] ?? '';
$min = (isset($_GET['min']) && $_GET['min'] !== '') ? $_GET['min'] : 0;
$max = (isset($_GET['max']) && $_GET['max'] !== '') ? $_GET['max'] : 999999;

$sql = "SELECT * FROM products WHERE 
        (productname LIKE :s1 OR detail LIKE :s2) 
        AND (price BETWEEN :min AND :max) 
        ORDER BY id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute([
    's1' => "%$search%",
    's2' => "%$search%",
    'min' => $min,
    'max' => $max
]);
?>

<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <title>📦 จัดการสินค้า - Admin Panel</title>
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
<body class="bg-[#f0f2f5] dark:bg-gray-900 min-h-screen text-slate-800 dark:text-gray-200 transition-colors duration-200">
    <?php include 'includes/navbar.php'; ?>
    
    <div class="max-w-6xl mx-auto p-4">
        <?php if(isset($_SESSION["error_msg"])): ?>
            <div class="bg-red-100 text-red-800 p-4 rounded-lg mb-6 border border-red-200">
                <?= htmlspecialchars($_SESSION["error_msg"]); unset($_SESSION["error_msg"]); ?>
            </div>
        <?php endif; ?>
        <?php if(isset($_GET["status"])): ?>
            <?php $msgs = ['success'=>'✅ เพิ่มสินค้าสำเร็จ!','updated'=>'✅ อัปเดตสินค้าสำเร็จ!','deleted'=>'🗑️ ลบสินค้าสำเร็จ!']; ?>
            <div class="bg-green-100 text-green-800 p-4 rounded-lg mb-6 border border-green-200">
                <?= $msgs[$_GET['status']] ?? 'ดำเนินการสำเร็จ' ?>
            </div>
        <?php endif; ?>

        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
            <h1 class="text-3xl font-bold text-gray-800 dark:text-white">📦 รายการสินค้าทั้งหมด</h1>
            <a href="create.php" class="bg-indigo-600 text-white px-6 py-2 rounded-lg hover:bg-indigo-700 transition shadow-md whitespace-nowrap">
                + เพิ่มสินค้าใหม่
            </a>
        </div>

        <form method="GET" class="bg-white dark:bg-gray-800 p-5 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 mb-10 transition-colors">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
                <div class="md:col-span-2 relative">
                    <label class="block text-xs font-semibold text-gray-400 dark:text-gray-300 mb-1 uppercase tracking-wider">ค้นหาชื่อหรือรายละเอียด</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" 
                               class="block w-full pl-9 pr-10 py-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 dark:text-white outline-none transition" 
                               placeholder="พิมพ์ชื่อสินค้าหรือสิ่งที่ต้องการหา...">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 dark:text-gray-300 mb-1 uppercase tracking-wider">ราคาต่ำสุด</label>
                        <input type="number" name="min" value="<?= htmlspecialchars($_GET['min'] ?? '') ?>" 
                               class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="0">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-400 dark:text-gray-300 mb-1 uppercase tracking-wider">ราคาสูงสุด</label>
                        <input type="number" name="max" value="<?= htmlspecialchars($_GET['max'] ?? '') ?>" 
                               class="w-full p-2.5 bg-white dark:bg-gray-700 border border-gray-200 dark:border-gray-600 rounded-xl text-sm dark:text-white outline-none focus:ring-2 focus:ring-indigo-500 transition" placeholder="99999">
                    </div>
                </div>
                <div class="flex flex-col xl:flex-row gap-2 w-full">
                    <button type="submit" class="w-full xl:flex-1 bg-indigo-600 text-white py-2.5 rounded-xl hover:bg-indigo-700 transition shadow-md font-bold text-sm">ค้นหา</button>
                    <?php if(!empty($search) || !empty($_GET['min']) || !empty($_GET['max'])): ?>
                        <a href="admin_index.php" class="w-full xl:w-auto text-center px-4 py-2.5 text-gray-500 dark:text-gray-300 bg-gray-100 dark:bg-gray-700 hover:bg-red-50 dark:hover:bg-red-900/40 hover:text-red-500 dark:hover:text-red-400 rounded-xl transition text-sm flex items-center justify-center font-medium border border-gray-200 dark:border-gray-600">ล้างค่า</a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php while ($row = $stmt->fetch()): ?>
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-sm border border-gray-100 dark:border-gray-700 overflow-hidden hover:shadow-xl transition-all duration-300 group">
                <div class="relative overflow-hidden">
                    <img class="h-56 w-full object-cover group-hover:scale-105 transition-transform duration-500" src="<?= htmlspecialchars($row['img']) ?>" alt="Product">
                    <div class="absolute top-3 right-3 bg-white/90 dark:bg-gray-800/90 backdrop-blur px-3 py-1 rounded-full shadow-sm border dark:border-gray-600">
                        <span class="text-indigo-600 dark:text-indigo-400 font-bold text-sm">฿<?= number_format($row['price'], 2) ?></span>
                    </div>
                </div>
                <div class="p-6">
                    <div class="flex justify-between items-start mb-3">
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white truncate"><?= htmlspecialchars($row['productname']) ?></h2>
                    </div>

                    <div class="flex flex-col gap-1.5 mb-5">
                        <div class="flex items-center text-[11px] text-gray-400 dark:text-gray-400">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <span>ผลิตเมื่อ: <?= date("d M Y", strtotime($row['production_date'])) ?></span>
                        </div>
                        <div class="flex items-center text-[11px] font-semibold <?= $row['stock'] > 0 ? 'text-emerald-500 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' ?>">
                            <svg class="w-3.5 h-3.5 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 11v10l8 4"></path></svg>
                            <span>สต็อก: <?= number_format($row['stock']) ?> ชิ้น <?= $row['stock'] <= 0 ? '(หมด)' : '' ?></span>
                        </div>
                    </div>

                    <p class="text-gray-500 dark:text-gray-400 text-sm line-clamp-2 h-10 mb-6"><?= htmlspecialchars($row['detail']) ?></p>
                    <div class="flex gap-2">
                        <a href="edit.php?id=<?= $row['id'] ?>" class="flex-1 text-center bg-gray-50 dark:bg-gray-700 text-gray-600 dark:text-gray-200 py-2.5 rounded-xl hover:bg-gray-100 dark:hover:bg-gray-600 transition font-medium text-sm border border-gray-100 dark:border-gray-600">แก้ไข</a>
                        <form action="actions/delete_product.php" method="POST" onsubmit="return confirm('ยืนยันการลบสินค้าชิ้นนี้หรือไม่?')" class="flex-1">
                            <input type="hidden" name="id" value="<?= $row['id'] ?>">
                            <button type="submit" class="w-full py-2.5 bg-red-50 dark:bg-red-900/30 text-red-500 dark:text-red-400 rounded-xl hover:bg-red-500 hover:text-white dark:hover:bg-red-600 dark:hover:text-white transition font-medium text-sm">
                                ลบ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </div>
</body>
</html>