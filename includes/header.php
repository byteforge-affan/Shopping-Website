<?php
// includes/header.php
$current = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title><?php echo isset($page_title) ? $page_title . ' — Arts Store' : 'Arts Store'; ?></title>
  <link rel="stylesheet" href="<?php echo $root ?? ''; ?>css/style.css"/>
  <link rel="stylesheet" href="<?php echo $root ?? ''; ?>css/responsive.css"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>

  <style>
  /* ══ LOGO ══ */
  .nav-logo {
    display: flex;
    align-items: center;
    gap: 12px;
    text-decoration: none;
    height: 50px;
  }
  .logo-bag-wrap {
    transition: transform .3s cubic-bezier(.34,1.56,.64,1);
  }
  .logo-bag-wrap svg { width: 40px; height: auto; display: block; }
  .nav-logo:hover .logo-bag-wrap { transform: scale(1.12) rotate(-4deg); }
  .logo-divider { width: 1px; height: 30px; background: #E0D0F5; }
  .logo-text-wrap { display: flex; flex-direction: column; justify-content: center; line-height: 1.2; }
  .logo-arts  { font-family: Georgia, serif; font-size: 22px; font-weight: 800; color: #1a1a2e; letter-spacing: 1px; text-transform: uppercase; }
  .logo-store { font-family: sans-serif; font-size: 10px; font-weight: 700; color: #7B5EA7; letter-spacing: 4px; text-transform: uppercase; }

  /* ══ HAMBURGER ══ */
  .nav-hamburger { display: none; flex-direction: column; gap: 5px; cursor: pointer; background: none; border: none; padding: 6px; z-index: 10001; }
  .nav-hamburger span { display: block; width: 24px; height: 2px; background: #1a1a2e; border-radius: 2px; transition: transform .35s cubic-bezier(.34,1.56,.64,1), opacity .3s ease; }

  /* ══ MOBILE CLOSE BUTTON ══ */
  .nav-close { display: none; position: fixed; top: 18px; right: 20px; font-size: 28px; color: #fff; cursor: pointer; z-index: 10002; background: none; border: none; }

  /* ══ MOBILE RESPONSIVE ══ */
  @media (max-width: 768px) {
    .nav-hamburger { display: flex !important; }
    .nav-menu {
      display: none;
      position: fixed;
      inset: 0;
      background: rgba(26,26,46,.97);
      flex-direction: column;
      align-items: center;
      justify-content: center;
      gap: 28px;
      z-index: 10000;
      list-style: none;
      margin: 0;
      padding: 0;
    }
    .nav-menu.open {
      display: flex;
      animation: mobileMenuIn .35s cubic-bezier(.16,1,.3,1) both;
    }
    @keyframes mobileMenuIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:none} }
    .nav-menu a { font-size: 24px !important; color: #fff !important; font-family: Georgia, serif; }
    .nav-menu a:hover, .nav-menu a.active { color: #7B5EA7 !important; }
    .nav-close.show { display: block; }
    .hide-mobile { display: none !important; }
  }
  @media (min-width: 769px) {
    .nav-hamburger { display: none !important; }
    .nav-close     { display: none !important; }
  }
  </style>
</head>
<body>

<!-- TOP BAR -->
<div class="top-bar">
  <span>🎁 Free shipping on orders over Rs. 5,000 &nbsp;|&nbsp; Gift wrapping available!</span>
  <div class="top-bar-links">
    <a href="<?php echo $root ?? ''; ?>help.php">Help &amp; FAQs</a>
    <?php if(isset($_SESSION['customer_id'])): ?>
      <a href="<?php echo $root ?? ''; ?>logout.php">Logout (<?php echo htmlspecialchars($_SESSION['customer_name']); ?>)</a>
    <?php else: ?>
      <a href="<?php echo $root ?? ''; ?>register.php">Register yourself</a>
    <?php endif; ?>
  </div>
</div>

<!-- NAVBAR -->
<nav class="navbar">

  <!-- LOGO with SVG bag -->
  <a href="<?php echo $root ?? ''; ?>index.php" class="nav-logo" title="Arts Store">
    <div class="logo-bag-wrap">
      <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
        <path d="M22 22V15C22 9.47715 26.4772 5 32 5C37.5228 5 42 9.47715 42 15V22"
              stroke="#C9A84C" stroke-width="5" stroke-linecap="round"/>
        <rect x="12" y="22" width="40" height="36" rx="4" fill="#7B5EA7"/>
        <path d="M32 34L34 38L39 39L34 40L32 44L30 40L25 39L30 38L32 34Z" fill="#FFD700"/>
      </svg>
    </div>
    <div class="logo-divider"></div>
    <div class="logo-text-wrap">
      <span class="logo-arts">Arts</span>
      <span class="logo-store">Store</span>
    </div>
  </a>

  <!-- HAMBURGER — mobile only -->
  <button class="nav-hamburger" id="navHamburger" aria-label="Open menu">
    <span></span><span></span><span></span>
  </button>

  <!-- NAV LINKS -->
  <ul class="nav-menu" id="navMenu">
    <li><a href="<?php echo $root ?? ''; ?>index.php"      class="<?php echo $current=='index.php'      ?'active':''; ?>">Home</a></li>
    <li><a href="<?php echo $root ?? ''; ?>products.php"   class="<?php echo $current=='products.php'   ?'active':''; ?>">Shop</a></li>
    <li><a href="<?php echo $root ?? ''; ?>categories.php" class="<?php echo $current=='categories.php' ?'active':''; ?>">Categories</a></li>
    <li><a href="<?php echo $root ?? ''; ?>about.php"      class="<?php echo $current=='about.php'      ?'active':''; ?>">About</a></li>
    <li><a href="<?php echo $root ?? ''; ?>contact.php"    class="<?php echo $current=='contact.php'    ?'active':''; ?>">Contact</a></li>
  </ul>

  <!-- Mobile close button -->
  <button class="nav-close" id="navClose" aria-label="Close menu">
    <i class="fas fa-times"></i>
  </button>

  <!-- RIGHT ICONS -->
  <div class="nav-icons">
    <a href="<?php echo $root ?? ''; ?>products.php?search=1" class="nav-icon" title="Search">
      <i class="fas fa-search"></i>
    </a>
    <a href="<?php echo $root ?? ''; ?>cart.php" class="nav-icon" title="Cart">
      <i class="fas fa-shopping-cart"></i>
      <span class="badge"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span>
    </a>
    <a href="<?php echo $root ?? ''; ?>wishlist.php" class="nav-icon" title="Wishlist">
      <i class="fas fa-heart"></i>
      <span class="badge"><?php echo isset($_SESSION['wishlist']) ? count($_SESSION['wishlist']) : 0; ?></span>
    </a>
    <?php if(isset($_SESSION['customer_id'])): ?>
      <a href="<?php echo $root ?? ''; ?>my-account.php" class="nav-icon" title="My Account">
        <i class="fas fa-user"></i>
      </a>
    <?php else: ?>
      <a href="<?php echo $root ?? ''; ?>admin/login.php"
         style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;
                background:#7B5EA7;color:#fff;font-size:11px;font-weight:600;
                letter-spacing:.1em;text-transform:uppercase;border-radius:4px;
                transition:background .2s;text-decoration:none;"
         onmouseover="this.style.background='#6a4d95'"
         onmouseout="this.style.background='#7B5EA7'">
        <i class="fas fa-user-shield"></i>
        <span class="hide-mobile">Admin</span>
      </a>
      <a href="<?php echo $root ?? ''; ?>employee/login.php"
         style="display:inline-flex;align-items:center;gap:5px;padding:7px 14px;
                background:#27ae60;color:#fff;font-size:11px;font-weight:600;
                letter-spacing:.1em;text-transform:uppercase;border-radius:4px;
                transition:background .2s;text-decoration:none;"
         onmouseover="this.style.background='#1e8449'"
         onmouseout="this.style.background='#27ae60'">
        <i class="fas fa-user-tie"></i>
        <span class="hide-mobile">Staff</span>
      </a>
    <?php endif; ?>
  </div>

</nav>

<!-- ★ ANIMATIONS.JS — puri website pe disaster animations ★ -->
<script src="<?php echo $root ?? ''; ?>js/animations.js" defer></script>

<script>
/* Mobile hamburger menu */
(function(){
  var hb   = document.getElementById('navHamburger');
  var menu = document.getElementById('navMenu');
  var cls  = document.getElementById('navClose');
  function openMenu(){
    menu.classList.add('open');
    if(cls) cls.classList.add('show');
    if(hb)  hb.classList.add('open');
    document.body.style.overflow = 'hidden';
  }
  function closeMenu(){
    menu.classList.remove('open');
    if(cls) cls.classList.remove('show');
    if(hb)  hb.classList.remove('open');
    document.body.style.overflow = '';
  }
  if(hb)   hb.addEventListener('click', openMenu);
  if(cls)  cls.addEventListener('click', closeMenu);
  if(menu) menu.querySelectorAll('a').forEach(function(a){
    a.addEventListener('click', closeMenu);
  });
})();
</script>