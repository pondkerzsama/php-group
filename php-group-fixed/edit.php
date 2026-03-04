<?php 
require_once 'includes/db.php';
require_once 'includes/auth_check.php';
require_admin();

// ดึงข้อมูลสินค้าเดิมออกมาตาม ID ที่ส่งมาใน URL
if (!isset($_GET['id'])) {
    header("Location: index.php");
    exit();
}

$id = $_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    die("ไม่พบสินค้าชิ้นนี้ครับ");
}

// ดึงหมวดหมู่สำหรับ dropdown
$stmt_cats = $pdo->query("SELECT * FROM categories ORDER BY id ASC");
$categories = $stmt_cats->fetchAll();
?>
<!DOCTYPE html>
<html lang="th" class="light">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลสินค้า - 🛒Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config = { darkMode: 'class' }</script>
    <script>
        // ตรวจสอบค่าธีมตอนโหลดหน้าเว็บ
        if (localStorage.getItem('color-theme') === 'dark' || (!('color-theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        // ฟังก์ชันสลับธีมพร้อมบันทึกค่า
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
<body class="bg-[#f0f2f5] dark:bg-gray-900 min-h-screen text-slate-800 dark:text-gray-200 flex flex-col transition-colors duration-200">
    
    <?php include 'includes/navbar.php'; ?>

    <div class="flex-1 flex items-center justify-center p-6">
        <div class="bg-white dark:bg-gray-800 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100 dark:border-gray-700 transition-colors duration-200">
            <h2 class="text-2xl font-bold text-gray-800 dark:text-white mb-6 text-center">✏️ แก้ไขสินค้า</h2>
            
            <form action="actions/update_product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300">ชื่อสินค้า</label>
                    <input type="text" name="productname" value="<?= htmlspecialchars($product['productname']) ?>" required 
                        class="mt-1 block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300">รายละเอียด</label>
                    <textarea name="detail" rows="3" 
                        class="mt-1 block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"><?= htmlspecialchars($product['detail']) ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300">ราคา (฿)</label>
                        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required 
                            class="mt-1 block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300">จำนวนในสต็อก</label>
                        <input type="number" name="stock" value="<?= $product['stock'] ?>" required 
                            class="mt-1 block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition" 
                            min="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300">หมวดหมู่</label>
                    <select name="category_id" required
                        class="mt-1 block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                        <option value="">-- เลือกหมวดหมู่ --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= ($product['category_id'] == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300">วันที่ผลิต</label>
                    <input type="date" name="production_date" value="<?= $product['production_date'] ?>"
                        class="mt-1 block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <div class="bg-gray-50 dark:bg-gray-700/50 p-4 rounded-xl border border-dashed border-gray-300 dark:border-gray-600 transition-colors">
                    <label class="block text-sm font-semibold text-gray-600 dark:text-gray-300 mb-2">รูปภาพสินค้า (URL หรือ อัปโหลดใหม่)</label>
                    <input type="text" name="img" value="<?= htmlspecialchars($product['img']) ?>" placeholder="https://... หรือ path รูปภาพ" 
                        class="block w-full px-4 py-2 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 dark:text-white rounded-lg text-sm mb-3 focus:ring-2 focus:ring-indigo-500 outline-none transition">
                    
                    <div class="relative">
                        <input type="file" name="img_file" accept="image/*" 
                            class="block w-full text-xs text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 dark:file:bg-indigo-900/30 file:text-indigo-700 dark:file:text-indigo-400 hover:file:bg-indigo-100 dark:hover:file:bg-indigo-800/50 cursor-pointer transition">
                    </div>
                    <p class="text-[10px] text-gray-400 dark:text-gray-500 mt-2">* หากไม่อัปโหลดใหม่ ระบบจะใช้รูปเดิมครับ</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" 
                        class="flex-1 bg-indigo-600 text-white py-2.5 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 dark:shadow-none transition-all active:scale-95">อัปเดตข้อมูล</button>
                    <a href="admin_index.php" 
                        class="flex-1 bg-gray-100 dark:bg-gray-700 text-gray-500 dark:text-gray-300 py-2.5 rounded-xl font-bold text-center hover:bg-gray-200 dark:hover:bg-gray-600 transition-all">ยกเลิก</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.querySelector('form').addEventListener('submit', function(e) {
            const urlInput = document.querySelector('input[name="img"]').value.trim();
            const fileInput = document.querySelector('input[name="img_file"]').files.length;

            // ในหน้า Edit ปกติจะมีรูปเดิมอยู่แล้ว แต่กันพลาดเผื่อ User ลบ URL ทิ้ง
            if (urlInput === "" && fileInput === 0) {
                e.preventDefault(); 
                alert("กรุณาใส่ URL รูปภาพ หรือ อัปโหลดไฟล์รูปภาพอย่างใดอย่างหนึ่งครับ");
            }
        });
    </script>
</body>
</html>