<?php
session_start();
include('../include/conn.php');
include('../include/header.php');

// Lấy user hiện tại đang đăng nhập
$session_user_id = $_SESSION['id_user'] ?? 0;

// Lấy id_user từ GET nếu có, ngược lại dùng session
$id_user = intval($_GET['id'] ?? $session_user_id);

if (!$id_user) {
    echo "Người dùng không tồn tại!";
    exit;
}

// ================== LẤY THÔNG TIN NGƯỜI DÙNG ==================
$stmt = $conn->prepare("SELECT * FROM users WHERE id_user=?");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$user) {
    echo "Người dùng không tồn tại!";
    exit;
}

// ================== LẤY BÀI ĐĂNG CỦA NGƯỜI DÙNG ==================
$stmt = $conn->prepare("SELECT p.*, 
                               (SELECT COUNT(*) FROM comments c WHERE c.id_post=p.id_post) as comment_count
                        FROM posts p
                        WHERE p.id_user=? AND p.status=1
                        ORDER BY p.created_at DESC");
$stmt->bind_param("i", $id_user);
$stmt->execute();
$res = $stmt->get_result();
$posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Hàm lấy avatar (mặc định nếu không có)
function getAvatar($avatarName) {
    $default = 'default.png';
    return $avatarName ? $avatarName : $default;
}
?>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/list.css">

<div class="main-content">

    <div class="profile-container">
        <!-- Cột trái: hồ sơ -->
        <div class="profile-left">
            <div class="user-profile-card">
                <img src="../uploads/user/<?= getAvatar($user['avatar']) ?>" alt="Avatar" class="avatar-large">
                <h2><?= htmlspecialchars($user['fullname']) ?></h2>
                <p>Email: <?= htmlspecialchars($user['email']) ?></p>
                <p>SĐT: <?= htmlspecialchars($user['sdt'] ?: 'Chưa có') ?></p>
                <p>Địa chỉ: <?= htmlspecialchars($user['diachi'] ?: 'Chưa có') ?></p>

                <?php if ($session_user_id && $session_user_id === $user['id_user']): ?>
                    <a href="edit_profile.php" class="btn-edit-profile">Sửa hồ sơ</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Cột phải: bài đăng -->
        <div class="profile-right">
            <h3>Bài đăng của <?= htmlspecialchars($user['fullname']) ?></h3>
            <div class="post-grid">
                <?php if (!empty($posts)): ?>
                    <?php foreach ($posts as $post): ?>
                        <?php
                        $img_res = $conn->query("SELECT link FROM image_post WHERE id_post=".$post['id_post']." LIMIT 1");
                        $img_row = $img_res->fetch_assoc();
                        $img_src = $img_row['link'] ?? 'default_post.png';
                        ?>
                        <div class="post-card">
                            <img src="../uploads/post/<?= htmlspecialchars($img_src) ?>" alt="Hình bài đăng">
                            <div class="post-info">
                                <p><?= htmlspecialchars(mb_substr($post['mo_ta'],0,80)) ?><?= mb_strlen($post['mo_ta'])>80 ? "..." : "" ?></p>
                                <small>Vị trí: <?= htmlspecialchars($post['vitri'] ?: 'Chưa có') ?></small>
                                <small>Bình luận: <?= $post['comment_count'] ?></small>
                            </div>
                            <div class="post-actions">
                                <a href="detail_post.php?id=<?= $post['id_post'] ?>">Xem chi tiết</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>Người dùng chưa có bài đăng nào.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<style>
.main-content { padding: 20px; }
.profile-container { display: flex; gap: 20px; flex-wrap: wrap; }
.profile-left { flex: 1; min-width: 250px; }
.profile-right { flex: 2; min-width: 400px; }

.user-profile-card { border: 1px solid #ddd; border-radius: 8px; padding: 20px; text-align: center; background: #fff; }
.avatar-large { width: 120px; height: 120px; border-radius: 50%; object-fit: cover; margin-bottom: 10px; }

.btn-edit-profile { display: inline-block; margin-top: 10px; padding: 6px 12px; background: #007bff; color: #fff; border-radius: 6px; text-decoration: none; }
.btn-edit-profile:hover { background: #0056b3; }

.post-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap: 15px; margin-top: 10px; }
.post-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; transition: 0.2s; }
.post-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.post-card img { width: 100%; height: 150px; object-fit: cover; }
.post-info { padding: 10px; }
.post-info p { margin:0 0 5px 0; font-size: 14px; }
.post-info small { color: #666; display:block; margin-top:2px; }
.post-actions { padding: 10px; text-align: right; }
.post-actions a { text-decoration: none; color: #007bff; font-size: 13px; }
.post-actions a:hover { text-decoration: underline; }
</style>
