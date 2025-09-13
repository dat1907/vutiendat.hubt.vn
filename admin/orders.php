<?php
$page_title = 'Quản Lý Đơn Hàng - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

// Xử lý cập nhật trạng thái đơn hàng
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
        $stmt->execute([$status, $order_id]);
        $message = 'Cập nhật trạng thái đơn hàng thành công!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $message = 'Lỗi khi cập nhật trạng thái!';
        $message_type = 'danger';
    }
}

// Xử lý xóa đơn hàng
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        $pdo->beginTransaction();
        
        // Xóa chi tiết đơn hàng trước
        $stmt = $pdo->prepare("DELETE FROM order_items WHERE order_id = ?");
        $stmt->execute([$id]);
        
        // Xóa đơn hàng
        $stmt = $pdo->prepare("DELETE FROM orders WHERE id = ?");
        $stmt->execute([$id]);
        
        $pdo->commit();
        $message = 'Xóa đơn hàng thành công!';
        $message_type = 'success';
    } catch (PDOException $e) {
        $pdo->rollBack();
        $message = 'Lỗi khi xóa đơn hàng!';
        $message_type = 'danger';
    }
}

// Lấy danh sách đơn hàng
$search = isset($_GET['search']) ? $_GET['search'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

$sql = "SELECT o.*, u.full_name as customer_name, u.email as customer_email 
        FROM orders o 
        LEFT JOIN users u ON o.customer_id = u.id 
        WHERE 1=1";

if ($search) {
    $sql .= " AND (u.full_name LIKE :search OR u.email LIKE :search OR o.id LIKE :search OR o.customer_name LIKE :search)";
}

if ($status_filter) {
    $sql .= " AND o.status = :status";
}

$sql .= " ORDER BY o.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if ($status_filter) {
    $stmt->bindValue(':status', $status_filter);
}

$stmt->execute();
$orders = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Quản Lý Đơn Hàng</h1>
                    <a href="add_order.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Tạo Đơn Hàng
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Bộ lọc -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-6">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo ID đơn hàng, tên khách hàng hoặc email..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <select name="status" class="form-select">
                                    <option value="">Tất cả trạng thái</option>
                                    <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                    <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                    <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Đã gửi hàng</option>
                                    <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                    <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách đơn hàng -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($orders): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID Đơn Hàng</th>
                                            <th>Khách Hàng</th>
                                            <th>Tổng Tiền</th>
                                            <th>Trạng Thái</th>
                                            <th>Ngày Đặt</th>
                                            <th>Ghi Chú</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($orders as $order): ?>
                                            <tr>
                                                <td><strong>#<?php echo $order['id']; ?></strong></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($order['customer_name'] ?: 'N/A'); ?></strong><br>
                                                    <small class="text-muted"><?php echo htmlspecialchars($order['customer_email'] ?: ''); ?></small>
                                                </td>
                                                <td>
                                                    <span class="text-success font-weight-bold"><?php echo number_format($order['total_amount']); ?> VNĐ</span>
                                                </td>
                                                <td>
                                                    <form method="POST" class="d-inline">
                                                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                                            <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Chờ xử lý</option>
                                                            <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Đang xử lý</option>
                                                            <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Đã gửi hàng</option>
                                                            <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Đã giao hàng</option>
                                                            <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Đã hủy</option>
                                                        </select>
                                                        <input type="hidden" name="update_status" value="1">
                                                    </form>
                                                </td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($order['created_at'])); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($order['notes'], 0, 30)) . (strlen($order['notes']) > 30 ? '...' : ''); ?></small>
                                                </td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="edit_order.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="?delete=<?php echo $order['id']; ?>" class="btn btn-sm btn-danger" 
                                                           onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này? Tất cả chi tiết đơn hàng cũng sẽ bị xóa.')">
                                                            <i class="fas fa-trash"></i>
                                                        </a>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                <h5>Không có đơn hàng nào</h5>
                                <p class="text-muted">Chưa có đơn hàng nào hoặc không tìm thấy kết quả phù hợp.</p>
                                <a href="add_order.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Tạo Đơn Hàng
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
