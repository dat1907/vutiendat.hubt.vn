<?php
$page_title = 'Trang Quản Trị - WebCBan';
require_once '../config/database.php';
require_once 'includes/admin_header.php';

// Thống kê tổng quan
$stats = [];

// Tổng số sản phẩm
$stmt = $pdo->query("SELECT COUNT(*) as total FROM products WHERE status = 'active'");
$stats['products'] = $stmt->fetch()['total'];

// Tổng số khách hàng
$stmt = $pdo->query("SELECT COUNT(*) as total FROM customers");
$stats['customers'] = $stmt->fetch()['total'];

// Tổng số đơn hàng
$stmt = $pdo->query("SELECT COUNT(*) as total FROM orders");
$stats['orders'] = $stmt->fetch()['total'];

// Tổng doanh thu
$stmt = $pdo->query("SELECT SUM(total_amount) as total FROM orders WHERE status IN ('delivered', 'processing')");
$stats['revenue'] = $stmt->fetch()['total'] ?: 0;

// Đơn hàng gần đây
$stmt = $pdo->query("SELECT o.*, u.full_name as customer_name FROM orders o 
                     LEFT JOIN users u ON o.customer_id = u.id 
                     ORDER BY o.created_at DESC LIMIT 5");
$recent_orders = $stmt->fetchAll();

// Sản phẩm sắp hết hàng
$stmt = $pdo->query("SELECT * FROM products WHERE quantity <= 5 AND status = 'active' ORDER BY quantity ASC LIMIT 5");
$low_stock = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-2 p-0">
            <?php include 'includes/sidebar.php'; ?>
        </div>
        <div class="col-md-10">
            <div class="p-4">
                <h1 class="mb-4">Dashboard</h1>
                
                <!-- Thống kê tổng quan -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-box fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['products']); ?></h3>
                                <p>Sản Phẩm</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-users fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['customers']); ?></h3>
                                <p>Khách Hàng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-shopping-cart fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['orders']); ?></h3>
                                <p>Đơn Hàng</p>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card stats-card">
                            <div class="card-body text-center">
                                <i class="fas fa-money-bill-wave fa-2x mb-2"></i>
                                <h3><?php echo number_format($stats['revenue']); ?> VNĐ</h3>
                                <p>Doanh Thu</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Đơn hàng gần đây -->
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h5>Đơn Hàng Gần Đây</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($recent_orders): ?>
                                    <div class="table-responsive">
                                        <table class="table table-striped">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Khách Hàng</th>
                                                    <th>Tổng Tiền</th>
                                                    <th>Trạng Thái</th>
                                                    <th>Ngày Đặt</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($recent_orders as $order): ?>
                                                    <tr>
                                                        <td>#<?php echo $order['id']; ?></td>
                                                        <td><?php echo htmlspecialchars($order['customer_name'] ?: 'N/A'); ?></td>
                                                        <td><?php echo number_format($order['total_amount']); ?> VNĐ</td>
                                                        <td>
                                                            <span class="badge bg-<?php 
                                                                echo $order['status'] == 'delivered' ? 'success' : 
                                                                    ($order['status'] == 'processing' ? 'warning' : 'secondary'); 
                                                            ?>">
                                                                <?php echo ucfirst($order['status']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('d/m/Y', strtotime($order['created_at'])); ?></td>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                    <a href="orders.php" class="btn btn-primary">Xem Tất Cả Đơn Hàng</a>
                                <?php else: ?>
                                    <p class="text-muted">Chưa có đơn hàng nào.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Sản phẩm sắp hết hàng -->
                    <div class="col-md-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>Sản Phẩm Sắp Hết Hàng</h5>
                            </div>
                            <div class="card-body">
                                <?php if ($low_stock): ?>
                                    <?php foreach ($low_stock as $product): ?>
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <div>
                                                <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                                <small class="text-muted">Còn: <?php echo $product['quantity']; ?> sản phẩm</small>
                                            </div>
                                            <span class="badge bg-warning"><?php echo $product['quantity']; ?></span>
                                        </div>
                                        <hr>
                                    <?php endforeach; ?>
                                    <a href="products.php" class="btn btn-warning btn-sm">Quản Lý Kho</a>
                                <?php else: ?>
                                    <p class="text-muted">Tất cả sản phẩm đều còn đủ hàng.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin_footer.php'; ?>
