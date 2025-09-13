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
$message_type = '';

// Get user information
try {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
    
    if (!$user) {
        header('Location: auth/logout.php');
        exit();
    }
} catch (PDOException $e) {
    $message = 'Lỗi khi tải thông tin người dùng';
    $message_type = 'danger';
}

// Handle avatar upload
if ($_POST && isset($_POST['upload_avatar'])) {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $max_size = 5 * 1024 * 1024; // 5MB
        
        $file_type = $_FILES['avatar']['type'];
        $file_size = $_FILES['avatar']['size'];
        
        if (!in_array($file_type, $allowed_types)) {
            $message = 'Chỉ chấp nhận file ảnh (JPG, PNG, GIF, WebP)!';
            $message_type = 'danger';
        } else if ($file_size > $max_size) {
            $message = 'Kích thước file không được vượt quá 5MB!';
            $message_type = 'danger';
        } else {
            // Create uploads/avatars directory if it doesn't exist
            $upload_dir = 'uploads/avatars/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
            $new_filename = 'avatar_' . $user_id . '_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                try {
                    // Delete old avatar if exists
                    if (isset($user['avatar']) && $user['avatar'] && file_exists($user['avatar'])) {
                        unlink($user['avatar']);
                    }
                    
                    // Update database
                    $stmt = $pdo->prepare("UPDATE users SET avatar = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                    $stmt->execute([$upload_path, $user_id]);
                    
                    $message = 'Cập nhật ảnh đại diện thành công!';
                    $message_type = 'success';
                    
                    // Refresh user data
                    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $user = $stmt->fetch();
                } catch (PDOException $e) {
                    $message = 'Lỗi khi cập nhật ảnh đại diện: ' . $e->getMessage();
                    $message_type = 'danger';
                }
            } else {
                $message = 'Lỗi khi tải lên file ảnh!';
                $message_type = 'danger';
            }
        }
    } else {
        $message = 'Vui lòng chọn file ảnh!';
        $message_type = 'warning';
    }
}

// Handle remove avatar
if ($_POST && isset($_POST['remove_avatar'])) {
    try {
        // Delete avatar file if exists
        if (isset($user['avatar']) && $user['avatar'] && file_exists($user['avatar'])) {
            unlink($user['avatar']);
        }
        
        // Update database
        $stmt = $pdo->prepare("UPDATE users SET avatar = NULL, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$user_id]);
        
        $message = 'Xóa ảnh đại diện thành công!';
        $message_type = 'success';
        
        // Refresh user data
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
    } catch (PDOException $e) {
        $message = 'Lỗi khi xóa ảnh đại diện: ' . $e->getMessage();
        $message_type = 'danger';
    }
}

// Handle profile update
if ($_POST && isset($_POST['update_profile'])) {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validate required fields
    if (empty($full_name) || empty($email)) {
        $message = 'Vui lòng điền đầy đủ họ tên và email!';
        $message_type = 'warning';
    } else {
        try {
            // Check if email is already taken by another user
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt->execute([$email, $user_id]);
            
            if ($stmt->fetch()) {
                $message = 'Email này đã được sử dụng bởi tài khoản khác!';
                $message_type = 'danger';
            } else {
                // Update user information
                $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$full_name, $email, $phone, $address, $user_id]);
                
                $message = 'Cập nhật thông tin thành công!';
                $message_type = 'success';
                
                // Refresh user data
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$user_id]);
                $user = $stmt->fetch();
            }
        } catch (PDOException $e) {
            $message = 'Lỗi khi cập nhật thông tin: ' . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// Handle password change
if ($_POST && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $message = 'Vui lòng điền đầy đủ thông tin mật khẩu!';
        $message_type = 'warning';
    } else if ($new_password !== $confirm_password) {
        $message = 'Mật khẩu mới và xác nhận mật khẩu không khớp!';
        $message_type = 'danger';
    } else if (strlen($new_password) < 6) {
        $message = 'Mật khẩu mới phải có ít nhất 6 ký tự!';
        $message_type = 'warning';
    } else {
        // Verify current password
        if (password_verify($current_password, $user['password'])) {
            try {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
                $stmt->execute([$hashed_password, $user_id]);
                
                $message = 'Đổi mật khẩu thành công!';
                $message_type = 'success';
            } catch (PDOException $e) {
                $message = 'Lỗi khi đổi mật khẩu: ' . $e->getMessage();
                $message_type = 'danger';
            }
        } else {
            $message = 'Mật khẩu hiện tại không đúng!';
            $message_type = 'danger';
        }
    }
}

