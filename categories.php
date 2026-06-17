<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Categories';

$subfolder = trim(dirname($_SERVER['PHP_SELF']), '/');
$base_url  = '/' . $subfolder . '/';

function catImgSrc($path, $base_url) {
    if(empty($path)) return '';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

$cats_result = $conn->query("
    SELECT c.*, COUNT(p.product_id) as prod_count
    FROM categories c
    LEFT JOIN products p ON p.cat_id = c.cat_id
    GROUP BY c.cat_id
    ORDER BY c.cat_id ASC
");
$categories = [];
if($cats_result) while($row = $cats_result->fetch_assoc()) $categories[] = $row;

// Accent colors per category
$accents = ['#7B5EA7','#e91e8c','#F97316','#16A34A','#2563EB'];
$emojis  = ['🎁','💌','👜','👛','✨'];
?>
<?php include 'includes/header.php'; ?>

<style>
/* ── Variables ── */
:root {
  --brand: #7B5EA7;
  --dark:  #0f0c1a;
  --light: #faf8ff;
}

/* ── Hero ── */
.cat-hero {
  background: linear-gradient(135deg, #0f0c1a 0%, #1e1030 55%, #2d1a52 100%);
  padding: 80px 60px 90px;
  position: relative;
  overflow: hidden;
  text-align: center;
}
.cat-hero::before {
  content: '';
  position: absolute;
  inset: 0;
  background-image:
    radial-gradient(circle at 20% 50%, rgba(123,94,167,.35) 0%, transparent 50%),
    radial-gradient(circle at 80% 20%, rgba(233,30,140,.20) 0%, transparent 40%),
    radial-gradient(circle at 60% 80%, rgba(249,115,22,.15) 0%, transparent 40%);
}
.cat-hero::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),
                    linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);
  background-size: 50px 50px;
}
.cat-hero-inner {
  position: relative;
  z-index: 2;
}
.cat-hero-eyebrow {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .25em;
  text-transform: uppercase;
  color: rgba(255,255,255,.5);
  margin-bottom: 20px;
}
.cat-hero-eyebrow span {
  width: 28px; height: 1px;
  background: rgba(255,255,255,.3);
  display: inline-block;
}
.cat-hero h1 {
  font-family: 'Playfair Display', serif;
  font-size: clamp(48px, 6vw, 80px);
  font-weight: 900;
  color: #fff;
  line-height: 1.05;
  margin-bottom: 16px;
  letter-spacing: -.02em;
}
.cat-hero h1 em {
  font-style: italic;
  color: #c4a8ff;
}
.cat-hero-sub {
  font-size: 15px;
  color: rgba(255,255,255,.55);
  max-width: 420px;
  margin: 0 auto 40px;
  line-height: 1.7;
}

/* Count pills strip */
.cat-pills {
  display: flex;
  justify-content: center;
  gap: 10px;
  flex-wrap: wrap;
  position: relative;
  z-index: 2;
}
.cat-pill {
  padding: 7px 18px;
  border-radius: 30px;
  font-size: 12px;
  font-weight: 600;
  letter-spacing: .06em;
  border: 1px solid rgba(255,255,255,.15);
  color: rgba(255,255,255,.7);
  background: rgba(255,255,255,.06);
  cursor: pointer;
  transition: all .2s;
  text-decoration: none;
}
.cat-pill:hover {
  background: rgba(255,255,255,.15);
  color: #fff;
  border-color: rgba(255,255,255,.3);
}

/* ── Main grid ── */
.cat-shell {
  max-width: 1200px;
  margin: 0 auto;
  padding: 70px 40px 90px;
}

/* ── Masonry-style bento grid ── */
.cat-bento {
  display: grid;
  grid-template-columns: repeat(12, 1fr);
  grid-auto-rows: 80px;
  gap: 16px;
}

/* Card base */
.cat-bento-card {
  position: relative;
  overflow: hidden;
  border-radius: 16px;
  text-decoration: none;
  color: #fff;
  display: flex;
  flex-direction: column;
  justify-content: flex-end;
  cursor: pointer;
  transition: transform .35s cubic-bezier(.34,1.56,.64,1), box-shadow .35s;
}
.cat-bento-card:hover {
  transform: translateY(-6px) scale(1.01);
  box-shadow: 0 24px 60px rgba(0,0,0,.25);
}

/* Grid positions — 5 categories */
.cat-bento-card:nth-child(1) { grid-column: 1/8;  grid-row: 1/5; }  /* Large left */
.cat-bento-card:nth-child(2) { grid-column: 8/13; grid-row: 1/3; }  /* Top right */
.cat-bento-card:nth-child(3) { grid-column: 8/13; grid-row: 3/5; }  /* Mid right */
.cat-bento-card:nth-child(4) { grid-column: 1/7;  grid-row: 5/8; }  /* Bottom left */
.cat-bento-card:nth-child(5) { grid-column: 7/13; grid-row: 5/8; }  /* Bottom right */

