-- Database: webcban_db
-- Tạo cơ sở dữ liệu cho website bán hàng

DROP DATABASE IF EXISTS webcban_db;
CREATE DATABASE webcban_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE webcban_db;

-- Bảng người dùng (users) - Tạo trước để làm foreign key
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    avatar VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng danh mục sản phẩm
CREATE TABLE categories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng sản phẩm
CREATE TABLE products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(10,2) NOT NULL,
    quantity INT DEFAULT 0,
    category_id INT,
    image_url VARCHAR(255),
    status ENUM('active', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- Bảng khách hàng (giữ lại để tương thích)
CREATE TABLE customers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng đơn hàng
CREATE TABLE orders (
    id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    customer_name VARCHAR(255) NOT NULL,
    customer_phone VARCHAR(20) NOT NULL,
    customer_email VARCHAR(255) NOT NULL,
    customer_address TEXT NOT NULL,
    status ENUM('pending', 'processing', 'shipped', 'delivered', 'cancelled') DEFAULT 'pending',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Bảng chi tiết đơn hàng
CREATE TABLE order_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- Bảng giỏ hàng (cart)
CREATE TABLE cart (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    product_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_product (user_id, product_id)
);

-- Bảng liên hệ
CREATE TABLE contacts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    subject VARCHAR(200),
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Thêm dữ liệu mẫu

-- Thêm user admin mặc định
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@webcban.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrator', 'admin');

-- Thêm user thường mẫu
INSERT INTO users (username, email, password, full_name, phone, address) VALUES
('user1', 'user1@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nguyễn Văn User', '0123456789', '123 Đường ABC, Quận 1, TP.HCM'),
('user2', 'user2@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Trần Thị User', '0987654321', '456 Đường XYZ, Quận 2, TP.HCM');

-- Danh mục sản phẩm
INSERT INTO categories (name, description) VALUES
('Điện thoại', 'Các loại điện thoại thông minh'),
('Laptop', 'Máy tính xách tay các loại'),
('Phụ kiện', 'Phụ kiện điện tử và công nghệ'),
('Tablet', 'Máy tính bảng các hãng');

-- Sản phẩm mẫu
INSERT INTO products (name, description, price, quantity, category_id, image_url) VALUES
('iPhone 15 Pro', 'Điện thoại iPhone 15 Pro mới nhất với chip A17 Pro', 29990000, 50, 1, 'https://via.placeholder.com/300x200?text=iPhone+15+Pro'),
('Samsung Galaxy S24', 'Điện thoại Samsung Galaxy S24 với camera AI', 22990000, 30, 1, 'https://via.placeholder.com/300x200?text=Galaxy+S24'),
('MacBook Air M2', 'Laptop MacBook Air với chip M2 mạnh mẽ', 32990000, 20, 2, 'https://via.placeholder.com/300x200?text=MacBook+Air'),
('Dell XPS 13', 'Laptop Dell XPS 13 thiết kế mỏng nhẹ', 25990000, 15, 2, 'https://via.placeholder.com/300x200?text=Dell+XPS+13'),
('AirPods Pro', 'Tai nghe không dây AirPods Pro với chống ồn', 5990000, 100, 3, 'https://via.placeholder.com/300x200?text=AirPods+Pro'),
('iPad Air', 'Máy tính bảng iPad Air với chip M1', 16990000, 25, 4, 'https://via.placeholder.com/300x200?text=iPad+Air');

-- Khách hàng mẫu (giữ lại để tương thích)
INSERT INTO customers (name, email, phone, address) VALUES
('Nguyễn Văn A', 'nguyenvana@email.com', '0123456789', '123 Đường ABC, Quận 1, TP.HCM'),
('Trần Thị B', 'tranthib@email.com', '0987654321', '456 Đường XYZ, Quận 2, TP.HCM'),
('Lê Văn C', 'levanc@email.com', '0369258147', '789 Đường DEF, Quận 3, TP.HCM');

-- Thêm foreign key cho orders sau khi có dữ liệu users
ALTER TABLE orders ADD FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE;

-- Đơn hàng mẫu (sử dụng user_id thay vì customer_id)
INSERT INTO orders (customer_id, total_amount, customer_name, customer_phone, customer_email, customer_address, status, notes) VALUES
(2, 29990000, 'Nguyễn Văn User', '0123456789', 'user1@email.com', '123 Đường ABC, Quận 1, TP.HCM', 'delivered', 'Giao hàng thành công'),
(3, 48980000, 'Trần Thị User', '0987654321', 'user2@email.com', '456 Đường XYZ, Quận 2, TP.HCM', 'processing', 'Đang xử lý đơn hàng'),
(2, 5990000, 'Nguyễn Văn User', '0123456789', 'user1@email.com', '123 Đường ABC, Quận 1, TP.HCM', 'pending', 'Chờ xác nhận');

-- Chi tiết đơn hàng
INSERT INTO order_items (order_id, product_id, quantity, price) VALUES
(1, 1, 1, 29990000),
(2, 3, 1, 32990000),
(2, 5, 1, 5990000),
(3, 5, 1, 5990000);
