<?php
$page_title = 'Order Requests';
require_once 'includes/auth.php';

$subfolder = trim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(__DIR__))
), '/');
$base_url = '/' . $subfolder . '/';

function reqProdImg($path, $base_url) {
    if(empty($path)) return '../images/placeholder.jpg';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

$msg = '';

// Approve / Reject action
if(isset($_POST['action'], $_POST['request_id'])) {
    $rid    = (int)$_POST['request_id'];
    $action = $_POST['action'];
    if(in_array($action, ['approved','rejected'])) {
        $conn->query("UPDATE order_requests SET status='$action', updated_at=NOW() WHERE request_id=$rid");
        $msg = 'Request ' . ucfirst($action) . ' successfully.';
    }
}

// Filter
$filter = isset($_GET['type']) ? clean($conn, $_GET['type']) : '';
$where  = $filter ? "WHERE req.type='$filter'" : "WHERE 1=1";

// Pagination
$page = max(1,(int)($_GET['page'] ?? 1));
$per  = 15;
$off  = ($page-1) * $per;

$total_r = $conn->query("SELECT COUNT(*) c FROM order_requests req $where")->fetch_assoc()['c'];
$pages   = ceil($total_r / $per);

$requests = $conn->query("
    SELECT req.*,
           o.order_number, o.total, o.status as order_status, o.created_at as order_date,
           c.full_name, c.phone, c.email
    FROM order_requests req
    LEFT JOIN orders o ON req.order_id = o.order_id
    LEFT JOIN customers c ON req.customer_id = c.customer_id
    $where
    ORDER BY req.created_at DESC
    LIMIT $per OFFSET $off
");

// View single request
$view_req   = null;
$view_items = [];
if(isset($_GET['view'])) {
    $vid = (int)$_GET['view'];
    $vr  = $conn->query("
        SELECT req.*,
               o.order_number, o.total, o.status as order_status, o.address, o.city,
               c.full_name, c.phone, c.email
        FROM order_requests req
        LEFT JOIN orders o ON req.order_id = o.order_id
        LEFT JOIN customers c ON req.customer_id = c.customer_id
        WHERE req.request_id = $vid
    ");
    if($vr) $view_req = $vr->fetch_assoc();

    if($view_req) {
        $ir = $conn->query("
            SELECT oi.*, p.product_name, p.product_image
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.product_id
            WHERE oi.order_id = {$view_req['order_id']}
        ");
        if($ir) while($row = $ir->fetch_assoc()) $view_items[] = $row;
    }
}
?>

<?php if($msg): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<?php if($view_req): ?>
<!-- ===== DETAIL VIEW ===== -->
<div style="margin-bottom:20px;">
  <a href="order_requests.php" class="btn btn-sm btn-outline-purple">
    <i class="fas fa-arrow-left"></i> Back to Requests
  </a>
</div>

<div class="grid-2">
  <div class="panel">
    <div class="panel-header">
      <h3>Request Details</h3>
      <?php
        $sc = ['pending'=>'warning','approved'=>'success','rejected'=>'danger'];
        $sc_class = $sc[$view_req['status']] ?? 'warning';
      ?>
      <span class="badge badge-<?php echo $view_req['status']; ?>"><?php echo ucfirst($view_req['status']); ?></span>
    </div>
    <div class="panel-body">
      <table style="width:100%;font-size:13px;border-collapse:collapse;">
        <?php foreach([
          ['Type',         '<span style="font-weight:700;color:'.($view_req['type']==='replace'?'#7B5EA7':'#F97316').';">'.ucfirst($view_req['type']).'</span>'],
          ['Order Number', '<strong style="color:var(--purple);">'.$view_req['order_number'].'</strong>'],
          ['Customer',     htmlspecialchars($view_req['full_name'])],
          ['Phone',        htmlspecialchars($view_req['phone'])],
          ['Email',        htmlspecialchars($view_req['email'])],
          ['Order Total',  'Rs. '.number_format($view_req['total'])],
          ['Order Status', ucfirst($view_req['order_status'])],
          ['Reason',       htmlspecialchars($view_req['reason'] ?: '— No reason given')],
          ['Submitted On', date('d M Y, h:i A', strtotime($view_req['created_at']))],
        ] as $row): ?>
        <tr>
          <td style="padding:9px 0;color:var(--gray);width:130px;"><?php echo $row[0]; ?></td>
          <td style="padding:9px 0;"><?php echo $row[1]; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>

      <?php if($view_req['status'] === 'pending'): ?>
      <div style="display:flex;gap:10px;margin-top:24px;">
        <form method="POST">
          <input type="hidden" name="request_id" value="<?php echo $view_req['request_id']; ?>"/>
          <input type="hidden" name="action" value="approved"/>
          <button type="submit" class="btn btn-green"><i class="fas fa-check"></i> Approve</button>
        </form>
        <form method="POST">
          <input type="hidden" name="request_id" value="<?php echo $view_req['request_id']; ?>"/>
          <input type="hidden" name="action" value="rejected"/>
          <button type="submit" class="btn" style="background:#EF4444;color:#fff;"><i class="fas fa-times"></i> Reject</button>
        </form>
      </div>
      <?php else: ?>
      <div style="margin-top:20px;padding:12px 16px;border-radius:8px;
                  background:<?php echo $view_req['status']==='approved'?'#F0FDF4':'#FEF2F2'; ?>;
                  color:<?php echo $view_req['status']==='approved'?'#15803D':'#B91C1C'; ?>;
                  font-size:13px;font-weight:600;">
        <i class="fas fa-<?php echo $view_req['status']==='approved'?'check-circle':'times-circle'; ?>"></i>
        This request has been <?php echo ucfirst($view_req['status']); ?>.
      </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- ORDER ITEMS WITH IMAGES -->
  <div class="panel">
    <div class="panel-header"><h3>Order Items (<?php echo $view_req['type']==='replace'?'To be Replaced':'To be Returned'; ?>)</h3></div>
    <table class="dash-table">
      <thead>
        <tr>
          <th>Image</th>
          <th>Product</th>
          <th>Qty</th>
          <th>Price</th>
          <th>Total</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($view_items as $it): ?>
        <tr>
          <td>
            <img src="<?php echo htmlspecialchars(reqProdImg($it['product_image']??'', $base_url)); ?>"
                 style="width:50px;height:50px;object-fit:cover;border-radius:6px;border:1px solid #eee;"
                 onerror="this.onerror=null;this.src='../images/placeholder.jpg'"/>
          </td>
          <td style="font-weight:600;"><?php echo htmlspecialchars($it['product_name']); ?></td>
          <td><?php echo $it['qty']; ?></td>
          <td>Rs. <?php echo number_format($it['price']); ?></td>
          <td style="font-weight:700;color:var(--purple);">Rs. <?php echo number_format($it['price']*$it['qty']); ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<?php else: ?>
<!-- ===== LIST VIEW ===== -->
<div class="panel">
  <div class="panel-header">
    <h3>Order Requests (<?php echo $total_r; ?>)</h3>
    <div style="display:flex;gap:6px;">
      <a href="order_requests.php" class="btn btn-sm <?php echo !$filter?'btn-purple':'btn-outline-purple'; ?>">All</a>
      <a href="?type=replace" class="btn btn-sm <?php echo $filter==='replace'?'btn-purple':'btn-outline-purple'; ?>">Replace</a>
      <a href="?type=return"  class="btn btn-sm <?php echo $filter==='return' ?'btn-purple':'btn-outline-purple'; ?>">Return</a>
    </div>
  </div>

  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead>
      <tr>
        <th>#</th>
        <th>Item</th>
        <th>Order #</th>
        <th>Customer</th>
        <th>Type</th>
        <th>Reason</th>
        <th>Date</th>
        <th>Status</th>
        <th>Action</th>
      </tr>
    </thead>
    <tbody>
    <?php if($requests && $requests->num_rows > 0):
      $i = $off + 1;
      while($req = $requests->fetch_assoc()):
        // Get first item image for this order
        $img_res = $conn->query("SELECT p.product_image, p.product_name
                                  FROM order_items oi
                                  JOIN products p ON p.product_id = oi.product_id
                                  WHERE oi.order_id = {$req['order_id']}
                                  LIMIT 1");
        $first_item = $img_res ? $img_res->fetch_assoc() : null;
    ?>
    <tr>
      <td style="color:var(--gray);"><?php echo $i++; ?></td>
      <td>
        <?php if($first_item): ?>
        <div style="display:flex;align-items:center;gap:8px;">
          <img src="<?php echo htmlspecialchars(reqProdImg($first_item['product_image']??'', $base_url)); ?>"
               style="width:40px;height:40px;object-fit:cover;border-radius:6px;border:1px solid #eee;flex-shrink:0;"
               onerror="this.onerror=null;this.src='../images/placeholder.jpg'"/>
          <span style="font-size:12px;color:var(--gray);"><?php echo htmlspecialchars(mb_strimwidth($first_item['product_name'],0,22,'...')); ?></span>
        </div>
        <?php else: ?>
          <span style="color:var(--gray);font-size:12px;">—</span>
        <?php endif; ?>
      </td>
      <td style="font-weight:600;color:var(--purple);font-size:12px;"><?php echo $req['order_number']; ?></td>
      <td>
        <?php echo htmlspecialchars($req['full_name']); ?><br/>
        <span style="font-size:11px;color:var(--gray);"><?php echo htmlspecialchars($req['phone']); ?></span>
      </td>
      <td>
        <span style="font-weight:700;color:<?php echo $req['type']==='replace'?'#7B5EA7':'#F97316'; ?>;">
          <?php echo ucfirst($req['type']); ?>
        </span>
      </td>
      <td style="font-size:12px;color:var(--gray);max-width:150px;">
        <?php echo htmlspecialchars(mb_strimwidth($req['reason']??'—',0,40,'...')); ?>
      </td>
      <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y', strtotime($req['created_at'])); ?></td>
      <td><span class="badge badge-<?php echo $req['status']; ?>"><?php echo ucfirst($req['status']); ?></span></td>
      <td style="display:flex;gap:5px;flex-wrap:wrap;">
        <a href="?view=<?php echo $req['request_id']; ?>" class="btn btn-sm btn-icon btn-outline-purple" title="View">
          <i class="fas fa-eye"></i>
        </a>
        <?php if($req['status']==='pending'): ?>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>"/>
          <input type="hidden" name="action" value="approved"/>
          <button class="btn btn-sm btn-icon btn-green" title="Approve"><i class="fas fa-check"></i></button>
        </form>
        <form method="POST" style="display:inline;">
          <input type="hidden" name="request_id" value="<?php echo $req['request_id']; ?>"/>
          <input type="hidden" name="action" value="rejected"/>
          <button class="btn btn-sm btn-icon" style="background:#EF4444;color:#fff;border:none;border-radius:4px;padding:5px 8px;cursor:pointer;" title="Reject">
            <i class="fas fa-times"></i>
          </button>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endwhile; else: ?>
    <tr><td colspan="9" style="text-align:center;padding:40px;color:var(--gray);">No requests found</td></tr>
    <?php endif; ?>
    </tbody>
  </table>
  </div>

  <?php if($pages > 1): ?>
  <div style="padding:16px 20px;" class="pagination">
    <?php for($p=1;$p<=$pages;$p++): ?>
      <a href="?page=<?php echo $p; ?>&type=<?php echo $filter; ?>"
         class="<?php echo $p==$page?'active':''; ?>"><?php echo $p; ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>