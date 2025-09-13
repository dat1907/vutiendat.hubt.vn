<?php
session_start();
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;

if (!$order_id) {
    header('Location: index.php');
    exit();
}

// Get order details
try {
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: index.php');
        exit();
    }
    
    // Get order items
    $stmt = $pdo->prepare("
        SELECT oi.*, p.name, p.image_url 
        FROM order_items oi 
        JOIN products p ON oi.product_id = p.id 
        WHERE oi.order_id = ?
    ");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}

$page_title = 'Đặt hàng thành công';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="text-center mb-4">
                <i class="fas fa-check-circle fa-5x text-success mb-3"></i>
                <h2 class="text-success">Đặt hàng thành công!</h2>
                <p class="lead">Cảm ơn bạn đã mua hàng tại WebCBan</p>
            </div>
            
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0"><i class="fas fa-receipt"></i> Thông tin đơn hàng #<?php echo $order['id']; ?></h5>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <h6>Thông tin khách hàng:</h6>
                            <p class="mb-1"><strong>Họ tên:</strong> <?php echo htmlspecialchars($order['full_name']); ?></p>
                            <p class="mb-1"><strong>Email:</strong> <?php echo htmlspecialchars($order['email']); ?></p>
                            <?php if ($order['phone']): ?>
                                <p class="mb-1"><strong>Điện thoại:</strong> <?php echo htmlspecialchars($order['phone']); ?></p>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h6>Thông tin đơn hàng:</h6>
                            <p class="mb-1"><strong>Mã đơn hàng:</strong> #<?php echo $order['id']; ?></p>
                            <p class="mb-1"><strong>Ngày đặt:</strong> <?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></p>
                            <p class="mb-1"><strong>Trạng thái:</strong> 
                                <span class="badge bg-warning">Chờ xử lý</span>
                            </p>
                            <p class="mb-1"><strong>Tổng tiền:</strong> 
                                <span class="text-success fw-bold"><?php echo number_format($order['total_amount']); ?> VNĐ</span>
                            </p>
                        </div>
                    </div>
                    
                    <?php if ($order['notes']): ?>
                        <div class="mb-3">
                            <h6>Ghi chú:</h6>
                            <p class="text-muted"><?php echo nl2br(htmlspecialchars($order['notes'])); ?></p>
                        </div>
                    <?php endif; ?>
                    
                    <h6>Chi tiết sản phẩm:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Đơn giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($order_items as $item): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                     class="me-2" style="width: 40px; height: 40px; object-fit: cover;" 
                                                     alt="<?php echo htmlspecialchars($item['name']); ?>">
                                                <?php echo htmlspecialchars($item['name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo $item['quantity']; ?></td>
                                        <td><?php echo number_format($item['price']); ?> VNĐ</td>
                                        <td><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr class="table-success">
                                    <th colspan="3">Tổng cộng:</th>
                                    <th><?php echo number_format($order['total_amount']); ?> VNĐ</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
            
            <div class="alert alert-info mt-4">
                <h6><i class="fas fa-info-circle"></i> Thông tin quan trọng:</h6>
                <ul class="mb-0">
                    <li>Đơn hàng của bạn sẽ được xử lý trong vòng 24 giờ</li>
                    <li>Chúng tôi sẽ liên hệ với bạn để xác nhận đơn hàng</li>
                    <li>Thời gian giao hàng dự kiến: 1-3 ngày làm việc</li>
                    <li>Bạn có thể theo dõi đơn hàng trong mục "Đơn hàng của tôi"</li>
                </ul>
            </div>
            
            <div class="text-center mt-4">
                <a href="index.php" class="btn btn-primary me-2">
                    <i class="fas fa-home"></i> Về trang chủ
                </a>
                <a href="products.php" class="btn btn-outline-primary me-2">
                    <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                </a>
                <a href="orders.php" class="btn btn-outline-success">
                    <i class="fas fa-list-alt"></i> Xem đơn hàng của tôi
                </a>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
