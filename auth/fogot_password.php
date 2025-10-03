<?php
if (session_status() === PHP_SESSION_NONE) session_start();
include('../include/conn.php');
include('../include/header.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/../PHPMailer-master/src/Exception.php';
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';

$thong_bao = '';
$loai_tb = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    if (empty($email)) {
        $thong_bao = 'Vui lòng nhập email!';
        $loai_tb = 'thatbai';
    } else {
        $check = $conn->prepare("SELECT * FROM users WHERE email=?");
        if(!$check) die("Lỗi prepare SELECT: ".$conn->error);
        $check->bind_param("s", $email);
        $check->execute();
        $result = $check->get_result();

        if($result->num_rows > 0){
            $user = $result->fetch_assoc();
            $token = bin2hex(random_bytes(16));
            $expire = date('Y-m-d H:i:s', time() + 3600); // 1 giờ

            // Cập nhật token & expire
            $stmt = $conn->prepare("UPDATE users SET reset_token=?, reset_expire=? WHERE email=?");
            if(!$stmt) die("Lỗi prepare UPDATE: ".$conn->error);
            $stmt->bind_param("sss", $token, $expire, $email);
            $stmt->execute();

            // Link reset
            $reset_link = "http://localhost/webthuetro/auth/reset_password.php?email={$email}&token={$token}";

            // Gửi mail
            $mail = new PHPMailer(true);
            $mail->CharSet = 'UTF-8';
            try {
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'tranvokimthach@gmail.com'; // Gmail của bạn
                $mail->Password = 'lfvytyyhryftcgqc'; // App Password
                $mail->SMTPSecure = 'tls';
                $mail->Port = 587;

                $mail->setFrom('tranvokimthach@gmail.com', 'Web Thuê Trọ');
                $mail->addAddress($email, $user['fullname']);
                $mail->isHTML(true);
                $mail->Subject = 'Yêu cầu đặt lại mật khẩu - Web Thuê Trọ';
                $mail->Body = "
                <div style='font-family: Arial, sans-serif; color:#333;'>
                    <div style='text-align:center; margin-bottom:20px;'>
                        <img src='http://localhost/webthuetro/img/logo.png' width='100' alt='Web Thuê Trọ'>
                    </div>
                    <h2 style='color:#4CAF50;'>Đặt lại mật khẩu</h2>
                    <p>Chào <strong>{$user['fullname']}</strong>,</p>
                    <p>Nhấn nút bên dưới để đặt mật khẩu mới (hết hạn sau 1 giờ):</p>
                    <p style='text-align:center; margin:20px 0;'>
                        <a href='{$reset_link}' style='background-color:#4CAF50; color:white; padding:10px 20px; text-decoration:none; border-radius:5px;'>Đặt lại mật khẩu</a>
                    </p>
                    <p>Nếu không yêu cầu, hãy bỏ qua email này.</p>
                    <hr>
                    <p style='font-size:12px; color:#888; text-align:center;'>Web Thuê Trọ &copy; 2025</p>
                </div>";
                $mail->AltBody = "Chào {$user['fullname']},\nLink đặt lại mật khẩu: {$reset_link}\nHết hạn sau 1 giờ.\nBỏ qua nếu không yêu cầu.";
                $mail->send();

                $thong_bao = 'Đã gửi link đặt lại mật khẩu vào email!';
                $loai_tb = 'thanhcong';
            } catch(Exception $e){
                $thong_bao = "Mail gửi thất bại: {$mail->ErrorInfo}";
                $loai_tb = 'thatbai';
            }
        } else {
            $thong_bao = 'Email không tồn tại!';
            $loai_tb = 'thatbai';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Quên mật khẩu - Web Thuê Trọ</title>
<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/auth.css">
</head>
<body>
<div class="khung_form">
<img src="../img/logo.png" class="logo_auth">
<h2>Quên mật khẩu</h2>
<?php if($thong_bao): ?>
<div class="thongbao <?php echo $loai_tb; ?>"><?php echo htmlspecialchars($thong_bao); ?></div>
<?php endif; ?>
<form method="POST">
<div class="form_nhap"><input type="email" name="email" placeholder="Nhập email của bạn" req_
