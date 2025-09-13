<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['user_id']) || !isset($_GET['id'])) {
    echo '<div class="alert alert-danger">Không có quyền truy cập</div>';
    exit();
}

$order_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Get order details
    $stmt = $pdo->prepare("
        SELECT o.*, u.full_name, u.email, u.phone 
        FROM orders o 
        JOIN users u ON o.customer_id = u.id 
        WHERE o.id = ? AND o.customer_id = ?
    ");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo '<div class="alert alert-danger">Đơn hàng không tồn tại</div>';
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
    
    // Status mapping
    $status_map = [
        'pending' => ['class' => 'warning', 'text' => 'Chờ xử lý'],
        'processing' => ['class' => 'info', 'text' => 'Đang xử lý'],
        'shipped' => ['class' => 'primary', 'text' => 'Đã giao vận'],
        'delivered' => ['class' => 'success', 'text' => 'Đã giao hàng'],
        'cancelled' => ['class' => 'danger', 'text' => 'Đã hủy']
    ];
    
    $status = $status_map[$order['status']] ?? ['class' => 'secondary', 'text' => 'Không xác định'];
    
} catch (PDOException $e) {
    echo '<div class="alert alert-danger">Lỗi khi tải thông tin đơn hàng</div>';
    exit();
}
?>

<div class="row">
    <div class="col-md-6">
        <h6>Thông tin đơn hàng:</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Mã đơn hàng:</strong></td>
                <td>#<?php echo $order['id']; ?></td>
            </tr>
            <tr>
                <td><strong>Ngày đặt:</strong></td>
                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
            </tr>
            <tr>
                <td><strong>Trạng thái:</strong></td>
                <td><span class="badge bg-<?php echo $status['class']; ?>"><?php echo $status['text']; ?></span></td>
            </tr>
            <tr>
                <td><strong>Tổng tiền:</strong></td>
                <td><span class="text-success fw-bold"><?php echo number_format($order['total_amount']); ?> VNĐ</span></td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <h6>Thông tin khách hàng:</h6>
        <table class="table table-sm">
            <tr>
                <td><strong>Họ tên:</strong></td>
                <td><?php echo htmlspecialchars($order['full_name']); ?></td>
            </tr>
            <tr>
                <td><strong>Email:</strong></td>
                <td><?php echo htmlspecialchars($order['email']); ?></td>
            </tr>
            <?php if ($order['phone']): ?>
            <tr>
                <td><strong>Điện thoại:</strong></td>
                <td><?php echo htmlspecialchars($order['phone']); ?></td>
            </tr>
            <?php endif; ?>
        </table>
    </div>
</div>

<?php if ($order['notes']): ?>
<div class="mb-3">
    <h6>Ghi chú:</h6>
    <div class="alert alert-light">
        <?php echo nl2br(htmlspecialchars($order['notes'])); ?>
    </div>
</div>
<?php endif; ?>

<h6>Chi tiết sản phẩm:</h6>
<div class="table-responsive">
    <table class="table table-sm">
        <thead class="table-light">
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
                                 class="me-2 rounded" style="width: 40px; height: 40px; object-fit: cover;" 
                                 alt="<?php echo htmlspecialchars($item['name']); ?>">
                            <span><?php echo htmlspecialchars($item['name']); ?></span>
                        </div>
                    </td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price']); ?> VNĐ</td>
                    <td><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</td>
                </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot class="table-success">
            <tr>
                <th colspan="3">Tổng cộng:</th>
                <th><?php echo number_format($order['total_amount']); ?> VNĐ</th>
            </tr>
        </tfoot>
    </table>
</div>
