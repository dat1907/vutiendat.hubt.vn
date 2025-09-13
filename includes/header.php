<?php
// Configure session settings before starting session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    session_start();
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title : 'Đạt Apple'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-apple-alt"></i> Đạt Apple
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Trang Chủ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="products.php">Sản Phẩm</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="contact.php">Liên Hệ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">Giới Thiệu</a>
                    </li>
                </ul>
                
                <!-- Search Form -->
                <form class="d-flex me-3" method="GET" action="products.php">
                    <div class="input-group">
                        <input class="form-control" type="search" name="search" placeholder="Tìm kiếm sản phẩm..." 
                               value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>" 
                               style="min-width: 250px;">
                        <button class="btn btn-outline-light" type="submit">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>
                </form>
                
                <ul class="navbar-nav">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="cart.php">
                                <i class="fas fa-shopping-cart"></i> Giỏ hàng
                                <span class="badge bg-danger" id="cart-count">0</span>
                            </a>
                        </li>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown">
                                <?php
                                // Get user avatar for header
                                if (isset($_SESSION['user_id'])) {
                                    try {
                                        $stmt = $pdo->prepare("SELECT avatar FROM users WHERE id = ?");
                                        $stmt->execute([$_SESSION['user_id']]);
                                        $user_avatar = $stmt->fetchColumn();
                                    } catch (PDOException $e) {
                                        $user_avatar = null;
                                    }
                                }
                                ?>
                                <?php if (!empty($user_avatar) && file_exists($user_avatar)): ?>
                                    <img src="<?php echo htmlspecialchars($user_avatar); ?>" 
                                         alt="Avatar" class="rounded-circle me-2" 
                                         style="width: 32px; height: 32px; object-fit: cover;">
                                <?php else: ?>
                                    <i class="fas fa-user me-2"></i>
                                <?php endif; ?>
                                <?php echo htmlspecialchars($_SESSION['full_name']); ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-edit"></i> Thông tin cá nhân</a></li>
                                <li><a class="dropdown-item" href="orders.php"><i class="fas fa-list-alt"></i> Đơn hàng của tôi</a></li>
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item" href="admin/index.php"><i class="fas fa-user-shield"></i> Quản trị</a></li>
                                <?php endif; ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="auth/logout.php"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a></li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/login.php">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="auth/register.php">
                                <i class="fas fa-user-plus"></i> Đăng ký
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>
