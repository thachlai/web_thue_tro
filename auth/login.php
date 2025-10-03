<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../include/conn.php');
include('../include/header.php');

$thong_bao = '';
$loai_tb   = ''; // thanhcong | thatbai | canhbao
$email_fill = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email_fill  = trim($_POST['email'] ?? '');
    $matkhau_raw = trim($_POST['matkhau'] ?? '');

    if ($email_fill === '' || $matkhau_raw === '') {
        $thong_bao = 'Vui lòng nhập email và mật khẩu.';
        $loai_tb   = 'canhbao';
    } else {
        // Lấy user theo email
        $stmt = $conn->prepare("SELECT id_user, fullname, role, status, password 
                                FROM users 
                                WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email_fill);
        $stmt->execute();
        $rs = $stmt->get_result();

        if ($rs->num_rows === 1) {
            $u = $rs->fetch_assoc();

            if ((int)$u['status'] === 0) {
                $thong_bao = 'Tài khoản của bạn đang bị khóa.';
                $loai_tb   = 'thatbai';
            } elseif ($u['password'] === md5($matkhau_raw)) {
                // Đăng nhập thành công → lưu session
                $_SESSION['id_user']  = $u['id_user'];
                $_SESSION['fullname'] = $u['fullname'];
                $_SESSION['role']     = (int)$u['role'];

                if ((int)$u['role'] === 0) { // admin
                    header("Location: ../admin/admin.php");
                } else { // user
                    header("Location: ../user/index.php");
                }
                exit;
            } else {
                $thong_bao = 'Email hoặc mật khẩu không đúng.';
                $loai_tb   = 'thatbai';
            }
        } else {
            $thong_bao = 'Email hoặc mật khẩu không đúng.';
            $loai_tb   = 'thatbai';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8" />
    <title>Đăng Nhập - WebThuêTrọ</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>

<body>

<div class="khung_form">
    <img src="../img/logo.png" alt="Logo Đăng Nhập" class="logo_auth">
    <h2>Đăng Nhập</h2>

    <?php if (!empty($thong_bao)): ?>
        <div class="thongbao <?php echo $loai_tb; ?>">
            <?php echo htmlspecialchars($thong_bao); ?>
        </div>
    <?php endif; ?>

    <form method="post" action="">
        <div class="form_nhap">
            <input type="email" name="email" placeholder="Email"
                   value="<?= htmlspecialchars($email_fill) ?>" required>
        </div>
        <div class="form_nhap">
            <input type="password" name="matkhau" placeholder="Mật Khẩu" required>
        </div>
        <button type="submit" class="nut_submit">Đăng Nhập</button>
    </form>

    <p style="margin-top:12px; font-size:14px;">
        <a href="signup.php">Chưa có tài khoản? Đăng ký</a> |
        <a href="forgot_password.php">Quên mật khẩu?</a>
    </p>
</div>

</body>
</html>
