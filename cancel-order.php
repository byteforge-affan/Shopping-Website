<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit; }

$cid = (int)$_SESSION['customer_id'];
$oid = (int)($_GET['id'] ?? 0);

if($oid > 0) {
    // Only cancel if: belongs to this customer AND status is still pending
    $check = $conn->query("SELECT order_id FROM orders WHERE order_id=$oid AND customer_id=$cid AND status='pending'");
    if($check && $check->num_rows > 0) {
        $conn->query("UPDATE orders SET status='cancelled' WHERE order_id=$oid");
        // Restore stock
        $items = $conn->query("SELECT product_id, qty FROM order_items WHERE order_id=$oid");
        if($items) while($it = $items->fetch_assoc()) {
            $conn->query("UPDATE products SET stock = stock + {$it['qty']} WHERE product_id = {$it['product_id']}");
        }
        header('Location: my-account.php?msg=Order+cancelled+successfully#orders');
    } else {
        header('Location: my-account.php?error=Cannot+cancel+this+order#orders');
    }
} else {
    header('Location: my-account.php');
}
exit;
