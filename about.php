<?php
session_start();
$page_title = 'About';
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero">
  <h1>About</h1>
</div>

<section class="about-section">
  <div class="about-grid">
    <div class="reveal-left">
      <img class="about-img"
           src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=700"
           alt="Arts Store"/>
    </div>
    <div class="about-text reveal-right">
      <h2>Our Story</h2>
      <p>
        Arts Store was founded with one simple mission — to make beautiful, meaningful gifts and
        accessories accessible to everyone. From our humble beginning as a small stationery shop,
        we have grown into a beloved destination for gift articles, greeting cards, dolls,
        handbags, wallets, and beauty products.
      </p>
      <p>
        In this fast-paced world where time is precious, we believe shopping for the perfect gift
        or accessory should be effortless. That is why we built Arts Store — so you can browse
        our carefully curated collection and place your order from the comfort of your home.
      </p>
      <p>
        Every product in our store is chosen with care. We work with talented artisans and
        quality-conscious brands to bring you items that are not just beautiful, but meaningful.
      </p>
      <a href="products.php" class="btn-shop" style="margin-top:10px;">Browse Our Collection</a>
    </div>
  </div>

  <!-- STATS with count-up -->
  <div class="about-stats-grid">
    <?php foreach([
      ['5',    '+', 'Years in Business'],
      ['200',  '+', 'Products Available'],
      ['10000','',  'Happy Customers'],
      ['7',    '',  'Days Return Policy'],
    ] as $stat): ?>
    <div class="about-stat-card reveal-up">
      <div class="about-stat-num"
           data-target="<?php echo $stat[0]; ?>"
           data-suffix="<?php echo $stat[1]; ?>">
        0<?php echo $stat[1]; ?>
      </div>
      <p class="about-stat-lbl"><?php echo $stat[2]; ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<!-- WHY US -->
<section style="background:#EDE9E3;padding:70px 40px;">
  <div class="section-header">
    <h2>Why Choose Us</h2>
    <div class="section-line"></div>
  </div>
  <div class="why-grid">
    <?php foreach([
      ['fas fa-gift',       'Unique Products',   'Carefully curated collection of gifts, cards, and accessories for every occasion.'],
      ['fas fa-truck',      'Home Delivery',     'We deliver right to your doorstep across Pakistan. Cash on delivery available.'],
      ['fas fa-undo-alt',   '7-Day Returns',     'Not satisfied? Return or exchange any product within 7 days of delivery.'],
      ['fas fa-shield-alt', 'Secure Payments',   'Pay by credit card, cheque, or cash on delivery. All transactions are safe.'],
      ['fas fa-star',       'Quality Guarantee', 'Every product is quality checked before dispatch. Warranty cards included.'],
      ['fas fa-headset',    '24/7 Support',      'Our support team is always ready to help you with orders and queries.'],
    ] as $i => $f): ?>
    <div class="why-card reveal-up" style="transition-delay:<?php echo $i*0.08; ?>s">
      <div class="why-icon"><i class="<?php echo $f[0]; ?>"></i></div>
      <h3><?php echo $f[1]; ?></h3>
      <p><?php echo $f[2]; ?></p>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<style>
