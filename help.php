<?php
session_start();
$page_title = 'Help & FAQs';
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero"><h1>Help & FAQs</h1></div>

<section style="padding:70px 40px;max-width:900px;margin:0 auto;">

  <div class="section-header" style="text-align:left;margin-bottom:40px;">
    <div class="section-label">Support</div>
    <h2 class="section-title" style="font-family:'Playfair Display',serif;font-size:36px;font-weight:700;margin-bottom:12px;">
      Frequently Asked Questions
    </h2>
    <p style="color:#888;font-size:14px;">Can't find what you're looking for? <a href="contact.php" style="color:#7B5EA7;">Contact us</a></p>
  </div>

  <?php
  $faqs = [
    ['Ordering', [
      ['Do I need to register to browse products?',
       'No! You can browse all products without registering. However, you must register and login to place an order.'],
      ['How do I place an order?',
       'Add products to your cart, go to checkout, fill your delivery address, choose a payment method, and click "Place Order". You will receive a unique 16-digit order number.'],
      ['Can I cancel my order?',
       'Yes, you can cancel your order as long as it has not been dispatched from our store. Once dispatched, cancellation is not possible. Go to My Account → Orders to cancel.'],
      ['What is the minimum order amount for free shipping?',
       'Orders above Rs. 5,000 qualify for free shipping. A shipping charge of Rs. 200 applies to orders below this amount.'],
    ]],
    ['Payment', [
      ['What payment methods are accepted?',
       'We accept Credit/Debit Card (online payment), Cheque, and Cash on Delivery (VPP). For card and cheque payments, orders are dispatched only after payment clearance.'],
      ['Is online payment safe?',
       'Yes. All online payments are processed through a secure payment gateway. Your card details are never stored on our servers.'],
      ['What is VPP / Cash on Delivery?',
       'VPP means you pay cash to our delivery person when your order arrives at your door. No advance payment needed.'],
    ]],
    ['Delivery', [
      ['How long does delivery take?',
       'Standard delivery takes 3-5 working days within Pakistan. Delivery times may vary for remote areas.'],
      ['Do you deliver across Pakistan?',
       'Yes! We deliver to all major cities including Karachi, Lahore, Islamabad, Rawalpindi, Faisalabad, Multan, Peshawar and Quetta.'],
      ['How do I track my order?',
       'Go to My Account → My Orders to see the status of your orders. You can also use the Track Order page with your 16-digit order number.'],
    ]],
    ['Returns & Warranty', [
      ['What is your return policy?',
       'If you are not satisfied with a product, you can request a return or replacement within 7 days of delivery. The product must be in original condition.'],
      ['How do I return a product?',
       'Contact us via the Contact page or call us within 7 days of delivery. We will arrange a pickup. Your refund will be processed within 5-7 working days.'],
      ['Do products come with warranty?',
       'Select products include a warranty card. The warranty details are mentioned on the product detail page. Warranty cards are provided in the package.'],
    ]],
    ['Account', [
      ['How do I register?',
       'Click "Register yourself" in the top bar or visit the Register page. Fill in your name, email, phone, and password.'],
      ['I forgot my password. What do I do?',
       'Currently, please contact us via the Contact page and we will reset your password. A self-service password reset feature is coming soon.'],
      ['Can I change my delivery address?',
       'Yes, go to My Account → Edit Profile to update your default address. You can also enter a different address at checkout.'],
    ]],
  ];

  foreach($faqs as $section): ?>
  <div style="margin-bottom:48px;">
    <h3 style="font-family:'Playfair Display',serif;font-size:22px;font-weight:700;
               color:#1a1a1a;margin-bottom:20px;padding-bottom:10px;
               border-bottom:2px solid #7B5EA7;display:inline-block;">
      <?php echo $section[0]; ?>
    </h3>

    <div style="margin-top:24px;">
      <?php foreach($section[1] as $i => $faq): ?>
      <div class="faq-item" style="border:1px solid #e0e0e0;margin-bottom:10px;border-radius:4px;overflow:hidden;">
        <button onclick="toggleFaq(this)"
                style="width:100%;text-align:left;padding:16px 20px;background:#fff;border:none;
                       cursor:pointer;font-family:'Josefin Sans',sans-serif;font-size:14px;
                       font-weight:400;color:#1a1a1a;display:flex;justify-content:space-between;
                       align-items:center;transition:background .2s;">
          <span><?php echo $faq[0]; ?></span>
          <span style="font-size:18px;color:#7B5EA7;transition:transform .3s;" class="faq-icon">+</span>
        </button>
        <div class="faq-answer" style="display:none;padding:0 20px 16px;font-size:13px;
                                       color:#555;line-height:1.8;background:#fafafa;
                                       border-top:1px solid #f0f0f0;">
          <?php echo $faq[1]; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endforeach; ?>

  <!-- STILL NEED HELP -->
  <div style="background:#EDE9E3;padding:40px;text-align:center;border-radius:4px;margin-top:20px;">
    <h3 style="font-family:'Playfair Display',serif;font-size:26px;margin-bottom:12px;">Still need help?</h3>
    <p style="color:#888;font-size:14px;margin-bottom:24px;">Our team is available Mon-Sat, 9AM to 8PM</p>
    <div style="display:flex;gap:20px;justify-content:center;flex-wrap:wrap;">
      <a href="contact.php" class="btn-shop">Send Us a Message</a>
      <a href="tel:+923001234567" class="btn-outline">
        Call: +92-300-1234567
      </a>
    </div>
  </div>

</section>

<?php include 'includes/footer.php'; ?>

<script>
function toggleFaq(btn) {
  var answer = btn.nextElementSibling;
  var icon   = btn.querySelector('.faq-icon');
  var isOpen = answer.style.display === 'block';
  // Close all
  document.querySelectorAll('.faq-answer').forEach(function(a){ a.style.display='none'; });
  document.querySelectorAll('.faq-icon').forEach(function(i){ i.textContent='+'; i.style.transform=''; });
  // Open this one if was closed
  if(!isOpen) {
    answer.style.display = 'block';
    icon.textContent = '−';
    icon.style.transform = 'rotate(180deg)';
    btn.style.background = '#f9f6ff';
  } else {
    btn.style.background = '#fff';
  }
}
</script>
