<?php
session_start();
require_once 'includes/db.php';
$page_title = 'My Account';

if(!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit; }

$cid  = (int)$_SESSION['customer_id'];
$cust = $conn->query("SELECT * FROM customers WHERE customer_id=$cid")->fetch_assoc();

// ── FIXED QUERY — no replacement_order_id column needed ──
$orders_res = $conn->query("SELECT * FROM orders WHERE customer_id=$cid ORDER BY created_at DESC");
$orders = [];
if($orders_res) while($row = $orders_res->fetch_assoc()) $orders[] = $row;

// ── Requests (only if table exists) ──
$my_requests = [];
$tbl_check = $conn->query("SHOW TABLES LIKE 'order_requests'");
if($tbl_check && $tbl_check->num_rows > 0) {
    $req_res = $conn->query("SELECT * FROM order_requests WHERE customer_id=$cid ORDER BY created_at DESC");
    if($req_res) while($r = $req_res->fetch_assoc()) $my_requests[$r['order_id']] = $r;
}

$action_msg = $action_err = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action   = $_POST['action']   ?? '';
    $order_id = (int)($_POST['order_id'] ?? 0);
    $chk      = $conn->query("SELECT * FROM orders WHERE order_id=$order_id AND customer_id=$cid");
    $order_row= $chk ? $chk->fetch_assoc() : null;

    if(!$order_row) {
        $action_err = 'Invalid order.';

    } elseif($action === 'cancel') {
        if(!in_array($order_row['status'], ['pending','confirmed'])) {
            $action_err = 'This order cannot be cancelled — it has already been dispatched.';
        } else {
            $items_res = $conn->query("SELECT product_id, qty FROM order_items WHERE order_id=$order_id");
            if($items_res) while($item = $items_res->fetch_assoc())
                $conn->query("UPDATE products SET stock = stock + {$item['qty']} WHERE product_id={$item['product_id']}");
            $conn->query("UPDATE orders SET status='cancelled', updated_at=NOW() WHERE order_id=$order_id");
            $action_msg = 'Order cancelled successfully.';
        }

    } elseif(in_array($action, ['replace','return'])) {
        if($order_row['status'] !== 'delivered') {
            $action_err = 'Replace/Return is only available for delivered orders.';
        } else {
            $diff_days = (time() - strtotime($order_row['updated_at'] ?? $order_row['created_at'])) / 86400;
            if($diff_days > 7) {
                $action_err = 'The 7-day return/replace window has expired.';
            } else {
                if($tbl_check && $tbl_check->num_rows > 0) {
                    $ex = $conn->query("SELECT request_id FROM order_requests WHERE order_id=$order_id AND customer_id=$cid AND status='pending'");
                    if($ex && $ex->num_rows > 0) {
                        $action_err = 'A request for this order is already pending.';
                    } else {
                        $reason = clean($conn, $_POST['reason'] ?? '');
                        $type   = $action;
                        $stmt   = $conn->prepare("INSERT INTO order_requests (order_id,customer_id,type,reason,status,created_at) VALUES (?,?,?,?,'pending',NOW())");
                        $stmt->bind_param("iiss", $order_id, $cid, $type, $reason);
                        $stmt->execute()
                            ? $action_msg = "Your ".($type==='replace'?'replacement':'return & refund')." request has been submitted!"
                            : $action_err = 'Could not submit request. Please try again.';
                    }
                } else {
                    $action_err = 'Request system not available yet. Please contact us via the Contact page.';
                }
            }
        }
    }

    // Reload after action
    $orders_res = $conn->query("SELECT * FROM orders WHERE customer_id=$cid ORDER BY created_at DESC");
    $orders = [];
    if($orders_res) while($row = $orders_res->fetch_assoc()) $orders[] = $row;

    if($tbl_check && $tbl_check->num_rows > 0) {
        $req_res = $conn->query("SELECT * FROM order_requests WHERE customer_id=$cid ORDER BY created_at DESC");
        $my_requests = [];
        if($req_res) while($r = $req_res->fetch_assoc()) $my_requests[$r['order_id']] = $r;
    }
}

$total_orders     = count($orders);
$delivered_orders = count(array_filter($orders, fn($o) => $o['status']==='delivered'));
$pending_orders   = count(array_filter($orders, fn($o) => in_array($o['status'],['pending','confirmed','dispatched'])));
$wl_res   = $conn->query("SELECT COUNT(*) c FROM wishlist WHERE customer_id=$cid");
$wl_count = $wl_res ? $wl_res->fetch_assoc()['c'] : 0;