/* ── ABOUT STATS ── */
.about-stats-grid {
  display: grid;
  grid-template-columns: repeat(4,1fr);
  gap: 30px;
  margin-top: 80px;
  text-align: center;
}
.about-stat-card {
  border: 1px solid #e0e0e0;
  padding: 40px 20px;
  transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
}
.about-stat-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 12px 30px rgba(123,94,167,.12);
  border-color: #7B5EA7;
}
.about-stat-num {
  font-family: 'Playfair Display', serif;
  font-size: 52px;
  font-weight: 700;
  color: #7B5EA7;
  line-height: 1;
  transition: color .3s ease;
}
.about-stat-card:hover .about-stat-num { color: #5a4080; }
.about-stat-lbl {
  font-size: 12px;
  letter-spacing: .12em;
  text-transform: uppercase;
  color: #888;
  margin-top: 12px;
}

/* ── REVEAL CLASSES ── */
.reveal-left  { opacity:0; transform:translateX(-40px); transition:opacity .7s ease, transform .7s ease; }
.reveal-right { opacity:0; transform:translateX(40px);  transition:opacity .7s ease, transform .7s ease; }
.reveal-up    { opacity:0; transform:translateY(30px);  transition:opacity .6s ease, transform .6s ease; }
.revealed     { opacity:1 !important; transform:none !important; }

/* ── WHY GRID ── */
.why-grid {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  gap: 30px;
  margin-top: 50px;
}
.why-card {
  background: #fff;
  padding: 40px 30px;
  text-align: center;
  border: 1px solid #e0e0e0;
  transition: transform .3s ease, box-shadow .3s ease;
}
.why-card:hover {
  transform: translateY(-5px);
  box-shadow: 0 10px 28px rgba(0,0,0,.08);
}
.why-icon {
  font-size: 32px;
  color: #7B5EA7;
  margin-bottom: 18px;
  transition: transform .3s cubic-bezier(.34,1.56,.64,1);
}
.why-card:hover .why-icon { transform: scale(1.18); }
.why-card h3 {
  font-family: 'Playfair Display', serif;
  font-size: 20px;
  margin-bottom: 12px;
}
.why-card p { font-size: 13px; color: #888; line-height: 1.8; }

@media(max-width:768px) {
  .about-stats-grid { grid-template-columns: 1fr 1fr; gap:16px; margin-top:50px; }
  .about-stat-num   { font-size:38px; }
  .why-grid         { grid-template-columns: 1fr; }
}
@media(max-width:480px) {
  .about-stats-grid { grid-template-columns: 1fr 1fr; }
}
</style>

<script>
/* ── NUMBER COUNT-UP ── */
function countUp(el) {
  var target = parseInt(el.getAttribute('data-target'), 10);
  var suffix = el.getAttribute('data-suffix') || '';
  var duration = 2000;
  var stepTime = 16;
  var steps    = duration / stepTime;
  var inc      = target / steps;
  var current  = 0;

  /* Easing — fast start, slow end */
  function easeOut(t) { return 1 - Math.pow(1 - t, 3); }

  var startTime = null;
  function tick(timestamp) {
    if (!startTime) startTime = timestamp;
    var elapsed  = timestamp - startTime;
    var progress = Math.min(elapsed / duration, 1);
    var eased    = easeOut(progress);
    var value    = Math.floor(eased * target);

    /* Format — 10000 → 10k */
    var display = value >= 1000
      ? (value / 1000).toFixed(value % 1000 === 0 ? 0 : 1) + 'k'
      : value.toString();

    el.textContent = display + suffix;

    if (progress < 1) {
      requestAnimationFrame(tick);
    } else {
      /* Final value — exact */
      var finalDisplay = target >= 1000
        ? (target / 1000).toFixed(target % 1000 === 0 ? 0 : 1) + 'k'
        : target.toString();
      el.textContent = finalDisplay + suffix;
    }
  }
  requestAnimationFrame(tick);
}

/* ── INTERSECTION OBSERVER ── */
var observed = new Set();

var io = new IntersectionObserver(function(entries) {
  entries.forEach(function(entry) {
    if (!entry.isIntersecting) return;
    var el = entry.target;

    /* Reveal classes */
    if (el.classList.contains('reveal-left') ||
        el.classList.contains('reveal-right') ||
        el.classList.contains('reveal-up')) {
      el.classList.add('revealed');
    }

    /* Count-up */
    if (el.classList.contains('about-stat-num') && !observed.has(el)) {
      observed.add(el);
      countUp(el);
    }

    io.unobserve(el);
  });
}, { threshold: 0.25 });

/* Observe everything */
document.querySelectorAll(
  '.reveal-left, .reveal-right, .reveal-up, .about-stat-num'
).forEach(function(el) { io.observe(el); });
</script>

<?php include 'includes/footer.php'; ?>