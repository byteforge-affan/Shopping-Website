<?php
session_start();
require_once 'includes/db.php';

$id = (int)($_GET['id'] ?? 0);
$product = null;

if($id) {
    $res = $conn->query("SELECT p.*, c.cat_name FROM products p LEFT JOIN categories c ON p.cat_id=c.cat_id WHERE p.product_id=$id");
    if($res) $product = $res->fetch_assoc();
}

if(!$product) {
    header('Location: products.php');
    exit;
}

$page_title = $product['product_name'];

// Product code display
$product_code_display = $product['product_code'];
// Related products
$related_res = $conn->query("SELECT * FROM products WHERE cat_id={$product['cat_id']} AND product_id != $id LIMIT 4");
$related = [];
if($related_res) while($row = $related_res->fetch_assoc()) $related[] = $row;
?>
<?php include 'includes/header.php'; ?>

<!-- BREADCRUMB -->
<div style="padding:14px 40px;background:#f5f5f5;font-size:12px;color:#888;">
  <a href="index.php" style="color:#888;">Home</a> &nbsp;/&nbsp;
  <a href="products.php" style="color:#888;">Shop</a> &nbsp;/&nbsp;
  <a href="products.php?cat=<?php echo urlencode($product['cat_id']); ?>" style="color:#888;">
    <?php echo htmlspecialchars($product['cat_name'] ?? ''); ?>
  </a> &nbsp;/&nbsp;
  <span style="color:#333;"><?php echo htmlspecialchars($product['product_name']); ?></span>
</div>

<section class="product-detail">
  <div class="detail-grid">

    <!-- IMAGE -->
    <div>
      <img class="detail-img"
           src="<?php echo htmlspecialchars($product['product_image'] ?? 'images/placeholder.jpg'); ?>"
           alt="<?php echo htmlspecialchars($product['product_name']); ?>"
           style="width:100%;object-fit:cover;"/>
    </div>

    <!-- INFO -->
    <div class="detail-info">
      <p style="font-size:12px;color:#888;letter-spacing:.15em;text-transform:uppercase;margin-bottom:10px;">
        <?php echo htmlspecialchars($product['cat_name'] ?? ''); ?>
      </p>
      <h1><?php echo htmlspecialchars($product['product_name']); ?></h1>
      <p class="detail-price">Rs. <?php echo number_format($product['price']); ?></p>
      <p class="detail-id">Product ID: <?php echo $product_code_display; ?></p>

      <p class="detail-desc"><?php echo nl2br(htmlspecialchars($product['description'] ?? 'Premium quality product from Arts Store.')); ?></p>

      <?php if($product['has_warranty']): ?>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:20px;font-size:13px;color:#4CAF50;">
          <i class="fas fa-shield-alt"></i> Warranty card included
        </div>
      <?php endif; ?>

      <?php if($product['stock'] > 0): ?>
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:28px;font-size:13px;color:#4CAF50;">
          <i class="fas fa-check-circle"></i> In Stock (<?php echo $product['stock']; ?> available)
        </div>
      <?php else: ?>
        <div style="font-size:13px;color:#e74c3c;margin-bottom:28px;">
          <i class="fas fa-times-circle"></i> Out of Stock
        </div>
      <?php endif; ?>

      <?php if($product['stock'] > 0): ?>
      <div style="display:flex;align-items:center;gap:16px;margin-bottom:20px;">
        <div style="display:flex;align-items:center;border:1px solid #e0e0e0;">
          <button onclick="var q=document.getElementById('qty');if(q.value>1)q.value--;"
                  style="width:40px;height:44px;background:none;border:none;font-size:18px;cursor:pointer;">−</button>
          <input id="qty" type="number" value="1" min="1" max="<?php echo $product['stock']; ?>"
                 style="width:50px;height:44px;border:none;border-left:1px solid #e0e0e0;border-right:1px solid #e0e0e0;
                        text-align:center;font-size:14px;font-family:'Josefin Sans',sans-serif;outline:none;"/>
          <button onclick="var q=document.getElementById('qty');if(q.value<<?php echo $product['stock']; ?>)q.value++;"
                  style="width:40px;height:44px;background:none;border:none;font-size:18px;cursor:pointer;">+</button>
        </div>
        <button class="btn-shop" style="border-radius:4px;"
                onclick="addToCart(<?php echo $product['product_id']; ?>, document.getElementById('qty').value)">
          <i class="fas fa-shopping-cart"></i>&nbsp; Add to Cart
        </button>
        <button class="product-heart" style="font-size:22px;background:none;border:1px solid #e0e0e0;
                width:44px;height:44px;border-radius:4px;display:flex;align-items:center;justify-content:center;"
                onclick="this.classList.toggle('active');toggleWishlist(this,<?php echo $product['product_id']; ?>)">
          <i class="fas fa-heart"></i>
        </button>
      </div>
      <?php endif; ?>

      <div style="border-top:1px solid #e0e0e0;padding-top:20px;margin-top:10px;">
        <?php foreach([
          ['fas fa-truck',      'Free delivery on orders over Rs. 5,000'],
          ['fas fa-undo-alt',   '7-day return & exchange policy'],
          ['fas fa-lock',       'Secure payment guaranteed'],
        ] as $f): ?>
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:12px;font-size:13px;color:#555;">
          <i class="<?php echo $f[0]; ?>" style="color:#7B5EA7;width:16px;"></i>
          <?php echo $f[1]; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- RELATED PRODUCTS -->
  <?php if(!empty($related)): ?>
  <div style="margin-top:80px;">
    <div class="section-header">
      <h2>Related Products</h2>
      <div class="section-line"></div>
    </div>
    <div class="product-grid" style="margin-top:40px;">
      <?php foreach($related as $r): ?>
      <div class="product-card">
        <div class="product-thumb">
          <img src="<?php echo htmlspecialchars($r['product_image'] ?? 'images/placeholder.jpg'); ?>"
               alt="<?php echo htmlspecialchars($r['product_name']); ?>"/>
          <button class="product-heart" onclick="this.classList.toggle('active')"><i class="fas fa-heart"></i></button>
          <div class="product-actions">
            <a href="product-detail.php?id=<?php echo $r['product_id']; ?>" class="prod-action-btn"><i class="fas fa-eye"></i></a>
            <button class="prod-action-btn" onclick="addToCart(<?php echo $r['product_id']; ?>)"><i class="fas fa-shopping-cart"></i></button>
          </div>
        </div>
        <p class="product-name"><?php echo htmlspecialchars($r['product_name']); ?></p>
        <p class="product-price">Rs. <?php echo number_format($r['price']); ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>
