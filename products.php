<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Shop';

$subfolder = trim(dirname($_SERVER['PHP_SELF']), '/');
$base_url  = '/' . $subfolder . '/';

function prodImgSrc($path, $base_url) {
    if(empty($path)) return '';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

$search = isset($_GET['q'])    ? $conn->real_escape_string(trim($_GET['q']))   : '';
$cat    = isset($_GET['cat'])  ? $conn->real_escape_string(trim($_GET['cat'])) : '';
$page   = isset($_GET['page']) ? max(1,(int)$_GET['page'])                    : 1;
$min    = isset($_GET['min'])  ? (float)$_GET['min']                          : 0;
$max    = isset($_GET['max'])  ? (float)$_GET['max']                          : 0;
$per    = 12;
$offset = ($page - 1) * $per;

$where = "WHERE p.is_active = 1";
if($search) $where .= " AND (p.product_name LIKE '%$search%' OR p.description LIKE '%$search%' OR p.product_code LIKE '%$search%')";
if($cat)    $where .= " AND c.cat_slug='$cat'";
if($max > 0) $where .= " AND p.price >= $min AND p.price <= $max";



$result   = $conn->query("SELECT p.*, c.cat_name, c.cat_slug FROM products p LEFT JOIN categories c ON p.cat_id=c.cat_id $where ORDER BY p.created_at DESC LIMIT $per OFFSET $offset");
$products = [];
if($result) while($row = $result->fetch_assoc()) $products[] = $row;

$count_r = $conn->query("SELECT COUNT(*) as total FROM products p LEFT JOIN categories c ON p.cat_id=c.cat_id $where");
$total   = $count_r ? (int)$count_r->fetch_assoc()['total'] : 0;
$pages   = ceil($total / $per);

$cats_r = $conn->query("SELECT * FROM categories ORDER BY cat_name ASC");
$cats   = [];
if($cats_r) while($row = $cats_r->fetch_assoc()) $cats[] = $row;

$total_all = (int)($conn->query("SELECT COUNT(*) as c FROM products")->fetch_assoc()['c'] ?? 0);
$show_demo = ($total_all < 10);

$demo = [
    ['name'=>'Crystal Vase',       'price'=>'2,500','cat'=>'Home Decor',   'badge'=>'NEW','img'=>$base_url.'images/demo/crystal-vase.jpg'],
    ['name'=>'Perfume Gift Set',   'price'=>'3,800','cat'=>'Beauty',       'badge'=>'HOT','img'=>$base_url.'images/demo/perfume-set.jpg'],
    ['name'=>'Luxury Watch Box',   'price'=>'5,500','cat'=>'Gift Articles','badge'=>'NEW','img'=>$base_url.'images/demo/watch-box.jpg'],
    ['name'=>'Scented Candle Set', 'price'=>'1,400','cat'=>'Home Decor',   'badge'=>'',  'img'=>$base_url.'images/demo/candle-set.jpg'],
    ['name'=>'Rose Gold Bracelet', 'price'=>'4,200','cat'=>'Jewellery',    'badge'=>'NEW','img'=>$base_url.'images/demo/bracelet.jpg'],
    ['name'=>'Mini Makeup Kit',    'price'=>'2,100','cat'=>'Beauty',       'badge'=>'HOT','img'=>$base_url.'images/demo/makeup-kit.jpg'],
    ['name'=>'Leather Clutch Bag', 'price'=>'3,300','cat'=>'Hand Bags',    'badge'=>'',  'img'=>$base_url.'images/demo/clutch-bag.jpg'],
    ['name'=>'Silk Scarf',         'price'=>'1,900','cat'=>'Accessories',  'badge'=>'',  'img'=>$base_url.'images/demo/silk-scarf.jpg'],
    ['name'=>'Body Lotion Set',    'price'=>'1,650','cat'=>'Beauty',       'badge'=>'',  'img'=>$base_url.'images/demo/perfume-set.jpg'],
    ['name'=>'Pearl Necklace',     'price'=>'6,200','cat'=>'Jewellery',    'badge'=>'HOT','img'=>$base_url.'images/demo/bracelet.jpg'],
];
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero"><h1>Shop</h1></div>

<section class="products-section">

  <!-- ── SEARCH BAR ── -->
  <form method="GET" style="display:flex;gap:12px;margin-bottom:30px;max-width:500px;">
    <?php if($cat): ?>
      <input type="hidden" name="cat" value="<?php echo htmlspecialchars($cat); ?>"/>
    <?php endif; ?>
    <?php if($min || $max): ?>
      <input type="hidden" name="min" value="<?php echo $min; ?>"/>
      <input type="hidden" name="max" value="<?php echo $max; ?>"/>
    <?php endif; ?>
    <input type="text" name="q" value="<?php echo htmlspecialchars($search); ?>"
           placeholder="Search products..."
           style="flex:1;padding:10px 16px;border:1px solid #e0e0e0;border-radius:4px;font-size:14px;outline:none;"/>
    <button type="submit" class="btn-shop" style="border-radius:4px;padding:10px 24px;">Search</button>
  </form>

  <!-- ── FILTER TABS ── -->
  <div class="filter-bar" style="margin-bottom:30px;">
    <div class="filter-tabs" style="display:flex;gap:8px;flex-wrap:wrap;">
      <a href="<?php echo $base_url; ?>products.php"
         class="filter-tab <?php echo (!$cat && !$max) ? 'active' : ''; ?>">
        All Products
      </a>
      <?php foreach($cats as $c): ?>
        <a href="<?php echo $base_url; ?>products.php?cat=<?php echo urlencode($c['cat_slug']); ?>"
           class="filter-tab <?php echo $cat==$c['cat_slug'] ? 'active' : ''; ?>">
          <?php echo htmlspecialchars($c['cat_name']); ?>
        </a>
      <?php endforeach; ?>
    </div>

    <!-- ── ACTIVE FILTERS ── -->
    <div style="display:flex;flex-wrap:wrap;gap:8px;margin-top:12px;align-items:center;">

      <?php if($max > 0): ?>
      <span style="background:#7B5EA7;color:#fff;padding:5px 14px;border-radius:20px;
                   font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:8px;">
        💰 Price: Rs. <?php echo number_format($min); ?>
        – <?php echo $max >= 999999 ? 'Above' : 'Rs. '.number_format($max); ?>
        <a href="<?php echo $base_url; ?>products.php<?php echo $cat ? '?cat='.urlencode($cat) : ''; ?>"
           style="color:#fff;font-weight:900;font-size:14px;line-height:1;text-decoration:none;">✕</a>
      </span>
      <?php endif; ?>

      <?php if($search): ?>
      <span style="background:#555;color:#fff;padding:5px 14px;border-radius:20px;
                   font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:8px;">
        🔍 "<?php echo htmlspecialchars($search); ?>"
        <a href="<?php echo $base_url; ?>products.php<?php echo $cat ? '?cat='.urlencode($cat) : ''; ?>"
           style="color:#fff;font-weight:900;font-size:14px;line-height:1;text-decoration:none;">✕</a>
      </span>
      <?php endif; ?>

      <?php if($cat): ?>
      <span style="background:#28a745;color:#fff;padding:5px 14px;border-radius:20px;
                   font-size:12px;font-weight:600;display:inline-flex;align-items:center;gap:8px;">
        🏷️ <?php echo htmlspecialchars($cat); ?>
        <a href="<?php echo $base_url; ?>products.php"
           style="color:#fff;font-weight:900;font-size:14px;line-height:1;text-decoration:none;">✕</a>
      </span>
      <?php endif; ?>

      <p style="font-size:13px;color:#888;margin:0;">
        <?php echo $show_demo ? count($demo) : $total; ?> products found
      </p>

    </div>
  </div>

  <!-- ── PRODUCT GRID ── -->
  <div class="product-grid">
    <?php if(!empty($products)): ?>
      <?php foreach($products as $p):
        $pSrc = prodImgSrc($p['product_image'] ?? '', $base_url);
      ?>
      <div class="product-card">
        <div class="product-thumb">
          <?php if($pSrc): ?>
            <img src="<?php echo $pSrc; ?>"
                 alt="<?php echo htmlspecialchars($p['product_name']); ?>"
                 onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
            <div class="no-img-placeholder" style="display:none;">
              <i class="fas fa-image"></i><span>No Image</span>
            </div>
          <?php else: ?>
            <div class="no-img-placeholder">
              <i class="fas fa-image"></i><span>No Image</span>
            </div>
          <?php endif; ?>
          <?php if(!empty($p['is_new'])): ?>
            <span class="product-badge">NEW</span>
          <?php endif; ?>
          <button class="product-heart" onclick="this.classList.toggle('active')">
            <i class="fas fa-heart"></i>
          </button>
          <div class="product-actions">
            <a href="<?php echo $base_url; ?>product-detail.php?id=<?php echo (int)$p['product_id']; ?>"
               class="prod-action-btn" title="View"><i class="fas fa-eye"></i></a>
            <button class="prod-action-btn" title="Add to Cart"
                    onclick="addToCart(<?php echo (int)$p['product_id']; ?>)">
              <i class="fas fa-shopping-cart"></i>
            </button>
          </div>
        </div>
        <p style="font-size:11px;color:#aaa;margin:8px 0 2px;
                  text-transform:uppercase;letter-spacing:.08em;">
          <?php echo htmlspecialchars($p['cat_name'] ?? ''); ?>
        </p>
        <p class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></p>
        <p class="product-price">Rs. <?php echo number_format($p['price']); ?></p>
      </div>
      <?php endforeach; ?>

    <?php elseif($show_demo && !$max && !$cat && !$search): ?>
      <?php foreach($demo as $p): ?>
      <div class="product-card product-card--demo">
        <div class="product-thumb">
          <img src="<?php echo $p['img']; ?>"
               alt="<?php echo htmlspecialchars($p['name']); ?>"
               onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
          <div class="no-img-placeholder" style="display:none;">
            <i class="fas fa-image"></i><span>Coming Soon</span>
          </div>
          <?php if($p['badge']): ?>
            <span class="product-badge <?php echo $p['badge']==='HOT'?'sale':''; ?>">
              <?php echo $p['badge']; ?>
            </span>
          <?php endif; ?>
          <button class="product-heart" onclick="this.classList.toggle('active')">
            <i class="fas fa-heart"></i>
          </button>
        </div>
        <p style="font-size:11px;color:#aaa;margin:8px 0 2px;
                  text-transform:uppercase;letter-spacing:.08em;">
          <?php echo $p['cat']; ?>
        </p>
        <p class="product-name"><?php echo $p['name']; ?></p>
        <p class="product-price">Rs. <?php echo $p['price']; ?></p>
      </div>
      <?php endforeach; ?>

    <?php else: ?>
      <div style="grid-column:1/-1;text-align:center;padding:80px 20px;">
        <i class="fas fa-box-open"
           style="font-size:64px;color:#e0d6f0;display:block;margin-bottom:20px;"></i>
        <h3 style="font-size:20px;color:#555;margin-bottom:10px;font-weight:600;">
          No Products Found
        </h3>
        <p style="font-size:14px;color:#aaa;margin-bottom:24px;line-height:1.8;">
          <?php if($search || $cat || $max): ?>
            No results match your filters. Try removing a filter.
          <?php else: ?>
            We are stocking up our shelves. Check back soon!
          <?php endif; ?>
        </p>
        <a href="<?php echo $base_url; ?>products.php"
           style="display:inline-block;padding:11px 30px;background:#7B5EA7;color:#fff;
                  border-radius:4px;text-decoration:none;font-size:13px;font-weight:600;">
          View All Products
        </a>
      </div>
    <?php endif; ?>
  </div>

  <!-- ── PAGINATION ── -->
  <?php if($pages > 1): ?>
  <div style="display:flex;justify-content:center;gap:8px;margin-top:50px;flex-wrap:wrap;">
    <?php if($page > 1): ?>
      <a href="?page=<?php echo $page-1; ?>&cat=<?php echo urlencode($cat); ?>&q=<?php echo urlencode($search); ?>&min=<?php echo $min; ?>&max=<?php echo $max; ?>"
         style="display:inline-flex;align-items:center;justify-content:center;
                width:40px;height:40px;border:1px solid #e0e0e0;
                background:#fff;color:#333;text-decoration:none;">&#8592;</a>
    <?php endif; ?>

    <?php for($i=1; $i<=$pages; $i++): ?>
      <a href="?page=<?php echo $i; ?>&cat=<?php echo urlencode($cat); ?>&q=<?php echo urlencode($search); ?>&min=<?php echo $min; ?>&max=<?php echo $max; ?>"
         style="display:inline-flex;align-items:center;justify-content:center;
                width:40px;height:40px;border:1px solid #e0e0e0;font-size:13px;
                text-decoration:none;transition:all .2s;
                background:<?php echo $i==$page?'#7B5EA7':'#fff'; ?>;
                color:<?php echo $i==$page?'#fff':'#333'; ?>;">
        <?php echo $i; ?>
      </a>
    <?php endfor; ?>

    <?php if($page < $pages): ?>
      <a href="?page=<?php echo $page+1; ?>&cat=<?php echo urlencode($cat); ?>&q=<?php echo urlencode($search); ?>&min=<?php echo $min; ?>&max=<?php echo $max; ?>"
         style="display:inline-flex;align-items:center;justify-content:center;
                width:40px;height:40px;border:1px solid #e0e0e0;
                background:#fff;color:#333;text-decoration:none;">&#8594;</a>
    <?php endif; ?>
  </div>
  <?php endif; ?>

</section>

<?php include 'includes/footer.php'; ?>