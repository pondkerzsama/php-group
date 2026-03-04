<nav class="bg-[#1e293b] text-white shadow-md mb-8">
    <div class="max-w-6xl mx-auto px-4">
        <div class="flex justify-between items-center h-16">
            <div class="flex items-center gap-2">
                <span class="text-2xl">🛒</span>
                <span class="font-bold text-xl tracking-tight">Gameproduct Store</span>
            </div>
            <div class="flex space-x-4">
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                    <a href="admin_index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium border-b-2 border-indigo-400">📦 จัดการสินค้า</a>
                    <a href="create.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">+ เพิ่มสินค้า</a>
                <?php endif; ?>
                <a href="index.php" class="hover:bg-indigo-500 px-3 py-2 rounded-md transition text-sm font-medium">🏪 หน้าร้าน</a>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded-md transition text-sm font-medium">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</nav>