<?php
session_start();
session_destroy(); // สั่งทำลายข้อมูล Session ทั้งหมด (ฉีกบัตรผ่านทิ้ง)
header("Location: auth.php"); // กลับไปหน้า Login/Register
exit();
?>