<?php
session_start();
require_once 'config/database.php';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <!-- Phone Banner -->
    <div class="phone-banner bg-gradient-primary text-white p-4 rounded mb-4" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="display-5 mb-3"><i class="fas fa-mobile-alt"></i> Điện Thoại Thông Minh</h2>
                <p class="lead mb-3">Khám phá bộ sưu tập điện thoại mới nhất với công nghệ tiên tiến và giá cả hợp lý</p>
                <div class="row">
                    <div class="col-sm-4 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-check-circle me-2"></i>
                            <small>Chính hãng 100%</small>
                        </div>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shipping-fast me-2"></i>
                            <small>Giao hàng miễn phí</small>
                        </div>
                    </div>
                    <div class="col-sm-4 mb-2">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-shield-alt me-2"></i>
                            <small>Bảo hành 12 tháng</small>
                        </div>
                    </div>
                </div>
                <a href="products.php?category=1" class="btn btn-light btn-lg mt-2">
                    <i class="fas fa-mobile-alt"></i> Xem Điện Thoại
                </a>
            </div>
            <div class="col-md-4 text-center">
                <div class="phone-showcase">
                    <i class="fas fa-mobile-alt fa-8x opacity-75"></i>
                    <div class="mt-3">
                        <span class="badge bg-warning text-dark fs-6">Giảm giá đến 30%</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="hero-section bg-primary text-white p-5 rounded mb-4">
        <h1 class="display-4">Chào mừng đến với cửa hàng của Đạt</h1>
        <p class="lead">Khám phá các sản phẩm chất lượng cao với giá cả hợp lý</p>
        <a href="products.php" class="btn btn-light btn-lg">Xem Sản Phẩm</a>
    </div>


    <div class="row mt-5">
        <div class="col-12">
            <h2 class="text-center mb-4">Sản Phẩm Nổi Bật</h2>
        </div>
        <?php
        try {
            $stmt = $pdo->query("SELECT * FROM products ORDER BY created_at DESC LIMIT 3");
            $featured_products = $stmt->fetchAll();
            
            if ($featured_products) {
                foreach ($featured_products as $product) {
                    echo '<div class="col-md-4 mb-4">';
                    echo '<div class="card">';
                    if ($product['image_url'] && file_exists($product['image_url'])) {
                        echo '<img src="' . htmlspecialchars($product['image_url']) . '" class="card-img-top" style="height: 200px; object-fit: cover;" alt="' . htmlspecialchars($product['name']) . '">';
                    } else {
                        echo '<img src="https://via.placeholder.com/300x200?text=No+Image" class="card-img-top" style="height: 200px; object-fit: cover;" alt="No Image">';
                    }
                    echo '<div class="card-body">';
                    echo '<h5 class="card-title">' . htmlspecialchars($product['name']) . '</h5>';
                    echo '<p class="card-text">' . htmlspecialchars(substr($product['description'], 0, 100)) . '...</p>';
                    echo '<p class="text-primary font-weight-bold">' . number_format($product['price']) . ' VNĐ</p>';
                    echo '<a href="product_detail.php?id=' . $product['id'] . '" class="btn btn-primary">Xem Chi Tiết</a>';
                    echo '</div>';
                    echo '</div>';
                    echo '</div>';
                }
            } else {
                echo '<div class="col-12 text-center">';
                echo '<p>Chưa có sản phẩm nào. <a href="admin/add_product.php">Thêm sản phẩm đầu tiên</a></p>';
                echo '</div>';
            }
        } catch (PDOException $e) {
            echo '<div class="col-12 text-center">';
            echo '<p class="text-muted">Đang tải sản phẩm...</p>';
            echo '</div>';
        }
        ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
        