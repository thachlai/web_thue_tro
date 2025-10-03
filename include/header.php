<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Web thuê trọ</title>
    <link rel="stylesheet" href="../css/main.css">
</head>
<body>
<header>
    <div class="header-left">
        <a href="../user/index.php">
            <img src="../img/logo.png" alt="Logo" class="logo">
        </a>
    </div>
    <div class="header-right">
        <nav>
            <ul class="menu">
                <li><a href="../user/index.php">Trang Chủ</a></li>
                <li><a href="../user/list_post.php">Bài Đăng</a></li>
                <li><a href="../user/packages">Gói đăng ký</a></li>
                <li class="user-menu">
                    <a href="javascript:void(0)"> 
                        <img src="../img/user_icon.png" alt="User" class="user-icon">
                    </a>
                    <ul class="dropdown">
                        <?php if(!isset($_SESSION['id_user'])): ?>
                            <li><a href="../auth/login.php">Đăng nhập</a></li>
                            <li><a href="../auth/signup.php">Đăng ký</a></li>
                        <?php else: ?>
                            <li class="username">Xin chào, <?php echo htmlspecialchars($_SESSION['fullname']); ?></li>
                            <li><a href="../user/profile.php">Hồ sơ</a></li>
                            <li><a href="../user/add_post.php">Tạo bài đăng</a></li>
                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 0): ?>
                                <li><a href="../admin/admin.php">Trang Quản Trị</a></li>
                            <?php endif; ?>
                            <li><a href="../auth/logout.php">Đăng xuất</a></li>
                        <?php endif; ?>
                    </ul>
                </li>
            </ul>
        </nav>
    </div>
</header>
<div class="container">
