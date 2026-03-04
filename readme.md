# 📦 Stock Management System (Pro Edition)
**The most reliable, open-source inventory control system powered by Modern PHP.**

[![PHP Version](https://img.shields.io/badge/php-%3E%3D%208.1-777bb4.svg?style=flat-square&logo=php)](https://www.php.net/)
[![Laravel Framework](https://img.shields.io/badge/framework-Laravel%2010.x-red?style=flat-square&logo=laravel)](https://laravel.com/)
[![License](https://img.shields.io/badge/license-MIT-blue.svg?style=flat-square)](LICENSE)
[![Maintenance](https://img.shields.io/badge/Maintained%3F-yes-green.svg)](https://github.com/)

---

### 🐘 ทำไมต้องระบบนี้? (The "Why")
ระบบนี้ไม่ได้แค่เก็บข้อมูล แต่ถูกออกแบบมาเพื่อ **ป้องกันความผิดพลาด** ในคลังสินค้า ลดขั้นตอนการทำงานที่ซ้ำซ้อน และให้ข้อมูลที่แม่นยำที่สุดสำหรับเจ้าของธุรกิจ

---

### ✨ ฟีเจอร์ระดับเทพ (Full Features)

| Feature | Description | Status |
| :--- | :--- | :---: |
| **Inventory Tracking** | ติดตามสต็อก Real-time รายชิ้น/รายล็อต | ✅ |
| **Barcode Generator** | สร้างและพิมพ์ Barcode จากระบบได้ทันที | ✅ |
| **Multi-Warehouse** | รองรับการจัดการหลายคลังสินค้าพร้อมกัน | ✅ |
| **Low Stock Alert** | แจ้งเตือนผ่าน LINE หรือ Email เมื่อของใกล้หมด | 🛠️ |
| **Audit Logs** | ระบบบันทึกการทำงาน ใครทำอะไร ที่ไหน เมื่อไหร่ | ✅ |

---

### 🛠️ เทคโนโลยีเบื้องหลัง (Detailed Tech Stack)
* **Core Engine:** PHP 8.1+ (Built with Laravel / Clean Architecture)
* **Database:** MySQL 8.0 (Optimized with Indexing)
* **Frontend:** Tailwind CSS & Alpine.js (Lightweight & Fast)
* **Caching:** Redis (For high-speed sessions)
* **Reporting:** DomPDF for PDF Generation

---

### ⚙️ ขั้นตอนการติดตั้ง (Step-by-Step Installation)

#### 1. Requirements
* PHP >= 8.1
* Composer
* MySQL / MariaDB
* Apache/Nginx

#### 2. Clone & Install
```bash
# Clone the project
git clone [https://github.com/your-username/stock-management.git](https://github.com/your-username/stock-management.git)
cd stock-management

# Install PHP dependencies
composer install --optimize-autoloader --no-dev

# Install Frontend assets (If applicable)
npm install && npm run build
