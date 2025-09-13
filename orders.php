<?php
session_start();
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's orders
try {
    $stmt = $pdo->prepare("
        SELECT o.*, COUNT(oi.id) as item_count
        FROM orders o 
        LEFT JOIN order_items oi ON o.id = oi.order_id 
        WHERE o.customer_id = ? 
        GROUP BY o.id 
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $orders = $stmt->fetchAll();
} catch (PDOException $e) {
    $orders = [];
}

$page_title = 'Đơn hàng của tôi';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-list-alt"></i> Đơn hàng của tôi</h2>
            
            <?php if (empty($orders)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-clipboard-list fa-5x text-muted mb-3"></i>
                    <h4>Chưa có đơn hàng nào</h4>
                    <p class="text-muted">Bạn chưa đặt đơn hàng nào</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Bắt đầu mua sắm
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($orders as $order): ?>
                        <div class="col-lg-6 mb-4">
                            <div class="card">
                                <div class="card-header d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0">Đơn hàng #<?php echo $order['id']; ?></h6>
                                    <?php
                                    $status_class = '';
                                    $status_text = '';
                                    switch ($order['status']) {
                                        case 'pending':
                                            $status_class = 'bg-warning';
                                            $status_text = 'Chờ xử lý';
                                            break;
                                        case 'processing':
                                            $status_class = 'bg-info';
                                            $status_text = 'Đang xử lý';
                                            break;
                                        case 'shipped':
                                            $status_class = 'bg-primary';
                                            $status_text = 'Đã giao vận';
                                            break;
                                        case 'delivered':
                                            $status_class = 'bg-success';
                                            $status_text = 'Đã giao hàng';
                                            break;
                                        case 'cancelled':
                                            $status_class = 'bg-danger';
                                            $status_text = 'Đã hủy';
                                            break;
                                    }
                                    ?>
                                    <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                </div>
                                <div class="card-body">
                                    <div class="row mb-2">
                                        <div class="col-sm-6">
                                            <small class="text-muted">Ngày đặt:</small><br>
                                            <strong><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></strong>
                                        </div>
                                        <div class="col-sm-6">
                                            <small class="text-muted">Tổng tiền:</small><br>
                                            <strong class="text-success"><?php echo number_format($order['total_amount']); ?> VNĐ</strong>
                                        </div>
                                    </div>
                                    
                                    <div class="mb-2">
                                        <small class="text-muted">Số sản phẩm:</small>
                                        <span class="badge bg-secondary"><?php echo $order['item_count']; ?> sản phẩm</span>
                                    </div>
                                    
                                    <?php if ($order['notes']): ?>
                                        <div class="mb-2">
                                            <small class="text-muted">Ghi chú:</small><br>
                                            <small><?php echo htmlspecialchars(substr($order['notes'], 0, 100)); ?><?php echo strlen($order['notes']) > 100 ? '...' : ''; ?></small>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        <button class="btn btn-outline-primary btn-sm" onclick="viewOrderDetails(<?php echo $order['id']; ?>)">
                                            <i class="fas fa-eye"></i> Chi tiết
                                        </button>
                                        
                                        <?php if ($order['status'] === 'pending'): ?>
                                            <button class="btn btn-outline-danger btn-sm" onclick="cancelOrder(<?php echo $order['id']; ?>)">
                                                <i class="fas fa-times"></i> Hủy đơn
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Chi tiết đơn hàng</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailsContent">
                <div class="text-center">
                    <i class="fas fa-spinner fa-spin"></i> Đang tải...
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
    modal.show();
    
    fetch('get_order_details.php?id=' + orderId)
        .then(response => response.text())
        .then(data => {
            document.getElementById('orderDetailsContent').innerHTML = data;
        })
        .catch(error => {
            document.getElementById('orderDetailsContent').innerHTML = 
                '<div class="alert alert-danger">Lỗi khi tải chi tiết đơn hàng</div>';
        });
}

function cancelOrder(orderId) {
    if (confirm('Bạn có chắc muốn hủy đơn hàng này?')) {
        fetch('cancel_order.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'order_id=' + orderId
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert(data.message || 'Có lỗi xảy ra');
            }
        })
        .catch(error => {
            alert('Có lỗi xảy ra, vui lòng thử lại');
        });
    }
}
</script>

<?php require_once 'includes/footer.php'; ?>
