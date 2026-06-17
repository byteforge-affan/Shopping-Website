<?php
session_start();
require_once 'includes/db.php';

if(!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit; }

$cid  = (int)$_SESSION['customer_id'];
$name = clean($conn, $_POST['full_name'] ?? '');
$phone= clean($conn, $_POST['phone']     ?? '');
$addr = clean($conn, $_POST['address']   ?? '');

if($name) {
    $conn->query("UPDATE customers SET full_name='$name', phone='$phone', address='$addr' WHERE customer_id=$cid");
    $_SESSION['customer_name'] = $name;
}
header('Location: my-account.php?updated=1');
exit;
