<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and order exists
if (!isset($_SESSION['user_id']) || !isset($_GET['order_id']) || !isset($_GET['amount'])) {
    header('Location: index.php');
    exit();
}

// Validate amount
if (!is_numeric($_GET['amount']) || $_GET['amount'] <= 0) {
    header('Location: cart.php');
    exit();
}

$order_id = $_GET['order_id'];
$amount = $_GET['amount'];

// Verify order belongs to user
try {
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ?");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();
    
    if (!$order) {
        header('Location: index.php');
        exit();
    }
} catch (PDOException $e) {
    header('Location: index.php');
    exit();
}

// VNPay Configuration
$vnp_Url = "https://sandbox.vnpayment.vn/paymentv2/vpcpay.html";
$vnp_Returnurl = "http://localhost/webcban/vnpay_return.php";
$vnp_TmnCode = "DEMOV210"; // Mã demo của VNPay
$vnp_HashSecret = "RAOEXHYVSDDIIENYWSLDIIZTANXUXZFJ"; // Hash secret demo

// Payment data
$vnp_TxnRef = time() . '_' . $order_id; // Mã giao dịch unique
$vnp_OrderInfo = 'Thanh toan don hang ' . $order_id;
$vnp_OrderType = 'other';
$vnp_Amount = $amount * 100; // VNPay yêu cầu số tiền * 100
$vnp_Locale = 'vn';
$vnp_IpAddr = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

$inputData = array(
    "vnp_Version" => "2.1.0",
    "vnp_TmnCode" => $vnp_TmnCode,
    "vnp_Amount" => $vnp_Amount,
    "vnp_Command" => "pay",
    "vnp_CreateDate" => date('YmdHis'),
    "vnp_CurrCode" => "VND",
    "vnp_IpAddr" => $vnp_IpAddr,
    "vnp_Locale" => $vnp_Locale,
    "vnp_OrderInfo" => $vnp_OrderInfo,
    "vnp_OrderType" => $vnp_OrderType,
    "vnp_ReturnUrl" => $vnp_Returnurl,
    "vnp_TxnRef" => $vnp_TxnRef,
);

// Sắp xếp dữ liệu theo thứ tự alphabet
ksort($inputData);
$query = "";
$i = 0;
$hashdata = "";

foreach ($inputData as $key => $value) {
    if ($i == 1) {
        $hashdata .= '&' . urlencode($key) . "=" . urlencode($value);
    } else {
        $hashdata .= urlencode($key) . "=" . urlencode($value);
        $i = 1;
    }
    $query .= urlencode($key) . "=" . urlencode($value) . '&';
}

$vnp_Url = $vnp_Url . "?" . $query;
if (isset($vnp_HashSecret)) {
    $vnpSecureHash = hash_hmac('sha512', $hashdata, $vnp_HashSecret);
    $vnp_Url .= 'vnp_SecureHash=' . $vnpSecureHash;
}

// Redirect to VNPay
header('Location: ' . $vnp_Url);
exit();
?>
