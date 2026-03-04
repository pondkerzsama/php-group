<?php
require_once '../includes/db.php';
require_once '../includes/auth_check.php';
require_admin(); // ต้องเป็น admin เท่านั้น

if (isset($_POST['id'])) {
    $id = $_POST['id'];
    try {
        $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
        $stmt->execute([$id]);
        
        header("Location: ../admin_index.php?status=deleted");
        exit();
    } catch (PDOException $e) {
        die("Delete Error: " . $e->getMessage());
    }
}