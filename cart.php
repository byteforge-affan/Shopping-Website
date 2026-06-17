<?php
session_start();
require_once 'includes/db.php';
$page_title = 'My Cart';

// Add to cart via POST
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['product_id'])) {
    $pid = (int)$_POST['product_id'];
    $qty = max(1, (int)($_POST['qty'] ?? 1));
    if(!isset($_SESSION['cart'][$pid])) $_SESSION['cart'][$pid] = 0;
    $_SESSION['cart'][$pid] += $qty;
    header('Location: cart.php');
    exit;
}

// Remove item
if(isset($_GET['remove'])) {
    unset($_SESSION['cart'][(int)$_GET['remove']]);
    header('Location: cart.php');
    exit;
}

// Update qty
if(isset($_POST['update'])) {
    foreach($_POST['qty_update'] as $pid => $qty) {
        $qty = (int)$qty;
        if($qty < 1) unset($_SESSION['cart'][$pid]);
        else $_SESSION['cart'][$pid] = $qty;
    }
    header('Location: cart.php');
    exit;
}

// Load cart products from DB
$cart_items = [];
$subtotal   = 0;
if(!empty($_SESSION['cart'])) {
    $ids = implode(',', array_map('intval', array_keys($_SESSION['cart'])));
    $res = $conn->query("SELECT * FROM products WHERE product_id IN ($ids)");
    if($res) {
        while($row = $res->fetch_assoc()) {
            $row['qty'] = $_SESSION['cart'][$row['product_id']];
            $row['line_total'] = $row['price'] * $row['qty'];
            $subtotal += $row['line_total'];
            $cart_items[] = $row;
        }
    }
}
$shipping = ($subtotal >= 5000) ? 0 : 200;
$total    = $subtotal + $shipping;
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero"><h1>My Cart</h1></div>

<section class="cart-section">

<?php if(empty($cart_items)): ?>
  <div style="text-align:center;padding:80px 20px;">
    <div style="font-size:64px;color:#e0e0e0;margin-bottom:20px;"><i class="fas fa-shopping-cart"></i></div>
    <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:12px;">Your cart is empty</h2>
    <p style="color:#888;margin-bottom:30px;">Looks like you haven't added anything yet.</p>
    <a href="products.php" class="btn-shop">Continue Shopping</a>
  </div>

<?php else: ?>

  <form method="POST">
  <input type="hidden" name="update" value="1"/>
  <table class="cart-table">
    <thead>
      <tr>
        <th style="width:80px;"></th>
        <th>Product</th>
        <th>Price</th>
        <th>Quantity</th>
        <th>Total</th>
        <th></th>
      </tr>
    </thead>
    <tbody>
      <?php foreach($cart_items as $item): ?>
      <tr>
        <td>
          <img class="cart-item-img"
               src="<?php echo htmlspecialchars($item['product_image'] ?? 'images/placeholder.jpg'); ?>"
               alt="<?php echo htmlspecialchars($item['product_name']); ?>"/>
        </td>
        <td>
          <a class="cart-item-name"
             href="product-detail.php?id=<?php echo $item['product_id']; ?>">
            <?php echo htmlspecialchars($item['product_name']); ?>
          </a>
          <br/>
          <span style="font-size:11px;color:#888;">ID: <?php printf('%02d%05d', $item['cat_id'] ?? 0, $item['product_id']); ?></span>
        </td>
        <td>Rs. <?php echo number_format($item['price']); ?></td>
        <td>
          <input class="qty-input" type="number"
                 name="qty_update[<?php echo $item['product_id']; ?>]"
                 value="<?php echo $item['qty']; ?>" min="1" max="99"/>
        </td>
        <td style="font-weight:600;">Rs. <?php echo number_format($item['line_total']); ?></td>
        <td>
          <a href="cart.php?remove=<?php echo $item['product_id']; ?>"
             style="color:#e74c3c;font-size:18px;" title="Remove">
            <i class="fas fa-times"></i>
          </a>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>

  <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:30px;flex-wrap:wrap;gap:30px;">
    <div style="display:flex;gap:12px;">
      <a href="products.php" class="btn-outline">&#8592; Continue Shopping</a>
      <button type="submit" class="btn-shop" style="border-radius:4px;">Update Cart</button>
    </div>

    <!-- ORDER SUMMARY -->
    <div class="cart-summary">
      <h3>Order Summary</h3>
      <div class="summary-row">
        <span>Subtotal</span>
        <span>Rs. <?php echo number_format($subtotal); ?></span>
      </div>
      <div class="summary-row">
        <span>Shipping</span>
        <span><?php echo $shipping==0 ? '<span style="color:green;">FREE</span>' : 'Rs. '.number_format($shipping); ?></span>
      </div>
      <?php if($shipping > 0): ?>
      <div style="font-size:12px;color:#888;padding:6px 0;">
        Add Rs. <?php echo number_format(5000 - $subtotal); ?> more for free shipping
      </div>
      <?php endif; ?>
      <div class="summary-row total">
        <span>Total</span>
        <span style="color:#7B5EA7;">Rs. <?php echo number_format($total); ?></span>
      </div>
      <a href="checkout.php" class="btn-submit" style="display:block;text-align:center;margin-top:20px;padding:14px;">
        Proceed to Checkout
      </a>
    </div>
  </div>
  </form>

<?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
