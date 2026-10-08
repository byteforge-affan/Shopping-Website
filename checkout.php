<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Checkout';

if(empty($_SESSION['cart'])) { header('Location: cart.php'); exit; }
if(!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit; }

$error = $success = '';
$order_number = '';

// Per-session checkout nonce; UNIQUE in orders prevents duplicate commits.
if (empty($_SESSION['checkout_token'])) $_SESSION['checkout_token'] = bin2hex(random_bytes(32));
$cart_items = [];
$subtotal = 0;
$cartValid = is_array($_SESSION['cart'] ?? null) && count($_SESSION['cart']) > 0;
if ($cartValid) {
    foreach ($_SESSION['cart'] as $id => $qty) {
        if (!ctype_digit((string)$id) || (int)$id < 1 ||
            !ctype_digit((string)$qty) || (int)$qty < 1 || (int)$qty > 10000) {
            $cartValid = false; break;
        }
    }
}
if ($cartValid) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $res = $conn->query("SELECT * FROM products WHERE product_id IN ($ids)");
    if ($res) while ($row = $res->fetch_assoc()) {
        $row['qty'] = (int)$_SESSION['cart'][$row['product_id']];
        $row['line_total'] = (float)$row['price'] * $row['qty'];
        $subtotal += $row['line_total'];
        $cart_items[] = $row;
    }
    if (count($cart_items) !== count($_SESSION['cart'])) $cartValid = false;
}
$shipping = $subtotal >= 5000 ? 0 : 200;
$total = $subtotal + $shipping;
if (!$cartValid) $error = 'Your cart contains invalid or unavailable products. Please update it.';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delivery_type = (int)($_POST['delivery_type'] ?? 0);
    $address = clean($conn, $_POST['address'] ?? '');
    $city = clean($conn, $_POST['city'] ?? '');
    $notes = clean($conn, $_POST['notes'] ?? '');
    // ── Payment details by type ────────────────────────────────────────────
    $payment_details = null;

    if($delivery_type === 1) {
        $card_name   = clean($conn, $_POST['card_name']  ?? '');
        $card_number = preg_replace('/\D/', '', $_POST['card_number'] ?? '');
        $card_expiry = clean($conn, $_POST['card_expiry'] ?? '');
        $card_cvv    = preg_replace('/\D/', '', $_POST['card_cvv'] ?? '');
        $card_type   = clean($conn, $_POST['card_type']  ?? '');

        if(!$card_name || strlen($card_number) < 15 || !$card_expiry || strlen($card_cvv) < 3) {
            $error = 'Please fill in all credit / debit card details correctly.';
        } else {
            $payment_details = json_encode([
                'method'      => 'card',
                'card_type'   => $card_type,
                'card_name'   => $card_name,
                'card_last4'  => substr($card_number, -4),
                'card_expiry' => $card_expiry,
            ]);
        }

    } elseif($delivery_type === 2) {
        $cheque_number = clean($conn, $_POST['cheque_number'] ?? '');
        $cheque_bank   = clean($conn, $_POST['cheque_bank']   ?? '');
        $cheque_branch = clean($conn, $_POST['cheque_branch'] ?? '');
        $cheque_date   = clean($conn, $_POST['cheque_date']   ?? '');
        $cheque_name   = clean($conn, $_POST['cheque_name']   ?? '');

        if(!$cheque_number || !$cheque_bank || !$cheque_date || !$cheque_name) {
            $error = 'Please fill in all cheque details.';
        } else {
            $payment_details = json_encode([
                'method'        => 'cheque',
                'cheque_number' => $cheque_number,
                'bank_name'     => $cheque_bank,
                'branch'        => $cheque_branch,
                'cheque_date'   => $cheque_date,
                'account_name'  => $cheque_name,
            ]);
        }

    } elseif($delivery_type === 3) {
        $payment_details = json_encode(['method' => 'cod']);
    } else {
        $error = 'Invalid payment method.';
    }

    if(!$address || !$city || strlen($address)>2000 || strlen($city)>100) {
        $error = 'Please enter your delivery address.';
    }


    if (!hash_equals($_SESSION['checkout_token'], (string)($_POST['checkout_token'] ?? ''))) {
        $error = 'Checkout form expired. Please reload this page.';
    }
    if (!$cartValid) $error = 'Your cart contains invalid or unavailable products.';
    if (!$error) {
        try {
            $conn->begin_transaction();
            $ids = array_map('intval', array_keys($_SESSION['cart']));
            sort($ids, SORT_NUMERIC);
            $locked = [];
            $sum = 0.0;
            $lock = $conn->prepare("SELECT product_code,price,stock,is_active FROM products WHERE product_id=? FOR UPDATE");
            foreach ($ids as $pid) {
                $qty = (int)$_SESSION['cart'][$pid];
                $lock->bind_param('i', $pid);
                if (!$lock->execute()) throw new RuntimeException('Lock failed');
                $p = $lock->get_result()->fetch_assoc();
                if (!$p || (int)$p['is_active'] !== 1 || (int)$p['stock'] < $qty ||
                    (float)$p['price'] < 0) throw new RuntimeException('Unavailable stock');
                $locked[$pid] = ['qty'=>$qty,'price'=>(float)$p['price'],'code'=>$p['product_code']];
                $sum += $qty * (float)$p['price'];
            }
            $ship = $sum >= 5000 ? 0.0 : 200.0;
            $grand = $sum + $ship;
            $pid7 = substr(str_pad(preg_replace('/[^0-9]/','',$locked[$ids[0]]['code']),7,'0',STR_PAD_LEFT),-7);
            $order_num = $delivery_type.$pid7.sprintf('%08d',random_int(10000000,99999999));
            $cid = (int)$_SESSION['customer_id'];
            $token = $_SESSION['checkout_token'];
            $ins = $conn->prepare("INSERT INTO orders
                (order_number,customer_id,delivery_type,payment_details,address,city,notes,
                 subtotal,shipping,total,checkout_token,status,created_at)
                 VALUES (?,?,?,?,?,?,?,?,?,?,?,'pending',NOW())");
            $ins->bind_param('siissssddds',$order_num,$cid,$delivery_type,$payment_details,
                $address,$city,$notes,$sum,$ship,$grand,$token);
            if (!$ins->execute()) throw new RuntimeException('Order insert failed');
            $oid = $conn->insert_id;
            $item = $conn->prepare("INSERT INTO order_items (order_id,product_id,qty,price) VALUES (?,?,?,?)");
            $stock = $conn->prepare("UPDATE products SET stock=stock-? WHERE product_id=? AND stock>=? AND is_active=1");
            foreach ($ids as $pid) {
                $qty = $locked[$pid]['qty'];
                $price = $locked[$pid]['price'];
                $item->bind_param('iiid',$oid,$pid,$qty,$price);
                if (!$item->execute()) throw new RuntimeException('Item insert failed');
                $stock->bind_param('iii',$qty,$pid,$qty);
                if (!$stock->execute() || $stock->affected_rows !== 1)
                    throw new RuntimeException('Stock update failed');
            }
            if (!$conn->commit()) throw new RuntimeException('Commit failed');
            $_SESSION['cart'] = [];
            unset($_SESSION['checkout_token']);
            $order_number = $order_num;
            $success = 'Order placed successfully!';
        } catch (Throwable $e) {
            try { $conn->rollback(); } catch (Throwable $ignored) {}
            error_log('Checkout failure: '.$e->getMessage());
            $error = 'Could not place order. Please review your cart and try again.';
        }
    }
}
?>
<?php include 'includes/header.php'; ?>

