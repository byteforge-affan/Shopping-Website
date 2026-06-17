<?php
$page_title = 'Dashboard';
require_once 'includes/auth.php';

$total_orders    = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c'];
$confirmed       = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='confirmed'")->fetch_assoc()['c'];
$dispatched      = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='dispatched'")->fetch_assoc()['c'];
$delivered       = $conn->query("SELECT COUNT(*) c FROM orders WHERE status='delivered'")->fetch_assoc()['c'];

$recent = $conn->query("SELECT o.*, c.full_name, c.phone FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id ORDER BY o.created_at DESC LIMIT 10");
$delivery_map = [1=>'Credit Card', 2=>'Cheque', 3=>'Cash on Delivery'];
?>

<div class="stat-grid">
  <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-list"></i></div><div class="stat-info"><p>Total Orders</p><h3><?php echo $total_orders; ?></h3></div></div>
  <div class="stat-card"><div class="stat-icon blue"><i class="fas fa-check-double"></i></div><div class="stat-info"><p>Confirmed</p><h3><?php echo $confirmed; ?></h3><small>Ready to dispatch</small></div></div>
  <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-truck"></i></div><div class="stat-info"><p>Dispatched</p><h3><?php echo $dispatched; ?></h3></div></div>
  <div class="stat-card"><div class="stat-icon green"><i class="fas fa-check-circle"></i></div><div class="stat-info"><p>Delivered</p><h3><?php echo $delivered; ?></h3></div></div>
</div>

<div class="panel">
  <div class="panel-header">
    <h3>Recent Orders — Quick Update</h3>
    <a href="orders.php" class="btn btn-sm btn-outline-purple">View All</a>
  </div>
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Payment</th><th>Total</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
    <tbody>
      <?php if($recent&&$recent->num_rows>0): while($o=$recent->fetch_assoc()): ?>
      <tr>
        <td style="font-size:11px;font-weight:600;color:var(--purple);"><?php echo substr($o['order_number'],0,10).'...'; ?></td>
        <td><?php echo htmlspecialchars($o['full_name']??''); ?></td>
        <td style="font-size:12px;"><?php echo htmlspecialchars($o['phone']??''); ?></td>
        <td style="font-size:12px;"><?php echo $delivery_map[$o['delivery_type']]??''; ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($o['total']); ?></td>
        <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y',strtotime($o['created_at'])); ?></td>
        <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
        <td><a href="orders.php?view=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-icon btn-outline-purple"><i class="fas fa-eye"></i></a></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--gray);">No orders yet</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php
// Footer inline
echo '</div></div></div>';
echo '<script>document.querySelectorAll(".confirm-delete").forEach(function(el){el.addEventListener("click",function(e){if(!confirm("Are you sure?"))e.preventDefault();});});</script>';
echo '</body></html>';
?>
