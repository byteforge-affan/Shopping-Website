<?php // includes/footer.php ?>

<!-- FOOTER -->
<footer class="site-footer">
  <div class="footer-grid">

    <!-- Brand -->
    <div>
      <div class="footer-logo"><h1>ArtsStore</h1></div>
      <p class="footer-desc">
        Your one-stop destination for gift articles, greeting cards, hand bags,
        wallets and beauty products. Shop with ease from the comfort of your home.
      </p>
    </div>

    <!-- Categories -->
    <div class="footer-col">
      <h4>Categories</h4>
      <ul>
        <li><a href="products.php?cat=gift-articles">Gift Articles</a></li>
        <li><a href="products.php?cat=greeting-cards">Greeting Cards</a></li>
        <li><a href="products.php?cat=hand-bags">Hand Bags</a></li>
        <li><a href="products.php?cat=wallet">Wallets</a></li>
        <li><a href="products.php?cat=beauty-products">Beauty Products</a></li>
      </ul>
    </div>

    <!-- Help -->
    <div class="footer-col">
      <h4>Help</h4>
      <ul>
        <li><a href="about.php">About Us</a></li>
        <li><a href="contact.php">Contact Us</a></li>
        <li><a href="help.php">FAQs</a></li>
        <li><a href="register.php">Register</a></li>
        <li><a href="login.php">Login</a></li>
        <li><a href="track-order.php">Track Order</a></li>
      </ul>
    </div>

    <!-- Newsletter + Contact -->
    <div class="footer-col">
      <h4>Newsletter</h4>
      <p style="font-size:13px;color:#bbb;margin-bottom:10px;line-height:1.7;">
        Subscribe to get special offers, gift ideas and the latest updates.
      </p>
      <form class="newsletter-form" onsubmit="return false;">
        <input type="email" placeholder="Your email address"/>
        <button type="submit"><i class="fas fa-arrow-right"></i></button>
      </form>

      <div style="margin-top:24px;">
        <p style="font-size:11px;color:#aaa;letter-spacing:.12em;text-transform:uppercase;margin-bottom:12px;font-weight:600;">Get in Touch</p>

        <p style="font-size:13px;color:#ccc;margin-bottom:8px;">
          <i class="fas fa-phone" style="color:#7B5EA7;margin-right:10px;width:14px;"></i>
          +92-300-1234567
        </p>

        <!-- Dynamic email: opens user's email client with To field pre-filled -->
        <p style="font-size:13px;color:#ccc;">
          <i class="fas fa-envelope" style="color:#7B5EA7;margin-right:10px;width:14px;"></i>
          <a href="mailto:info@artsstore.pk"
             style="color:#ccc;text-decoration:none;transition:color .2s;"
             onmouseover="this.style.color='#7B5EA7'"
             onmouseout="this.style.color='#ccc'">
            info@artsstore.pk
          </a>
        </p>
      </div>
    </div>

  </div>

  <div class="footer-bottom">
    <span>&copy; <?php echo date('Y'); ?> Arts Store. All Rights Reserved.</span>
    <span>Designed with <span style="color:#7B5EA7;">♥</span> for art lovers</span>
    <span style="display:flex;gap:16px;">
      <a href="<?php echo $root??''; ?>admin/login.php"
         style="font-size:11px;color:#aaa;letter-spacing:.08em;transition:color .2s;"
         onmouseover="this.style.color='#7B5EA7'" onmouseout="this.style.color='#aaa'">
        <i class="fas fa-user-shield" style="margin-right:4px;"></i>Admin
      </a>
      <a href="<?php echo $root??''; ?>employee/login.php"
         style="font-size:11px;color:#aaa;letter-spacing:.08em;transition:color .2s;"
         onmouseover="this.style.color='#27ae60'" onmouseout="this.style.color='#aaa'">
        <i class="fas fa-user-tie" style="margin-right:4px;"></i>Staff
      </a>
    </span>
  </div>
</footer>

<!-- SCROLL TO TOP -->
<button class="scroll-top" id="scrollTop" onclick="window.scrollTo({top:0,behavior:'smooth'})">
  <i class="fas fa-chevron-up"></i>
</button>

<script src="<?php echo $root ?? ''; ?>js/main.js"></script>
<script src="js/animations.js"></script>
</body>
</html>