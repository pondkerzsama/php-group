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
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>แก้ไขข้อมูลสินค้า - 🛒Gameproduct Store</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-[#f0f2f5] min-h-screen text-slate-800 flex flex-col">
    
    <?php include 'includes/navbar.php'; ?>

    <div class="flex-1 flex items-center justify-center p-6">
        <div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-gray-100">
            <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">✏️ แก้ไขสินค้า</h2>
            
            <form action="actions/update_product.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <input type="hidden" name="id" value="<?= $product['id'] ?>">

                <div>
                    <label class="block text-sm font-semibold text-gray-600">ชื่อสินค้า</label>
                    <input type="text" name="productname" value="<?= htmlspecialchars($product['productname']) ?>" required 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">รายละเอียด</label>
                    <textarea name="detail" rows="3" 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition"><?= htmlspecialchars($product['detail']) ?></textarea>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">ราคา (฿)</label>
                        <input type="number" step="0.01" name="price" value="<?= $product['price'] ?>" required 
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-600">จำนวนในสต็อก</label>
                        <input type="number" name="stock" value="<?= $product['stock'] ?>" required 
                            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition" 
                            min="0">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-600">วันที่ผลิต</label>
                    <input type="date" name="production_date" value="<?= $product['production_date'] ?>" required 
                        class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:outline-none transition">
                </div>

                <div class="bg-gray-50 p-4 rounded-xl border border-dashed border-gray-300">
                    <label class="block text-sm font-semibold text-gray-600 mb-2">รูปภาพสินค้า (URL หรือ อัปโหลดใหม่)</label>
                    <input type="url" name="img" value="<?= htmlspecialchars($product['img']) ?>" 
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-sm mb-3 focus:ring-2 focus:ring-indigo-500 outline-none">
                    
                    <div class="relative">
                        <input type="file" name="img_file" accept="image/*" 
                            class="block w-full text-xs text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer">
                    </div>
                    <p class="text-[10px] text-gray-400 mt-2">* หากไม่อัปโหลดใหม่ ระบบจะใช้รูปเดิมครับ</p>
                </div>

                <div class="flex gap-3 pt-4">
                    <button type="submit" 
                        class="flex-1 bg-indigo-600 text-white py-2.5 rounded-xl font-bold hover:bg-indigo-700 shadow-lg shadow-indigo-200 transition-all active:scale-95">อัปเดตข้อมูล</button>
                    <a href="index.php" 
                        class="flex-1 bg-gray-100 text-gray-500 py-2.5 rounded-xl font-bold text-center hover:bg-gray-200 transition-all">ยกเลิก</a>
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