# WebCBan - Website Bán Hàng PHP

## Giới Thiệu

WebCBan là một website bán hàng trực tuyến được phát triển bằng PHP và MySQL, được thiết kế cho đồ án sinh viên. Website cung cấy đầy đủ các chức năng CRUD (Create, Read, Update, Delete) và có giao diện quản trị hoàn chỉnh.

## Tính Năng Chính

### Website Khách Hàng
- **Trang chủ**: Hiển thị sản phẩm nổi bật và thông tin giới thiệu
- **Danh sách sản phẩm**: Xem tất cả sản phẩm với tính năng tìm kiếm và lọc theo danh mục
- **Chi tiết sản phẩm**: Xem thông tin chi tiết từng sản phẩm
- **Liên hệ**: Form liên hệ với validation
- **Giới thiệu**: Thông tin về công ty và đội ngũ

### Trang Quản Trị (Admin)
- **Dashboard**: Thống kê tổng quan về sản phẩm, khách hàng, đơn hàng
- **Quản lý sản phẩm**: CRUD đầy đủ cho sản phẩm
- **Quản lý danh mục**: CRUD cho danh mục sản phẩm
- **Quản lý khách hàng**: CRUD cho thông tin khách hàng
- **Quản lý đơn hàng**: Xem và cập nhật trạng thái đơn hàng
- **Tin nhắn liên hệ**: Quản lý các tin nhắn từ khách hàng

## Công Nghệ Sử Dụng

- **Backend**: PHP 7.4+
- **Database**: MySQL 5.7+
- **Frontend**: HTML5, CSS3, JavaScript
- **Framework CSS**: Bootstrap 5.1.3
- **Icons**: Font Awesome 6.0.0
- **Server**: Apache (XAMPP)

## Cài Đặt và Chạy

### Yêu Cầu Hệ Thống
- XAMPP (Apache + MySQL + PHP)
- PHP 7.4 trở lên
- MySQL 5.7 trở lên

### Hướng Dẫn Cài Đặt

1. **Cài đặt XAMPP**
   - Tải và cài đặt XAMPP từ https://www.apachefriends.org/
   - Khởi động Apache và MySQL trong XAMPP Control Panel

2. **Sao chép mã nguồn**
   ```bash
   # Sao chép thư mục dự án vào htdocs của XAMPP
   cp -r webcban C:/xampp/htdocs/
   ```

3. **Tạo cơ sở dữ liệu**
   - Mở phpMyAdmin: http://localhost/phpmyadmin
   - Tạo database mới tên `webcban_db`
   - Import file `database/webcban_db.sql` vào database vừa tạo

4. **Cấu hình kết nối database**
   - Mở file `config/database.php`
   - Kiểm tra thông tin kết nối database (mặc định: localhost, root, không password)

5. **Chạy website**
   - Truy cập: http://localhost/webcban
   - Trang quản trị: http://localhost/webcban/admin

## Cấu Trúc Thư Mục

```
webcban/
├── admin/                  # Trang quản trị
│   ├── includes/          # Header, footer, sidebar admin
│   ├── index.php          # Dashboard
│   ├── products.php       # Quản lý sản phẩm
│   ├── categories.php     # Quản lý danh mục
│   ├── customers.php      # Quản lý khách hàng
│   ├── orders.php         # Quản lý đơn hàng
│   └── contacts.php       # Tin nhắn liên hệ
├── assets/                # Tài nguyên tĩnh
│   ├── css/
│   │   └── style.css      # CSS tùy chỉnh
│   └── js/
│       └── main.js        # JavaScript
├── config/
│   └── database.php       # Cấu hình kết nối DB
├── database/
│   └── webcban_db.sql     # File SQL tạo database
├── includes/              # Header, footer chung
│   ├── header.php
│   └── footer.php
├── index.php              # Trang chủ
├── products.php           # Danh sách sản phẩm
├── product_detail.php     # Chi tiết sản phẩm
├── contact.php            # Trang liên hệ
├── about.php              # Trang giới thiệu
└── README.md              # Tài liệu hướng dẫn
```

