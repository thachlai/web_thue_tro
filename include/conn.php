<?php
$host = "localhost";      // Host XAMPP
$user = "root";           // User mặc định XAMPP
$password = "";           // Mặc định XAMPP không có mật khẩu
$dbname = "webthuetro";  // Tên cơ sở dữ liệu

// Tạo kết nối
$conn = new mysqli($host, $user, $password, $dbname);

// Kiểm tra kết nối
if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Thiết lập charset UTF-8 để tránh lỗi tiếng Việt
$conn->set_charset("utf8");
?>
