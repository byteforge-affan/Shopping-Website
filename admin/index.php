<?php
$page_title = 'Dashboard';
require_once 'includes/auth.php';
// NOTE: $pending_orders and $pending_requests are already defined in auth.php

// Stats
$total_orders    = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$total_products  = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'];
$total_customers = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'];
$total_revenue   = $conn->query("SELECT SUM(total) s FROM orders WHERE status='delivered'")->fetch_assoc()['s'] ?? 0;
$low_stock       = $conn->query("SELECT COUNT(*) c FROM products WHERE stock < 5")->fetch_assoc()['c'];

// Recent orders
$recent_orders = $conn->query("SELECT o.*, c.full_name FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id ORDER BY o.created_at DESC LIMIT 8");

// Recent customers
$recent_customers = $conn->query("SELECT * FROM customers ORDER BY created_at DESC LIMIT 5");
?>

<!-- STAT CARDS -->
<div class="stat-grid">
  <div class="stat-card">
    <div class="stat-icon purple"><i class="fas fa-shopping-bag"></i></div>
    <div class="stat-info">
      <p>Total Orders</p>
      <h3><?php echo $total_orders; ?></h3>
      <small><i class="fas fa-clock"></i> <?php echo $pending_orders; ?> pending</small>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div>
    <div class="stat-info">
      <p>Total Revenue</p>
      <h3>Rs. <?php echo number_format($total_revenue); ?></h3>
      <small>From delivered orders</small>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon orange"><i class="fas fa-box"></i></div>
    <div class="stat-info">
      <p>Products</p>
      <h3><?php echo $total_products; ?></h3>
      <small style="color:<?php echo $low_stock>0?'#e74c3c':'#27ae60'; ?>">
        <?php echo $low_stock; ?> low stock
      </small>
    </div>
  </div>
  <div class="stat-card">
    <div class="stat-icon blue"><i class="fas fa-users"></i></div>
    <div class="stat-info">
      <p>Customers</p>
      <h3><?php echo $total_customers; ?></h3>
      <small>Registered users</small>
    </div>
  </div>
  <!-- ── NEW: Order Requests card ── -->
  <div class="stat-card" style="cursor:pointer;" onclick="location.href='order-requests.php'">
    <div class="stat-icon red" style="background:#fde8e8;"><i class="fas fa-exchange-alt" style="color:#e74c3c;"></i></div>
    <div class="stat-info">
      <p>Order Requests</p>
      <h3><?php echo $pending_requests; ?></h3>
      <small style="color:<?php echo $pending_requests>0?'#e74c3c':'#27ae60'; ?>">
        <?php echo $pending_requests>0 ? $pending_requests.' need review' : 'All clear'; ?>
      </small>
    </div>
  </div>
</div>

<div class="grid-2">

<!-- RECENT ORDERS -->
<div class="panel">
  <div class="panel-header">
    <h3><i class="fas fa-shopping-bag" style="color:var(--purple);margin-right:8px;"></i> Recent Orders</h3>
    <a href="orders.php" class="btn btn-sm btn-outline-purple">View All</a>
  </div>
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead>
      <tr>
        <th>Order #</th>
        <th>Customer</th>
        <th>Amount</th>
        <th>Status</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php if($recent_orders && $recent_orders->num_rows > 0):
        while($o = $recent_orders->fetch_assoc()): ?>
      <tr>
        <td style="font-size:11px;font-weight:600;color:var(--purple);">
          <?php echo substr($o['order_number'],0,8).'...'; ?>
        </td>
        <td><?php echo htmlspecialchars($o['full_name'] ?? 'Guest'); ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($o['total']); ?></td>
        <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
        <td>
          <a href="orders.php?view=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-icon btn-outline-purple" title="View">
            <i class="fas fa-eye"></i>
          </a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="5" style="text-align:center;color:var(--gray);padding:30px;">No orders yet</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- LOW STOCK + RECENT CUSTOMERS -->
<div>
  <!-- LOW STOCK -->
  <div class="panel" style="margin-bottom:24px;">
    <div class="panel-header">
      <h3><i class="fas fa-exclamation-triangle" style="color:var(--orange);margin-right:8px;"></i> Low Stock Alert</h3>
      <a href="stock.php" class="btn btn-sm btn-outline-purple">Manage</a>
    </div>
    <div style="overflow-x:auto;">
    <table class="dash-table">
      <thead><tr><th>Product</th><th>Category</th><th>Stock</th><th></th></tr></thead>
      <tbody>
        <?php
        $low = $conn->query("SELECT p.*,c.cat_name FROM products p LEFT JOIN categories c ON p.cat_id=c.cat_id WHERE p.stock<5 ORDER BY p.stock ASC LIMIT 5");
        if($low && $low->num_rows > 0):
          while($p = $low->fetch_assoc()): ?>
        <tr>
          <td><?php echo htmlspecialchars($p['product_name']); ?></td>
          <td style="color:var(--gray);"><?php echo htmlspecialchars($p['cat_name']??''); ?></td>
          <td>
            <span style="font-weight:700;color:<?php echo $p['stock']==0?'var(--red)':'var(--orange)'; ?>;">
              <?php echo $p['stock']; ?>
            </span>
          </td>
          <td>
            <a href="products.php?edit=<?php echo $p['product_id']; ?>" class="btn btn-sm btn-icon btn-outline-purple">
              <i class="fas fa-edit"></i>
            </a>
          </td>
        </tr>
        <?php endwhile; else: ?>
        <tr><td colspan="4" style="text-align:center;color:var(--gray);padding:24px;">All stock levels OK</td></tr>
        <?php endif; ?>
      </tbody>
    </table>
    </div>
  </div>

  <!-- RECENT CUSTOMERS -->
  <div class="panel">
    <div class="panel-header">
      <h3><i class="fas fa-user-plus" style="color:var(--blue);margin-right:8px;"></i> New Customers</h3>
      <a href="customers.php" class="btn btn-sm btn-outline-purple">View All</a>
    </div>
    <?php if($recent_customers && $recent_customers->num_rows > 0):
      while($c = $recent_customers->fetch_assoc()): ?>
    <div style="display:flex;align-items:center;gap:14px;padding:12px 20px;border-bottom:1px solid #f5f5f5;">
      <div style="width:36px;height:36px;background:var(--purple-light);border-radius:50%;
                  display:flex;align-items:center;justify-content:center;font-size:13px;
                  font-weight:600;color:var(--purple);flex-shrink:0;">
        <?php echo strtoupper(substr($c['full_name'],0,1)); ?>
      </div>
      <div style="flex:1;">
        <p style="font-size:13px;"><?php echo htmlspecialchars($c['full_name']); ?></p>
        <p style="font-size:11px;color:var(--gray);"><?php echo htmlspecialchars($c['email']); ?></p>
      </div>
      <span style="font-size:11px;color:var(--gray);"><?php echo date('d M', strtotime($c['created_at'])); ?></span>
    </div>
    <?php endwhile; else: ?>
    <p style="text-align:center;padding:24px;color:var(--gray);font-size:13px;">No customers yet</p>
    <?php endif; ?>
  </div>
</div>

</div><!-- grid-2 -->

<?php require_once 'includes/footer.php'; ?>