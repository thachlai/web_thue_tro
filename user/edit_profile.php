<?php
session_start();
include('../include/conn.php');
include('../include/header.php');

// Kiểm tra user đã đăng nhập chưa
$id_user = $_SESSION['id_user'] ?? 0;
if (!$id_user) {
    header("Location: login.php");
    exit;
}

// Lấy thông tin user hiện tại
$stmt = $conn->prepare("SELECT * FROM users WHERE id_user=?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "Người dùng không tồn tại!";
    exit;
}

$thong_bao = '';
$loai_tb = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $sdt      = trim($_POST['sdt'] ?? '');
    $diachi   = trim($_POST['diachi'] ?? '');
    $gioitinh = trim($_POST['gioitinh'] ?? '');
    $ngaysinh = trim($_POST['ngaysinh'] ?? '');

    // Xử lý avatar nếu có upload
    $avatar_name = $user['avatar'];
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0) {
        $ext = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
        $new_name = 'avatar_'.$id_user.'_'.time().'.'.$ext;
        $upload_dir = '../uploads/user/';
        if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_dir.$new_name)) {
            $avatar_name = $new_name;
        }
    }

    // Cập nhật database
    $stmt = $conn->prepare("UPDATE users SET fullname=?, email=?, sdt=?, diachi=?, gioitinh=?, ngaysinh=?, avatar=? WHERE id_user=?");
    $stmt->bind_param("sssssssi", $fullname, $email, $sdt, $diachi, $gioitinh, $ngaysinh, $avatar_name, $id_user);
    if ($stmt->execute()) {
        $thong_bao = "Cập nhật hồ sơ thành công!";
        $loai_tb = "thanhcong";
        // Cập nhật session fullname
        $_SESSION['fullname'] = $fullname;
    } else {
        $thong_bao = "Cập nhật thất bại: " . $stmt->error;
        $loai_tb = "thatbai";
    }
    $stmt->close();
}
?>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/auth.css">

<div class="khung_form">
    <h2>Sửa Hồ Sơ</h2>

    <?php if (!empty($thong_bao)): ?>
        <div class="thongbao <?= $loai_tb ?>">
            <?= htmlspecialchars($thong_bao) ?>
        </div>
    <?php endif; ?>

    <form method="post" action="" enctype="multipart/form-data">
        <div class="form_nhap">
            <label>Họ và tên</label>
            <input type="text" name="fullname" value="<?= htmlspecialchars($user['fullname']) ?>" required>
        </div>
        <div class="form_nhap">
            <label>Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
        </div>
        <div class="form_nhap">
            <label>SĐT</label>
            <input type="text" name="sdt" value="<?= htmlspecialchars($user['sdt']) ?>">
        </div>
        <div class="form_nhap">
            <label>Địa chỉ</label>
            <input type="text" name="diachi" value="<?= htmlspecialchars($user['diachi']) ?>">
        </div>
        <div class="form_nhap">
            <label>Giới tính</label>
            <select name="gioitinh">
                <option value="">Chọn giới tính</option>
                <option value="Nam" <?= $user['gioitinh']=='Nam'?'selected':'' ?>>Nam</option>
                <option value="Nữ" <?= $user['gioitinh']=='Nữ'?'selected':'' ?>>Nữ</option>
                <option value="Khác" <?= $user['gioitinh']=='Khác'?'selected':'' ?>>Khác</option>
            </select>
        </div>
        <div class="form_nhap">
            <label>Ngày sinh</label>
            <input type="date" name="ngaysinh" value="<?= htmlspecialchars($user['ngaysinh']) ?>">
        </div>
        <div class="form_nhap">
            <label>Avatar</label><br>
            <img src="../uploads/user/<?= htmlspecialchars($user['avatar'] ?: 'default.png') ?>" alt="Avatar" width="100"><br>
            <input type="file" name="avatar" accept="image/*">
        </div>

        <button type="submit" class="nut_submit">Cập nhật</button>
        <a href="profile.php" class="nut_submit" style="background:#6c757d;">Hủy</a>
    </form>
</div>

<style>
.khung_form { max-width:500px; margin:50px auto; padding:20px; background:#fff; border-radius:8px; box-shadow:0 4px 12px rgba(0,0,0,0.1); }
.form_nhap { margin-bottom:15px; }
.form_nhap input, .form_nhap select { width:100%; padding:8px; border-radius:4px; border:1px solid #ccc; }
.nut_submit { display:inline-block; padding:8px 15px; background:#007bff; color:#fff; border:none; border-radius:4px; cursor:pointer; text-decoration:none; margin-top:10px; }
.nut_submit:hover { background:#0056b3; }
</style>
