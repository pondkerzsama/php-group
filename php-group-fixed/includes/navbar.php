<?php
// ดึงจำนวนสินค้าในตะกร้าสำหรับแสดงบน Navbar
$nav_cart_count = 0;
if (isset($_SESSION['user_id'])) {
    global $pdo; 
    if (isset($pdo)) {
        $stmt_nav_cart = $pdo->prepare("SELECT SUM(quantity) FROM cart_items ci JOIN carts c ON ci.cart_id = c.id WHERE c.user_id = ?");
        $stmt_nav_cart->execute([$_SESSION['user_id']]);
        $nav_cart_count = $stmt_nav_cart->fetchColumn() ?: 0;
    }
}
?>
<nav class="bg-[#1e293b] text-white shadow-md mb-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <a href="index.php" class="flex items-center gap-2 hover:opacity-80 transition">
                    <span class="text-2xl">🛒</span>
                    <span class="font-bold text-xl tracking-tight">Gameproduct Store</span>
                </a>
            </div>
            <div class="flex space-x-4 items-center">
                <button onclick="toggleTheme()" class="p-2 rounded-md hover:bg-gray-700 transition" title="สลับธีมมืด/สว่าง">🌙/☀️</button>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="admin_dashboard.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">📊 Dashboard</a>
                        <a href="admin_index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">📦 จัดการสินค้า</a>
                    <?php endif; ?>
                    
                    <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">🏪 หน้าร้าน</a>
                    <a href="order_history.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">📜 ประวัติสั่งซื้อ</a>
                    
                    <a href="cart.php" class="flex items-center gap-1.5 hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">
                        🛒 
                        <?php if($nav_cart_count > 0): ?>
                            <span class="bg-red-500 text-white rounded-full px-2 py-0.5 text-xs font-bold"><?= $nav_cart_count ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <span class="text-gray-300 text-sm hidden md:block">สวัสดี, <b><?= htmlspecialchars($_SESSION['username']) ?></b></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md transition text-sm font-medium">ออกจากระบบ</a>
                <?php else: ?>
                    <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">🏪 หน้าร้าน</a>
                    <a href="auth.php" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-sm font-bold transition shadow-md">ล็อกอิน / สมัครสมาชิก</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>