<?php
$page_title = 'Dispatch';
require_once 'includes/auth.php';
$msg='';

// Mark as dispatched
if(isset($_GET['dispatch'])) {
    $oid=(int)$_GET['dispatch'];
    $conn->query("UPDATE orders SET status='dispatched' WHERE order_id=$oid AND status='confirmed'");
    $msg='Order marked as dispatched!';
}
// Mark as delivered
if(isset($_GET['deliver'])) {
    $oid=(int)$_GET['deliver'];
    $conn->query("UPDATE orders SET status='delivered' WHERE order_id=$oid AND status='dispatched'");
    $msg='Order marked as delivered!';
}

// Confirmed orders (ready to dispatch)
$confirmed = $conn->query("SELECT o.*,c.full_name,c.phone,c.address FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id WHERE o.status='confirmed' ORDER BY o.created_at ASC");
// Dispatched orders (out for delivery)
$dispatched = $conn->query("SELECT o.*,c.full_name,c.phone,c.address FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id WHERE o.status='dispatched' ORDER BY o.created_at ASC");
$delivery_map=[1=>'Credit Card',2=>'Cheque',3=>'Cash on Delivery'];
?>
<?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>

<!-- CONFIRMED — READY TO DISPATCH -->
<div class="panel" style="margin-bottom:24px;">
  <div class="panel-header">
    <h3><i class="fas fa-box-open" style="color:var(--orange);margin-right:8px;"></i>
      Ready to Dispatch
      <span style="background:var(--orange);color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;margin-left:8px;">
        <?php echo $confirmed ? $confirmed->num_rows : 0; ?>
      </span>
    </h3>
  </div>
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Delivery Address</th><th>Payment</th><th>Total</th><th>Date</th><th>Action</th></tr></thead>
    <tbody>
      <?php if($confirmed && $confirmed->num_rows>0): while($o=$confirmed->fetch_assoc()): ?>
      <tr>
        <td style="font-size:11px;font-weight:600;color:var(--purple);"><?php echo $o['order_number']; ?></td>
        <td><?php echo htmlspecialchars($o['full_name']??''); ?></td>
        <td><?php echo htmlspecialchars($o['phone']??''); ?></td>
        <td style="font-size:12px;max-width:200px;"><?php echo htmlspecialchars($o['address'].', '.$o['city']); ?></td>
        <td style="font-size:12px;"><?php echo $delivery_map[$o['delivery_type']]??''; ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($o['total']); ?></td>
        <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y',strtotime($o['created_at'])); ?></td>
        <td>
          <a href="dispatch.php?dispatch=<?php echo $o['order_id']; ?>"
             class="btn btn-sm btn-orange" style="background:var(--orange);color:#fff;"
             onclick="return confirm('Mark this order as dispatched?')">
            <i class="fas fa-truck"></i> Dispatch
          </a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--gray);">No orders ready for dispatch</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<!-- DISPATCHED — OUT FOR DELIVERY -->
<div class="panel">
  <div class="panel-header">
    <h3><i class="fas fa-truck" style="color:var(--blue);margin-right:8px;"></i>
      Out for Delivery
      <span style="background:var(--blue);color:#fff;font-size:11px;padding:2px 8px;border-radius:10px;margin-left:8px;">
        <?php echo $dispatched ? $dispatched->num_rows : 0; ?>
      </span>
    </h3>
  </div>
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Address</th><th>Payment</th><th>Total</th><th>Action</th></tr></thead>
    <tbody>
      <?php if($dispatched && $dispatched->num_rows>0): while($o=$dispatched->fetch_assoc()): ?>
      <tr>
        <td style="font-size:11px;font-weight:600;color:var(--purple);"><?php echo $o['order_number']; ?></td>
        <td><?php echo htmlspecialchars($o['full_name']??''); ?></td>
        <td><?php echo htmlspecialchars($o['phone']??''); ?></td>
        <td style="font-size:12px;"><?php echo htmlspecialchars($o['address'].', '.$o['city']); ?></td>
        <td style="font-size:12px;"><?php echo $delivery_map[$o['delivery_type']]??''; ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($o['total']); ?></td>
        <td>
          <a href="dispatch.php?deliver=<?php echo $o['order_id']; ?>"
             class="btn btn-sm btn-green"
             onclick="return confirm('Mark as delivered?')">
            <i class="fas fa-check-circle"></i> Delivered
          </a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="7" style="text-align:center;padding:30px;color:var(--gray);">No orders out for delivery</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>

<?php
echo '</div></div></div>';
echo '</body></html>';
?>