$page_title = 'Thông tin cá nhân';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="index.php">Trang Chủ</a></li>
                    <li class="breadcrumb-item active">Thông tin cá nhân</li>
                </ol>
            </nav>
            
            <h2><i class="fas fa-user"></i> Thông tin cá nhân</h2>
            
            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message_type; ?> alert-dismissible fade show">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <!-- Avatar Section -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-camera"></i> Ảnh đại diện</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <?php if (isset($user['avatar']) && $user['avatar'] && file_exists($user['avatar'])): ?>
                                    <img src="<?php echo htmlspecialchars($user['avatar']); ?>" 
                                         alt="Avatar" class="rounded-circle" 
                                         style="width: 150px; height: 150px; object-fit: cover; border: 3px solid #dee2e6;">
                                <?php else: ?>
                                    <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center mx-auto" 
                                         style="width: 150px; height: 150px; border: 3px solid #dee2e6;">
                                        <i class="fas fa-user fa-4x text-white"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <input type="file" class="form-control" name="avatar" 
                                           accept="image/jpeg,image/png,image/gif,image/webp" required>
                                    <div class="form-text">Chấp nhận: JPG, PNG, GIF, WebP (tối đa 5MB)</div>
                                </div>
                                <button type="submit" name="upload_avatar" class="btn btn-primary btn-sm">
                                    <i class="fas fa-upload"></i> Tải lên ảnh
                                </button>
                            </form>
                            
                            <?php if (isset($user['avatar']) && $user['avatar']): ?>
                                <form method="POST" class="mt-2" onsubmit="return confirm('Bạn có chắc muốn xóa ảnh đại diện?')">
                                    <button type="submit" name="remove_avatar" class="btn btn-outline-danger btn-sm">
                                        <i class="fas fa-trash"></i> Xóa ảnh
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- Profile Information -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user-edit"></i> Cập nhật thông tin</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Tên đăng nhập</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    <div class="form-text">Tên đăng nhập không thể thay đổi</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="full_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="full_name" name="full_name" 
                                           value="<?php echo htmlspecialchars($user['full_name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Số điện thoại</label>
                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="address" class="form-label">Địa chỉ</label>
                                    <textarea class="form-control" id="address" name="address" rows="3"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="mb-3">
                                    <small class="text-muted">
                                        <i class="fas fa-clock"></i> 
                                        Tài khoản tạo: <?php echo date('d/m/Y H:i', strtotime($user['created_at'])); ?>
                                    </small>
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Cập nhật thông tin
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <!-- Change Password -->
                <div class="col-lg-4 mb-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-key"></i> Đổi mật khẩu</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mật khẩu hiện tại <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="current_password" name="current_password" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password" 
                                           minlength="6" required>
                                    <div class="form-text">Mật khẩu phải có ít nhất 6 ký tự</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Xác nhận mật khẩu mới <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" 
                                           minlength="6" required>
                                </div>
                                
                                <button type="submit" name="change_password" class="btn btn-warning">
                                    <i class="fas fa-key"></i> Đổi mật khẩu
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Statistics -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-chart-bar"></i> Thống kê tài khoản</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            // Get user statistics
                            try {
                                // Total orders
                                $stmt = $pdo->prepare("SELECT COUNT(*) as total_orders FROM orders WHERE customer_id = ?");
                                $stmt->execute([$user_id]);
                                $total_orders = $stmt->fetchColumn();
                                
                                // Total spent
                                $stmt = $pdo->prepare("SELECT SUM(total_amount) as total_spent FROM orders WHERE customer_id = ? AND status IN ('delivered', 'processing')");
                                $stmt->execute([$user_id]);
                                $total_spent = $stmt->fetchColumn() ?: 0;
                                
                                // Cart items
                                $stmt = $pdo->prepare("SELECT COUNT(*) as cart_items FROM cart WHERE user_id = ?");
                                $stmt->execute([$user_id]);
                                $cart_items = $stmt->fetchColumn();
                            } catch (PDOException $e) {
                                $total_orders = 0;
                                $total_spent = 0;
                                $cart_items = 0;
                            }
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-md-4">
                                    <div class="border rounded p-3">
                                        <h3 class="text-primary"><?php echo $total_orders; ?></h3>
                                        <p class="mb-0">Tổng đơn hàng</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3">
                                        <h3 class="text-success"><?php echo number_format($total_spent); ?> VNĐ</h3>
                                        <p class="mb-0">Tổng chi tiêu</p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="border rounded p-3">
                                        <h3 class="text-warning"><?php echo $cart_items; ?></h3>
                                        <p class="mb-0">Sản phẩm trong giỏ</p>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mt-3 text-center">
                                <a href="orders.php" class="btn btn-outline-primary me-2">
                                    <i class="fas fa-list"></i> Xem đơn hàng
                                </a>
                                <a href="cart.php" class="btn btn-outline-success">
                                    <i class="fas fa-shopping-cart"></i> Xem giỏ hàng
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
