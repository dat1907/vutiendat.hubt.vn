<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || !isset($_POST['order_id'])) {
    echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
    exit();
}

$order_id = (int)$_POST['order_id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if order exists and belongs to user
    $stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND customer_id = ? AND status = 'pending'");
    $stmt->execute([$order_id, $user_id]);
    $order = $stmt->fetch();
    
    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Đơn hàng không tồn tại hoặc không thể hủy']);
        exit();
    }
    
    $pdo->beginTransaction();
    
    // Get order items to restore product quantities
    $stmt = $pdo->prepare("SELECT product_id, quantity FROM order_items WHERE order_id = ?");
    $stmt->execute([$order_id]);
    $order_items = $stmt->fetchAll();
    
    // Restore product quantities
    foreach ($order_items as $item) {
        $stmt = $pdo->prepare("UPDATE products SET quantity = quantity + ? WHERE id = ?");
        $stmt->execute([$item['quantity'], $item['product_id']]);
    }
    
    // Update order status
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
    $stmt->execute([$order_id]);
    
    $pdo->commit();
    
    echo json_encode(['success' => true, 'message' => 'Đã hủy đơn hàng thành công']);
    
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
}
?>
