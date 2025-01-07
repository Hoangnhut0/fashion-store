<?php
session_start();

// Kiểm tra nếu người dùng đã đăng nhập
if (isset($_SESSION['logged_in'])) {
    $role = $_SESSION['role']; // Lấy quyền của người dùng trước khi hủy session
    session_destroy(); // Hủy session

    // Điều hướng dựa trên quyền của người dùng
    if ($role === 'admin') {
        header("Location: admin/"); // Trang chủ admin
    } elseif($role === 'user') {
        header("Location: index.php"); // Trang chủ người dùng
    }
    exit;
}
?>
