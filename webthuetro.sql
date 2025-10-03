CREATE TABLE users (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(32) NOT NULL, -- MD5
    sdt VARCHAR(15),
    diachi VARCHAR(255),
    gioitinh VARCHAR(10),
    ngaysinh DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    role TINYINT DEFAULT 1, -- 0=admin, 1=nguoidung
    status TINYINT DEFAULT 1 -- 0=khóa, 1=mở
);
CREATE TABLE posts (
    id_post INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    mo_ta TEXT,
    vitri VARCHAR(255),
    status TINYINT DEFAULT 1, -- 0=khóa, 1=mở
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);

CREATE TABLE image_post (
    id_image INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    link VARCHAR(255),
    FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE
);
CREATE TABLE video_post (
    id_video INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL,
    link VARCHAR(255),
    FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE
);
CREATE TABLE comments (
    id_comment INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_post INT NOT NULL,
    mo_ta TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE
);
CREATE TABLE packages (
    id_package INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    duration INT NOT NULL, -- thời gian gói tính theo tháng
    features TEXT -- mô tả hoặc JSON các tính năng VIP: "noi_bat, video, thoi_gian_dang_tin"
);
CREATE TABLE user_packages (
    id_user_pkg INT AUTO_INCREMENT PRIMARY KEY,
    id_user INT NOT NULL,
    id_package INT NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status TINYINT DEFAULT 1, -- 1=active, 0=expired
    UNIQUE KEY uq_active_user (id_user, status), -- đảm bảo 1 user chỉ có 1 gói active
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE,
    FOREIGN KEY (id_package) REFERENCES packages(id_package) ON DELETE CASCADE
);
CREATE TABLE applications (
    id_app INT AUTO_INCREMENT PRIMARY KEY,
    id_post INT NOT NULL, -- bài đăng mà sinh viên ứng tuyển
    id_user INT NOT NULL, -- sinh viên ứng tuyển
    message TEXT, -- nội dung gửi ứng tuyển
    status TINYINT DEFAULT 0, -- 0=chờ, 1=chấp nhận, 2=từ chối
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_post) REFERENCES posts(id_post) ON DELETE CASCADE,
    FOREIGN KEY (id_user) REFERENCES users(id_user) ON DELETE CASCADE
);
ALTER TABLE users ADD avatar VARCHAR(255) DEFAULT NULL;
