<?php
session_start();
include('../include/conn.php');
include('../include/header.php');
// include('../include/sidebar.php');

$id_user = $_SESSION['id_user'] ?? 0;
$id_post = intval($_GET['id'] ?? 0);

if (!$id_post) {
    echo "Bài đăng không tồn tại!";
    exit;
}

// ================== LẤY THÔNG TIN BÀI ĐĂNG ==================
$stmt = $conn->prepare("SELECT p.*, u.fullname, u.sdt, u.diachi, u.avatar
                        FROM posts p
                        JOIN users u ON p.id_user=u.id_user
                        WHERE p.id_post=? AND p.status=1");
$stmt->bind_param("i", $id_post);
$stmt->execute();
$post = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$post) {
    echo "Bài đăng không tồn tại hoặc đã bị khóa!";
    exit;
}

// ================== LẤY ẢNH VÀ VIDEO ==================
$images = [];
$videos = [];

$res = $conn->query("SELECT link FROM image_post WHERE id_post=$id_post");
while ($row = $res->fetch_assoc()) $images[] = $row['link'];

$res = $conn->query("SELECT link FROM video_post WHERE id_post=$id_post");
while ($row = $res->fetch_assoc()) $videos[] = $row['link'];

// ================== XỬ LÝ COMMENT ==================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comment'])) {
    $comment = trim($_POST['comment']);
    if ($id_user && $comment !== '') {
        $stmt = $conn->prepare("INSERT INTO comments (id_user, id_post, mo_ta) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $id_user, $id_post, $comment);
        $stmt->execute();
        $stmt->close();
        header("Location: detail_post.php?id=$id_post");
        exit;
    }
}

// ================== LẤY BÌNH LUẬN ==================
$stmt = $conn->prepare("SELECT c.*, u.fullname, u.avatar, u.id_user
                        FROM comments c
                        JOIN users u ON c.id_user=u.id_user
                        WHERE c.id_post=?
                        ORDER BY c.created_at ASC");
$stmt->bind_param("i", $id_post);
$stmt->execute();
$res = $stmt->get_result();
$comments = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ================== HÀM LẤY AVATAR ==================
function getAvatar($avatarName) {
    $default = 'default.png';
    return $avatarName ? $avatarName : $default;
}
?>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/detail_post.css">

<div class="main-content">

    <div class="post-detail-container">
        <!-- Bên trái: bài đăng -->
        <div class="post-left">
            <h2>Bài đăng</h2>
            <p><?= nl2br(htmlspecialchars($post['mo_ta'])) ?></p>
            <p><strong>Vị trí:</strong> <?= htmlspecialchars($post['vitri'] ?: 'Chưa có') ?></p>

            <!-- Ảnh -->
            <?php if (!empty($images)): ?>
                <div class="post-images">
                    <?php foreach ($images as $img): ?>
                        <img src="../uploads/post/<?= htmlspecialchars($img) ?>" alt="Post Image">
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Video -->
            <?php if (!empty($videos)): ?>
                <div class="post-videos">
                    <?php foreach ($videos as $vid): ?>
                        <video src="../uploads/post/<?= htmlspecialchars($vid) ?>" controls></video>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Bên phải: thông tin người đăng -->
        <div class="post-right">
            <h3>Người đăng</h3>
            <div class="user-profile">
                <img src="../uploads/user/<?= htmlspecialchars(getAvatar($post['avatar'])) ?>" alt="Avatar">
                <p><a href="profile.php?id=<?= $post['id_user'] ?>"><?= htmlspecialchars($post['fullname']) ?></a></p>
                <p>SĐT: <?= htmlspecialchars($post['sdt'] ?: 'Chưa có') ?></p>
                <p>Địa chỉ: <?= htmlspecialchars($post['diachi'] ?: 'Chưa có') ?></p>
            </div>
        </div>
    </div>

    <!-- Bình luận -->
    <div class="comments-section">
        <h3>Bình luận (<?= count($comments) ?>)</h3>
        <div class="comments-list">
            <?php foreach ($comments as $c): ?>
                <div class="comment-card">
                    <div class="comment-left">
                        <a href="profile.php?id=<?= $c['id_user'] ?>">
                            <img src="../uploads/user/<?= htmlspecialchars(getAvatar($c['avatar'])) ?>" alt="Avatar">
                            <p><?= htmlspecialchars($c['fullname']) ?></p>
                        </a>
                    </div>
                    <div class="comment-right">
                        <p><?= nl2br(htmlspecialchars($c['mo_ta'])) ?></p>
                        <small><?= $c['created_at'] ?></small>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Form thêm bình luận -->
        <?php if ($id_user): ?>
        <form method="POST" class="comment-form">
            <textarea name="comment" placeholder="Viết bình luận..." required></textarea>
            <button type="submit">Gửi</button>
        </form>
        <?php else: ?>
            <p>Vui lòng <a href="login.php">đăng nhập</a> để bình luận.</p>
        <?php endif; ?>
    </div>

</div>

<style>
.post-detail-container {
    display: flex;
    gap: 20px;
    margin-bottom: 30px;
}
.post-left {
    flex: 2;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    background: #fff;
}
.post-right {
    flex: 1;
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    background: #f9f9f9;
    text-align: center;
}
.post-right img {
    width: 100px;
    height: 100px;
    object-fit: cover;
    border-radius: 50%;
}
.post-images img, .post-videos video {
    max-width: 100%;
    margin-top: 10px;
    border-radius: 8px;
}
.comments-section {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    background: #fff;
}
.comment-card {
    display: flex;
    gap: 10px;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}
.comment-left {
    width: 80px;
    text-align: center;
}
.comment-left img {
    width: 50px;
    height: 50px;
    border-radius: 50%;
}
.comment-left p {
    margin: 5px 0 0 0;
    font-size: 13px;
}
.comment-right p {
    margin: 0 0 5px 0;
}
.comment-right small {
    color: #666;
    font-size: 12px;
}
.comment-form textarea {
    width: 100%;
    height: 60px;
    padding: 8px;
    margin-top: 10px;
    border-radius: 6px;
    border: 1px solid #ccc;
}
.comment-form button {
    margin-top: 5px;
    padding: 6px 12px;
    border: none;
    background: #007bff;
    color: #fff;
    border-radius: 6px;
    cursor: pointer;
}
.comment-form button:hover {
    background: #0056b3;
}
</style>
