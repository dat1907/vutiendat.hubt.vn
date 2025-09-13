<?php
$page_title = 'Thêm Khách Hàng - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($name && $email) {
        try {
            $stmt = $pdo->prepare("INSERT INTO customers (name, email, phone, address) VALUES (?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $address]);
            $message = 'Thêm khách hàng thành công!';
            $message_type = 'success';
            // Reset form
            $_POST = [];
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Email này đã được sử dụng!';
            } else {
                $message = 'Lỗi khi thêm khách hàng: ' . $e->getMessage();
            }
            $message_type = 'danger';
        }
    } else {
        $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        $message_type = 'warning';
    }
}
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Thêm Khách Hàng Mới</h1>
                    <a href="customers.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Quay Lại
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row justify-content-center">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Họ và Tên *</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="<?php echo isset($_POST['name']) ? htmlspecialchars($_POST['name']) : ''; ?>" required>
                                                <div class="invalid-feedback">
                                                    Vui lòng nhập họ và tên.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" required>
                                                <div class="invalid-feedback">
                                                    Vui lòng nhập email hợp lệ.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số Điện Thoại</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Địa Chỉ</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="customers.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Lưu Khách Hàng
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