/* Image fill */
.cat-bento-img {
  position: absolute;
  inset: 0;
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform .6s cubic-bezier(.25,.46,.45,.94);
}
.cat-bento-card:hover .cat-bento-img { transform: scale(1.08); }

/* Gradient overlay */
.cat-bento-overlay {
  position: absolute;
  inset: 0;
  background: linear-gradient(
    to top,
    rgba(0,0,0,.85) 0%,
    rgba(0,0,0,.35) 50%,
    rgba(0,0,0,.05) 100%
  );
  transition: opacity .3s;
}
.cat-bento-card:hover .cat-bento-overlay { opacity: .9; }

/* Accent color tint on hover */
.cat-bento-tint {
  position: absolute;
  inset: 0;
  opacity: 0;
  transition: opacity .35s;
}
.cat-bento-card:hover .cat-bento-tint { opacity: .15; }

/* No image fallback */
.cat-bento-fallback {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 64px;
}

/* Content */
.cat-bento-content {
  position: relative;
  z-index: 2;
  padding: 24px 28px;
  transform: translateY(8px);
  transition: transform .3s;
}
.cat-bento-card:hover .cat-bento-content { transform: translateY(0); }

.cat-bento-emoji {
  font-size: 28px;
  margin-bottom: 6px;
  display: block;
  filter: drop-shadow(0 2px 4px rgba(0,0,0,.3));
}
.cat-bento-name {
  font-family: 'Playfair Display', serif;
  font-size: 26px;
  font-weight: 800;
  line-height: 1.1;
  margin-bottom: 6px;
  text-shadow: 0 2px 8px rgba(0,0,0,.4);
}
.cat-bento-card:nth-child(2) .cat-bento-name,
.cat-bento-card:nth-child(3) .cat-bento-name { font-size: 20px; }

.cat-bento-meta {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 12px;
  color: rgba(255,255,255,.7);
}
.cat-bento-count {
  display: inline-flex;
  align-items: center;
  gap: 5px;
  background: rgba(255,255,255,.12);
  border: 1px solid rgba(255,255,255,.18);
  border-radius: 20px;
  padding: 3px 10px;
  font-size: 11px;
  font-weight: 600;
  backdrop-filter: blur(4px);
}
.cat-bento-arrow {
  opacity: 0;
  transform: translateX(-8px);
  transition: all .25s;
  font-weight: 700;
  font-size: 13px;
}
.cat-bento-card:hover .cat-bento-arrow {
  opacity: 1;
  transform: translateX(0);
}

/* Number watermark */
.cat-bento-num {
  position: absolute;
  top: 16px;
  right: 20px;
  font-size: 64px;
  font-weight: 900;
  line-height: 1;
  color: rgba(255,255,255,.08);
  font-family: 'Playfair Display', serif;
  pointer-events: none;
  z-index: 1;
  transition: color .3s;
}
.cat-bento-card:hover .cat-bento-num { color: rgba(255,255,255,.14); }

/* ── Stats strip ── */
.cat-stats {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: 1px;
  background: #e8e0f5;
  border: 1px solid #e8e0f5;
  border-radius: 14px;
  overflow: hidden;
  margin-top: 50px;
}
.cat-stat-item {
  background: #fff;
  padding: 28px 20px;
  text-align: center;
}
.cat-stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 36px;
  font-weight: 800;
  color: var(--brand);
  line-height: 1;
  margin-bottom: 6px;
}
.cat-stat-lbl {
  font-size: 12px;
  color: #888;
  text-transform: uppercase;
  letter-spacing: .1em;
  font-weight: 600;
}

/* ── Stagger animation ── */
@keyframes fadeSlideUp {
  from { opacity: 0; transform: translateY(28px); }
  to   { opacity: 1; transform: translateY(0); }
}
.cat-bento-card {
  opacity: 0;
  animation: fadeSlideUp .55s ease forwards;
}
.cat-bento-card:nth-child(1) { animation-delay: .05s; }
.cat-bento-card:nth-child(2) { animation-delay: .15s; }
.cat-bento-card:nth-child(3) { animation-delay: .25s; }
.cat-bento-card:nth-child(4) { animation-delay: .35s; }
.cat-bento-card:nth-child(5) { animation-delay: .45s; }

/* ── Responsive ── */
@media (max-width: 900px) {
  .cat-bento {
    grid-template-columns: 1fr 1fr;
    grid-auto-rows: 200px;
  }
  .cat-bento-card:nth-child(1) { grid-column: 1/3; grid-row: auto; }
  .cat-bento-card:nth-child(2) { grid-column: auto; grid-row: auto; }
  .cat-bento-card:nth-child(3) { grid-column: auto; grid-row: auto; }
  .cat-bento-card:nth-child(4) { grid-column: auto; grid-row: auto; }
  .cat-bento-card:nth-child(5) { grid-column: auto; grid-row: auto; }
  .cat-hero { padding: 60px 24px 70px; }
  .cat-shell { padding: 48px 20px 60px; }
  .cat-stats { grid-template-columns: 1fr; }
}
@media (max-width: 560px) {
  .cat-bento { grid-template-columns: 1fr; grid-auto-rows: 220px; }
  .cat-bento-card:nth-child(1) { grid-column: 1; }
}
</style>

