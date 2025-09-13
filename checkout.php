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
$error = '';

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
    
    if (empty($cart_items)) {
        header('Location: cart.php');
        exit();
    }
    
    // Calculate total
    $total = 0;
    foreach ($cart_items as $item) {
        $total += $item['price'] * $item['quantity'];
    }
} catch (PDOException $e) {
    $error = 'Lỗi khi tải giỏ hàng';
}

// Handle order submission
if ($_POST && isset($_POST['place_order'])) {
    $customer_name = trim($_POST['customer_name']);
    $customer_phone = trim($_POST['customer_phone']);
    $customer_email = trim($_POST['customer_email']);
    $customer_address = trim($_POST['customer_address']);
    $tinh = trim($_POST['ten_tinh']);
    $quan = trim($_POST['ten_quan']);
    $phuong = trim($_POST['ten_phuong']);
    $notes = trim($_POST['notes']);
    $payment_method = $_POST['payment_method'] ?? 'cod';
    
    // Validate required fields
    if (empty($customer_name) || empty($customer_phone) || empty($customer_email) || empty($customer_address)) {
        $error = 'Vui lòng điền đầy đủ thông tin giao hàng!';
    } else {
        // Combine full address
        $full_address = $customer_address;
        if ($phuong) $full_address .= ', ' . $phuong;
        if ($quan) $full_address .= ', ' . $quan;
        if ($tinh) $full_address .= ', ' . $tinh;
        
        try {
            $pdo->beginTransaction();
            
            // Create order with customer info and payment method
            $stmt = $pdo->prepare("INSERT INTO orders (customer_id, total_amount, customer_name, customer_phone, customer_email, customer_address, notes, payment_method, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $order_status = ($payment_method === 'vnpay') ? 'pending_payment' : 'processing';
            $stmt->execute([$user_id, $total, $customer_name, $customer_phone, $customer_email, $full_address, $notes, $payment_method, $order_status]);
        $order_id = $pdo->lastInsertId();
        
        // Add order items and update product quantities
        foreach ($cart_items as $item) {
            // Check stock again
            $stmt = $pdo->prepare("SELECT quantity FROM products WHERE id = ?");
            $stmt->execute([$item['product_id']]);
            $current_stock = $stmt->fetchColumn();
            
            if ($current_stock < $item['quantity']) {
                throw new Exception("Sản phẩm {$item['name']} không đủ số lượng trong kho");
            }
            
            // Add to order_items
            $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
            
            // Update product quantity
            $stmt = $pdo->prepare("UPDATE products SET quantity = quantity - ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['product_id']]);
        }
        
        // Clear cart
        $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        
        $pdo->commit();
        
        // Handle payment method redirection
        if ($payment_method === 'vnpay') {
            // Redirect to VNPay payment gateway
            header("Location: vnpay_payment.php?order_id=$order_id&amount=$total");
            exit();
        } else {
            // COD - redirect to success page
            header("Location: order_success.php?order_id=$order_id");
            exit();
        }
            } catch (Exception $e) {
            $pdo->rollBack();
            $error = $e->getMessage();
        }
    }
}

$page_title = 'Thanh toán';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <h2><i class="fas fa-credit-card"></i> Thanh toán</h2>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <div class="row">
                <div class="col-lg-8">
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5><i class="fas fa-list"></i> Đơn hàng của bạn</h5>
                        </div>
                        <div class="card-body">
                            <?php foreach ($cart_items as $item): ?>
                                <div class="row align-items-center border-bottom py-3">
                                    <div class="col-md-2">
                                        <img src="<?php echo htmlspecialchars($item['image_url']); ?>" 
                                             class="img-fluid rounded" alt="<?php echo htmlspecialchars($item['name']); ?>">
                                    </div>
                                    <div class="col-md-6">
                                        <h6><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <p class="text-muted mb-0">Giá: <?php echo number_format($item['price']); ?> VNĐ</p>
                                        <small class="text-muted">Số lượng: <?php echo $item['quantity']; ?></small>
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <strong><?php echo number_format($item['price'] * $item['quantity']); ?> VNĐ</strong>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-user"></i> Thông tin giao hàng</h5>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_name" class="form-label">Họ và tên <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="customer_name" name="customer_name" 
                                               value="<?php echo isset($_POST['customer_name']) ? htmlspecialchars($_POST['customer_name']) : ''; ?>" required>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="customer_phone" class="form-label">Số điện thoại <span class="text-danger">*</span></label>
                                        <input type="tel" class="form-control" id="customer_phone" name="customer_phone" 
                                               value="<?php echo isset($_POST['customer_phone']) ? htmlspecialchars($_POST['customer_phone']) : ''; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_email" class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" id="customer_email" name="customer_email" 
                                           value="<?php echo isset($_POST['customer_email']) ? htmlspecialchars($_POST['customer_email']) : ''; ?>" required>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label for="tinh" class="form-label">Tỉnh/Thành phố</label>
                                        <select class="form-select" id="tinh" name="tinh">
                                            <option value="">Chọn Tỉnh/Thành phố</option>
                                        </select>
                                        <input type="hidden" id="ten_tinh" name="ten_tinh" value="<?php echo isset($_POST['ten_tinh']) ? htmlspecialchars($_POST['ten_tinh']) : ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="quan" class="form-label">Quận/Huyện</label>
                                        <select class="form-select" id="quan" name="quan">
                                            <option value="">Chọn Quận/Huyện</option>
                                        </select>
                                        <input type="hidden" id="ten_quan" name="ten_quan" value="<?php echo isset($_POST['ten_quan']) ? htmlspecialchars($_POST['ten_quan']) : ''; ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label for="phuong" class="form-label">Phường/Xã</label>
                                        <select class="form-select" id="phuong" name="phuong">
                                            <option value="">Chọn Phường/Xã</option>
                                        </select>
                                        <input type="hidden" id="ten_phuong" name="ten_phuong" value="<?php echo isset($_POST['ten_phuong']) ? htmlspecialchars($_POST['ten_phuong']) : ''; ?>">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="customer_address" class="form-label">Địa chỉ cụ thể <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="customer_address" name="customer_address" 
                                           placeholder="Số nhà, tên đường..." 
                                           value="<?php echo isset($_POST['customer_address']) ? htmlspecialchars($_POST['customer_address']) : ''; ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Ghi chú (tùy chọn)</label>
                                    <textarea class="form-control" id="notes" name="notes" rows="3" 
                                              placeholder="Ghi chú về đơn hàng..."><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                                </div>
                                
                                <!-- Payment Method Selection -->
                                <div class="mb-4">
                                    <label class="form-label">Phương thức thanh toán <span class="text-danger">*</span></label>
                                    <div class="payment-methods">
                                        <div class="form-check payment-option mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="cod" value="cod" 
                                                   <?php echo (!isset($_POST['payment_method']) || $_POST['payment_method'] === 'cod') ? 'checked' : ''; ?>>
                                            <label class="form-check-label w-100" for="cod">
                                                <div class="payment-card">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-money-bill-wave fa-2x text-success me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Thanh toán khi nhận hàng (COD)</h6>
                                                            <small class="text-muted">Thanh toán bằng tiền mặt khi nhận hàng</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                        
                                        <div class="form-check payment-option mb-3">
                                            <input class="form-check-input" type="radio" name="payment_method" id="vnpay" value="vnpay"
                                                   <?php echo (isset($_POST['payment_method']) && $_POST['payment_method'] === 'vnpay') ? 'checked' : ''; ?>>
                                            <label class="form-check-label w-100" for="vnpay">
                                                <div class="payment-card">
                                                    <div class="d-flex align-items-center">
                                                        <i class="fas fa-credit-card fa-2x text-primary me-3"></i>
                                                        <div>
                                                            <h6 class="mb-1">Thanh toán qua VNPay</h6>
                                                            <small class="text-muted">Thanh toán trực tuyến qua thẻ ATM, Visa, MasterCard</small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="d-grid">
                                    <button type="submit" name="place_order" class="btn btn-success btn-lg">
                                        <i class="fas fa-check"></i> Đặt hàng
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-header">
                            <h5><i class="fas fa-calculator"></i> Tổng đơn hàng</h5>
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
                            <div class="d-flex justify-content-between mb-2">
                                <span>Thuế:</span>
                                <span>Đã bao gồm</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Tổng cộng:</strong>
                                <strong class="text-success fs-4"><?php echo number_format($total); ?> VNĐ</strong>
                            </div>
                            
                            <div class="alert alert-info">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    Đơn hàng sẽ được xử lý trong vòng 24h và giao hàng trong 1-3 ngày làm việc.
                                </small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-3">
                        <a href="cart.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left"></i> Quay lại giỏ hàng
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Lấy tỉnh thành
    $.getJSON('https://provinces.open-api.vn/api/p/', function(provinces) {
        provinces.forEach(function(province) {
            $("#tinh").append(`<option value="${province.code}">${province.name}</option>`);
        });
    });

    // Xử lý khi chọn tỉnh
    $("#tinh").change(function() {
        const provinceCode = $(this).val();
        $("#ten_tinh").val($(this).find("option:selected").text());
        
        // Lấy quận/huyện
        $.getJSON(`https://provinces.open-api.vn/api/p/${provinceCode}?depth=2`, function(provinceData) {
            $("#quan").html('<option value="">Chọn Quận/Huyện</option>');
            provinceData.districts.forEach(function(district) {
                $("#quan").append(`<option value="${district.code}">${district.name}</option>`);
            });
        });
    });

    // Xử lý khi chọn quận
    $("#quan").change(function() {
        const districtCode = $(this).val();
        $("#ten_quan").val($(this).find("option:selected").text());
        
        // Lấy phường/xã
        $.getJSON(`https://provinces.open-api.vn/api/d/${districtCode}?depth=2`, function(districtData) {
            $("#phuong").html('<option value="">Chọn Phường/Xã</option>');
            districtData.wards.forEach(function(ward) {
                $("#phuong").append(`<option value="${ward.code}">${ward.name}</option>`);
            });
        });
    });

    // Xử lý khi chọn phường
    $("#phuong").change(function() {
        $("#ten_phuong").val($(this).find("option:selected").text());
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
