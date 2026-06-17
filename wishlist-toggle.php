<?php
session_start();
header('Content-Type: application/json');
require_once 'includes/db.php';

$pid = (int)($_POST['product_id'] ?? 0);
if($pid <= 0) { echo json_encode(['success'=>false]); exit; }

if(isset($_SESSION['customer_id'])) {
    $cid = (int)$_SESSION['customer_id'];
    $chk = $conn->query("SELECT wishlist_id FROM wishlist WHERE customer_id=$cid AND product_id=$pid");
    if($chk && $chk->num_rows > 0) {
        $conn->query("DELETE FROM wishlist WHERE customer_id=$cid AND product_id=$pid");
        echo json_encode(['success'=>true, 'added'=>false]);
    } else {
        $conn->query("INSERT INTO wishlist (customer_id, product_id) VALUES ($cid, $pid)");
        echo json_encode(['success'=>true, 'added'=>true]);
    }
} else {
    // Session-based wishlist for guests
    if(!isset($_SESSION['wishlist'])) $_SESSION['wishlist'] = [];
    if(isset($_SESSION['wishlist'][$pid])) {
        unset($_SESSION['wishlist'][$pid]);
        echo json_encode(['success'=>true, 'added'=>false]);
    } else {
        $_SESSION['wishlist'][$pid] = 1;
        echo json_encode(['success'=>true, 'added'=>true]);
    }
}
