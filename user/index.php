<?php
session_start();
include('../include/conn.php');
include('../include/header.php');

$id_user = $_SESSION['id_user'] ?? 0;
$search = $_GET['search'] ?? '';

// ================== HÀM LẤY AVATAR ==================
function getAvatar($avatarName) {
    $default = 'default.png';
    return $avatarName ? $avatarName : $default;
}

// ================== BÀI ĐĂNG NỔI BẬT (bình luận nhiều nhất) ==================
$hot_sql = "SELECT p.*, u.fullname, u.avatar, COUNT(c.id_comment) as comment_count
            FROM posts p
            JOIN users u ON p.id_user=u.id_user
            LEFT JOIN comments c ON c.id_post=p.id_post
            WHERE p.status=1
            GROUP BY p.id_post
            ORDER BY comment_count DESC
            LIMIT 5";

$hot_res = $conn->query($hot_sql);
$hot_posts = $hot_res->fetch_all(MYSQLI_ASSOC);

// ================== BÀI ĐĂNG MỚI NHẤT ==================
$new_sql = "SELECT p.*, u.fullname, u.avatar
            FROM posts p
            JOIN users u ON p.id_user=u.id_user
            WHERE p.status=1";

$params = [];
$types = "";

if ($search) {
    $new_sql .= " AND (p.mo_ta LIKE ? OR p.vitri LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$new_sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($new_sql);
if ($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$new_posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();
?>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/list.css">

<div class="main-content">

    <!-- Filter tìm kiếm -->
    <div class="filter-form">
        <form method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm nội dung hoặc vị trí..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm</button>
        </form>
    </div>

    <!-- Bài đăng nổi bật -->
    <h2>Bài đăng nổi bật</h2>
    <div class="post-grid">
        <?php foreach ($hot_posts as $post): ?>
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
                <div class="user-info">
                    <img src="../uploads/user/<?= getAvatar($post['avatar']) ?>" alt="Avatar" class="user-avatar">
                    <span><?= htmlspecialchars($post['fullname']) ?></span>
                </div>
                <div class="post-actions">
                    <a href="detail_post.php?id=<?= $post['id_post'] ?>">Xem chi tiết</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bài đăng mới nhất -->
    <h2>Bài đăng mới nhất</h2>
    <div class="post-grid">
        <?php if (!empty($new_posts)): ?>
            <?php foreach ($new_posts as $post): ?>
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
                    </div>
                    <div class="user-info">
                        <img src="../uploads/user/<?= getAvatar($post['avatar']) ?>" alt="Avatar" class="user-avatar">
                        <span><?= htmlspecialchars($post['fullname']) ?></span>
                    </div>
                    <div class="post-actions">
                        <a href="detail_post.php?id=<?= $post['id_post'] ?>">Xem chi tiết</a>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Chưa có bài đăng nào.</p>
        <?php endif; ?>
    </div>

</div>

<style>
.main-content { padding: 20px; }
.filter-form { margin-bottom: 20px; }
.filter-form input { padding: 6px 10px; width: 200px; border-radius: 5px; border: 1px solid #ccc; }
.filter-form button { padding: 6px 12px; border: none; background: #007bff; color: #fff; border-radius: 5px; cursor: pointer; }
.filter-form button:hover { background: #0056b3; }

.post-grid { display: grid; grid-template-columns: repeat(auto-fill,minmax(220px,1fr)); gap: 15px; margin-bottom: 30px; }
.post-card { border: 1px solid #ddd; border-radius: 8px; overflow: hidden; background: #fff; transition: 0.2s; }
.post-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.post-card img { width: 100%; height: 150px; object-fit: cover; }
.post-info { padding: 10px; }
.post-info p { margin:0 0 5px 0; font-size: 14px; }
.post-info small { color: #666; display:block; margin-top:2px; }
.user-info { display: flex; align-items: center; padding: 0 10px 10px 10px; }
.user-avatar { width: 30px; height: 30px; border-radius: 50%; object-fit: cover; margin-right: 8px; }
.post-actions { padding: 10px; text-align: right; }
.post-actions a { text-decoration: none; color: #007bff; font-size: 13px; }
.post-actions a:hover { text-decoration: underline; }
</style>
