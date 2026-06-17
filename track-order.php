<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Track Order';

$order = null;
$items = [];
$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['order_no'])) {
    $order_no = clean($conn, $_POST['order_number'] ?? $_GET['order_no'] ?? '');

    if(empty($order_no)) {
        $error = 'Please enter your order number.';
    } else {
        $r = $conn->query("SELECT o.*, c.full_name, c.email, c.phone
                           FROM orders o
                           LEFT JOIN customers c ON o.customer_id = c.customer_id
                           WHERE o.order_number = '$order_no'");
        if($r && $r->num_rows > 0) {
            $order = $r->fetch_assoc();
            $ir = $conn->query("SELECT oi.*, p.product_name, p.product_image
                                FROM order_items oi
                                LEFT JOIN products p ON oi.product_id = p.product_id
                                WHERE oi.order_id = {$order['order_id']}");
            if($ir) while($row = $ir->fetch_assoc()) $items[] = $row;
        } else {
            $error = 'Order not found. Please check your order number.';
        }
    }
}

$delivery_labels = [1=>'Credit Card', 2=>'Cheque', 3=>'Cash on Delivery'];

// Status steps for tracking bar
$status_steps = ['pending','confirmed','dispatched','delivered'];
$current_step = array_search($order['status'] ?? 'pending', $status_steps);
if($order && $order['status'] === 'cancelled') $current_step = -1;
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero"><h1>Track Order</h1></div>

<section style="padding:70px 40px;max-width:800px;margin:0 auto;">

  <!-- SEARCH FORM -->
  <div style="background:#EDE9E3;padding:40px;text-align:center;margin-bottom:50px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:8px;">Track Your Order</h2>
    <p style="color:#888;font-size:13px;margin-bottom:24px;">Enter your 16-digit order number</p>
    <form method="POST" style="display:flex;gap:12px;max-width:500px;margin:0 auto;">
      <input type="text" name="order_number"
             value="<?php echo htmlspecialchars($_POST['order_number'] ?? $_GET['order_no'] ?? ''); ?>"
             placeholder="e.g. 3GF000011234567"
             style="flex:1;padding:13px 18px;border:1px solid #d0c8bc;font-family:'Josefin Sans',sans-serif;
                    font-size:14px;background:#fff;outline:none;letter-spacing:.05em;"
             required/>
      <button type="submit" class="btn-shop" style="border-radius:4px;">Track</button>
    </form>
    <?php if($error): ?>
      <p style="color:#e74c3c;font-size:13px;margin-top:16px;"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></p>
    <?php endif; ?>
  </div>

  <?php if($order): ?>

    <!-- ORDER STATUS BAR -->
    <?php if($order['status'] !== 'cancelled'): ?>
    <div style="margin-bottom:50px;">
      <div style="display:flex;align-items:center;justify-content:space-between;position:relative;padding:0 20px;">
        <!-- Progress line -->
        <div style="position:absolute;top:20px;left:60px;right:60px;height:3px;background:#e0e0e0;z-index:0;"></div>
        <div style="position:absolute;top:20px;left:60px;height:3px;background:#7B5EA7;z-index:1;
                    width:<?php echo $current_step > 0 ? min(($current_step/3)*100, 100) : 0; ?>%;
                    transition:width .5s;"></div>

        <?php
        $step_labels = ['Order Placed','Confirmed','Dispatched','Delivered'];
        $step_icons  = ['fas fa-shopping-bag','fas fa-check-double','fas fa-truck','fas fa-home'];
        foreach($status_steps as $i => $step):
          $done    = $i <= $current_step;
          $active  = $i === $current_step;
        ?>
        <div style="display:flex;flex-direction:column;align-items:center;z-index:2;">
          <div style="width:40px;height:40px;border-radius:50%;
                      background:<?php echo $done?'#7B5EA7':'#e0e0e0'; ?>;
                      display:flex;align-items:center;justify-content:center;
                      font-size:14px;color:<?php echo $done?'#fff':'#aaa'; ?>;
                      box-shadow:<?php echo $active?'0 0 0 4px rgba(123,94,167,0.2)':'none'; ?>;
                      transition:all .3s;">
            <i class="<?php echo $step_icons[$i]; ?>"></i>
          </div>
          <p style="font-size:11px;font-weight:<?php echo $active?'600':'300'; ?>;
                    color:<?php echo $done?'#7B5EA7':'#aaa'; ?>;
                    margin-top:8px;text-align:center;letter-spacing:.05em;text-transform:uppercase;">
            <?php echo $step_labels[$i]; ?>
          </p>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php else: ?>
    <div style="text-align:center;padding:24px;background:#fde8e8;border-radius:4px;margin-bottom:40px;color:#e74c3c;">
      <i class="fas fa-times-circle" style="font-size:32px;margin-bottom:10px;display:block;"></i>
      <strong>This order has been cancelled.</strong>
    </div>
    <?php endif; ?>

    <!-- ORDER DETAILS -->
    <div style="border:1px solid #e0e0e0;padding:30px;margin-bottom:24px;">
      <h3 style="font-family:'Playfair Display',serif;font-size:22px;margin-bottom:20px;">Order Details</h3>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;font-size:13px;">
        <div>
          <p style="color:#888;margin-bottom:4px;">Order Number</p>
          <p style="font-weight:600;color:#7B5EA7;font-size:15px;"><?php echo $order['order_number']; ?></p>
        </div>
        <div>
          <p style="color:#888;margin-bottom:4px;">Status</p>
          <span style="display:inline-block;padding:4px 14px;border-radius:20px;font-size:12px;font-weight:600;
                       background:<?php
                         $sc=['pending'=>'#fff3cd','confirmed'=>'#cfe2ff','dispatched'=>'#fde8d8','delivered'=>'#d1e7dd','cancelled'=>'#f8d7da'];
                         echo $sc[$order['status']] ?? '#f0f0f0';
                       ?>;color:<?php
                         $tc=['pending'=>'#856404','confirmed'=>'#084298','dispatched'=>'#8B4513','delivered'=>'#0f5132','cancelled'=>'#842029'];
                         echo $tc[$order['status']] ?? '#333';
                       ?>;">
            <?php echo ucfirst($order['status']); ?>
          </span>
        </div>
        <div>
          <p style="color:#888;margin-bottom:4px;">Order Date</p>
          <p><?php echo date('d M Y, h:i A', strtotime($order['created_at'])); ?></p>
        </div>
        <div>
          <p style="color:#888;margin-bottom:4px;">Payment Method</p>
          <p><?php echo $delivery_labels[$order['delivery_type']] ?? 'N/A'; ?></p>
        </div>
        <div>
          <p style="color:#888;margin-bottom:4px;">Delivery Address</p>
          <p><?php echo htmlspecialchars($order['address'].', '.$order['city']); ?></p>
        </div>
        <div>
          <p style="color:#888;margin-bottom:4px;">Total Amount</p>
          <p style="font-weight:700;font-size:16px;color:#7B5EA7;">Rs. <?php echo number_format($order['total']); ?></p>
        </div>
      </div>
    </div>

    <!-- ORDER ITEMS -->
    <?php if(!empty($items)): ?>
    <div style="border:1px solid #e0e0e0;padding:30px;">
      <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;">Items Ordered</h3>
      <?php foreach($items as $it): ?>
      <div style="display:flex;gap:16px;align-items:center;padding:14px 0;border-bottom:1px solid #f5f5f5;">
        <img src="<?php echo htmlspecialchars($it['product_image'] ?? 'images/placeholder.jpg'); ?>"
             style="width:65px;height:65px;object-fit:cover;border:1px solid #e0e0e0;"/>
        <div style="flex:1;">
          <p style="font-size:14px;"><?php echo htmlspecialchars($it['product_name']); ?></p>
          <p style="font-size:12px;color:#888;">Qty: <?php echo $it['qty']; ?> × Rs. <?php echo number_format($it['price']); ?></p>
        </div>
        <p style="font-weight:600;">Rs. <?php echo number_format($it['price']*$it['qty']); ?></p>
      </div>
      <?php endforeach; ?>

      <div style="display:flex;justify-content:flex-end;margin-top:16px;flex-direction:column;align-items:flex-end;gap:8px;">
        <div style="display:flex;gap:40px;font-size:13px;color:#888;">
          <span>Subtotal</span><span>Rs. <?php echo number_format($order['subtotal']); ?></span>
        </div>
        <div style="display:flex;gap:40px;font-size:13px;color:#888;">
          <span>Shipping</span>
          <span><?php echo $order['shipping']==0 ? 'FREE' : 'Rs. '.number_format($order['shipping']); ?></span>
        </div>
        <div style="display:flex;gap:40px;font-size:16px;font-weight:700;color:#7B5EA7;margin-top:6px;">
          <span>Total</span><span>Rs. <?php echo number_format($order['total']); ?></span>
        </div>
      </div>
    </div>
    <?php endif; ?>

  <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>
