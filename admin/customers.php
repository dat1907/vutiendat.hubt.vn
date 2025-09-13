<?php
$page_title = 'Quản Lý Khách Hàng - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

$message = '';
$message_type = '';

// Xử lý xóa khách hàng
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    try {
        // Kiểm tra xem khách hàng có đơn hàng không
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM orders WHERE customer_id = ?");
        $stmt->execute([$id]);
        $count = $stmt->fetch()['count'];

        if ($count > 0) {
            $message = "Không thể xóa khách hàng này vì có {$count} đơn hàng liên quan!";
            $message_type = 'warning';
        } else {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            $message = 'Xóa khách hàng thành công!';
            $message_type = 'success';
        }
    } catch (PDOException $e) {
        $message = 'Lỗi khi xóa khách hàng!';
        $message_type = 'danger';
    }
}

// Lấy danh sách khách hàng
$search = isset($_GET['search']) ? $_GET['search'] : '';

$sql = "SELECT c.*, COUNT(o.id) as order_count, COALESCE(SUM(o.total_amount), 0) as total_spent 
        FROM customers c 
        LEFT JOIN orders o ON c.id = o.customer_id 
        WHERE 1=1";

if ($search) {
    $sql .= " AND (c.name LIKE :search OR c.email LIKE :search OR c.phone LIKE :search)";
}

$sql .= " GROUP BY c.id ORDER BY c.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}

$stmt->execute();
$customers = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h1>Quản Lý Khách Hàng</h1>
                    <a href="add_customer.php" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm Khách Hàng
                    </a>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                        <?php echo $message; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Tìm kiếm -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-10">
                                <input type="text" name="search" class="form-control" placeholder="Tìm kiếm theo tên, email hoặc số điện thoại..." value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Tìm
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Danh sách khách hàng -->
                <div class="card">
                    <div class="card-body">
                        <?php if ($customers): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên Khách Hàng</th>
                                            <th>Email</th>
                                            <th>Số Điện Thoại</th>
                                            <th>Địa Chỉ</th>
                                            <th>Số Đơn Hàng</th>
                                            <th>Tổng Chi Tiêu</th>
                                            <th>Ngày Đăng Ký</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($customers as $customer): ?>
                                            <tr>
                                                <td><?php echo $customer['id']; ?></td>
                                                <td>
                                                    <strong><?php echo htmlspecialchars($customer['name']); ?></strong>
                                                </td>
                                                <td><?php echo htmlspecialchars($customer['email']); ?></td>
                                                <td><?php echo htmlspecialchars($customer['phone']); ?></td>
                                                <td>
                                                    <small><?php echo htmlspecialchars(substr($customer['address'], 0, 30)) . (strlen($customer['address']) > 30 ? '...' : ''); ?></small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $customer['order_count']; ?></span>
                                                </td>
                                                <td>
                                                    <span class="text-success font-weight-bold"><?php echo number_format($customer['total_spent']); ?> VNĐ</span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($customer['created_at'])); ?></td>
                                                <td>
                                                    <div class="btn-group" role="group">
                                                        <a href="edit_customer.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-warning">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                        <a href="customer_orders.php?id=<?php echo $customer['id']; ?>" class="btn btn-sm btn-info">
                                                            <i class="fas fa-shopping-cart"></i>
                                                        </a>
                                                        <?php if ($customer['order_count'] == 0): ?>
                                                            <a href="?delete=<?php echo $customer['id']; ?>" class="btn btn-sm btn-danger" 
                                                               onclick="return confirm('Bạn có chắc chắn muốn xóa khách hàng này?')">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php else: ?>
                                                            <button type="button" class="btn btn-sm btn-secondary" disabled 
                                                                    title="Không thể xóa vì có đơn hàng liên quan">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-4">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>Không có khách hàng nào</h5>
                                <p class="text-muted">Chưa có khách hàng nào đăng ký hoặc không tìm thấy kết quả phù hợp.</p>
                                <a href="add_customer.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Thêm Khách Hàng
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
