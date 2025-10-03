<?php
if(session_status()===PHP_SESSION_NONE) session_start();
include('../include/conn.php');
include('../include/header.php');

$thong_bao = '';
$loai_tb = '';
$email = $_GET['email'] ?? '';
$token = $_GET['token'] ?? '';

if(!$email || !$token){
    die("Liên kết không hợp lệ!");
}

// Kiểm tra token
$check = $conn->prepare("SELECT * FROM users WHERE email=? AND reset_token=?");
$check->bind_param("ss", $email, $token);
$check->execute();
$result = $check->get_result();

if($result->num_rows===0){
    die("Liên kết không hợp lệ hoặc đã sử dụng!");
}

$user = $result->fetch_assoc();
$now = date('Y-m-d H:i:s');
if($user['reset_expire'] < $now){
    die("Liên kết đã hết hạn!");
}

if($_SERVER['REQUEST_METHOD']==='POST'){
    $matkhau = trim($_POST['matkhau']);
    $matkhau2 = trim($_POST['matkhau2']);

    if($matkhau!==$matkhau2){
        $thong_bao = "Mật khẩu không khớp!";
        $loai_tb = "thatbai";
    } else {
        $hash = md5($matkhau);
        $stmt = $conn->prepare("UPDATE users SET password=?, reset_token=NULL, reset_expire=NULL WHERE email=?");
        $stmt->bind_param("ss", $hash, $email);
        $stmt->execute();

        $thong_bao = "Đặt lại mật khẩu thành công!";
        $loai_tb = "thanhcong";
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Đặt lại mật khẩu - WebThuêTrọ</title>
<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="khung_form">
<img src="../img/logo.png" class="logo_auth">
<h2>Đặt lại mật khẩu</h2>

<?php if($thong_bao): ?>
<div class="thongbao <?php echo $loai_tb; ?>"><?php echo htmlspecialchars($thong_bao); ?></div>
<?php endif; ?>

<form method="POST">
    <div class="form_nhap"><input type="password" name="matkhau" placeholder="Mật khẩu mới" required></div>
    <div class="form_nhap"><input type="password" name="matkhau2" placeholder="Nhập lại mật khẩu" required></div>
    <button type="submit" class="nut_submit">Đặt lại mật khẩu</button>
</form>

<p style="margin-top:12px; font-size:14px;"><a href="login.php">Quay lại đăng nhập</a></p>
</div>
</body>
</html>
