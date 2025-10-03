<?php
session_start();
include('../include/functions.php');
include('../include/conn.php');
include('../include/header.php');
// include('../include/sidebar.php');

check_login();

$thong_bao = '';
$loai_tb = '';

$id_user = $_SESSION['id_user'] ?? 0; // Sử dụng đúng CSDL mới

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mo_ta = trim($_POST['mo_ta'] ?? '');
    $vitri = trim($_POST['vitri'] ?? '');

    if ($id_user <= 0) {
        $thong_bao = 'Bạn cần đăng nhập để tạo bài đăng!';
        $loai_tb = 'thatbai';
    } else {
        // Thêm bài đăng vào bảng posts
        $stmt = $conn->prepare("INSERT INTO posts (id_user, mo_ta, vitri) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $id_user, $mo_ta, $vitri);

        if ($stmt->execute()) {
            $id_post = $stmt->insert_id;

            // Upload nhiều ảnh
            if (!empty($_FILES['anh_post']['name'][0])) {
                foreach ($_FILES['anh_post']['name'] as $key => $name) {
                    if ($_FILES['anh_post']['error'][$key] === 0) {
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $filename = 'img_' . time() . '_' . $key . '.' . $ext;
                        $upload_path = '../uploads/post/' . $filename;
                        if (move_uploaded_file($_FILES['anh_post']['tmp_name'][$key], $upload_path)) {
                            $conn->query("INSERT INTO image_post (id_post, link) VALUES ($id_post, '$filename')");
                        }
                    }
                }
            }

            // Upload nhiều video
            if (!empty($_FILES['video_post']['name'][0])) {
                foreach ($_FILES['video_post']['name'] as $key => $name) {
                    if ($_FILES['video_post']['error'][$key] === 0) {
                        $ext = pathinfo($name, PATHINFO_EXTENSION);
                        $filename = 'vid_' . time() . '_' . $key . '.' . $ext;
                        $upload_path = '../uploads/post/' . $filename;
                        if (move_uploaded_file($_FILES['video_post']['tmp_name'][$key], $upload_path)) {
                            $conn->query("INSERT INTO video_post (id_post, link) VALUES ($id_post, '$filename')");
                        }
                    }
                }
            }

            $thong_bao = 'Tạo bài đăng thành công!';
            $loai_tb = 'thanhcong';
        } else {
            $thong_bao = 'Có lỗi xảy ra, thử lại!';
            $loai_tb = 'thatbai';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8">
<title>Tạo Bài Đăng - Simple Note</title>
<link rel="stylesheet" href="../css/main.css">
<link rel="stylesheet" href="../css/add.css">
</head>
<body>
<div class="main-content">
    <div class="khung_form">
        <div class="tieude">Tạo Bài Đăng Mới</div>

        <?php if(!empty($thong_bao)): ?>
        <div class="thongbao <?php echo $loai_tb; ?>"><?php echo $thong_bao; ?></div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-row">
                <div class="col-left">
                    <div class="noidung">
                        <label>Mô tả</label>
                        <textarea name="mo_ta" placeholder="Nhập nội dung bài đăng"></textarea>
                    </div>

                    <div class="noidung">
                        <label>Vị trí (tùy chọn)</label>
                        <input type="text" name="vitri" placeholder="Ví dụ: Hà Nội">
                    </div>

                    <div class="noidung">
                        <label>Ảnh (có thể thêm nhiều)</label>
                        <div id="image-inputs">
                            <div class="input-group">
                                <input type="file" name="anh_post[]" accept="image/*" onchange="previewImages()">
                                <button type="button" class="add-btn" onclick="addImageInput()">+</button>
                            </div>
                        </div>
                        <div id="preview-images" style="display:flex; gap:10px; margin-top:10px;"></div>
                    </div>

                    <div class="noidung">
                        <label>Video (có thể thêm nhiều)</label>
                        <div id="video-inputs">
                            <div class="input-group">
                                <input type="file" name="video_post[]" accept="video/*" onchange="previewVideos()">
                                <button type="button" class="add-btn" onclick="addVideoInput()">+</button>
                            </div>
                        </div>
                        <div id="preview-videos" style="display:flex; gap:10px; margin-top:10px;"></div>
                    </div>
                </div>
            </div>

            <button type="submit" class="nutbam">Tạo Bài Đăng</button>
        </form>
    </div>
</div>

<script>
// Thêm input ảnh/video
function addImageInput() {
    const container = document.getElementById('image-inputs');
    const div = document.createElement('div');
    div.className = 'input-group';
    div.innerHTML = `<input type="file" name="anh_post[]" accept="image/*" onchange="previewImages()">
                     <button type="button" class="add-btn" onclick="this.parentNode.remove()">-</button>`;
    container.appendChild(div);
}

function addVideoInput() {
    const container = document.getElementById('video-inputs');
    const div = document.createElement('div');
    div.className = 'input-group';
    div.innerHTML = `<input type="file" name="video_post[]" accept="video/*" onchange="previewVideos()">
                     <button type="button" class="add-btn" onclick="this.parentNode.remove()">-</button>`;
    container.appendChild(div);
}

// Preview ảnh
function previewImages() {
    const container = document.getElementById('preview-images');
    container.innerHTML = '';
    Array.from(document.querySelectorAll('input[name="anh_post[]"]')).forEach(input => {
        if(input.files[0]) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(input.files[0]);
            img.style.width = '100px';
            img.style.height = '100px';
            img.style.objectFit = 'cover';
            img.style.borderRadius = '8px';
            container.appendChild(img);
        }
    });
}

// Preview video
function previewVideos() {
    const container = document.getElementById('preview-videos');
    container.innerHTML = '';
    Array.from(document.querySelectorAll('input[name="video_post[]"]')).forEach(input => {
        if(input.files[0]) {
            const vid = document.createElement('video');
            vid.src = URL.createObjectURL(input.files[0]);
            vid.controls = true;
            vid.style.width = '150px';
            vid.style.height = '100px';
            vid.style.borderRadius = '8px';
            container.appendChild(vid);
        }
    });
}
</script>
</body>
</html>