<!-- ── HERO ── -->
<div class="cat-hero">
  <div class="cat-hero-inner">
    <div class="cat-hero-eyebrow">
      <span></span> Arts Store Collections <span></span>
    </div>
    <h1>Shop by <em>Category</em></h1>
    <p class="cat-hero-sub">
      From heartfelt gifts to everyday luxuries — discover everything curated just for you.
    </p>
    <!-- Quick jump pills -->
    <div class="cat-pills">
      <?php foreach($categories as $i => $cat): ?>
      <a href="<?php echo $base_url; ?>products.php?cat=<?php echo urlencode($cat['cat_slug']); ?>"
         class="cat-pill">
        <?php echo $emojis[$i] ?? '✦'; ?> <?php echo htmlspecialchars($cat['cat_name']); ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<!-- ── MAIN ── -->
<div class="cat-shell">

  <!-- Bento grid -->
  <?php if(!empty($categories)): ?>
  <div class="cat-bento">
    <?php foreach($categories as $i => $cat):
      $imgSrc = catImgSrc($cat['cat_image'] ?? '', $base_url);
      $accent = $accents[$i % count($accents)];
      $emoji  = $emojis[$i % count($emojis)];
      $num    = sprintf('%02d', $i+1);
    ?>
    <a href="<?php echo $base_url; ?>products.php?cat=<?php echo urlencode($cat['cat_slug']); ?>"
       class="cat-bento-card"
       style="background:<?php echo $accent; ?>22;">

      <!-- Image -->
      <?php if($imgSrc): ?>
        <img class="cat-bento-img"
             src="<?php echo htmlspecialchars($imgSrc); ?>"
             alt="<?php echo htmlspecialchars($cat['cat_name']); ?>"
             onerror="this.style.display='none'"/>
      <?php else: ?>
        <div class="cat-bento-fallback" style="background:<?php echo $accent; ?>22;">
          <?php echo $emoji; ?>
        </div>
      <?php endif; ?>

      <!-- Overlays -->
      <div class="cat-bento-overlay"></div>
      <div class="cat-bento-tint" style="background:<?php echo $accent; ?>;"></div>

      <!-- Watermark number -->
      <div class="cat-bento-num"><?php echo $num; ?></div>

      <!-- Content -->
      <div class="cat-bento-content">
        <span class="cat-bento-emoji"><?php echo $emoji; ?></span>
        <div class="cat-bento-name"><?php echo htmlspecialchars($cat['cat_name']); ?></div>
        <?php if(!empty($cat['cat_desc'])): ?>
          <p style="font-size:12px;color:rgba(255,255,255,.65);margin-bottom:10px;line-height:1.5;
                    max-width:300px;display:none;" class="cat-bento-desc">
            <?php echo htmlspecialchars(mb_substr($cat['cat_desc'],0,80)); ?>
          </p>
        <?php endif; ?>
        <div class="cat-bento-meta">
          <span class="cat-bento-count">
            <i class="fas fa-box" style="font-size:10px;"></i>
            <?php echo (int)$cat['prod_count']; ?> Products
          </span>
          <span class="cat-bento-arrow">Shop Now →</span>
        </div>
      </div>

    </a>
    <?php endforeach; ?>
  </div>

  <!-- Stats strip -->
  <?php
  $total_prods = array_sum(array_column($categories,'prod_count'));
  $total_cats  = count($categories);
  ?>
  <div class="cat-stats">
    <div class="cat-stat-item">
      <div class="cat-stat-num"><?php echo $total_cats; ?></div>
      <div class="cat-stat-lbl">Categories</div>
    </div>
    <div class="cat-stat-item">
      <div class="cat-stat-num"><?php echo $total_prods; ?>+</div>
      <div class="cat-stat-lbl">Products</div>
    </div>
    <div class="cat-stat-item">
      <div class="cat-stat-num">7</div>
      <div class="cat-stat-lbl">Day Returns</div>
    </div>
  </div>

  <?php else: ?>
  <div style="text-align:center;padding:100px 20px;">
    <div style="font-size:64px;margin-bottom:20px;">🛍️</div>
    <h3 style="font-size:22px;color:#555;margin-bottom:10px;">No Categories Yet</h3>
    <p style="color:#aaa;font-size:14px;">Categories will appear here once added from the admin panel.</p>
  </div>
  <?php endif; ?>

</div>

<?php include 'includes/footer.php'; ?>