<style>
.payment-panel{display:none;margin-top:20px;padding:22px 24px;border:1px solid #e0e0e0;border-radius:4px;background:#fafafa;animation:fadeIn .25s ease;}
.payment-panel.active{display:block;}
@keyframes fadeIn{from{opacity:0;transform:translateY(-6px)}to{opacity:1;transform:translateY(0)}}
.pay-grid{display:grid;gap:16px;}
.pay-grid.col2{grid-template-columns:1fr 1fr;}
.pay-grid.col3{grid-template-columns:1fr 1fr 1fr;}
.form-group{display:flex;flex-direction:column;gap:6px;}
.form-group label{font-size:12px;color:#555;font-weight:500;letter-spacing:.04em;text-transform:uppercase;}
.form-group input,.form-group select,.form-group textarea{padding:10px 14px;border:1px solid #d8d8d8;font-size:13px;font-family:inherit;outline:none;transition:border-color .2s;background:#fff;}
.form-group input:focus,.form-group select:focus{border-color:#7B5EA7;}
.card-type-row{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:4px;}
.card-type-btn{display:flex;align-items:center;gap:6px;padding:8px 14px;border:1px solid #d8d8d8;cursor:pointer;font-size:12px;background:#fff;transition:all .2s;user-select:none;}
.card-type-btn.selected{border-color:#7B5EA7;background:#f3eeff;color:#7B5EA7;font-weight:600;}
.card-type-btn i{font-size:18px;}
.secure-note{display:flex;align-items:center;gap:6px;font-size:11px;color:#888;margin-top:14px;}
.secure-note i{color:#4caf50;}
.card-number-wrap{position:relative;}
.card-number-wrap i{position:absolute;right:12px;top:50%;transform:translateY(-50%);color:#bbb;font-size:16px;}
</style>

<div class="page-hero"><h1>Checkout</h1></div>

<section style="padding:60px 40px;">

<?php if($success): ?>
  <div style="text-align:center;padding:60px 20px;max-width:600px;margin:0 auto;">
    <div style="font-size:64px;color:#4caf50;margin-bottom:20px;"><i class="fas fa-check-circle"></i></div>
    <h2 style="font-family:'Playfair Display',serif;font-size:32px;margin-bottom:16px;">Order Placed!</h2>
    <p style="color:#888;font-size:14px;margin-bottom:20px;">Thank you for your order.</p>
    <div style="background:#f5f5f5;padding:20px;border-radius:4px;margin-bottom:30px;">
      <p style="font-size:12px;color:#888;letter-spacing:.1em;text-transform:uppercase;margin-bottom:6px;">Your Order Number</p>
      <p style="font-family:'Playfair Display',serif;font-size:28px;font-weight:700;color:#7B5EA7;letter-spacing:.05em;">
        <?php echo htmlspecialchars($order_number); ?>
      </p>
    </div>
    <p style="font-size:13px;color:#888;margin-bottom:30px;">Please save your order number. You can use it to track your order.</p>
    <div style="display:flex;gap:16px;justify-content:center;">
      <a href="my-account.php" class="btn-shop">View My Orders</a>
      <a href="index.php" class="btn-outline">Continue Shopping</a>
    </div>
  </div>

<?php else: ?>

<div style="display:grid;grid-template-columns:1fr 380px;gap:50px;align-items:start;">

  <!-- LEFT: FORM -->
  <div>
    <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:30px;">Delivery Details</h2>

    <?php if($error): ?>
      <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST" id="checkoutForm">
      <input type="hidden" name="checkout_token" value="<?php echo htmlspecialchars($_SESSION['checkout_token'] ?? '', ENT_QUOTES); ?>"/>

      <div class="form-group" style="margin-bottom:16px;">
        <label>Delivery Address *</label>
        <input type="text" name="address" placeholder="Full street address" required
               value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"/>
      </div>

      <div class="form-group" style="margin-bottom:16px;">
        <label>City *</label>
        <select name="city" required>
          <option value="">Select City</option>
          <?php foreach(['Karachi','Lahore','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta'] as $c): ?>
            <option <?php echo (($_POST['city'] ?? '') === $c) ? 'selected' : ''; ?>><?php echo $c; ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="form-group" style="margin-bottom:30px;">
        <label>Order Notes (optional)</label>
        <textarea name="notes" rows="3" placeholder="Special delivery instructions..."><?php echo htmlspecialchars($_POST['notes'] ?? ''); ?></textarea>
      </div>

      <h3 style="font-family:'Playfair Display',serif;font-size:22px;margin:0 0 20px;">Payment Method</h3>

      <?php
      $methods = [
        1 => ['Credit / Debit Card',   'fas fa-credit-card',      'Pay online by credit or debit card. Order dispatched after payment clearance.'],
        2 => ['Cheque',                'fas fa-money-check',       'Pay by cheque. Order dispatched after cheque clearance.'],
        3 => ['Cash on Delivery (VPP)','fas fa-hand-holding-usd',  'Pay cash when your order arrives at your door.'],
      ];
      $selected_type = (int)($_POST['delivery_type'] ?? 3);
      foreach($methods as $val => $m): ?>
      <label style="display:flex;align-items:flex-start;gap:14px;padding:16px 20px;border:1px solid #e0e0e0;
                    margin-bottom:12px;cursor:pointer;transition:border-color .2s;"
             onmouseover="this.style.borderColor='#7B5EA7'" onmouseout="this.style.borderColor='#e0e0e0'">
        <input type="radio" name="delivery_type" value="<?php echo $val; ?>"
               <?php echo $val === $selected_type ? 'checked' : ''; ?>
               style="margin-top:3px;accent-color:#7B5EA7;"
               onchange="showPaymentPanel(<?php echo $val; ?>)"/>
        <div>
          <div style="display:flex;align-items:center;gap:8px;font-size:14px;font-weight:400;margin-bottom:4px;">
            <i class="<?php echo $m[1]; ?>" style="color:#7B5EA7;"></i><?php echo $m[0]; ?>
          </div>
          <div style="font-size:12px;color:#888;"><?php echo $m[2]; ?></div>
        </div>
      </label>
      <?php endforeach; ?>

      <!-- Panel 1: Card -->
      <div id="panel-1" class="payment-panel <?php echo $selected_type===1?'active':''; ?>">
        <p style="font-size:13px;font-weight:600;margin-bottom:16px;color:#333;">
          <i class="fas fa-credit-card" style="color:#7B5EA7;margin-right:6px;"></i>Card Details
        </p>
        <div style="margin-bottom:16px;">
          <label style="font-size:12px;color:#555;font-weight:500;letter-spacing:.04em;text-transform:uppercase;display:block;margin-bottom:8px;">Card Type</label>
          <div class="card-type-row" id="cardTypeRow">
            <?php foreach(['visa'=>['fab fa-cc-visa','Visa'],'mastercard'=>['fab fa-cc-mastercard','Mastercard'],'amex'=>['fab fa-cc-amex','Amex'],'other'=>['fas fa-credit-card','Other']] as $cval=>$ct): ?>
            <div class="card-type-btn" onclick="selectCardType('<?php echo $cval; ?>')" id="ct-<?php echo $cval; ?>">
              <i class="<?php echo $ct[0]; ?>"></i><?php echo $ct[1]; ?>
            </div>
            <?php endforeach; ?>
          </div>
          <input type="hidden" name="card_type" id="card_type" value="<?php echo htmlspecialchars($_POST['card_type'] ?? ''); ?>"/>
        </div>
        <div class="pay-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label>Cardholder Name *</label>
            <input type="text" name="card_name" placeholder="As printed on card"
                   value="<?php echo htmlspecialchars($_POST['card_name'] ?? ''); ?>" autocomplete="cc-name"/>
          </div>
        </div>
        <div class="pay-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label>Card Number *</label>
            <div class="card-number-wrap">
              <input type="text" name="card_number" id="card_number" placeholder="•••• •••• •••• ••••"
                     maxlength="19" inputmode="numeric" autocomplete="cc-number"
                     oninput="formatCardNumber(this)"
                     value="<?php echo htmlspecialchars($_POST['card_number'] ?? ''); ?>"/>
              <i class="fas fa-lock"></i>
            </div>
          </div>
        </div>
        <div class="pay-grid col2" style="margin-bottom:4px;">
          <div class="form-group">
            <label>Expiry Date *</label>
            <input type="text" name="card_expiry" placeholder="MM / YY" maxlength="7"
                   inputmode="numeric" autocomplete="cc-exp" oninput="formatExpiry(this)"
                   value="<?php echo htmlspecialchars($_POST['card_expiry'] ?? ''); ?>"/>
          </div>
          <div class="form-group">
            <label>CVV *</label>
            <input type="password" name="card_cvv" placeholder="•••" maxlength="4"
                   inputmode="numeric" autocomplete="cc-csc"/>
          </div>
        </div>
        <div class="secure-note">
          <i class="fas fa-shield-alt"></i>Your card details are encrypted and never stored in full.
        </div>
      </div>

      <!-- Panel 2: Cheque -->
      <div id="panel-2" class="payment-panel <?php echo $selected_type===2?'active':''; ?>">
        <p style="font-size:13px;font-weight:600;margin-bottom:16px;color:#333;">
          <i class="fas fa-money-check" style="color:#7B5EA7;margin-right:6px;"></i>Cheque Details
        </p>
        <div class="pay-grid col2" style="margin-bottom:16px;">
          <div class="form-group">
            <label>Cheque Number *</label>
            <input type="text" name="cheque_number" placeholder="e.g. 0012345" inputmode="numeric"
                   value="<?php echo htmlspecialchars($_POST['cheque_number'] ?? ''); ?>"/>
          </div>
          <div class="form-group">
            <label>Cheque Date *</label>
            <input type="date" name="cheque_date" value="<?php echo htmlspecialchars($_POST['cheque_date'] ?? ''); ?>"/>
          </div>
        </div>
        <div class="pay-grid" style="margin-bottom:16px;">
          <div class="form-group">
            <label>Account / Payee Name *</label>
            <input type="text" name="cheque_name" placeholder="Name on cheque"
                   value="<?php echo htmlspecialchars($_POST['cheque_name'] ?? ''); ?>"/>
          </div>
        </div>
        <div class="pay-grid col2" style="margin-bottom:4px;">
          <div class="form-group">
            <label>Bank Name *</label>
            <select name="cheque_bank">
              <option value="">Select Bank</option>
              <?php foreach(['HBL','MCB Bank','UBL','Allied Bank','Meezan Bank','Bank Alfalah','Habib Metropolitan','Faysal Bank','Standard Chartered','JS Bank','Silk Bank','Askari Bank','Other'] as $b): ?>
              <option <?php echo (($_POST['cheque_bank'] ?? '') === $b) ? 'selected':''; ?>><?php echo $b; ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="form-group">
            <label>Branch / City</label>
            <input type="text" name="cheque_branch" placeholder="e.g. Karachi Main"
                   value="<?php echo htmlspecialchars($_POST['cheque_branch'] ?? ''); ?>"/>
          </div>
        </div>
        <div class="secure-note" style="margin-top:14px;">
          <i class="fas fa-info-circle" style="color:#7B5EA7;"></i>
          Please make cheque payable to <strong style="color:#333;">Your Company Name</strong>. Order dispatched after clearance.
        </div>
      </div>

      <!-- Panel 3: COD -->
      <div id="panel-3" class="payment-panel <?php echo $selected_type===3?'active':''; ?>">
        <div style="display:flex;align-items:center;gap:12px;color:#555;font-size:13px;">
          <i class="fas fa-hand-holding-usd" style="font-size:28px;color:#7B5EA7;"></i>
          <div>
            <p style="font-weight:600;margin-bottom:3px;">Cash on Delivery</p>
            <p style="color:#888;">Please have the exact amount of
               <strong style="color:#7B5EA7;">Rs. <?php echo number_format($total); ?></strong>
               ready at the time of delivery.</p>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-submit" style="margin-top:28px;">
        Place Order &nbsp;<i class="fas fa-arrow-right"></i>
      </button>
    </form>
  </div>

  <!-- RIGHT: ORDER SUMMARY -->
  <div style="border:1px solid #e0e0e0;padding:30px;position:sticky;top:100px;">
    <h3 style="font-family:'Playfair Display',serif;font-size:20px;margin-bottom:20px;">Order Summary</h3>
    <?php foreach($cart_items as $item): ?>
    <div style="display:flex;gap:14px;align-items:center;padding:12px 0;border-bottom:1px solid #f0f0f0;">
      <img src="<?php echo htmlspecialchars($item['product_image'] ?? 'images/placeholder.jpg'); ?>"
           style="width:55px;height:55px;object-fit:cover;border:1px solid #e0e0e0;"/>
      <div style="flex:1;">
        <p style="font-size:13px;"><?php echo htmlspecialchars($item['product_name']); ?></p>
        <p style="font-size:12px;color:#888;">Qty: <?php echo $item['qty']; ?></p>
      </div>
      <p style="font-size:13px;font-weight:600;">Rs. <?php echo number_format($item['line_total']); ?></p>
    </div>
    <?php endforeach; ?>
    <div class="summary-row" style="margin-top:14px;"><span>Subtotal</span><span>Rs. <?php echo number_format($subtotal); ?></span></div>
    <div class="summary-row"><span>Shipping</span><span><?php echo $shipping==0?'FREE':'Rs. '.number_format($shipping); ?></span></div>
    <div class="summary-row total" style="font-size:18px;">
      <span>Total</span><span style="color:#7B5EA7;">Rs. <?php echo number_format($total); ?></span>
    </div>
  </div>

</div>
<?php endif; ?>
</section>

<script>
function showPaymentPanel(type) {
    [1,2,3].forEach(function(n){
        var el = document.getElementById('panel-'+n);
        if(el) el.classList.toggle('active', n===type);
    });
}
function selectCardType(val) {
    document.getElementById('card_type').value = val;
    document.querySelectorAll('.card-type-btn').forEach(function(b){ b.classList.remove('selected'); });
    var el = document.getElementById('ct-'+val);
    if(el) el.classList.add('selected');
}
function formatCardNumber(input) {
    var v = input.value.replace(/\D/g,'').substring(0,16);
    input.value = v.replace(/(.{4})/g,'$1 ').trim();
}
function formatExpiry(input) {
    var v = input.value.replace(/\D/g,'').substring(0,4);
    if(v.length >= 3) v = v.substring(0,2)+' / '+v.substring(2);
    input.value = v;
}
<?php if(isset($_POST['card_type']) && $_POST['card_type']): ?>
selectCardType('<?php echo htmlspecialchars($_POST['card_type']); ?>');
<?php endif; ?>
</script>

<?php include 'includes/footer.php'; ?>