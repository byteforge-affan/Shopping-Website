<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Home';

$subfolder = trim(dirname($_SERVER['PHP_SELF']), '/');
$base_url  = '/' . $subfolder . '/';

function imgPath($path, $base_url) {
    if(empty($path)) return '';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

$total_products = 0;
$count_result   = $conn->query("SELECT COUNT(*) as total FROM products");
if($count_result) $total_products = (int)$count_result->fetch_assoc()['total'];

$demo_threshold = 10;
$show_demo      = ($total_products < $demo_threshold);

$products_result = $conn->query("
    SELECT p.*, c.cat_slug, c.cat_name as cat_label
    FROM products p LEFT JOIN categories c ON p.cat_id = c.cat_id
    WHERE p.is_new = 1 ORDER BY p.created_at DESC LIMIT 8
");
$products = [];
if($products_result) while($row = $products_result->fetch_assoc()) $products[] = $row;

if(empty($products)) {
    $products_result = $conn->query("
        SELECT p.*, c.cat_slug, c.cat_name as cat_label
        FROM products p LEFT JOIN categories c ON p.cat_id = c.cat_id
        ORDER BY p.created_at DESC LIMIT 8
    ");
    if($products_result) while($row = $products_result->fetch_assoc()) $products[] = $row;
}

$featured_result = $conn->query("
    SELECT p.*, c.cat_slug, c.cat_name as cat_label
    FROM products p LEFT JOIN categories c ON p.cat_id = c.cat_id
    ORDER BY p.price DESC LIMIT 4
");
$featured = [];
if($featured_result) while($row = $featured_result->fetch_assoc()) $featured[] = $row;

$cats_result = $conn->query("
    SELECT c.*, COUNT(p.product_id) as prod_count
    FROM categories c LEFT JOIN products p ON p.cat_id = c.cat_id
    GROUP BY c.cat_id ORDER BY c.cat_id ASC LIMIT 5
");
$categories = [];
if($cats_result) while($row = $cats_result->fetch_assoc()) $categories[] = $row;

$bestsellers_result = $conn->query("
    SELECT p.*, COALESCE(SUM(oi.qty),0) as total_sold
    FROM products p LEFT JOIN order_items oi ON oi.product_id = p.product_id
    GROUP BY p.product_id ORDER BY total_sold DESC LIMIT 4
");
$bestsellers = [];
if($bestsellers_result) while($row = $bestsellers_result->fetch_assoc()) $bestsellers[] = $row;

// ── Fetch real customer feedback for reviews section ──
$reviews_result = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC LIMIT 6");
$reviews = [];
if($reviews_result) while($row = $reviews_result->fetch_assoc()) $reviews[] = $row;
?>
<?php include 'includes/header.php'; ?>

<!-- HERO SLIDER -->
<div class="hero-slider" id="heroSlider">
  <div class="slide active">
    <div class="slide-content">
      <p class="slide-sub">New Arrivals — 2024 Collection</p>
      <h1 class="slide-title">Gift &amp;<br/>Cards</h1>
      <a href="<?php echo $base_url; ?>products.php?cat=gift-articles" class="btn-shop">Shop Now</a>
    </div>
    <img class="slide-img" src="<?php echo $base_url; ?>images/gift.png" alt="Gift Articles"
         onerror="this.onerror=null;this.style.display='none'"/>
  </div>
  <div class="slide">
    <div class="slide-content">
      <p class="slide-sub">Trending Now</p>
      <h1 class="slide-title">Hand<br/>Bags</h1>
      <a href="<?php echo $base_url; ?>products.php?cat=hand-bags" class="btn-shop">Shop Now</a>
    </div>
    <img class="slide-img" src="<?php echo $base_url; ?>images/hand_bag.png" alt="Hand Bags"
         onerror="this.onerror=null;this.style.display='none'"/>
  </div>
  <div class="slide">
    <div class="slide-content">
      <p class="slide-sub">Special Collection</p>
      <h1 class="slide-title">Beauty<br/>Products</h1>
      <a href="<?php echo $base_url; ?>products.php?cat=beauty-products" class="btn-shop">Shop Now</a>
    </div>
    <img class="slide-img" src="<?php echo $base_url; ?>images/beauty_product.png" alt="Beauty Products"
         onerror="this.onerror=null;this.style.display='none'"/>
  </div>
  <button class="slider-arrow prev" onclick="changeSlide(-1)">&#8592;</button>
  <button class="slider-arrow next" onclick="changeSlide(1)">&#8594;</button>
  <div class="slider-dots">
    <span class="dot active" onclick="goToSlide(0)"></span>
    <span class="dot" onclick="goToSlide(1)"></span>
    <span class="dot" onclick="goToSlide(2)"></span>
  </div>
</div>

<!-- FEATURES STRIP -->
<section class="features-strip">
  <div class="feat-item">
    <div class="feat-icon"><i class="fas fa-truck"></i></div>
    <div class="feat-text"><strong>Free Delivery</strong><span>On orders above Rs. 5,000</span></div>
  </div>
  <div class="feat-item">
    <div class="feat-icon"><i class="fas fa-undo-alt"></i></div>
    <div class="feat-text"><strong>7-Day Returns</strong><span>Easy return &amp; exchange</span></div>
  </div>
  <div class="feat-item">
    <div class="feat-icon"><i class="fas fa-shield-alt"></i></div>
    <div class="feat-text"><strong>Secure Payment</strong><span>Card, Cheque or Cash on Delivery</span></div>
  </div>
  <div class="feat-item">
    <div class="feat-icon"><i class="fas fa-headset"></i></div>
    <div class="feat-text"><strong>24/7 Support</strong><span>Always here to help you</span></div>
  </div>
</section>

<!-- FEATURED CATEGORIES -->
<section class="feat-cats">
  <div class="section-header">
    <h2>Shop by Category</h2>
    <div class="section-line"></div>
  </div>
  <div class="feat-cats-grid">
    <?php if(!empty($categories)):
      $layout = ['fc-large', '', '', '', ''];
      foreach($categories as $i => $cat):
        $extraClass = $layout[$i] ?? '';
        $catSrc     = imgPath($cat['cat_image'] ?? '', $base_url);
    ?>
    <a href="<?php echo $base_url; ?>products.php?cat=<?php echo urlencode($cat['cat_slug']); ?>"
       class="fc-card <?php echo $extraClass; ?>">
      <?php if($catSrc): ?>
        <img src="<?php echo $catSrc; ?>" alt="<?php echo htmlspecialchars($cat['cat_name']); ?>"
             onerror="this.onerror=null;this.style.display='none'"/>
      <?php else: ?>
        <div class="no-img-placeholder"><i class="fas fa-image"></i></div>
      <?php endif; ?>
      <div class="fc-info">
        <h3><?php echo htmlspecialchars($cat['cat_name']); ?></h3>
        <span><?php echo (int)$cat['prod_count']; ?> Products &nbsp;→</span>
      </div>
    </a>
    <?php endforeach; else: ?>
      <p style="text-align:center;color:#999;padding:40px;grid-column:1/-1;">No categories found.</p>
    <?php endif; ?>
  </div>
</section>

<!-- NEW ARRIVALS -->
<section class="products-section">
  <div class="section-header">
    <h2>New Arrivals</h2>
    <div class="section-line"></div>
    <p style="color:#888;margin-top:8px;font-size:14px;">Fresh picks just added to our collection</p>
  </div>
  <div class="product-grid" id="productGrid">
  <?php
  $demo_products = [
    ['name'=>'Crystal Vase',       'price'=>'2,500','badge'=>'NEW','cat'=>'Home Decor',   'img'=>$base_url.'images/demo/crystal-vase.jpg'],
    ['name'=>'Perfume Gift Set',   'price'=>'3,800','badge'=>'HOT','cat'=>'Beauty',       'img'=>$base_url.'images/demo/perfume-set.jpg'],
    ['name'=>'Luxury Watch Box',   'price'=>'5,500','badge'=>'NEW','cat'=>'Gift Articles','img'=>$base_url.'images/demo/watch-box.jpg'],
    ['name'=>'Scented Candle Set', 'price'=>'1,400','badge'=>'',   'cat'=>'Home Decor',   'img'=>$base_url.'images/demo/candle-set.jpg'],
    ['name'=>'Rose Gold Bracelet', 'price'=>'4,200','badge'=>'NEW','cat'=>'Jewellery',    'img'=>$base_url.'images/demo/bracelet.jpg'],
    ['name'=>'Mini Makeup Kit',    'price'=>'2,100','badge'=>'HOT','cat'=>'Beauty',       'img'=>$base_url.'images/demo/makeup-kit.jpg'],
    ['name'=>'Leather Clutch Bag', 'price'=>'3,300','badge'=>'',   'cat'=>'Hand Bags',    'img'=>$base_url.'images/demo/clutch-bag.jpg'],
    ['name'=>'Silk Scarf',         'price'=>'1,900','badge'=>'',   'cat'=>'Accessories',  'img'=>$base_url.'images/demo/silk-scarf.jpg'],
  ];

  $real_count = count($products);
  $slots_left = ($show_demo && $real_count > 0) ? max(0, 8 - $real_count) : 0;

  if($real_count > 0):
    foreach($products as $p):
      $pSrc = imgPath($p['product_image'] ?? '', $base_url);
  ?>
    <div class="product-card" data-cat="<?php echo (int)$p['cat_id']; ?>">
      <div class="product-thumb">
        <?php if($pSrc): ?>
          <img src="<?php echo $pSrc; ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>"
               onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
          <div class="no-img-placeholder" style="display:none;"><i class="fas fa-image"></i><span>No Image</span></div>
        <?php else: ?>
          <div class="no-img-placeholder"><i class="fas fa-image"></i><span>No Image</span></div>
        <?php endif; ?>
        <span class="product-badge">NEW</span>
        <button class="product-heart" onclick="toggleWishlist(this,<?php echo (int)$p['product_id']; ?>)"><i class="fas fa-heart"></i></button>
        <div class="product-actions">
          <a href="<?php echo $base_url; ?>product-detail.php?id=<?php echo (int)$p['product_id']; ?>" class="prod-action-btn"><i class="fas fa-eye"></i></a>
          <button class="prod-action-btn" onclick="addToCart(<?php echo (int)$p['product_id']; ?>)"><i class="fas fa-shopping-cart"></i></button>
        </div>
      </div>
      <?php if(!empty($p['cat_label'])): ?>
        <p class="product-cat-label"><?php echo htmlspecialchars($p['cat_label']); ?></p>
      <?php endif; ?>
      <p class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></p>
      <p class="product-price">Rs. <?php echo number_format($p['price']); ?></p>
    </div>
  <?php endforeach;
    if($slots_left > 0):
      foreach(array_slice($demo_products, 0, $slots_left) as $p): ?>
    <div class="product-card product-card--demo" data-cat="0">
      <div class="product-thumb">
        <img src="<?php echo $p['img']; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"
             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
        <div class="no-img-placeholder" style="display:none;"><i class="fas fa-image"></i><span>Coming Soon</span></div>
        <?php if($p['badge']): ?>
          <span class="product-badge <?php echo $p['badge']==='HOT'?'sale':''; ?>"><?php echo $p['badge']; ?></span>
        <?php endif; ?>
      </div>
      <p class="product-cat-label"><?php echo $p['cat']; ?></p>
      <p class="product-name"><?php echo $p['name']; ?></p>
      <p class="product-price">Rs. <?php echo $p['price']; ?></p>
    </div>
  <?php endforeach; endif;
  elseif($show_demo):
    foreach($demo_products as $p): ?>
    <div class="product-card product-card--demo" data-cat="0">
      <div class="product-thumb">
        <img src="<?php echo $p['img']; ?>" alt="<?php echo htmlspecialchars($p['name']); ?>"
             onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
        <div class="no-img-placeholder" style="display:none;"><i class="fas fa-image"></i><span>Coming Soon</span></div>
        <?php if($p['badge']): ?>
          <span class="product-badge <?php echo $p['badge']==='HOT'?'sale':''; ?>"><?php echo $p['badge']; ?></span>
        <?php endif; ?>
        <button class="product-heart" onclick="this.classList.toggle('active')"><i class="fas fa-heart"></i></button>
      </div>
      <p class="product-cat-label"><?php echo $p['cat']; ?></p>
      <p class="product-name"><?php echo $p['name']; ?></p>
      <p class="product-price">Rs. <?php echo $p['price']; ?></p>
    </div>
  <?php endforeach;
  else: ?>
    <div style="grid-column:1/-1;text-align:center;padding:80px 20px;">
      <i class="fas fa-box-open" style="font-size:64px;color:#e0d6f0;display:block;margin-bottom:20px;"></i>
      <h3 style="font-size:20px;color:#555;margin-bottom:10px;">No Products Yet</h3>
      <p style="font-size:14px;color:#aaa;margin-bottom:24px;">We are stocking up our shelves. Check back soon!</p>
      <a href="<?php echo $base_url; ?>categories.php"
         style="display:inline-block;padding:11px 30px;background:#7B5EA7;color:#fff;border-radius:4px;font-size:13px;font-weight:600;">
        Browse Categories
      </a>
    </div>
  <?php endif; ?>
  </div>
  <div class="load-more-wrap">
    <a href="<?php echo $base_url; ?>products.php" class="btn-load">View All Products</a>
  </div>
</section>

<!-- FEATURED PRODUCTS -->
<?php if(!empty($featured)): ?>
<section class="products-section" style="background:#faf8ff;padding:60px 0;">
  <div class="section-header">
    <h2>Featured Products</h2>
    <div class="section-line"></div>
    <p style="color:#888;margin-top:8px;font-size:14px;">Handpicked premium products just for you</p>
  </div>
  <div class="product-grid">
    <?php foreach($featured as $p):
      $pSrc = imgPath($p['product_image'] ?? '', $base_url); ?>
    <div class="product-card">
      <div class="product-thumb">
        <?php if($pSrc): ?>
          <img src="<?php echo $pSrc; ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>"
               onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
          <div class="no-img-placeholder" style="display:none;"><i class="fas fa-image"></i><span>No Image</span></div>
        <?php else: ?>
          <div class="no-img-placeholder"><i class="fas fa-image"></i><span>No Image</span></div>
        <?php endif; ?>
        <span class="product-badge" style="background:#f0ad00;">⭐ TOP</span>
        <button class="product-heart" onclick="toggleWishlist(this,<?php echo (int)$p['product_id']; ?>)"><i class="fas fa-heart"></i></button>
        <div class="product-actions">
          <a href="<?php echo $base_url; ?>product-detail.php?id=<?php echo (int)$p['product_id']; ?>" class="prod-action-btn"><i class="fas fa-eye"></i></a>
          <button class="prod-action-btn" onclick="addToCart(<?php echo (int)$p['product_id']; ?>)"><i class="fas fa-shopping-cart"></i></button>
        </div>
      </div>
      <?php if(!empty($p['cat_label'])): ?>
        <p class="product-cat-label"><?php echo htmlspecialchars($p['cat_label']); ?></p>
      <?php endif; ?>
      <p class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></p>
      <p class="product-price">Rs. <?php echo number_format($p['price']); ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- SHOP BY PRICE -->
<section style="padding:60px 40px;background:#fff;">
  <div class="section-header">
    <h2>Shop by Price</h2>
    <div class="section-line"></div>
    <p style="color:#888;margin-top:8px;font-size:14px;">Find the perfect gift within your budget</p>
  </div>
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-top:40px;">
    <?php foreach([
      ['🎀','Budget Pick',  '#f0ad00','Under Rs. 500',    'Cards, accessories & more',  '0&max=500',        '#fff9f0','#fde8b0'],
      ['🛍️','Popular Range','#28a745','Rs. 500 – 1,500',  'Wallets, gift sets & more',  '500&max=1500',     '#f0fff4','#b0e8c8'],
      ['👜','Mid Range',    '#7B5EA7','Rs. 1,500 – 3,500','Bags, beauty & perfumes',    '1500&max=3500',    '#f0f4ff','#b0c4ff'],
      ['👑','Premium',      '#e91e8c','Rs. 3,500 & Above', 'Luxury gifts & hampers',    '3500&max=999999',  '#fff0f6','#ffb0d0'],
    ] as $pb): ?>
    <a href="<?php echo $base_url; ?>products.php?min=<?php echo $pb[5]; ?>" style="text-decoration:none;">
      <div style="background:linear-gradient(135deg,<?php echo $pb[6]; ?>,<?php echo $pb[7]; ?>);
                  border:1px solid <?php echo $pb[7]; ?>;border-radius:12px;padding:30px 20px;text-align:center;transition:all .3s;"
           onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 30px rgba(0,0,0,.10)'"
           onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
        <div style="font-size:32px;margin-bottom:12px;"><?php echo $pb[0]; ?></div>
        <p style="font-size:11px;font-weight:700;letter-spacing:.15em;text-transform:uppercase;color:<?php echo $pb[2]; ?>;margin-bottom:6px;"><?php echo $pb[1]; ?></p>
        <h3 style="font-size:22px;font-weight:800;color:#1a1a1a;margin-bottom:4px;"><?php echo $pb[3]; ?></h3>
        <p style="font-size:12px;color:#888;"><?php echo $pb[4]; ?></p>
        <div style="margin-top:16px;display:inline-block;padding:7px 20px;background:<?php echo $pb[2]; ?>;color:#fff;border-radius:20px;font-size:11px;font-weight:700;">SHOP NOW →</div>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<!-- BEST SELLERS -->
<?php if(!empty($bestsellers)): ?>
<section class="products-section" style="background:#f9f9f9;padding:40px 0;">
  <div class="section-header">
    <h2>Best Sellers</h2>
    <div class="section-line"></div>
    <p style="color:#888;margin-top:8px;font-size:14px;">Most loved products by our customers</p>
  </div>
  <div class="product-grid">
    <?php foreach($bestsellers as $p):
      $pSrc = imgPath($p['product_image'] ?? '', $base_url); ?>
    <div class="product-card">
      <div class="product-thumb">
        <?php if($pSrc): ?>
          <img src="<?php echo $pSrc; ?>" alt="<?php echo htmlspecialchars($p['product_name']); ?>"
               onerror="this.onerror=null;this.style.display='none';this.nextElementSibling.style.display='flex'"/>
          <div class="no-img-placeholder" style="display:none;"><i class="fas fa-image"></i><span>No Image</span></div>
        <?php else: ?>
          <div class="no-img-placeholder"><i class="fas fa-image"></i><span>No Image</span></div>
        <?php endif; ?>
        <span class="product-badge sale">BEST</span>
        <button class="product-heart" onclick="toggleWishlist(this,<?php echo (int)$p['product_id']; ?>)"><i class="fas fa-heart"></i></button>
        <div class="product-actions">
          <a href="<?php echo $base_url; ?>product-detail.php?id=<?php echo (int)$p['product_id']; ?>" class="prod-action-btn"><i class="fas fa-eye"></i></a>
          <button class="prod-action-btn" onclick="addToCart(<?php echo (int)$p['product_id']; ?>)"><i class="fas fa-shopping-cart"></i></button>
        </div>
      </div>
      <p class="product-name"><?php echo htmlspecialchars($p['product_name']); ?></p>
      <p class="product-price">Rs. <?php echo number_format($p['price']); ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>
<?php endif; ?>

<!-- HOT SALE BANNER -->
<section class="hot-sale-banner">
  <div class="hsb-left">
    <span class="hsb-tag">LIMITED TIME OFFER</span>
    <h2 class="hsb-title">Hot Sale <span>UP TO 30% OFF</span></h2>
    <p class="hsb-sub">On Gift Articles, Greeting Cards &amp; Beauty Products.<br/>Hurry — offer ends soon!</p>
    <div class="hsb-btns">
      <a href="<?php echo $base_url; ?>products.php" class="btn-shop">Shop the Sale</a>
      <a href="<?php echo $base_url; ?>products.php?cat=beauty-products" class="btn-outline" style="color:#fff;border-color:#fff;">Beauty</a>
    </div>
  </div>
  <div class="hsb-right">
    <?php
    $sale_cats      = array_slice($categories, 0, 3);
    $sale_discounts = ['30% OFF','20% OFF','15% OFF'];
    if(!empty($sale_cats)):
      foreach($sale_cats as $si => $sc):
        $scSrc = imgPath($sc['cat_image'] ?? '', $base_url); ?>
    <div class="hsb-card">
      <div class="hsb-card-badge"><?php echo $sale_discounts[$si]; ?></div>
      <?php if($scSrc): ?>
        <img src="<?php echo $scSrc; ?>" alt="<?php echo htmlspecialchars($sc['cat_name']); ?>"
             onerror="this.onerror=null;this.style.display='none'"/>
      <?php endif; ?>
      <p><?php echo htmlspecialchars($sc['cat_name']); ?></p>
    </div>
    <?php endforeach;
    else: ?>
    <div class="hsb-card"><div class="hsb-card-badge">30% OFF</div><img src="https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=300" alt="Beauty" onerror="this.onerror=null;this.style.display='none'"/><p>Beauty Products</p></div>
    <div class="hsb-card"><div class="hsb-card-badge">20% OFF</div><img src="https://images.unsplash.com/photo-1549465220-1a8b9238cd48?w=300" alt="Gifts" onerror="this.onerror=null;this.style.display='none'"/><p>Gift Articles</p></div>
    <div class="hsb-card"><div class="hsb-card-badge">15% OFF</div><img src="https://images.unsplash.com/photo-1548036328-c9fa89d128fa?w=300" alt="Bags" onerror="this.onerror=null;this.style.display='none'"/><p>Hand Bags</p></div>
    <?php endif; ?>
  </div>
</section>

<!-- ===== CUSTOMER REVIEWS — replaces newsletter ===== -->
<section style="padding:70px 40px;background:#faf8ff;">
  <div class="section-header">
    <h2>What Our Customers Say</h2>
    <div class="section-line"></div>
    <p style="color:#888;margin-top:8px;font-size:14px;">Real words from real shoppers</p>
  </div>

  <!-- Star summary bar -->
  <div style="display:flex;align-items:center;justify-content:center;gap:32px;
              margin:32px auto 48px;max-width:500px;flex-wrap:wrap;">
    <div style="text-align:center;">
      <p style="font-size:52px;font-weight:800;color:#7B5EA7;line-height:1;">4.8</p>
      <div style="color:#f0ad00;font-size:18px;letter-spacing:3px;margin:4px 0;">★★★★★</div>
      <p style="font-size:12px;color:#aaa;">Average Rating</p>
    </div>
    <div style="width:1px;height:60px;background:#e0e0e0;"></div>
    <div style="display:flex;flex-direction:column;gap:6px;min-width:180px;">
      <?php foreach([['5★',90],['4★',7],['3★',2],['2★',1],['1★',0]] as $r): ?>
      <div style="display:flex;align-items:center;gap:8px;">
        <span style="font-size:12px;color:#888;width:20px;"><?php echo $r[0]; ?></span>
        <div style="flex:1;height:6px;background:#ece8f5;border-radius:3px;overflow:hidden;">
          <div style="width:<?php echo $r[1]; ?>%;height:100%;background:#7B5EA7;border-radius:3px;"></div>
        </div>
        <span style="font-size:12px;color:#aaa;width:26px;"><?php echo $r[1]; ?>%</span>
      </div>
      <?php endforeach; ?>
    </div>
  </div>

  <!-- Review cards -->
  <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:24px;max-width:1100px;margin:0 auto;">

    <?php
    // Use real feedback from DB if available, otherwise use static reviews
    $static_reviews = [
      ['name'=>'Sara Ahmed',   'city'=>'Islamabad', 'stars'=>5, 'date'=>'March 2026',
       'text'=>'I recently ordered from Arts Store and the experience was amazing. The Leather Tote Bag arrived in perfect condition. Packaging was neat and delivery was on time. Will definitely order again!'],
      ['name'=>'Ali Raza',     'city'=>'Lahore',    'stars'=>5, 'date'=>'March 2026',
       'text'=>'Very happy with my purchase. The product quality is excellent and the delivery was faster than expected. Customer service was also very helpful. Highly recommend Arts Store!'],
      ['name'=>'Usman Malik',  'city'=>'Karachi',   'stars'=>5, 'date'=>'March 2026',
       'text'=>'Bought the Perfume Gift Set as a birthday present for my wife and she absolutely loved it. The presentation box was beautiful. This is now my go-to store for gifts!'],
      ['name'=>'Hira Ch',      'city'=>'Karachi',   'stars'=>5, 'date'=>'March 2026',
       'text'=>'Very smooth and simple website. Found exactly what I was looking for within minutes. The Cash on Delivery option is very convenient. Great work!']
    ];

    $display_reviews = !empty($reviews) ? array_map(function($r) {
      // Map feedback table to display format
      $name = explode('@', $r['email'])[0]; // extract name from email
      $name = ucwords(str_replace(['.','_','-'], ' ', $name));
      return ['name'=>$name,'city'=>'Pakistan','stars'=>5,'date'=>date('F Y',strtotime($r['created_at'])),'text'=>$r['message']];
    }, array_slice($reviews, 0, 6)) : $static_reviews;

    foreach($display_reviews as $rev):
      $initials = strtoupper(substr($rev['name'],0,1));
      $colors   = ['#7B5EA7','#e91e8c','#28a745','#f0ad00','#2196f3','#ff5722'];
      $color    = $colors[array_search($rev, $display_reviews) % count($colors)];
    ?>
    <div style="background:#fff;border:1px solid #ece8f5;border-radius:12px;
                padding:24px;box-shadow:0 2px 16px rgba(123,94,167,.06);
                transition:box-shadow .25s,transform .25s;position:relative;"
         onmouseover="this.style.boxShadow='0 8px 30px rgba(123,94,167,.14)';this.style.transform='translateY(-3px)'"
         onmouseout="this.style.boxShadow='0 2px 16px rgba(123,94,167,.06)';this.style.transform='translateY(0)'">

      <!-- Quote icon -->
      <div style="position:absolute;top:16px;right:20px;font-size:36px;color:#ece8f5;font-family:Georgia,serif;line-height:1;">"</div>

      <!-- Stars -->
      <div style="color:#f0ad00;font-size:14px;letter-spacing:2px;margin-bottom:14px;">
        <?php echo str_repeat('★', (int)$rev['stars']); ?><?php echo str_repeat('☆', 5-(int)$rev['stars']); ?>
      </div>

      <!-- Review text -->
      <p style="font-size:13px;color:#555;line-height:1.8;margin-bottom:18px;">
        <?php echo htmlspecialchars(mb_substr($rev['text'],0,160)).(mb_strlen($rev['text'])>160?'…':''); ?>
      </p>

      <!-- Reviewer -->
      <div style="display:flex;align-items:center;gap:12px;border-top:1px solid #f5f0ff;padding-top:14px;">
        <div style="width:38px;height:38px;border-radius:50%;background:<?php echo $color; ?>;
                    display:flex;align-items:center;justify-content:center;
                    font-size:15px;font-weight:700;color:#fff;flex-shrink:0;">
          <?php echo $initials; ?>
        </div>
        <div>
          <p style="font-size:13px;font-weight:600;color:#333;"><?php echo htmlspecialchars($rev['name']); ?></p>
          <p style="font-size:11px;color:#aaa;"><?php echo htmlspecialchars($rev['city']); ?> · <?php echo htmlspecialchars($rev['date']); ?></p>
        </div>
        <div style="margin-left:auto;">
          <span style="font-size:10px;background:#f0fdf4;color:#16a34a;border:1px solid #bbf7d0;
                       padding:2px 8px;border-radius:20px;font-weight:600;">✓ Verified</span>
        </div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- CTA -->
  <div style="text-align:center;margin-top:40px;">
    <a href="<?php echo $base_url; ?>contact.php"
       style="display:inline-flex;align-items:center;gap:8px;padding:12px 28px;
              border:2px solid #7B5EA7;color:#7B5EA7;border-radius:6px;
              font-size:13px;font-weight:600;text-decoration:none;transition:all .2s;"
       onmouseover="this.style.background='#7B5EA7';this.style.color='#fff'"
       onmouseout="this.style.background='transparent';this.style.color='#7B5EA7'">
      <i class="fas fa-pen"></i> Share Your Experience
    </a>
  </div>
</section>

<?php include 'includes/footer.php'; ?>

<script>
let current = 0;
const slides = document.querySelectorAll('.slide');
const dots   = document.querySelectorAll('.dot');

function changeSlide(dir) {
  slides[current].classList.remove('active');
  dots[current]?.classList.remove('active');
  current = (current + dir + slides.length) % slides.length;
  slides[current].classList.add('active');
  dots[current]?.classList.add('active');
}
function goToSlide(index) {
  slides[current].classList.remove('active');
  dots[current]?.classList.remove('active');
  current = index;
  slides[current].classList.add('active');
  dots[current]?.classList.add('active');
}
setInterval(() => changeSlide(1), 4500);
</script>