$delivery_labels = [1=>'Credit Card', 2=>'Cheque', 3=>'Cash on Delivery'];
$status_meta = [
    'pending'    => ['#FFF8E1','#F59E0B','clock'],
    'confirmed'  => ['#EFF6FF','#3B82F6','check-double'],
    'dispatched' => ['#FFF7ED','#F97316','truck'],
    'delivered'  => ['#F0FDF4','#22C55E','check-circle'],
    'cancelled'  => ['#FEF2F2','#EF4444','times-circle'],
];

$flash_msg = $_GET['msg']    ?? '';
$flash_err = $_GET['error']  ?? '';
$pw_msg    = $_GET['pw_msg'] ?? '';
$pw_type   = $_GET['pw_type']?? '';
$tab       = $_GET['tab']    ?? 'orders';
?>
<?php include 'includes/header.php'; ?>

<style>
:root{
  --brand:#7B5EA7;--brand-lt:#F5F0FF;--brand-dk:#5a4080;
  --surface:#F8F8FC;--border:#EBEBF0;--text:#1A1A2E;--muted:#8A8A9A;
  --radius:12px;--shadow:0 2px 20px rgba(123,94,167,.08);
}
.acc-page{background:var(--surface);min-height:70vh;padding:0 0 60px;}
.acc-hero{background:linear-gradient(135deg,#1e1030 0%,#3d1f6b 50%,#6b42c8 100%);padding:40px 60px 100px;position:relative;overflow:hidden;}
.acc-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:40px 40px;}
.blob-acc{position:absolute;border-radius:50%;filter:blur(70px);opacity:.25;}
.blob-acc-1{width:300px;height:300px;background:#a855f7;top:-80px;right:10%;animation:blobDrift 8s ease-in-out infinite;}
.blob-acc-2{width:200px;height:200px;background:#ec4899;bottom:-40px;right:30%;animation:blobDrift 10s ease-in-out infinite reverse;}
@keyframes blobDrift{0%,100%{transform:translate(0,0);}50%{transform:translate(20px,-20px);}}
.acc-hero-inner{position:relative;z-index:2;display:flex;align-items:center;gap:24px;}
.acc-hero-avatar{width:72px;height:72px;border-radius:50%;background:rgba(255,255,255,.15);border:2px solid rgba(255,255,255,.4);display:flex;align-items:center;justify-content:center;font-family:'Playfair Display',serif;font-size:28px;color:#fff;font-weight:700;flex-shrink:0;}
.acc-hero-name{font-family:'Playfair Display',serif;font-size:26px;color:#fff;font-weight:700;margin-bottom:4px;}
.acc-hero-email{font-size:13px;color:rgba(255,255,255,.6);}
.acc-hero-since{font-size:11px;color:rgba(255,255,255,.4);margin-top:4px;letter-spacing:.08em;text-transform:uppercase;}
.acc-stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;max-width:1100px;margin:-52px auto 0;padding:0 40px;position:relative;z-index:3;}
.acc-stat{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:20px 22px;box-shadow:var(--shadow);display:flex;align-items:center;gap:14px;transition:box-shadow .25s,transform .25s;}
.acc-stat:hover{box-shadow:0 6px 28px rgba(123,94,167,.14);transform:translateY(-2px);}
.acc-stat-icon{width:44px;height:44px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:18px;flex-shrink:0;}
.acc-stat-num{font-size:22px;font-weight:700;color:var(--text);line-height:1;margin-bottom:3px;}
.acc-stat-lbl{font-size:11px;color:var(--muted);letter-spacing:.06em;text-transform:uppercase;}
.acc-body{max-width:1100px;margin:32px auto 0;padding:0 40px;display:grid;grid-template-columns:230px 1fr;gap:28px;align-items:start;}
.acc-sidebar{background:#fff;border:1px solid var(--border);border-radius:var(--radius);overflow:hidden;box-shadow:var(--shadow);position:sticky;top:88px;}
.acc-nav a{display:flex;align-items:center;gap:12px;padding:14px 20px;font-size:13px;color:#555;transition:all .2s;border-left:3px solid transparent;text-decoration:none;}
.acc-nav a:hover,.acc-nav a.active{color:var(--brand);background:var(--brand-lt);border-left-color:var(--brand);}
.acc-nav a i{width:16px;font-size:13px;color:var(--brand);}
.acc-nav-div{height:1px;background:var(--border);margin:4px 12px;}
.acc-panel{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:28px;box-shadow:var(--shadow);margin-bottom:20px;}
.acc-panel-title{font-family:'Playfair Display',serif;font-size:22px;color:var(--text);margin-bottom:22px;padding-bottom:14px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
.acc-panel-title i{font-size:16px;color:var(--brand);}
.acc-alert{display:flex;align-items:center;gap:10px;padding:12px 18px;border-radius:8px;font-size:13px;margin-bottom:18px;}
.acc-alert.ok{background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;}
.acc-alert.er{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;}
.ord-card{border:1px solid var(--border);border-radius:10px;overflow:hidden;margin-bottom:14px;transition:box-shadow .2s;}
.ord-card:hover{box-shadow:var(--shadow);}
.ord-head{display:flex;justify-content:space-between;align-items:center;padding:14px 18px;background:#FAFAFC;flex-wrap:wrap;gap:10px;border-bottom:1px solid var(--border);}
.ord-num-lbl{font-size:9px;color:var(--muted);letter-spacing:.12em;text-transform:uppercase;margin-bottom:3px;}
.ord-num{font-family:'Playfair Display',serif;font-size:16px;font-weight:700;color:var(--brand);}
.ord-pill{display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:11px;font-weight:600;letter-spacing:.05em;text-transform:uppercase;}
.ord-dot{width:6px;height:6px;border-radius:50%;flex-shrink:0;}
.ord-body{padding:14px 18px;display:grid;grid-template-columns:1fr 1fr 1fr auto;gap:14px;align-items:center;}
.ord-meta-lbl{font-size:10px;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:4px;}
.ord-meta-val{font-size:13px;color:var(--text);font-weight:500;}
.ab{display:inline-flex;align-items:center;gap:5px;padding:6px 13px;border-radius:6px;font-size:11px;font-weight:600;cursor:pointer;background:none;font-family:inherit;transition:all .2s;border:1.5px solid;text-decoration:none;}
.ab-cancel{border-color:#EF4444;color:#EF4444;}.ab-cancel:hover{background:#EF4444;color:#fff;}
.ab-replace{border-color:var(--brand);color:var(--brand);}.ab-replace:hover{background:var(--brand);color:#fff;}
.ab-return{border-color:#F97316;color:#F97316;}.ab-return:hover{background:#F97316;color:#fff;}
.ab-track{border-color:#3B82F6;color:#3B82F6;}.ab-track:hover{background:#3B82F6;color:#fff;}
.ab-shop{border-color:#16A34A;color:#16A34A;}.ab-shop:hover{background:#16A34A;color:#fff;}
.days-chip{display:inline-flex;align-items:center;gap:4px;padding:3px 9px;border-radius:20px;font-size:10px;font-weight:600;}
.dc-ok{background:#F0FDF4;color:#16A34A;border:1px solid #BBF7D0;}
.dc-warn{background:#FFFBEB;color:#D97706;border:1px solid #FDE68A;}
.rbadge{display:inline-flex;align-items:center;gap:5px;padding:4px 11px;border-radius:6px;font-size:11px;font-weight:600;border:1px solid;}
.rb-pend{background:#FFF8E1;color:#92400E;border-color:#FDE68A;}
.rb-apr-ret{background:#F0FDF4;color:#15803D;border-color:#BBF7D0;}
.rb-apr-rep{background:#EDE9FF;color:#5B21B6;border-color:#DDD6FE;}
.rb-rej{background:#FEF2F2;color:#B91C1C;border-color:#FECACA;}
.res-box{border-radius:10px;padding:18px 20px;display:flex;align-items:flex-start;gap:14px;}
.res-box.res-approve-ret{background:#F0FDF4;border:1px solid #BBF7D0;}
.res-box.res-approve-rep{background:#EDE9FF;border:1px solid #DDD6FE;}
.res-box.res-reject{background:#FEF2F2;border:1px solid #FECACA;}
.res-icon{font-size:28px;flex-shrink:0;margin-top:2px;}
.res-box h4{font-size:14px;font-weight:700;margin-bottom:5px;}
.res-box p{font-size:12px;line-height:1.7;margin-bottom:12px;}
.pf-grid{display:grid;grid-template-columns:1fr 1fr;gap:16px;}
.pf-field{display:flex;flex-direction:column;gap:6px;}
.pf-field.full{grid-column:1/-1;}
.pf-field label{font-size:11px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.06em;}
.pf-field input,.pf-field select{padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;font-family:inherit;outline:none;transition:border-color .2s;background:#fff;}
.pf-field input:focus,.pf-field select:focus{border-color:var(--brand);}
.btn-save{padding:11px 28px;background:var(--brand);color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .2s;margin-top:6px;}
.btn-save:hover{background:var(--brand-dk);}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:9999;align-items:center;justify-content:center;}
.modal-overlay.open{display:flex;}
.modal-box{background:#fff;border-radius:var(--radius);padding:30px;width:100%;max-width:420px;box-shadow:0 20px 60px rgba(0,0,0,.2);animation:mIn .2s ease;}
@keyframes mIn{from{opacity:0;transform:translateY(-14px)}to{opacity:1;transform:translateY(0)}}
.modal-box h3{font-family:'Playfair Display',serif;font-size:20px;margin-bottom:8px;}
.modal-box p{font-size:13px;color:var(--muted);margin-bottom:18px;}
.modal-foot{display:flex;gap:10px;justify-content:flex-end;margin-top:18px;}
.btn-ghost{padding:10px 20px;background:none;color:var(--muted);border:1.5px solid var(--border);border-radius:8px;font-size:13px;cursor:pointer;font-family:inherit;}
.btn-ghost:hover{border-color:#aaa;color:#444;}
@media(max-width:900px){
  .acc-hero{padding:30px 24px 80px;}
  .acc-stats-row{grid-template-columns:1fr 1fr;padding:0 20px;}
  .acc-body{grid-template-columns:1fr;padding:0 20px;}
  .acc-sidebar{position:static;}
  .ord-body{grid-template-columns:1fr 1fr;}
  .pf-grid{grid-template-columns:1fr;}
}
@media(max-width:576px){
  .acc-stats-row{grid-template-columns:1fr 1fr;gap:10px;padding:0 12px;}
  .ord-body{grid-template-columns:1fr;}
  .acc-panel{padding:20px 16px;}
}
</style>

<div class="acc-page">

<!-- HERO -->
<div class="acc-hero">
  <div class="blob-acc blob-acc-1"></div>
  <div class="blob-acc blob-acc-2"></div>
  <div class="acc-hero-inner">
    <div class="acc-hero-avatar"><?php echo strtoupper(substr($cust['full_name']??'U',0,1)); ?></div>
    <div>
      <div class="acc-hero-name"><?php echo htmlspecialchars($cust['full_name']??''); ?></div>
      <div class="acc-hero-email"><?php echo htmlspecialchars($cust['email']??''); ?></div>
      <div class="acc-hero-since">Member since <?php echo date('M Y', strtotime($cust['created_at']??'now')); ?></div>
    </div>
  </div>
</div>

<!-- STAT CARDS -->
<div class="acc-stats-row">
  <?php foreach([
    ['#F5F0FF','#7B5EA7','shopping-bag',$total_orders,    'Total Orders'],
    ['#F0FDF4','#22C55E','check-circle', $delivered_orders,'Delivered'],
    ['#FFF7ED','#F97316','clock',        $pending_orders,  'In Progress'],
    ['#FFF8E1','#F59E0B','heart',        $wl_count,        'Wishlist'],
  ] as $s): ?>
  <div class="acc-stat">
    <div class="acc-stat-icon" style="background:<?php echo $s[0]; ?>;color:<?php echo $s[1]; ?>;">
      <i class="fas fa-<?php echo $s[2]; ?>"></i>
    </div>
    <div>
      <div class="acc-stat-num"><?php echo $s[3]; ?></div>
      <div class="acc-stat-lbl"><?php echo $s[4]; ?></div>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<!-- BODY -->
<div class="acc-body">

  <!-- SIDEBAR -->
  <aside class="acc-sidebar">
    <nav class="acc-nav">
      <a href="?tab=orders"  class="<?php echo $tab==='orders' ?'active':''; ?>"><i class="fas fa-box-open"></i> My Orders</a>
      <a href="?tab=profile" class="<?php echo $tab==='profile'?'active':''; ?>"><i class="fas fa-user-edit"></i> Edit Profile</a>
      <div class="acc-nav-div"></div>
      <a href="wishlist.php"><i class="fas fa-heart"></i> Wishlist
        <?php if($wl_count>0): ?>
          <span style="margin-left:auto;background:var(--brand);color:#fff;font-size:10px;padding:1px 7px;border-radius:20px;"><?php echo $wl_count; ?></span>
        <?php endif; ?>
      </a>
      <a href="track-order.php"><i class="fas fa-map-marker-alt"></i> Track Order</a>
      <a href="change-password.php"><i class="fas fa-lock"></i> Change Password</a>
      <div class="acc-nav-div"></div>
      <a href="logout.php" style="color:#EF4444;"><i class="fas fa-sign-out-alt" style="color:#EF4444;"></i> Logout</a>
    </nav>
  </aside>

  <!-- MAIN -->
  <main>
    <?php
    $show_ok = $action_msg ?: ($flash_msg && $flash_msg!='1' ? $flash_msg : ($flash_msg=='1'?'Profile updated!':''));
    $show_er = $action_err ?: $flash_err;
    ?>
    <?php if($show_ok): ?><div class="acc-alert ok"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($show_ok); ?></div><?php endif; ?>
    <?php if($show_er): ?><div class="acc-alert er"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($show_er); ?></div><?php endif; ?>
    <?php if($pw_msg):  ?><div class="acc-alert <?php echo $pw_type==='success'?'ok':'er'; ?>"><i class="fas fa-lock"></i> <?php echo htmlspecialchars($pw_msg); ?></div><?php endif; ?>

    <!-- ORDERS TAB -->
    <?php if($tab==='orders'): ?>
    <div class="acc-panel">
      <div class="acc-panel-title">
        <i class="fas fa-box-open"></i> My Orders
        <span style="margin-left:auto;font-size:13px;color:var(--muted);font-weight:300;"><?php echo $total_orders; ?> orders</span>
      </div>

      <?php if(empty($orders)): ?>
      <div style="text-align:center;padding:50px 0;color:var(--muted);">
        <i class="fas fa-shopping-bag" style="font-size:48px;opacity:.15;display:block;margin-bottom:16px;"></i>
        No orders yet.<br/>
        <a href="products.php" style="color:var(--brand);font-weight:600;font-size:13px;">Start shopping →</a>
      </div>
      <?php else: ?>
        <?php foreach($orders as $o):
          $sm          = $status_meta[$o['status']] ?? $status_meta['pending'];
          $can_cancel  = in_array($o['status'],['pending','confirmed']);
          $is_delivered= $o['status']==='delivered';
          $diff_days   = $is_delivered ? (time()-strtotime($o['updated_at']??$o['created_at']))/86400 : 999;
          $days_left   = max(0, 7-(int)$diff_days);
          $can_req     = $is_delivered && $diff_days<=7;
          $req         = $my_requests[$o['order_id']] ?? null;
          $has_req     = $req !== null;
          $rs          = $req['status'] ?? '';
          $rt          = $req['type']   ?? '';
        ?>
        <div class="ord-card">
          <!-- Head -->
          <div class="ord-head">
            <div>
              <div class="ord-num-lbl">Order Number</div>
              <div class="ord-num"><?php echo htmlspecialchars($o['order_number']); ?></div>
            </div>
            <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
              <span class="ord-pill" style="background:<?php echo $sm[0]; ?>;color:<?php echo $sm[1]; ?>;">
                <span class="ord-dot" style="background:<?php echo $sm[1]; ?>;"></span>
                <?php echo ucfirst($o['status']); ?>
              </span>
              <?php if($can_req && !$has_req): ?>
              <span class="days-chip <?php echo $days_left<=2?'dc-warn':'dc-ok'; ?>">
                <i class="fas fa-clock"></i> <?php echo $days_left; ?>d left
              </span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Body -->
          <div class="ord-body">
            <div>
              <div class="ord-meta-lbl">Date</div>
              <div class="ord-meta-val"><?php echo date('d M Y',strtotime($o['created_at'])); ?></div>
            </div>
            <div>
              <div class="ord-meta-lbl">Payment</div>
              <div class="ord-meta-val"><?php echo $delivery_labels[$o['delivery_type']]??'N/A'; ?></div>
            </div>
            <div>
              <div class="ord-meta-lbl">Total</div>
              <div class="ord-meta-val" style="font-weight:700;color:var(--brand);">Rs. <?php echo number_format($o['total']); ?></div>
            </div>

            <!-- Actions -->
            <div style="display:flex;flex-direction:column;align-items:flex-end;gap:6px;">
              <a href="track-order.php?order_no=<?php echo urlencode($o['order_number']); ?>" class="ab ab-track">
                <i class="fas fa-map-marker-alt"></i> Track
              </a>
              <?php if($can_cancel): ?>
              <button class="ab ab-cancel" onclick="openCancel(<?php echo $o['order_id']; ?>,'<?php echo htmlspecialchars($o['order_number']); ?>')">
                <i class="fas fa-times"></i> Cancel
              </button>
              <?php elseif($can_req && !$has_req): ?>
              <button class="ab ab-replace" onclick="openReq(<?php echo $o['order_id']; ?>,'<?php echo htmlspecialchars($o['order_number']); ?>','replace')">
                <i class="fas fa-exchange-alt"></i> Replace
              </button>
              <button class="ab ab-return" onclick="openReq(<?php echo $o['order_id']; ?>,'<?php echo htmlspecialchars($o['order_number']); ?>','return')">
                <i class="fas fa-undo"></i> Return
              </button>
              <?php elseif($has_req && $rs==='pending'): ?>
              <span class="rbadge rb-pend">
                <i class="fas fa-hourglass-half"></i> <?php echo ucfirst($rt); ?> — Under Review
              </span>
              <?php elseif($has_req && $rs==='approved' && $rt==='return'): ?>
              <span class="rbadge rb-apr-ret"><i class="fas fa-check-circle"></i> Return Approved</span>
              <?php elseif($has_req && $rs==='approved' && $rt==='replace'): ?>
              <span class="rbadge rb-apr-rep"><i class="fas fa-check-circle"></i> Replace Approved</span>
              <?php elseif($has_req && $rs==='rejected'): ?>
              <span class="rbadge rb-rej"><i class="fas fa-times-circle"></i> <?php echo ucfirst($rt); ?> Rejected</span>
              <?php elseif($is_delivered && $diff_days>7): ?>
              <span style="font-size:10px;color:var(--muted);"><i class="fas fa-lock" style="margin-right:3px;"></i>Window expired</span>
              <?php endif; ?>
            </div>
          </div>

          <!-- Result box for approved/rejected -->
          <?php if($has_req && $rs !== 'pending'): ?>
          <div style="padding:0 18px 16px;">
            <?php if($rs==='approved' && $rt==='return'): ?>
            <div class="res-box res-approve-ret">
              <div class="res-icon"><i class="fas fa-hand-holding-usd" style="color:#15803D;"></i></div>
              <div>
                <h4 style="color:#15803D;">Return approved — refund being processed</h4>
                <p style="color:#166534;">Please send the product back. Your refund of <strong>Rs. <?php echo number_format($o['total']); ?></strong> will be credited within 5–7 business days.</p>
                <a href="products.php" class="ab ab-shop"><i class="fas fa-shopping-bag"></i> Continue Shopping</a>
              </div>
            </div>
            <?php elseif($rs==='approved' && $rt==='replace'): ?>
            <div class="res-box res-approve-rep">
              <div class="res-icon"><i class="fas fa-exchange-alt" style="color:#5B21B6;"></i></div>
              <div>
                <h4 style="color:#5B21B6;">Replace approved — new order being prepared</h4>
                <p style="color:#4C1D95;">Our team will arrange pickup and send your replacement shortly. Contact us for updates.</p>
              </div>
            </div>
            <?php elseif($rs==='rejected'): ?>
            <div class="res-box res-reject">
              <div class="res-icon"><i class="fas fa-times-circle" style="color:#B91C1C;"></i></div>
              <div>
                <h4 style="color:#B91C1C;"><?php echo ucfirst($rt); ?> request not approved</h4>
                <p style="color:#991B1B;">Please contact our support team via the Contact page for more information.</p>
              </div>
            </div>
            <?php endif; ?>
          </div>
          <?php endif; ?>

        </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- PROFILE TAB -->
    <?php if($tab==='profile'): ?>
    <div class="acc-panel">
      <div class="acc-panel-title"><i class="fas fa-user-edit"></i> Edit Profile</div>
      <form method="POST" action="update-profile.php">
        <div class="pf-grid">
          <div class="pf-field">
            <label>Full Name</label>
            <input type="text" name="full_name" value="<?php echo htmlspecialchars($cust['full_name']??''); ?>"/>
          </div>
          <div class="pf-field">
            <label>Phone Number</label>
            <input type="text" name="phone" value="<?php echo htmlspecialchars($cust['phone']??''); ?>"/>
          </div>
          <div class="pf-field full">
            <label>Delivery Address</label>
            <input type="text" name="address" value="<?php echo htmlspecialchars($cust['address']??''); ?>"/>
          </div>
          <div class="pf-field">
            <label>City</label>
            <select name="city">
              <option value="">Select City</option>
              <?php foreach(['Karachi','Lahore','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta'] as $c): ?>
              <option <?php echo ($cust['city']??'')===$c?'selected':''; ?>><?php echo $c; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="pf-field">
            <label>Email</label>
            <input type="email" value="<?php echo htmlspecialchars($cust['email']??''); ?>" disabled style="opacity:.6;cursor:not-allowed;"/>
          </div>
        </div>
        <button type="submit" class="btn-save">Save Changes</button>
      </form>
    </div>
    <?php endif; ?>

  </main>
</div>
</div>

<!-- CANCEL MODAL -->
<div class="modal-overlay" id="cancelModal">
  <div class="modal-box">
    <h3><i class="fas fa-times-circle" style="color:#EF4444;margin-right:8px;"></i>Cancel Order</h3>
    <p>Are you sure you want to cancel order <strong id="c_num"></strong>? Stock will be restored automatically.</p>
    <form method="POST">
      <input type="hidden" name="action" value="cancel"/>
      <input type="hidden" name="order_id" id="c_id"/>
      <div class="modal-foot">
        <button type="button" class="btn-ghost" onclick="closeModal('cancelModal')">Keep Order</button>
        <button type="submit" class="ab ab-cancel" style="padding:10px 20px;">Yes, Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- REPLACE / RETURN MODAL -->
<div class="modal-overlay" id="reqModal">
  <div class="modal-box">
    <h3 id="req_title"></h3>
    <p id="req_desc"></p>
    <form method="POST">
      <input type="hidden" name="action"   id="req_action"/>
      <input type="hidden" name="order_id" id="req_id"/>
      <div class="pf-field">
        <label>Reason (optional)</label>
        <textarea name="reason" rows="3"
                  style="padding:10px 14px;border:1.5px solid var(--border);border-radius:8px;
                         font-size:13px;font-family:inherit;outline:none;resize:vertical;"
                  placeholder="Describe the issue..."></textarea>
      </div>
      <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:10px 14px;margin-top:12px;font-size:12px;color:#92400E;line-height:1.6;">
        <i class="fas fa-info-circle" style="margin-right:5px;"></i>
        Our team will contact you within 2 business days. Product must be unused and in original packaging.
      </div>
      <div class="modal-foot">
        <button type="button" class="btn-ghost" onclick="closeModal('reqModal')">Cancel</button>
        <button type="submit" class="btn-save" id="req_btn">Submit Request</button>
      </div>
    </form>
  </div>
</div>

<script>
function openCancel(id,num){
  document.getElementById('c_id').value=id;
  document.getElementById('c_num').textContent=num;
  document.getElementById('cancelModal').classList.add('open');
}
function openReq(id,num,type){
  document.getElementById('req_id').value=id;
  document.getElementById('req_action').value=type;
  var isReturn=type==='return';
  document.getElementById('req_title').innerHTML=isReturn
    ?'<i class="fas fa-undo" style="color:#F97316;margin-right:8px;"></i>Return & Refund'
    :'<i class="fas fa-exchange-alt" style="color:#7B5EA7;margin-right:8px;"></i>Replace Product';
  document.getElementById('req_desc').textContent=
    (isReturn?'Submit a return request for order ':'Submit a replacement request for order ')+num+'.';
  document.getElementById('req_btn').textContent=isReturn?'Submit Return':'Submit Replace';
  document.getElementById('reqModal').classList.add('open');
}
function closeModal(id){ document.getElementById(id).classList.remove('open'); }
document.querySelectorAll('.modal-overlay').forEach(function(el){
  el.addEventListener('click',function(e){ if(e.target===el) el.classList.remove('open'); });
});
</script>

<?php include 'includes/footer.php'; ?>