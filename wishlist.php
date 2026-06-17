<?php
session_start();
require_once 'includes/db.php';
$page_title = 'My Wishlist';

$products = [];

if(isset($_SESSION['customer_id'])) {
    // Logged in — load from DB
    $cid = (int)$_SESSION['customer_id'];
    $res = $conn->query("SELECT p.*, c.cat_name FROM wishlist w
                         LEFT JOIN products p ON w.product_id = p.product_id
                         LEFT JOIN categories c ON p.cat_id = c.cat_id
                         WHERE w.customer_id = $cid
                         ORDER BY w.added_at DESC");
    if($res) while($r = $res->fetch_assoc()) $products[] = $r;

    // Remove from wishlist
    if(isset($_GET['remove'])) {
        $rid = (int)$_GET['remove'];
        $conn->query("DELETE FROM wishlist WHERE customer_id=$cid AND product_id=$rid");
        header('Location: wishlist.php');
        exit;
    }
} else {
    // Session wishlist
    if(isset($_GET['remove']) && isset($_SESSION['wishlist'])) {
        $rid = (int)$_GET['remove'];
        unset($_SESSION['wishlist'][$rid]);
        header('Location: wishlist.php');
        exit;
    }
    if(!empty($_SESSION['wishlist'])) {
        $ids = implode(',', array_map('intval', array_keys($_SESSION['wishlist'])));
        $res = $conn->query("SELECT p.*, c.cat_name FROM products p LEFT JOIN categories c ON p.cat_id=c.cat_id WHERE p.product_id IN ($ids)");
        if($res) while($r = $res->fetch_assoc()) $products[] = $r;
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero"><h1>My Wishlist</h1></div>

<section style="padding:60px 40px;">

  <?php if(empty($products)): ?>
  <div style="text-align:center;padding:80px 20px;">
    <div style="font-size:64px;color:#e0e0e0;margin-bottom:20px;"><i class="fas fa-heart"></i></div>
    <h2 style="font-family:'Playfair Display',serif;font-size:28px;margin-bottom:12px;">Your wishlist is empty</h2>
    <p style="color:#888;margin-bottom:30px;">Save your favourite items to find them later!</p>
    <a href="products.php" class="btn-shop">Browse Products</a>
  </div>

  <?php else: ?>
  <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:30px;">
    <h2 style="font-family:'Playfair Display',serif;font-size:28px;"><?php echo count($products); ?> Items in Wishlist</h2>
    <a href="products.php" style="font-size:13px;color:#7B5EA7;">← Continue Shopping</a>
  </div>

  <div class="product-grid">
    <?php foreach($products as $p): ?>
    <div class="product-card">
      <div class="product-thumb">
        <img src="<?php echo htmlspecialchars($p['product_image'] ?? 'images/placeholder.jpg'); ?>"
             alt="<?php echo htmlspecialchars($p['product_name']); ?>"/>
        <?php if(!empty($p['is_new'])): ?>
          <span class="product-badge">NEW</span>
        <?php endif; ?>
        <div class="product-actions">
          <a href="product-detail.php?id=<?php echo $p['product_id']; ?>" class="prod-action-btn" title="View">
            <i class="fas fa-eye"></i>
          </a>
          <button class="prod-action-btn" title="Add to Cart"
                  onclick="addToCart(<?php echo $p['product_id']; ?>)">
            <i class="fas fa-shopping-cart"></i>
          </button>
        </div>
      </div>
      <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-top:4px;">
        <div>
          <p class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></p>
          <p style="font-size:12px;color:#888;margin-bottom:4px;"><?php echo htmlspecialchars($p['cat_name']??''); ?></p>
          <p class="product-price">Rs. <?php echo number_format($p['price']); ?></p>
        </div>
        <a href="wishlist.php?remove=<?php echo $p['product_id']; ?>"
           style="color:#e74c3c;font-size:18px;margin-top:2px;" title="Remove from Wishlist">
          <i class="fas fa-times"></i>
        </a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <?php endif; ?>
</section>

<?php include 'includes/footer.php'; ?>
