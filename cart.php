<?php
session_start();
require_once 'config/database.php';

// Redirect if not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: auth/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$message = '';

// Handle cart actions
if ($_POST) {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update':
                $cart_id = (int)$_POST['cart_id'];
                $quantity = (int)$_POST['quantity'];
                
                if ($quantity > 0) {
                    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
                    $stmt->execute([$quantity, $cart_id, $user_id]);
                    $message = 'Cập nhật giỏ hàng thành công!';
                } else {
                    $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                    $stmt->execute([$cart_id, $user_id]);
                    $message = 'Đã xóa sản phẩm khỏi giỏ hàng!';
                }
                break;
                
            case 'remove':
                $cart_id = (int)$_POST['cart_id'];
                $stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
                $stmt->execute([$cart_id, $user_id]);
                $message = 'Đã xóa sản phẩm khỏi giỏ hàng!';
                break;
                
            case 'clear':
                $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
                $stmt->execute([$user_id]);
                $message = 'Đã xóa toàn bộ giỏ hàng!';
                break;
        }
    }
}

// Get cart items
try {
    $stmt = $pdo->prepare("
        SELECT c.*, p.name, p.price, p.image_url, p.quantity as stock_quantity
        FROM cart c 
        JOIN products p ON c.product_id = p.id 
        WHERE c.user_id = ? 
        ORDER BY c.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $cart_items = $stmt->fetchAll();
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $cart_items = [];
    $total = 0;
}

$page_title = 'Giỏ hàng';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-shopping-cart"></i> Giỏ hàng của bạn</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-success alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (empty($cart_items)): ?>
                <div class="text-center py-5">
                    <i class="fas fa-shopping-cart fa-5x text-muted mb-3"></i>
                    <h4>Giỏ hàng trống</h4>
                    <p class="text-muted">Bạn chưa thêm sản phẩm nào vào giỏ hàng</p>
                    <a href="products.php" class="btn btn-primary">
                        <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-body">
                                <?php foreach ($cart_items as $item): ?>
                                    <div class="row align-items-center border-bottom py-3">
                                        <div class="col-md-2">
                                            <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                                 class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                        </div>
                                        <div class="col-md-4">
                                            <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                            <p class="text-muted mb-0">Giá: <?php echo number_format($item['price']); ?> VNĐ</p>
                                            <small class="text-muted">Còn lại: <?php echo $item['stock_quantity']; ?> sản phẩm</small>
                                        </div>
                                        <div class="col-md-3">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="update">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                <div class="input-group">
                                                    <input type="number" name="quantity" class="form-control" 
                                                           value="<?php echo $item['quantity']; ?>" 
                                                           min="0" max="<?php echo $item['stock_quantity']; ?>">
                                                    <button type="submit" class="btn btn-outline-primary btn-sm">
                                                        <i class="fas fa-sync"></i>
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                        <div class="col-md-2">
                                            <strong><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</strong>
                                        </div>
                                        <div class="col-md-1">
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="action" value="remove">
                                                <input type="hidden" name="cart_id" value="<?php echo $item['id']; ?>">
                                                <button type="submit" class="btn btn-outline-danger btn-sm" 
                                                        onclick="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <form method="POST" class="d-inline">
                                <input type="hidden" name="action" value="clear">
                                <button type="submit" class="btn btn-outline-danger" 
                                        onclick="return confirm('Bạn có chắc muốn xóa toàn bộ giỏ hàng?')">
                                    <i class="fas fa-trash-alt"></i> Xóa toàn bộ giỏ hàng
                                </button>
                            </form>
                            <a href="products.php" class="btn btn-outline-primary">
                                <i class="fas fa-shopping-bag"></i> Tiếp tục mua sắm
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5 class="mb-0">Tổng đơn hàng</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Tạm tính:</span>
                                    <span><?php echo number_format($total); ?> VNĐ</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Phí vận chuyển:</span>
                                    <span class="text-success">Miễn phí</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between mb-3">
                                    <strong>Tổng cộng:</strong>
                                    <strong class="text-primary"><?php echo number_format($total); ?> VNĐ</strong>
                                </div>
                                
                                <div class="d-grid">
                                    <a href="checkout.php" class="btn btn-primary btn-lg">
                                        <i class="fas fa-credit-card"></i> Thanh toán
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
