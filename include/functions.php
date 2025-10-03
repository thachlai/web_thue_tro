<?php

/**
 * Kiểm tra người dùng đã đăng nhập
 */
function check_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: ../auth/login.php");
        exit;
    }
}

/**
 * Kiểm tra người dùng đã login nhưng ko vào trang login/signup
 */
function check_auth() {
    if (isset($_SESSION['id_user'])) {
        if ($_SESSION['role'] == 0) { // admin
            header("Location: ../admin/admin.php");
        } else { // user
            header("Location: ../user/index.php");
        }
        exit;
    }
}

/**
 * Kiểm tra quyền admin
 * Nếu không phải admin → chuyển về trang user
 */
function check_admin() {
    check_login(); // chắc chắn người dùng đã login
    if ($_SESSION['role'] != 0) {
        header("Location: ../user/index.php");
        exit;
    }
}

?>
