<?php
session_start();
include('../include/conn.php'); // Kết nối DB
include('../include/header.php');

$thong_bao = '';
$loai_tb = ''; // thanhcong | thatbai | canhbao

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $hoten = trim($_POST['hoten']);
    $email = trim($_POST['email']);
    $matkhau = trim($_POST['matkhau']);
    $matkhau2 = trim($_POST['matkhau2']); // sửa cho khớp với form

    // Kiểm tra các trường rỗng
    if (empty($hoten) || empty($email) || empty($matkhau) || empty($matkhau2)) {
        $thong_bao = 'Vui lòng điền đầy đủ thông tin.';
        $loai_tb = 'canhbao';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $thong_bao = 'Email không hợp lệ.';
        $loai_tb = 'thatbai';
    } elseif ($matkhau !== $matkhau2) {
        $thong_bao = 'Mật khẩu nhập lại không khớp.';
        $loai_tb = 'thatbai';
    } else {
        // Kiểm tra email đã tồn tại chưa
        $stmt = $conn->prepare("SELECT id_user FROM users WHERE email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $thong_bao = 'Email này đã được đăng ký.';
            $loai_tb = 'thatbai';
        } else {
            // Thêm user mới (MD5)
            $matkhau_md5 = md5($matkhau);
            $stmt = $conn->prepare("INSERT INTO users(fullname,email,password) VALUES(?,?,?)");
            $stmt->bind_param("sss", $hoten, $email, $matkhau_md5);
            if ($stmt->execute()) {
                $_SESSION['id_user'] = $stmt->insert_id;
                $_SESSION['fullname'] = $hoten;
                $_SESSION['role'] = 1; // role mặc định người dùng
                $thong_bao = 'Đăng ký thành công! Chuyển hướng...';
                $loai_tb = 'thanhcong';
                header("Refresh:2; url=../user/index.php"); // chờ 2s rồi chuyển
            } else {
                $thong_bao = 'Đăng ký thất bại, vui lòng thử lại.';
                $loai_tb = 'thatbai';
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Đăng Ký - WebThuêTrọ</title>
    <link rel="stylesheet" href="../css/main.css">
    <link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="khung_form">
    <img src="../img/logo.png" alt="Logo" class="logo_auth">

    <h2>Đăng Ký</h2>

    <?php if (!empty($thong_bao)): ?>
        <div class="thongbao <?php echo $loai_tb; ?>">
            <?php echo $thong_bao; ?>
        </div>
    <?php endif; ?>

    <form action="signup.php" method="post">
        <div class="form_nhap">
            <input type="text" name="hoten" placeholder="Họ Tên" required>
        </div>

        <div class="form_nhap">
            <input type="email" name="email" placeholder="Email" required>
        </div>

        <div class="form_nhap">
            <input type="password" name="matkhau" placeholder="Mật Khẩu" required>
        </div>

        <div class="form_nhap">
            <input type="password" name="matkhau2" placeholder="Nhập Lại Mật Khẩu" required>
        </div>

        <button type="submit" class="nut_submit">Đăng Ký</button>
    </form>

    <p><a href="login.php">Đã có tài khoản? Đăng nhập</a></p>
</div>
</body>
</html>
