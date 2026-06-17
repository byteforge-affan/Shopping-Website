<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Confirm Replacement';

if(!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit; }

$cid      = (int)$_SESSION['customer_id'];
$order_id = (int)($_GET['order_id'] ?? 0);

// Original order verify karo
$orig_res = $conn->query("
    SELECT o.*, req.request_id, req.replacement_order_id
    FROM orders o
    JOIN order_requests req ON req.order_id = o.order_id
    WHERE o.order_id = $order_id
      AND o.customer_id = $cid
      AND req.type = 'replace'
      AND req.status = 'approved'
");
$orig = $orig_res ? $orig_res->fetch_assoc() : null;

if(!$orig) {
    header('Location: my-account.php?error=No+approved+replace+request+found.');
    exit;
}
if(!empty($orig['replacement_order_id'])) {
    header('Location: my-account.php?msg=Replacement+already+confirmed.');
    exit;
}

// Original order items
$items_res = $conn->query("
    SELECT oi.*, p.product_name, p.product_image
    FROM order_items oi
    JOIN products p ON p.product_id = oi.product_id
    WHERE oi.order_id = $order_id
");
$items = [];
if($items_res) while($it = $items_res->fetch_assoc()) $items[] = $it;

$err = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Same items, same price — no payment selection needed
    $first_pid  = $items[0]['product_id'] ?? 0;
    $pid7       = sprintf('%07d', $first_pid);
    $rand8      = sprintf('%08d', rand(10000000, 99999999));
    $new_order_num = ($orig['delivery_type'] ?? 3) . $pid7 . $rand8;

    $addr  = $conn->real_escape_string($orig['address']);
    $city  = $conn->real_escape_string($orig['city']);
    $notes = $conn->real_escape_string('REPLACEMENT for order #' . $orig['order_number']);
    $pd    = $conn->real_escape_string($orig['payment_details'] ?? '{"method":"cod"}');
    $dtype = (int)($orig['delivery_type'] ?? 3);
    $total = (float)$orig['total'];

    $conn->query("
        INSERT INTO orders
            (order_number, customer_id, delivery_type, payment_details,
             address, city, notes, subtotal, shipping, total, status, created_at)
        VALUES
            ('$new_order_num', $cid, $dtype, '$pd',
             '$addr', '$city', '$notes',
             $total, 0.00, $total, 'confirmed', NOW())
    ");
    $new_oid = $conn->insert_id;

    foreach($items as $it) {
        $pid = (int)$it['product_id'];
        $qty = (int)$it['qty'];
        $pr  = (float)$it['price'];
        $conn->query("INSERT INTO order_items (order_id, product_id, qty, price)
                      VALUES ($new_oid, $pid, $qty, $pr)");
    }

    // Original order status update
    $conn->query("UPDATE orders SET status='dispatched', updated_at=NOW() WHERE order_id=$order_id");

    // Mark replacement done
    $rid = (int)$orig['request_id'];
    $conn->query("UPDATE order_requests SET replacement_order_id=$new_oid, updated_at=NOW() WHERE request_id=$rid");

    header("Location: my-account.php?msg=Replacement+order+%23$new_order_num+confirmed+successfully!");
    exit;
}
?>
<?php include 'includes/header.php'; ?>

<style>
:root{--brand:#7B5EA7;--brand-lt:#F5F0FF;--brand-dk:#5a4080;--border:#EBEBF0;--muted:#8A8A9A;--radius:12px;--shadow:0 2px 20px rgba(123,94,167,.08);}
.rc-page{max-width:600px;margin:40px auto;padding:0 24px 60px;}
.rc-hero{background:linear-gradient(135deg,#1e1030,#6b42c8);border-radius:var(--radius);padding:30px 36px;margin-bottom:28px;color:#fff;}
.rc-hero h2{font-family:'Playfair Display',serif;font-size:26px;margin-bottom:6px;}
.rc-hero p{font-size:13px;opacity:.7;margin:0;}
.rc-card{background:#fff;border:1px solid var(--border);border-radius:var(--radius);padding:24px;box-shadow:var(--shadow);}
.rc-card-title{font-family:'Playfair Display',serif;font-size:18px;color:#1A1A2E;margin-bottom:16px;padding-bottom:12px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;}
.rc-card-title i{color:var(--brand);}
.item-row{display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid var(--border);}
.item-row:last-child{border-bottom:none;}
.item-img{width:56px;height:56px;border-radius:8px;object-fit:cover;border:1px solid var(--border);flex-shrink:0;}
.item-img-ph{width:56px;height:56px;border-radius:8px;background:var(--brand-lt);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;color:var(--brand);flex-shrink:0;}
.info-box{background:#EDE9FF;border:1px solid #DDD6FE;border-radius:8px;padding:12px 16px;font-size:13px;color:#5B21B6;line-height:1.7;margin:18px 0;}
.alert-er{background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;padding:12px 16px;border-radius:8px;font-size:13px;margin-bottom:18px;}
.btn-confirm{width:100%;padding:14px;background:var(--brand);color:#fff;border:none;border-radius:8px;font-size:15px;font-weight:600;cursor:pointer;font-family:inherit;transition:background .2s;margin-top:8px;}
.btn-confirm:hover{background:var(--brand-dk);}
.total-row{display:flex;justify-content:space-between;align-items:center;margin-top:16px;padding-top:14px;border-top:2px solid var(--border);}
</style>

<div class="rc-page">

  <div class="rc-hero">
    <h2><i class="fas fa-exchange-alt" style="margin-right:10px;"></i>Confirm Replacement</h2>
    <p>Original Order: <strong>#<?php echo htmlspecialchars($orig['order_number']); ?></strong></p>
  </div>

  <?php if($err): ?>
    <div class="alert-er"><i class="fas fa-exclamation-circle" style="margin-right:6px;"></i><?php echo htmlspecialchars($err); ?></div>
  <?php endif; ?>

  <div class="rc-card">
    <div class="rc-card-title"><i class="fas fa-box-open"></i> Items to be Replaced</div>

    <?php foreach($items as $it): ?>
    <div class="item-row">
      <?php if(!empty($it['product_image'])): ?>
        <img class="item-img" src="<?php echo htmlspecialchars($it['product_image']); ?>"
             alt="<?php echo htmlspecialchars($it['product_name']); ?>"
             onerror="this.style.display='none'"/>
      <?php else: ?>
        <div class="item-img-ph"><i class="fas fa-image"></i></div>
      <?php endif; ?>
      <div style="flex:1;">
        <div style="font-size:14px;font-weight:600;color:#1A1A2E;"><?php echo htmlspecialchars($it['product_name']); ?></div>
        <div style="font-size:12px;color:var(--muted);margin-top:3px;">
          Qty: <?php echo $it['qty']; ?> &nbsp;&bull;&nbsp; Rs. <?php echo number_format($it['price']); ?> each
        </div>
      </div>
      <div style="font-size:14px;font-weight:700;color:var(--brand);">
        Rs. <?php echo number_format($it['price'] * $it['qty']); ?>
      </div>
    </div>
    <?php endforeach; ?>

    <div class="total-row">
      <span style="font-size:13px;color:var(--muted);">Total (No charge — same item)</span>
      <span style="font-size:18px;font-weight:700;color:var(--brand);">Rs. <?php echo number_format($orig['total']); ?></span>
    </div>

    <div class="info-box">
      <i class="fas fa-info-circle" style="margin-right:6px;"></i>
      Confirm karne ke baad yahi items naye order mein dispatch kar diye jayenge. Koi aur payment nahi lagi — aap ne pehle already pay kar diya hua hai.
    </div>

    <div style="background:#FFFBEB;border:1px solid #FDE68A;border-radius:8px;padding:10px 14px;font-size:12px;color:#92400E;line-height:1.7;margin-bottom:4px;">
      <i class="fas fa-shield-alt" style="margin-right:5px;"></i>
      Original order dispatched mark kar diya jayega aur replacement order confirm ho jayegi.
    </div>

    <form method="POST">
      <button type="submit" class="btn-confirm">
        <i class="fas fa-check" style="margin-right:8px;"></i>Confirm Replacement
      </button>
    </form>
  </div>

  <div style="margin-top:18px;">
    <a href="my-account.php" style="font-size:13px;color:var(--muted);text-decoration:none;">
      <i class="fas fa-arrow-left" style="margin-right:5px;"></i> Back to My Account
    </a>
  </div>

</div>

<?php include 'includes/footer.php'; ?>