<?php
session_start();
session_destroy(); // สั่งทำลายข้อมูล Session ทั้งหมด (ฉีกบัตรผ่านทิ้ง)
header("Location: login.php"); // ส่ง User กลับไปหน้า Login
exit();
?>