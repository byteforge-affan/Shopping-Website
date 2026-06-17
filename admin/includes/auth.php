<?php
// admin/includes/auth.php — include at top of every admin page
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once '../includes/db.php';

// ── Global sidebar counters (available on every admin page) ──
$pending_orders   = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
$pending_requests = $conn->query("SELECT COUNT(*) c FROM order_requests WHERE status='pending'")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo isset($page_title) ? $page_title.' — Admin' : 'Admin Dashboard'; ?></title>
<link rel="stylesheet" href="../css/dashboard.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body>
<div class="dash-layout">

<!-- SIDEBAR -->
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>Arts Store</h2>
    <span>Admin Panel</span>
  </div>

  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='index.php'?'active':''; ?>">
      <i class="fas fa-tachometer-alt"></i> Dashboard
    </a>

    <div class="nav-section-label">Catalogue</div>
    <a href="products.php"   class="<?php echo basename($_SERVER['PHP_SELF'])=='products.php'  ?'active':''; ?>"><i class="fas fa-box"></i> Products</a>
    <a href="categories.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='categories.php'?'active':''; ?>"><i class="fas fa-tags"></i> Categories</a>
    <a href="stock.php"      class="<?php echo basename($_SERVER['PHP_SELF'])=='stock.php'     ?'active':''; ?>"><i class="fas fa-warehouse"></i> Stock</a>

    <div class="nav-section-label">Orders</div>
    <a href="orders.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='orders.php'?'active':''; ?>">
      <i class="fas fa-shopping-bag"></i> All Orders
      <?php if($pending_orders > 0): ?>
        <span class="badge-pill"><?php echo $pending_orders; ?></span>
      <?php endif; ?>
    </a>
    <a href="order-requests.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='order-requests.php'?'active':''; ?>">
      <i class="fas fa-exchange-alt"></i> Order Requests
      <?php if($pending_requests > 0): ?>
        <span class="badge-pill" style="background:#e74c3c;"><?php echo $pending_requests; ?></span>
      <?php endif; ?>
    </a>

    <div class="nav-section-label">People</div>
    <a href="employees.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='employees.php'?'active':''; ?>"><i class="fas fa-user-tie"></i> Employees</a>
    <a href="customers.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='customers.php'?'active':''; ?>"><i class="fas fa-users"></i> Customers</a>

    <div class="nav-section-label">Reports</div>
    <a href="feedback.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='feedback.php'?'active':''; ?>"><i class="fas fa-comments"></i> Feedback</a>
    <a href="reports.php"  class="<?php echo basename($_SERVER['PHP_SELF'])=='reports.php' ?'active':''; ?>"><i class="fas fa-chart-bar"></i> Reports</a>

    <div class="nav-section-label">Settings</div>
    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>

  <div class="sidebar-user">
    <div class="sidebar-avatar"><?php echo strtoupper(substr($_SESSION['admin_name']??'A',0,1)); ?></div>
    <div class="sidebar-user-info">
      <p><?php echo htmlspecialchars($_SESSION['admin_name']??''); ?></p>
      <span>Administrator</span>
    </div>
  </div>
</aside>

<!-- MAIN -->
<div class="dash-main">
<div class="dash-topbar">
  <h1><?php echo $page_title ?? 'Dashboard'; ?></h1>
  <div class="topbar-right">
    <a href="orders.php"     class="topbar-btn" title="Orders"><i class="fas fa-bell"></i></a>
    <a href="../index.php" target="_blank" class="topbar-btn" title="View Site"><i class="fas fa-globe"></i></a>
    <a href="logout.php"     class="topbar-btn" title="Logout"><i class="fas fa-sign-out-alt"></i></a>
  </div>
</div>
<div class="dash-content">