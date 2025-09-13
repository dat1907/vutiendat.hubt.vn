<?php
session_start();
$page_title = 'Sản Phẩm - Website Bán Hàng';
require_once 'config/database.php';
require_once 'includes/header.php';

// Lấy danh sách sản phẩm
$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';

$sql = "SELECT p.*, c.name as category_name FROM products p 
        LEFT JOIN categories c ON p.category_id = c.id 
        WHERE p.status = 'active'";

if ($search) {
    $sql .= " AND (p.name LIKE :search OR p.description LIKE :search)";
}

if ($category) {
    $sql .= " AND p.category_id = :category";
}

$sql .= " ORDER BY p.created_at DESC";

$stmt = $pdo->prepare($sql);

if ($search) {
    $stmt->bindValue(':search', '%' . $search . '%');
}
if ($category) {
    $stmt->bindValue(':category', $category);
}

$stmt->execute();
$products = $stmt->fetchAll();

// Lấy danh sách danh mục
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h1 class="mb-4">Sản Phẩm</h1>
        </div>
    </div>

    <!-- Bộ lọc và tìm kiếm -->
    <div class="row mb-4">
        <div class="col-md-8">
            <form method="GET" class="d-flex">
                <input type="text" name="search" class="form-control me-2" placeholder="Tìm kiếm sản phẩm..." value="<?php echo htmlspecialchars($search); ?>" id="searchInput">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>
        <div class="col-md-4">
            <form method="GET">
                <?php if ($search): ?>
                    <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                <?php endif; ?>
                <select name="category" class="form-select" onchange="this.form.submit()">
                    <option value="">Tất cả danh mục</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </form>
        </div>
    </div>

    <!-- Danh sách sản phẩm -->
    <div class="row">
        <?php if ($products): ?>
            <?php foreach ($products as $product): ?>
                <div class="col-md-4 mb-4 product-card">
                    <div class="card h-100">
                        <?php if ($product['image_url'] && file_exists($product['image_url'])): ?>
                            <img src="<?php echo htmlspecialchars($product['image_url']); ?>" 
                                 class="card-img-top product-image" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <?php else: ?>
                            <img src="https://via.placeholder.com/300x200?text=No+Image" 
                                 class="card-img-top product-image" alt="No Image">
                        <?php endif; ?>
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo htmlspecialchars(substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="mt-auto">
                                <p class="price mb-2"><?php echo number_format($product['price']); ?> VNĐ</p>
                                <p class="text-muted mb-2">
                                    <small>Danh mục: <?php echo htmlspecialchars($product['category_name'] ?: 'Chưa phân loại'); ?></small>
                                </p>
                                <p class="text-muted mb-3">
                                    <small>Còn lại: <?php echo $product['quantity']; ?> sản phẩm</small>
                                </p>
                                <div class="d-grid gap-2">
                                    <a href="product_detail.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">
                                        <i class="fas fa-eye"></i> Xem Chi Tiết
                                    </a>
                                    <?php if ($product['quantity'] > 0): ?>
                                        <button class="btn btn-success" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            <i class="fas fa-shopping-cart"></i> Thêm Vào Giỏ
                                        </button>
                                    <?php else: ?>
                                        <button class="btn btn-secondary" disabled>
                                            <i class="fas fa-times"></i> Hết Hàng
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12 text-center">
                <div class="alert alert-info">
                    <h4>Không tìm thấy sản phẩm nào</h4>
                    <p>Vui lòng thử lại với từ khóa khác hoặc xem tất cả sản phẩm.</p>
                    <a href="products.php" class="btn btn-primary">Xem Tất Cả Sản Phẩm</a>
                </div>
            </div>
        <?php endif; ?>
    </div>
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
