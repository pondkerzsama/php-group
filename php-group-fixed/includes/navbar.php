<nav class="bg-[#1e293b] text-white shadow-md mb-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🛒</span>
                <span class="font-bold text-xl tracking-tight">Gameproduct Store</span>
            </div>
            <div class="flex space-x-4 items-center">
                <button onclick="toggleTheme()" class="p-2 rounded-md hover:bg-gray-700 transition" title="สลับธีมมืด/สว่าง">🌙/☀️</button>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_dashboard.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">📊 Dashboard</a>
                    <a href="admin_index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">📦 จัดการสินค้า</a>
                <?php endif; ?>
                <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">🏪 หน้าร้าน</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md transition text-sm font-medium">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>