## Cơ Sở Dữ Liệu

### Các Bảng Chính

1. **products**: Thông tin sản phẩm
2. **categories**: Danh mục sản phẩm
3. **customers**: Thông tin khách hàng
4. **orders**: Đơn hàng
5. **order_items**: Chi tiết đơn hàng
6. **contacts**: Tin nhắn liên hệ

### Mối Quan Hệ
- products ↔ categories (Many-to-One)
- orders ↔ customers (Many-to-One)
- order_items ↔ orders (Many-to-One)
- order_items ↔ products (Many-to-One)

## Chức Năng CRUD

### Sản Phẩm (Products)
- **Create**: Thêm sản phẩm mới với đầy đủ thông tin
- **Read**: Xem danh sách và chi tiết sản phẩm
- **Update**: Cập nhật thông tin sản phẩm
- **Delete**: Xóa sản phẩm

### Danh Mục (Categories)
- **Create**: Thêm danh mục mới
- **Read**: Xem danh sách danh mục
- **Update**: Cập nhật thông tin danh mục
- **Delete**: Xóa danh mục (nếu không có sản phẩm)

### Khách Hàng (Customers)
- **Create**: Thêm khách hàng mới
- **Read**: Xem danh sách khách hàng
- **Update**: Cập nhật thông tin khách hàng
- **Delete**: Xóa khách hàng (nếu không có đơn hàng)

### Đơn Hàng (Orders)
- **Create**: Tạo đơn hàng mới
- **Read**: Xem danh sách đơn hàng
- **Update**: Cập nhật trạng thái đơn hàng
- **Delete**: Xóa đơn hàng

## Tính Năng Nổi Bật

- **Responsive Design**: Giao diện thân thiện trên mọi thiết bị
- **Search & Filter**: Tìm kiếm và lọc sản phẩm theo danh mục
- **Admin Dashboard**: Thống kê trực quan và quản lý dễ dàng
- **Form Validation**: Kiểm tra dữ liệu đầu vào
- **Security**: Sử dụng PDO để tránh SQL Injection
- **User Experience**: Giao diện đẹp mắt với Bootstrap và Font Awesome

## Hướng Dẫn Sử Dụng

### Cho Khách Hàng
1. Truy cập trang chủ để xem sản phẩm nổi bật
2. Vào trang "Sản Phẩm" để xem tất cả sản phẩm
3. Sử dụng tính năng tìm kiếm và lọc theo danh mục
4. Click vào sản phẩm để xem chi tiết
5. Sử dụng form liên hệ để gửi tin nhắn

### Cho Quản Trị Viên
1. Truy cập `/admin` để vào trang quản trị
2. Xem thống kê tổng quan tại Dashboard
3. Quản lý sản phẩm: thêm, sửa, xóa sản phẩm
4. Quản lý danh mục sản phẩm
5. Theo dõi khách hàng và đơn hàng
6. Xử lý tin nhắn liên hệ từ khách hàng

## Bảo Mật

- Sử dụng PDO Prepared Statements để tránh SQL Injection
- Validation và sanitization dữ liệu đầu vào
- HTML escaping để tránh XSS
- Error handling an toàn

## Mở Rộng

Website có thể được mở rộng thêm các tính năng:
- Hệ thống đăng nhập/đăng ký
- Giỏ hàng và thanh toán
- Quản lý kho hàng
- Báo cáo thống kê chi tiết
- API REST
- Tích hợp payment gateway

## Tác Giả

Dự án được phát triển cho mục đích học tập và đồ án sinh viên.

## Giấy Phép

Dự án này được phát hành dưới giấy phép MIT. Bạn có thể tự do sử dụng, chỉnh sửa và phân phối.

## Liên Hệ

- Email: info@webcban.com
- Website: http://localhost/webcban
- Admin Panel: http://localhost/webcban/admin

---

**Lưu ý**: Đây là phiên bản demo cho mục đích học tập. Trong môi trường production, cần bổ sung thêm các biện pháp bảo mật và tối ưu hóa hiệu suất.
