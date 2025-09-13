<?php
$page_title = 'Liên Hệ - Website Bán Hàng';
require_once 'config/database.php';
require_once 'includes/header.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $contact_message = trim($_POST['message']);

    if ($name && $email && $contact_message) {
        try {
            $stmt = $pdo->prepare("INSERT INTO contacts (name, email, subject, message) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $subject, $contact_message]);
            $message = 'Cảm ơn bạn đã liên hệ! Chúng tôi sẽ phản hồi trong thời gian sớm nhất.';
            $message_type = 'success';
        } catch (PDOException $e) {
            $message = 'Có lỗi xảy ra khi gửi tin nhắn. Vui lòng thử lại!';
            $message_type = 'danger';
        }
    } else {
        $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        $message_type = 'warning';
    }
}
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Liên Hệ Với Chúng Tôi</h1>
        </div>
    </div>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="contact-form">
                <h3 class="mb-4">Gửi Tin Nhắn</h3>
                <form method="POST" class="needs-validation" novalidate>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Họ và Tên *</label>
                            <input type="text" class="form-control" id="name" name="name" required>
                            <div class="invalid-feedback">
                                Vui lòng nhập họ và tên.
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                            <div class="invalid-feedback">
                                Vui lòng nhập email hợp lệ.
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subject" class="form-label">Tiêu Đề</label>
                        <input type="text" class="form-control" id="subject" name="subject">
                    </div>
                    <div class="mb-3">
                        <label for="message" class="form-label">Nội Dung *</label>
                        <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                        <div class="invalid-feedback">
                            Vui lòng nhập nội dung tin nhắn.
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-paper-plane"></i> Gửi Tin Nhắn
                    </button>
                </form>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Thông Tin Liên Hệ</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <i class="fas fa-map-marker-alt text-primary"></i>
                        <strong> Địa chỉ:</strong><br>
                        123 Đường ABC, Quận 1<br>
                        TP. Hồ Chí Minh, Việt Nam
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-phone text-primary"></i>
                        <strong> Điện thoại:</strong><br>
                        <a href="tel:0123456789">0123-456-789</a>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-envelope text-primary"></i>
                        <strong> Email:</strong><br>
                        <a href="mailto:info@webcban.com">info@webcban.com</a>
                    </div>
                    <div class="mb-3">
                        <i class="fas fa-clock text-primary"></i>
                        <strong> Giờ làm việc:</strong><br>
                        Thứ 2 - Thứ 6: 8:00 - 17:00<br>
                        Thứ 7: 8:00 - 12:00<br>
                        Chủ nhật: Nghỉ
                    </div>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Mạng Xã Hội</h5>
                </div>
                <div class="card-body text-center">
                    <a href="#" class="btn btn-outline-primary me-2 mb-2">
                        <i class="fab fa-facebook-f"></i> Facebook
                    </a>
                    <a href="#" class="btn btn-outline-info me-2 mb-2">
                        <i class="fab fa-twitter"></i> Twitter
                    </a>
                    <a href="#" class="btn btn-outline-danger me-2 mb-2">
                        <i class="fab fa-instagram"></i> Instagram
                    </a>
                    <a href="#" class="btn btn-outline-primary mb-2">
                        <i class="fab fa-linkedin-in"></i> LinkedIn
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-5">
        <div class="col-12">
            <h3 class="mb-3">Bản Đồ</h3>
            <div class="embed-responsive embed-responsive-16by9">
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3919.4326002932!2d106.69742831533353!3d10.776530192318146!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x31752f4b3332a4bd%3A0x8329c2d2b2b2b2b2!2zVHAuIEjhu5MgQ2jDrSBNaW5oLCBWaeG7h3QgTmFt!5e0!3m2!1svi!2s!4v1234567890123!5m2!1svi!2s" 
                        width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy" class="rounded"></iframe>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
