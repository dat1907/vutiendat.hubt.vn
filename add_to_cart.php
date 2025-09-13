<?php
// Configure session before starting
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_lifetime', 0);
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_strict_mode', 1);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.cookie_path', '/');
    ini_set('session.cookie_domain', '');
    session_start();
}

require_once 'config/database.php';

// Set JSON header
header('Content-Type: application/json');

// Debug session information
$debug_info = [
    'session_id' => session_id(),
    'session_status' => session_status(),
    'session_data' => $_SESSION,
    'cookies' => $_COOKIE,
    'user_id_exists' => isset($_SESSION['user_id']),
    'user_id_value' => isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null
];

// Check if user is logged in
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false, 
        'message' => 'Vui lòng đăng nhập để thêm sản phẩm vào giỏ hàng',
        'debug' => $debug_info
    ]);
    exit();
}

if ($_POST && isset($_POST['product_id'])) {
    $user_id = $_SESSION['user_id'];
    $product_id = (int)$_POST['product_id'];
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    
    if ($quantity <= 0) {
        echo json_encode(['success' => false, 'message' => 'Số lượng không hợp lệ']);
        exit();
    }
    
    try {
        // Check if product exists and has enough stock
        $stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND status = 'active'");
        $stmt->execute([$product_id]);
        $product = $stmt->fetch();
        
        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Sản phẩm không tồn tại']);
            exit();
        }
        
        if ($product['quantity'] < $quantity) {
            echo json_encode(['success' => false, 'message' => 'Không đủ số lượng trong kho']);
            exit();
        }
        
        // Check if product already in cart
        $stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
        $stmt->execute([$user_id, $product_id]);
        $existing_item = $stmt->fetch();
        
        if ($existing_item) {
            // Update quantity
            $new_quantity = $existing_item['quantity'] + $quantity;
            if ($new_quantity > $product['quantity']) {
                echo json_encode(['success' => false, 'message' => 'Tổng số lượng vượt quá số lượng trong kho']);
                exit();
            }
            
            $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?");
            $stmt->execute([$new_quantity, $user_id, $product_id]);
        } else {
            // Add new item to cart
            $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
            $stmt->execute([$user_id, $product_id, $quantity]);
        }
        
        // Get cart count
        $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetchColumn() ?: 0;
        
        echo json_encode([
            'success' => true, 
            'message' => 'Đã thêm sản phẩm vào giỏ hàng',
            'cart_count' => $cart_count
        ]);
        
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Lỗi hệ thống, vui lòng thử lại']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
}
?>
