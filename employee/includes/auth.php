<?php
if(session_status()===PHP_SESSION_NONE) session_start();
if(!isset($_SESSION['emp_id'])) { header('Location: login.php'); exit; }
require_once '../includes/db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/><meta name="viewport" content="width=device-width,initial-scale=1"/>
<title><?php echo isset($page_title)?$page_title.' — Employee':'Employee Dashboard'; ?></title>
<link rel="stylesheet" href="../css/dashboard.css"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<style>
.sidebar { background: #1a3a2a; }
.sidebar-logo span { color: #81c784; }
.sidebar-nav a.active { border-left-color: #4caf50; }
.dash-topbar { border-bottom-color: #e8f5e9; }
.stat-icon.green2 { background:#e8f5e9; color:#2e7d32; }
</style>
</head>
<body>
<div class="dash-layout">
<aside class="sidebar">
  <div class="sidebar-logo">
    <h2>Arts Store</h2>
    <span>Employee Panel</span>
  </div>
  <nav class="sidebar-nav">
    <div class="nav-section-label">Main</div>
    <a href="index.php"   class="<?php echo basename($_SERVER['PHP_SELF'])=='index.php'  ?'active':''; ?>"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
    <div class="nav-section-label">Orders</div>
    <a href="orders.php"  class="<?php echo basename($_SERVER['PHP_SELF'])=='orders.php' ?'active':''; ?>">
      <i class="fas fa-shopping-bag"></i> Manage Orders
      <?php
        $pc = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='confirmed'")->fetch_assoc()['c'];
        if($pc>0) echo "<span class='badge-pill' style='background:#27ae60;'>$pc</span>";
      ?>
    </a>
    <a href="dispatch.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='dispatch.php'?'active':''; ?>"><i class="fas fa-truck"></i> Dispatch</a>
    <div class="nav-section-label">Account</div>
    <a href="change-password.php" class="<?php echo basename($_SERVER['PHP_SELF'])=='change-password.php'?'active':''; ?>"><i class="fas fa-lock"></i> Change Password</a>
    <a href="../index.php" target="_blank"><i class="fas fa-external-link-alt"></i> View Website</a>
    <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
  </nav>
  <div class="sidebar-user">
    <div class="sidebar-avatar" style="background:#27ae60;"><?php echo strtoupper(substr($_SESSION['emp_name']??'E',0,1)); ?></div>
    <div class="sidebar-user-info">
      <p><?php echo htmlspecialchars($_SESSION['emp_name']??''); ?></p>
      <span>Employee</span>
    </div>
  </div>
</aside>
<div class="dash-main">
<div class="dash-topbar">
  <h1><?php echo $page_title??'Dashboard'; ?></h1>
  <div class="topbar-right">
    <a href="orders.php" class="topbar-btn"><i class="fas fa-bell"></i></a>
    <a href="logout.php" class="topbar-btn"><i class="fas fa-sign-out-alt"></i></a>
  </div>
</div>
<div class="dash-content">
