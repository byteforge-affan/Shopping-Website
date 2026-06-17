<?php
session_start();
header('Content-Type: application/json');

$pid = (int)($_POST['product_id'] ?? 0);
$qty = max(1, (int)($_POST['qty'] ?? 1));

if($pid > 0) {
    if(!isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid] = 0;
    $_SESSION['cart'][$pid] += $qty;
    echo json_encode(['success' => true, 'cart_count' => count($_SESSION['cart'])]);
} else {
    echo json_encode(['success' => false]);
}
