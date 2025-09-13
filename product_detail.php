<?php
session_start();
$page_title = 'Chi Tiết Sản Phẩm - Website Bán Hàng';
require_once 'config/database.php';
require_once 'includes/header.php';

$product_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$product_id) {
    header('Location: products.php');
    exit;
}

// Lấy thông tin sản phẩm
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ? AND p.status = 'active'");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if (!$product) {
    header('Location: products.php');
    exit;
}

// Lấy sản phẩm liên quan
$stmt = $pdo->prepare("SELECT * FROM products WHERE category_id = ? AND id != ? AND status = 'active' LIMIT 3");
$stmt->execute([$product['category_id'], $product_id]);
$related_products = $stmt->fetchAll();
?>

<main class="container mt-4">
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
            <li class="breadcrumb-item"><a href="products.php">Sản Phẩm</a></li>
            <li class="breadcrumb-item active"><?php echo htmlspecialchars($product['name']); ?></li>
        </ol>
    </nav>

    <div class="row">
        <div class="col-md-6">
            <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                     class="img-fluid rounded" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
                <img src="https://via.placeholder.com/500x400?text=No+Image" 
                     class="img-fluid rounded" alt="No Image">
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <h1><?php echo htmlspecialchars($product['name']); ?></h1>
            <p class="text-muted">Danh mục: <?php echo htmlspecialchars($product['category_name'] ?: 'Chưa phân loại'); ?></p>
            
            <div class="price mb-3">
                <h3 class="text-success"><?php echo number_format($product['price']); ?> VNĐ</h3>
            </div>

            <div class="mb-3">
                <strong>Mô tả sản phẩm:</strong>
                <p><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
            </div>

            <div class="mb-3">
                <strong>Tình trạng:</strong>
                <?php if ($product['quantity'] > 0): ?>
                    <span class="badge bg-success">Còn hàng (<?php echo $product['quantity']; ?> sản phẩm)</span>
                <?php else: ?>
                    <span class="badge bg-danger">Hết hàng</span>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <strong>Ngày thêm:</strong>
                <span><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></span>
            </div>

            <div class="d-grid gap-2 d-md-flex">
                <?php if ($product['quantity'] > 0): ?>
                    <button class="btn btn-success btn-lg me-md-2" onclick="addToCart(<?php echo $product['id']; ?>)">
                        <i class="fas fa-shopping-cart"></i> Thêm Vào Giỏ Hàng
                    </button>
                    <button class="btn btn-primary btn-lg" onclick="buyNow(<?php echo $product['id']; ?>)">
                        <i class="fas fa-bolt"></i> Mua Ngay
                    </button>
                <?php else: ?>
                    <button class="btn btn-secondary btn-lg" disabled>
                        <i class="fas fa-times"></i> Hết Hàng
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($related_products): ?>
    <div class="row mt-5">
        <div class="col-12">
            <h3>Sản Phẩm Liên Quan</h3>
        </div>
        <?php foreach ($related_products as $related): ?>
            <div class="col-md-4 mb-4">
                <div class="card">
                    <img src="<?php echo $related['image_url'] ?: 'https://via.placeholder.com/300x200?text=No+Image'; ?>" 
                         class="card-img-top product-image" alt="<?php echo htmlspecialchars($related['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($related['name']); ?></h5>
                        <p class="card-text"><?php echo htmlspecialchars(substr($related['description'], 0, 80)) . '...'; ?></p>
                        <p class="price"><?php echo number_format($related['price']); ?> VNĐ</p>
                        <a href="product_detail.php?id=<?php echo $related['id']; ?>" class="btn btn-primary">Xem Chi Tiết</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</main>

<script>
function addToCart(productId) {
    <?php if (isset($_SESSION['user_id'])): ?>
        fetch('add_to_cart.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'product_id=' + productId + '&quantity=1'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update cart count
                document.getElementById('cart-count').textContent = data.cart_count;
                
                // Show success message
                showAlert('success', data.message);
            } else {
                showAlert('danger', data.message);
            }
        })
        .catch(error => {
            showAlert('danger', 'Có lỗi xảy ra, vui lòng thử lại');
        });
    <?php else: ?>
        showAlert('warning', 'Vui lòng <a href="auth/login.php">đăng nhập</a> để thêm sản phẩm vào giỏ hàng');
    <?php endif; ?>
}

function buyNow(productId) {
    <?php if (isset($_SESSION['user_id'])): ?>
        addToCart(productId);
        setTimeout(() => {
            window.location.href = 'cart.php';
        }, 1000);
    <?php else: ?>
        window.location.href = 'auth/login.php';
    <?php endif; ?>
}

function showAlert(type, message) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; max-width: 400px;';
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 5000);
}
</script>

<?php require_once 'includes/footer.php'; ?>
