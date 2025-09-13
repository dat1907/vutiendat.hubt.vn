<?php
$page_title = 'Chỉnh Sửa Khách Hàng - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';
$customer_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$customer_id) {
    header('Location: customers.php');
    exit;
}

// Lấy thông tin khách hàng
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$customer_id]);
$customer = $stmt->fetch();

if (!$customer) {
    header('Location: customers.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    if ($name && $email) {
        try {
            $stmt = $pdo->prepare("UPDATE customers SET name = ?, email = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$name, $email, $phone, $address, $customer_id]);
            $message = 'Cập nhật khách hàng thành công!';
            $message_type = 'success';
            
            // Cập nhật lại thông tin khách hàng
            $stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
            $stmt->execute([$customer_id]);
            $customer = $stmt->fetch();
        } catch (PDOException $e) {
            if ($e->getCode() == 23000) {
                $message = 'Email này đã được sử dụng bởi khách hàng khác!';
            } else {
                $message = 'Lỗi khi cập nhật khách hàng: ' . $e->getMessage();
            }
            $message_type = 'danger';
        }
    } else {
        $message = 'Vui lòng điền đầy đủ thông tin bắt buộc!';
        $message_type = 'warning';
    }
}

// Lấy thống kê đơn hàng của khách hàng
$stmt = $pdo->prepare("SELECT COUNT(*) as order_count, COALESCE(SUM(total_amount), 0) as total_spent FROM orders WHERE customer_id = ?");
$stmt->execute([$customer_id]);
$stats = $stmt->fetch();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Chỉnh Sửa Khách Hàng</h1>
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

                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Thông Tin Khách Hàng</h5>
                            </div>
                            <div class="card-body">
                                <form method="POST" class="needs-validation" novalidate>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="name" class="form-label">Họ và Tên *</label>
                                                <input type="text" class="form-control" id="name" name="name" 
                                                       value="<?php echo htmlspecialchars($customer['name']); ?>" required>
                                                <div class="invalid-feedback">
                                                    Vui lòng nhập họ và tên.
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="email" class="form-label">Email *</label>
                                                <input type="email" class="form-control" id="email" name="email" 
                                                       value="<?php echo htmlspecialchars($customer['email']); ?>" required>
                                                <div class="invalid-feedback">
                                                    Vui lòng nhập email hợp lệ.
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label for="phone" class="form-label">Số Điện Thoại</label>
                                        <input type="tel" class="form-control" id="phone" name="phone" 
                                               value="<?php echo htmlspecialchars($customer['phone']); ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label for="address" class="form-label">Địa Chỉ</label>
                                        <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($customer['address']); ?></textarea>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <a href="customers.php" class="btn btn-secondary">
                                            <i class="fas fa-times"></i> Hủy
                                        </a>
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Cập Nhật
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Thống Kê</h5>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <strong>ID Khách Hàng:</strong> #<?php echo $customer['id']; ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Ngày Đăng Ký:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($customer['created_at'])); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Cập Nhật Cuối:</strong><br>
                                    <?php echo date('d/m/Y H:i', strtotime($customer['updated_at'])); ?>
                                </div>
                                <div class="mb-3">
                                    <strong>Tổng Đơn Hàng:</strong><br>
                                    <span class="badge bg-primary fs-6"><?php echo $stats['order_count']; ?> đơn</span>
                                </div>
                                <div class="mb-3">
                                    <strong>Tổng Chi Tiêu:</strong><br>
                                    <span class="text-success fs-5"><?php echo number_format($stats['total_spent']); ?> VNĐ</span>
                                </div>
                                <div class="d-grid">
                                    <a href="customer_orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-info">
                                        <i class="fas fa-shopping-cart"></i> Xem Đơn Hàng
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
