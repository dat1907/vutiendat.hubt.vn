<?php
session_start();
require_once 'config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'count' => 0]);
    exit();
}

try {
    $stmt = $pdo->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $count = $stmt->fetchColumn() ?: 0;
    
    echo json_encode(['success' => true, 'count' => $count]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'count' => 0]);
}
?>
