<?php
session_start();
require_once 'config/database.php';

// VNPay Configuration
$vnp_HashSecret = "RAOEXHYVSDDIIENYWSLDIIZTANXUXZFJ";

$vnp_SecureHash = $_GET['vnp_SecureHash'];
$inputData = array();
foreach ($_GET as $key => $value) {
    if (substr($key, 0, 4) == "vnp_") {
        $inputData[$key] = $value;
    }
}

unset($inputData['vnp_SecureHash']);
ksort($inputData);
$i = 0;
$hashData = "";
foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashData = $hashData . '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashData = $hashData . urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
}

$secureHash = hash_hmac('sha512', $hashData, $vnp_HashSecret);

// Verify signature
if ($secureHash == $vnp_SecureHash) {
    $order_id = $_GET['vnp_TxnRef'];
    $vnp_ResponseCode = $_GET['vnp_ResponseCode'];
    $vnp_TransactionNo = $_GET['vnp_TransactionNo'];
    $vnp_Amount = $_GET['vnp_Amount'] / 100; // Convert back from VNPay format
    
    try {
        if ($vnp_ResponseCode == '00') {
            // Payment successful
            $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', vnpay_transaction_id = ?, payment_date = NOW() WHERE id = ?");
            $stmt->execute([$vnp_TransactionNo, $order_id]);
            
            $message = 'Thanh toán thành công!';
            $message_type = 'success';
        } else {
            // Payment failed
            $stmt = $pdo->prepare("UPDATE orders SET status = 'payment_failed' WHERE id = ?");
            $stmt->execute([$order_id]);
            
            $message = 'Thanh toán thất bại. Vui lòng thử lại!';
            $message_type = 'danger';
        }
    } catch (PDOException $e) {
        $message = 'Lỗi xử lý thanh toán: ' . $e->getMessage();
        $message_type = 'danger';
    }
} else {
    $message = 'Chữ ký không hợp lệ!';
    $message_type = 'danger';
}

$page_title = 'Kết quả thanh toán';
require_once 'includes/header.php';
?>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header text-center">
                    <h4><i class="fas fa-credit-card"></i> Kết quả thanh toán VNPay</h4>
                </div>
                <div class="card-body text-center">
                    <div class="alert alert-<?php echo $message_type; ?>">
                        <?php if ($message_type === 'success'): ?>
                            <i class="fas fa-check-circle fa-3x mb-3"></i>
                        <?php else: ?>
                            <i class="fas fa-times-circle fa-3x mb-3"></i>
                        <?php endif; ?>
                        <h5><?php echo $message; ?></h5>
                    </div>
                    
                    <?php if (isset($order_id)): ?>
                        <div class="payment-details mt-4">
                            <h6>Thông tin giao dịch:</h6>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>Mã đơn hàng:</strong></td>
                                    <td>#<?php echo htmlspecialchars($order_id); ?></td>
                                </tr>
                                <?php if (isset($vnp_TransactionNo)): ?>
                                <tr>
                                    <td><strong>Mã giao dịch VNPay:</strong></td>
                                    <td><?php echo htmlspecialchars($vnp_TransactionNo); ?></td>
                                </tr>
                                <?php endif; ?>
                                <?php if (isset($vnp_Amount)): ?>
                                <tr>
                                    <td><strong>Số tiền:</strong></td>
                                    <td><?php echo number_format($vnp_Amount); ?> VNĐ</td>
                                </tr>
                                <?php endif; ?>
                                <tr>
                                    <td><strong>Trạng thái:</strong></td>
                                    <td>
                                        <?php if ($vnp_ResponseCode == '00'): ?>
                                            <span class="badge bg-success">Thành công</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Thất bại</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    <?php endif; ?>
                    
                    <div class="mt-4">
                        <?php if ($message_type === 'success'): ?>
                            <a href="orders.php" class="btn btn-primary me-2">
                                <i class="fas fa-list"></i> Xem đơn hàng
                            </a>
                        <?php else: ?>
                            <a href="cart.php" class="btn btn-warning me-2">
                                <i class="fas fa-shopping-cart"></i> Quay lại giỏ hàng
                            </a>
                        <?php endif; ?>
                        <a href="index.php" class="btn btn-secondary">
                            <i class="fas fa-home"></i> Trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
