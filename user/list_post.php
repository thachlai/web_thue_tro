<?php
session_start();
include('../include/conn.php');
include('../include/header.php');
// include('../include/sidebar.php');

$id_user = $_SESSION['id_user'] ?? 0;

// ================== LẤY DATA ==================
$search = $_GET['search'] ?? '';

$sql = "SELECT p.*, u.fullname
        FROM posts p
        JOIN users u ON p.id_user=u.id_user
        WHERE p.status=1"; // lấy tất cả bài đăng mở

$params = [];
$types = "";

if ($search) {
    $sql .= " AND (p.mo_ta LIKE ? OR p.vitri LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$res = $stmt->get_result();
$posts = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

?>

<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/list.css">

<div class="main-content">

    <!-- Filter -->
    <div class="khung-form">
        <form method="GET" class="filter">
            <input type="text" name="search" placeholder="Tìm kiếm nội dung hoặc vị trí..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm</button>
        </form>
    </div>

    <!-- Posts -->
    <div class="khung-form">
        <h3>📌 Tất cả bài đăng</h3>
        <div class="grid-list">
            <?php if (!empty($posts)): ?>
                <?php foreach ($posts as $post): ?>
                    <div class="post-card">
                        <?php
                        // Lấy ảnh đầu tiên (nếu có)
                        $img_res = $conn->query("SELECT link FROM image_post WHERE id_post=".$post['id_post']." LIMIT 1");
                        $img_row = $img_res->fetch_assoc();
                        $img_src = $img_row['link'] ?? 'default_post.png';
                        ?>
                        <img src="../uploads/post/<?= htmlspecialchars($img_src) ?>" class="post-img">

                        <div class="post-content">
                            <p><?= htmlspecialchars(mb_substr($post['mo_ta'],0,80)) ?><?= mb_strlen($post['mo_ta'])>80 ? "..." : "" ?></p>
                            <small>Vị trí: <?= htmlspecialchars($post['vitri'] ?: 'Chưa có') ?></small>
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

</div>

<style>
/* Lưới bài đăng */
.grid-list {
    display: grid;
    grid-template-columns: repeat(auto-fill,minmax(220px,1fr));
    gap: 15px;
}
.post-card {
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
    background: #fff;
    transition: 0.2s;
}
.post-card:hover { box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
.post-img {
    width: 100%;
    height: 150px;
    object-fit: cover;
}
.post-content { padding: 10px; }
.post-content p { margin:0 0 5px 0; font-size: 14px; }
.post-content small { color: #666; }
.post-actions { padding: 10px; text-align: right; }
.post-actions a { text-decoration: none; color: #007bff; font-size: 13px; }
</style>
