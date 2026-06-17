<?php
$page_title = 'Orders';
require_once 'includes/auth.php';
// Fix image base URL (same logic as products.php)
$subfolder = trim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(__DIR__))
), '/');
$base_url = '/' . $subfolder . '/';

function orderProdImg($path, $base_url) {
    if(empty($path)) return '../images/placeholder.jpg';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

$msg = '';

// Update order status
if(isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = clean($conn, $_POST['status']);
    $allowed = ['pending','confirmed','dispatched','delivered','cancelled'];
    if(in_array($status, $allowed)) {
        $conn->query("UPDATE orders SET status='$status' WHERE order_id=$oid");
        $msg = 'Order status updated successfully.';
    }
}

// Filter
$status_filter = isset($_GET['status']) ? clean($conn, $_GET['status']) : '';
$where = $status_filter ? "WHERE o.status='$status_filter'" : "WHERE 1=1";

// Pagination
$page = max(1, (int)($_GET['page'] ?? 1));
$per  = 15;
$off  = ($page - 1) * $per;

$total_rows = $conn->query("SELECT COUNT(*) c FROM orders o $where")->fetch_assoc()['c'];
$pages      = ceil($total_rows / $per);

$orders = $conn->query("SELECT o.*, c.full_name, c.phone FROM orders o
                         LEFT JOIN customers c ON o.customer_id=c.customer_id
                         $where ORDER BY o.created_at DESC LIMIT $per OFFSET $off");

$delivery_map = [1=>'Credit Card', 2=>'Cheque', 3=>'Cash on Delivery'];
$all_statuses = ['pending','confirmed','dispatched','delivered','cancelled'];

// View single order
$view_order = null;
$view_items = [];
if(isset($_GET['view'])) {
    $vid = (int)$_GET['view'];
    $vr  = $conn->query("SELECT o.*, c.full_name, c.email, c.phone FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id WHERE o.order_id=$vid");
    if($vr) $view_order = $vr->fetch_assoc();
    $ir = $conn->query("SELECT oi.*, p.product_name, p.product_image, p.product_code FROM order_items oi LEFT JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=$vid");
    if($ir) while($row = $ir->fetch_assoc()) $view_items[] = $row;
}
?>

<?php if($msg): ?>
  <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<?php if($view_order): ?>
<!-- ===== ORDER DETAIL VIEW ===== -->
<div style="margin-bottom:20px;">
  <a href="orders.php" class="btn btn-sm btn-outline-purple"><i class="fas fa-arrow-left"></i> Back to Orders</a>
</div>

<div class="grid-2">
  <div class="panel">
    <div class="panel-header">
      <h3>Order Details</h3>
      <span class="badge badge-<?php echo $view_order['status']; ?>"><?php echo ucfirst($view_order['status']); ?></span>
    </div>
    <div class="panel-body">
      <table style="width:100%;font-size:13px;border-collapse:collapse;">
        <?php foreach([
          ['Order Number',  '<strong style="color:var(--purple);font-size:15px;">'.$view_order['order_number'].'</strong>'],
          ['Date',          date('d M Y, h:i A', strtotime($view_order['created_at']))],
          ['Customer',      htmlspecialchars($view_order['full_name'])],
          ['Phone',         htmlspecialchars($view_order['phone'])],
          ['Email',         htmlspecialchars($view_order['email'])],
          ['Delivery Type', $delivery_map[$view_order['delivery_type']] ?? 'N/A'],
          ['Address',       htmlspecialchars($view_order['address'].', '.$view_order['city'])],
          ['Notes',         htmlspecialchars($view_order['notes'] ?: '—')],
          ['Subtotal',      'Rs. '.number_format($view_order['subtotal'])],
          ['Shipping',      $view_order['shipping']==0 ? 'FREE' : 'Rs. '.number_format($view_order['shipping'])],
          ['Total',         '<strong style="color:var(--purple);font-size:15px;">Rs. '.number_format($view_order['total']).'</strong>'],
        ] as $row): ?>
        <tr>
          <td style="padding:9px 0;color:var(--gray);width:140px;"><?php echo $row[0]; ?></td>
          <td style="padding:9px 0;"><?php echo $row[1]; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>

      <!-- STATUS UPDATE -->
      <form method="POST" style="margin-top:24px;display:flex;gap:12px;align-items:flex-end;">
        <input type="hidden" name="order_id" value="<?php echo $view_order['order_id']; ?>"/>
        <div class="form-group" style="margin:0;flex:1;">
          <label>Update Status</label>
          <select name="status" class="form-control">
            <?php foreach($all_statuses as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $s==$view_order['status']?'selected':''; ?>>
                <?php echo ucfirst($s); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" name="update_status" class="btn btn-purple">Update</button>
      </form>
    </div>
  </div>

  <div class="panel">
  <div class="panel-header"><h3>Order Items</h3></div>
  <table class="dash-table">
    <thead>
      <tr>
        <th>Product</th>
        <th>Code</th>
        <th>Qty</th>
        <th>Price</th>
        <th>Total</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($view_items as $it): ?>
      <tr>
        <td style="display:flex;align-items:center;gap:10px;">
          <img src="<?php echo htmlspecialchars(orderProdImg($it['product_image']??'', $base_url)); ?>"
               class="product-thumb-sm"
               onerror="this.onerror=null;this.src='../images/placeholder.jpg'"/>
          <?php echo htmlspecialchars($it['product_name']); ?>
        </td>
        <td style="font-size:12px;color:var(--gray);font-weight:600;">
          <?php echo htmlspecialchars($it['product_code'] ?? '—'); ?>
        </td>
        <td><?php echo $it['qty']; ?></td>
        <td>Rs. <?php echo number_format($it['price']); ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($it['price'] * $it['qty']); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<?php else: ?>
<!-- ===== ORDERS LIST ===== -->
<div class="panel">
  <div class="panel-header">
    <h3>All Orders (<?php echo $total_rows; ?>)</h3>
    <!-- Status filter tabs -->
    <div style="display:flex;gap:6px;">
      <a href="orders.php" class="btn btn-sm <?php echo !$status_filter?'btn-purple':'btn-outline-purple'; ?>">All</a>
      <?php foreach($all_statuses as $s): ?>
        <a href="orders.php?status=<?php echo $s; ?>"
           class="btn btn-sm <?php echo $status_filter==$s?'btn-purple':'btn-outline-purple'; ?>">
          <?php echo ucfirst($s); ?>
        </a>
      <?php endforeach; ?>
    </div>
  </div>

  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Order Number</th>
        <th>Customer</th>
        <th>Payment</th>
        <th>Total</th>
        <th>Date</th>
        <th>Status</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
           <?php if($orders && $orders->num_rows > 0):
    $i = $off + 1;
    while($o = $orders->fetch_assoc()): ?>
    
<tr>
  <td style="color:var(--gray);"><?php echo $i++; ?></td>
  <td style="font-weight:600;color:var(--purple);font-size:12px;"><?php echo $o['order_number']; ?></td>
  <td>
    <?php echo htmlspecialchars($o['full_name'] ?? 'Guest'); ?><br/>
    <span style="font-size:11px;color:var(--gray);"><?php echo htmlspecialchars($o['phone'] ?? ''); ?></span>
  </td>
  <td><?php echo $delivery_map[$o['delivery_type']] ?? 'N/A'; ?></td>
  <td style="font-weight:600;">Rs. <?php echo number_format($o['total']); ?></td>
  <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y', strtotime($o['created_at'])); ?></td>
  <td>
    <span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span>
  </td>
  <td>
    <a href="orders.php?view=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-outline-purple">
      <i class="fas fa-eye"></i>
    </a>
  </td>
</tr>

<?php endwhile; ?>

<?php else: ?>
<tr>
  <td colspan="8" style="text-align:center;padding:40px;color:var(--gray);">
    No orders found
  </td>
</tr>
<?php endif; ?>
    </tbody>
  </table>
  </div>

  <!-- PAGINATION -->
  <?php if($pages > 1): ?>
  <div style="padding:16px 20px;" class="pagination">
    <?php for($p=1;$p<=$pages;$p++): ?>
      <a href="?page=<?php echo $p; ?>&status=<?php echo $status_filter; ?>"
         class="<?php echo $p==$page?'active':''; ?>"><?php echo $p; ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
