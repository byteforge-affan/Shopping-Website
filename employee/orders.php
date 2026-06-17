<?php
$page_title = 'Manage Orders';
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
$delivery_map = [1=>'Credit Card', 2=>'Cheque', 3=>'Cash on Delivery'];

// Update status
if($_SERVER['REQUEST_METHOD']==='POST'&&isset($_POST['update_status'])) {
    $oid    = (int)$_POST['order_id'];
    $status = clean($conn, $_POST['status']);
    $allowed= ['confirmed','dispatched','delivered','cancelled'];
    if(in_array($status,$allowed)) {
        $conn->query("UPDATE orders SET status='$status' WHERE order_id=$oid");
        $msg = 'Order status updated!';
    }
}

// View single order
$view_order=null; $view_items=[];
if(isset($_GET['view'])) {
    $vid=(int)$_GET['view'];
    $vr=$conn->query("SELECT o.*,c.full_name,c.email,c.phone FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id WHERE o.order_id=$vid");
    if($vr) $view_order=$vr->fetch_assoc();
    $ir=$conn->query("SELECT oi.*,p.product_name,p.product_image FROM order_items oi LEFT JOIN products p ON oi.product_id=p.product_id WHERE oi.order_id=$vid");
    if($ir) while($row=$ir->fetch_assoc()) $view_items[]=$row;
}

// Filter
$sf = clean($conn, $_GET['status']??'');
$where = $sf ? "WHERE o.status='$sf'" : "WHERE 1=1";
$page=max(1,(int)($_GET['page']??1)); $per=12; $off=($page-1)*$per;
$total=$conn->query("SELECT COUNT(*) c FROM orders o $where")->fetch_assoc()['c'];
$pages=ceil($total/$per);
$orders=$conn->query("SELECT o.*,c.full_name,c.phone FROM orders o LEFT JOIN customers c ON o.customer_id=c.customer_id $where ORDER BY o.created_at DESC LIMIT $per OFFSET $off");
?>

<?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>

<?php if($view_order): ?>
<div style="margin-bottom:20px;"><a href="orders.php" class="btn btn-sm btn-outline-purple"><i class="fas fa-arrow-left"></i> Back</a></div>
<div class="grid-2">
  <div class="panel">
    <div class="panel-header">
      <h3>Order: <?php echo $view_order['order_number']; ?></h3>
      <span class="badge badge-<?php echo $view_order['status']; ?>"><?php echo ucfirst($view_order['status']); ?></span>
    </div>
    <div class="panel-body">
      <table style="width:100%;font-size:13px;border-collapse:collapse;">
        <?php foreach([
          ['Customer',      htmlspecialchars($view_order['full_name'])],
          ['Phone',         htmlspecialchars($view_order['phone'])],
          ['Email',         htmlspecialchars($view_order['email'])],
          ['Payment',       $delivery_map[$view_order['delivery_type']]??'N/A'],
          ['Address',       htmlspecialchars($view_order['address'].', '.$view_order['city'])],
          ['Notes',         htmlspecialchars($view_order['notes']?:'—')],
          ['Total',         '<strong style="color:var(--purple);">Rs. '.number_format($view_order['total']).'</strong>'],
          ['Date',          date('d M Y, h:i A',strtotime($view_order['created_at']))],
        ] as $row): ?>
        <tr>
          <td style="padding:9px 0;color:var(--gray);width:120px;"><?php echo $row[0]; ?></td>
          <td style="padding:9px 0;"><?php echo $row[1]; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <!-- Employee can update to dispatched or delivered -->
      <form method="POST" style="margin-top:20px;display:flex;gap:12px;align-items:flex-end;">
        <input type="hidden" name="order_id" value="<?php echo $view_order['order_id']; ?>"/>
        <div class="form-group" style="margin:0;flex:1;">
          <label>Update Status</label>
          <select name="status" class="form-control">
            <?php foreach(['confirmed','dispatched','delivered','cancelled'] as $s): ?>
              <option value="<?php echo $s; ?>" <?php echo $s==$view_order['status']?'selected':''; ?>><?php echo ucfirst($s); ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <button type="submit" name="update_status" class="btn btn-green">Update</button>
      </form>
    </div>
  </div>
  <div class="panel">
    <div class="panel-header"><h3>Order Items</h3></div>
    <table class="dash-table">
      <thead><tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead>
      <tbody>
        <?php foreach($view_items as $it): ?>
        <tr>
          <td style="display:flex;align-items:center;gap:10px;">
           <img src="<?php echo htmlspecialchars(orderProdImg($it['product_image']??'', $base_url)); ?>"
     class="product-thumb-sm"
     onerror="this.onerror=null;this.src='../images/placeholder.jpg'"/>
            <?php echo htmlspecialchars($it['product_name']); ?>
          </td>
          <td><?php echo $it['qty']; ?></td>
          <td>Rs. <?php echo number_format($it['price']); ?></td>
          <td style="font-weight:600;">Rs. <?php echo number_format($it['price']*$it['qty']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<div class="panel">
  <div class="panel-header">
    <h3>All Orders (<?php echo $total; ?>)</h3>
    <div style="display:flex;gap:6px;flex-wrap:wrap;">
      <a href="orders.php" class="btn btn-sm <?php echo !$sf?'btn-purple':'btn-outline-purple'; ?>">All</a>
      <?php foreach(['confirmed','dispatched','delivered','cancelled'] as $s): ?>
        <a href="orders.php?status=<?php echo $s; ?>" class="btn btn-sm <?php echo $sf==$s?'btn-purple':'btn-outline-purple'; ?>"><?php echo ucfirst($s); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead><tr><th>Order #</th><th>Customer</th><th>Phone</th><th>Payment</th><th>Total</th><th>Date</th><th>Status</th><th>View</th></tr></thead>
    <tbody>
      <?php if($orders&&$orders->num_rows>0): while($o=$orders->fetch_assoc()): ?>
      <tr>
        <td style="font-size:11px;font-weight:600;color:var(--purple);"><?php echo $o['order_number']; ?></td>
        <td><?php echo htmlspecialchars($o['full_name']??''); ?></td>
        <td style="font-size:12px;"><?php echo htmlspecialchars($o['phone']??''); ?></td>
        <td style="font-size:12px;"><?php echo $delivery_map[$o['delivery_type']]??''; ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($o['total']); ?></td>
        <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y',strtotime($o['created_at'])); ?></td>
        <td><span class="badge badge-<?php echo $o['status']; ?>"><?php echo ucfirst($o['status']); ?></span></td>
        <td><a href="orders.php?view=<?php echo $o['order_id']; ?>" class="btn btn-sm btn-icon btn-outline-purple"><i class="fas fa-eye"></i></a></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="8" style="text-align:center;padding:30px;color:var(--gray);">No orders found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
</div>
<?php endif; ?>

<?php
echo '</div></div></div>';
echo '<script>document.querySelectorAll(".confirm-delete").forEach(function(el){el.addEventListener("click",function(e){if(!confirm("Sure?"))e.preventDefault();});});</script>';
echo '</body></html>';
?>
