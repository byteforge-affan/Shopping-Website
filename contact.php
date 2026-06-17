<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Contact';

$success = $error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email   = clean($conn, $_POST['email']   ?? '');
    $subject = clean($conn, $_POST['subject'] ?? '');
    $message = clean($conn, $_POST['message'] ?? '');
    if(!$email || !$message) {
        $error = 'Please fill in your email and message.';
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (email,subject,message,created_at) VALUES (?,?,?,NOW())");
        if($stmt){ $stmt->bind_param("sss",$email,$subject,$message); $stmt->execute(); }
        $success = 'Thank you! Your message has been received. We will get back to you soon.';
    }
}
?>
<?php include 'includes/header.php'; ?>

<!-- HERO -->
<div class="ct-hero">
  <div class="ct-hero-inner">
    <span class="ct-hero-tag">Get In Touch</span>
    <h1 class="ct-hero-title">Contact <em>Us</em></h1>
    <p class="ct-hero-sub">We'd love to hear from you — drop us a message and we'll respond within 24 hours.</p>
  </div>
</div>

<!-- INFO CARDS -->
<div class="ct-cards-row">
<?php foreach([
  ['fas fa-map-marker-alt','#7B5EA7','Visit Us',     'Arts Store, Shop #12<br>M.A. Jinnah Road, Karachi'],
  ['fas fa-phone-alt',     '#22C55E','Call Us',      '+92-300-1234567<br>+92-21-1234567'],
  ['fas fa-envelope',      '#F97316','Email Us',     'info@artsstore.pk<br>support@artsstore.pk'],
  ['fas fa-clock',         '#3B82F6','Working Hours','Mon–Sat: 9AM–8PM<br>Sunday: 11AM–6PM'],
] as $i=>$c): ?>
<div class="ct-card" style="--clr:<?php echo $c[1]; ?>;animation-delay:<?php echo $i*.1; ?>s">
  <div class="ct-card-icon"><i class="<?php echo $c[0]; ?>"></i></div>
  <div>
    <div class="ct-card-lbl"><?php echo $c[2]; ?></div>
    <div class="ct-card-val"><?php echo $c[3]; ?></div>
  </div>
</div>
<?php endforeach; ?>
</div>

<!-- MAIN -->
<section class="ct-main">

  <!-- FORM -->
  <div class="ct-form-side reveal-left">
    <span class="ct-badge">Message Us</span>
    <h2 class="ct-form-h2">Send a Message</h2>
    <p class="ct-form-p">Fill out the form and our team will get back to you shortly.</p>

    <?php if($success): ?>
    <div class="ct-success">
      <div class="ct-success-ico"><i class="fas fa-check"></i></div>
      <div><strong>Message Sent!</strong><br/><span><?php echo $success; ?></span></div>
    </div>
    <?php endif; ?>

    <?php if($error): ?>
    <div class="ct-err"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
    <?php endif; ?>

    <form method="POST">
      <div class="ct-f">
        <i class="fas fa-envelope ct-fi"></i>
        <input type="email" name="email" placeholder=" " required value="<?php echo htmlspecialchars($_POST['email']??''); ?>"/>
        <label>Email Address *</label>
      </div>
      <div class="ct-f">
        <i class="fas fa-tag ct-fi"></i>
        <input type="text" name="subject" placeholder=" " value="<?php echo htmlspecialchars($_POST['subject']??''); ?>"/>
        <label>Subject</label>
      </div>
      <div class="ct-f ct-f-ta">
        <i class="fas fa-comment ct-fi"></i>
        <textarea name="message" placeholder=" " rows="5" required><?php echo htmlspecialchars($_POST['message']??''); ?></textarea>
        <label>Your Message *</label>
      </div>
      <button type="submit" class="ct-btn">
        <span>Send Message</span><i class="fas fa-paper-plane"></i>
      </button>
    </form>
  </div>

  <!-- RIGHT -->
  <div class="ct-right-side reveal-right">

    <!-- Map card -->
    <div class="ct-map">
      <div class="ct-map-pin"><i class="fas fa-map-marker-alt"></i></div>
      <div class="ct-map-lbl"><strong>Arts Store</strong><br>M.A. Jinnah Road, Karachi</div>
      <a href="https://maps.google.com" target="_blank" class="ct-map-link">
        <i class="fas fa-directions"></i> Get Directions
      </a>
    </div>

    <!-- Social -->
    <div class="ct-social-row">
      <span class="ct-social-ttl">Follow Us</span>
      <div class="ct-socials">
        <?php foreach([
          ['fab fa-facebook-f','#1877F2'],
          ['fab fa-instagram', '#E1306C'],
          ['fab fa-whatsapp',  '#25D366'],
          ['fab fa-tiktok',    '#1a1a1a'],
        ] as $s): ?>
        <a href="#" class="ct-soc" style="--sc:<?php echo $s[1]; ?>">
          <i class="<?php echo $s[0]; ?>"></i>
        </a>
        <?php endforeach; ?>
      </div>
    </div>

    <!-- FAQ -->
    <div class="ct-faq">
      <i class="fas fa-question-circle ct-faq-ico"></i>
      <div>
        <strong>Have Questions?</strong>
        <p>Check our Help & FAQs for quick answers about orders, payments and delivery.</p>
      </div>
      <a href="help.php" class="ct-faq-link">View FAQs</a>
    </div>

  </div>
</section>

<style>
/* HERO */
.ct-hero{background:linear-gradient(135deg,#1a0a2e 0%,#2d1b69 55%,#5a4080 100%);padding:90px 60px 70px;text-align:center;position:relative;overflow:hidden}
.ct-hero::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:44px 44px}
.ct-hero::after{content:'';position:absolute;inset:0;background:radial-gradient(circle at 20% 50%,rgba(123,94,167,.3) 0%,transparent 50%),radial-gradient(circle at 80% 20%,rgba(245,200,66,.15) 0%,transparent 40%)}
.ct-hero-inner{position:relative;z-index:2;animation:ctIn .7s ease both}
@keyframes ctIn{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
.ct-hero-tag{display:inline-block;background:rgba(245,200,66,.15);border:1px solid rgba(245,200,66,.4);color:#F5C842;font-size:10px;font-weight:600;letter-spacing:.22em;text-transform:uppercase;padding:5px 16px;border-radius:20px;margin-bottom:16px}
.ct-hero-title{font-family:'Playfair Display',serif;font-size:56px;font-weight:700;color:#fff;line-height:1.1;margin-bottom:14px}
.ct-hero-title em{font-style:italic;color:#F5C842}
.ct-hero-sub{font-size:15px;color:rgba(255,255,255,.6);max-width:480px;margin:0 auto;line-height:1.8}

/* INFO CARDS */
.ct-cards-row{display:grid;grid-template-columns:repeat(4,1fr);background:#fff;border-bottom:1px solid #EBEBF0}
.ct-card{display:flex;align-items:center;gap:14px;padding:26px 22px;border-right:1px solid #EBEBF0;opacity:0;transform:translateY(18px);animation:ctCardIn .5s ease forwards;transition:background .25s ease}
.ct-card:last-child{border-right:none}
.ct-card:hover{background:#FAFAFC}
@keyframes ctCardIn{to{opacity:1;transform:none}}
.ct-card-icon{width:46px;height:46px;border-radius:10px;background:color-mix(in srgb,var(--clr) 12%,#fff);color:var(--clr);display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;transition:transform .3s cubic-bezier(.34,1.56,.64,1),background .25s ease,color .25s ease}
.ct-card:hover .ct-card-icon{transform:scale(1.12) rotate(-6deg);background:var(--clr);color:#fff}
.ct-card-lbl{font-size:9px;font-weight:600;letter-spacing:.14em;text-transform:uppercase;color:#aaa;margin-bottom:3px}
.ct-card-val{font-size:12px;color:#1A1A2E;line-height:1.6;font-weight:500}

/* MAIN */
.ct-main{display:grid;grid-template-columns:1fr 1fr;min-height:600px}

/* FORM SIDE */
.ct-form-side{padding:60px 50px;background:#fff}
.ct-badge{display:inline-block;font-size:9px;font-weight:600;letter-spacing:.2em;text-transform:uppercase;color:#7B5EA7;border:1px solid #C4B0E0;padding:3px 12px;border-radius:20px;margin-bottom:12px}
.ct-form-h2{font-family:'Playfair Display',serif;font-size:30px;font-weight:700;color:#1A1A2E;margin-bottom:8px}
.ct-form-p{font-size:13px;color:#888;line-height:1.7;margin-bottom:28px}

/* Floating fields */
.ct-f{position:relative;margin-bottom:22px}
.ct-fi{position:absolute;left:14px;top:17px;font-size:13px;color:#bbb;pointer-events:none;z-index:2;transition:color .2s ease}
.ct-f input,.ct-f textarea{width:100%;padding:16px 14px 8px 40px;border:1.5px solid #EBEBF0;border-radius:8px;font-family:'Josefin Sans',sans-serif;font-size:14px;color:#1A1A2E;background:#FAFAFC;outline:none;transition:border-color .25s ease,box-shadow .25s ease,background .25s ease;resize:none}
.ct-f input:focus,.ct-f textarea:focus{border-color:#7B5EA7;background:#fff;box-shadow:0 0 0 3px rgba(123,94,167,.10)}
.ct-f input:focus~.ct-fi,.ct-f textarea:focus~.ct-fi{color:#7B5EA7}
.ct-f label{position:absolute;left:40px;top:14px;font-size:13px;color:#aaa;pointer-events:none;transition:all .2s ease}
.ct-f input:focus~label,.ct-f input:not(:placeholder-shown)~label,
.ct-f textarea:focus~label,.ct-f textarea:not(:placeholder-shown)~label{top:5px;font-size:9px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#7B5EA7}
.ct-f-ta textarea{padding-top:20px;min-height:120px}
.ct-f-ta .ct-fi{top:18px}

/* Submit */
.ct-btn{width:100%;padding:15px 24px;background:linear-gradient(135deg,#7B5EA7,#9b59b6);color:#fff;border:none;border-radius:8px;font-family:'Josefin Sans',sans-serif;font-size:12px;font-weight:600;letter-spacing:.15em;text-transform:uppercase;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:10px;transition:transform .25s ease,box-shadow .25s ease;overflow:hidden;position:relative}
.ct-btn:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(123,94,167,.38)}
.ct-btn:active{transform:scale(.97)}
.ct-btn i{transition:transform .3s ease}
.ct-btn:hover i{transform:translateX(5px) rotate(-20deg)}

/* Success / Error */
.ct-success{display:flex;align-items:flex-start;gap:14px;background:#F0FDF4;border:1px solid #BBF7D0;border-left:4px solid #22C55E;border-radius:8px;padding:14px 18px;margin-bottom:22px;font-size:13px;color:#15803D;animation:ctFD .4s ease both}
.ct-success-ico{width:30px;height:30px;background:#22C55E;border-radius:50%;display:flex;align-items:center;justify-content:center;color:#fff;font-size:13px;flex-shrink:0}
.ct-err{background:#FEF2F2;border:1px solid #FECACA;border-left:4px solid #EF4444;border-radius:8px;padding:12px 16px;font-size:13px;color:#B91C1C;margin-bottom:22px;animation:ctShk .45s ease}
@keyframes ctFD{from{opacity:0;transform:translateY(-8px)}to{opacity:1;transform:none}}
@keyframes ctShk{0%,100%{transform:translateX(0)}25%{transform:translateX(-8px)}75%{transform:translateX(8px)}}

/* RIGHT SIDE */
.ct-right-side{padding:60px 50px;background:#F8F6FC;display:flex;flex-direction:column;gap:22px}

/* Map */
.ct-map{flex:1;min-height:240px;background:linear-gradient(rgba(26,10,46,.75),rgba(26,10,46,.55)),url('https://images.unsplash.com/photo-1524661135-423995f22d0b?w=800') center/cover;border-radius:12px;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:12px;position:relative;overflow:hidden}
.ct-map::before{content:'';position:absolute;inset:0;background-image:linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);background-size:32px 32px}
.ct-map-pin{width:52px;height:52px;background:#7B5EA7;border-radius:50% 50% 50% 0;transform:rotate(-45deg);display:flex;align-items:center;justify-content:center;box-shadow:0 8px 24px rgba(123,94,167,.5);animation:pinBounce 2.2s ease-in-out infinite;position:relative;z-index:2}
@keyframes pinBounce{0%,100%{transform:rotate(-45deg) translateY(0)}50%{transform:rotate(-45deg) translateY(-10px)}}
.ct-map-pin i{transform:rotate(45deg);color:#fff;font-size:20px}
.ct-map-lbl{color:#fff;font-size:13px;line-height:1.6;text-align:center;position:relative;z-index:2}
.ct-map-lbl strong{font-size:15px;display:block;margin-bottom:2px}
.ct-map-link{display:inline-flex;align-items:center;gap:7px;background:rgba(255,255,255,.15);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,.3);color:#fff;padding:8px 20px;border-radius:20px;font-size:11px;font-weight:600;text-decoration:none;transition:background .25s ease,transform .25s ease;position:relative;z-index:2}
.ct-map-link:hover{background:rgba(255,255,255,.25);transform:translateY(-2px)}

/* Social */
.ct-social-row{background:#fff;border:1px solid #EBEBF0;border-radius:12px;padding:18px 22px;display:flex;align-items:center;gap:18px}
.ct-social-ttl{font-size:11px;font-weight:600;letter-spacing:.12em;text-transform:uppercase;color:#888;white-space:nowrap}
.ct-socials{display:flex;gap:10px}
.ct-soc{width:40px;height:40px;border-radius:10px;background:color-mix(in srgb,var(--sc) 12%,#fff);color:var(--sc);display:flex;align-items:center;justify-content:center;font-size:16px;text-decoration:none;transition:transform .3s cubic-bezier(.34,1.56,.64,1),background .25s ease,color .25s ease}
.ct-soc:hover{transform:translateY(-5px) scale(1.12);background:var(--sc);color:#fff}

/* FAQ */
.ct-faq{background:linear-gradient(135deg,#F5F0FF,#EDE8F5);border:1px solid #C4B0E0;border-radius:12px;padding:18px 20px;display:flex;align-items:center;gap:14px}
.ct-faq-ico{font-size:26px;color:#7B5EA7;flex-shrink:0}
.ct-faq strong{font-size:13px;color:#1A1A2E;display:block;margin-bottom:2px}
.ct-faq p{font-size:12px;color:#888;line-height:1.6;margin:0}
.ct-faq-link{margin-left:auto;flex-shrink:0;padding:8px 16px;background:#7B5EA7;color:#fff;border-radius:6px;font-size:10px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;text-decoration:none;transition:background .2s ease,transform .2s ease;white-space:nowrap}
.ct-faq-link:hover{background:#5a4080;transform:translateY(-1px)}

/* Reveal */
.reveal-left{opacity:0;transform:translateX(-35px);transition:opacity .7s ease,transform .7s ease}
.reveal-right{opacity:0;transform:translateX(35px);transition:opacity .7s ease,transform .7s ease}
.revealed{opacity:1!important;transform:none!important}

/* Responsive */
@media(max-width:992px){
  .ct-cards-row{grid-template-columns:1fr 1fr}
  .ct-card:nth-child(2){border-right:none}
  .ct-card:nth-child(3){border-top:1px solid #EBEBF0}
  .ct-main{grid-template-columns:1fr}
  .ct-form-side,.ct-right-side{padding:40px 28px}
}
@media(max-width:576px){
  .ct-hero{padding:60px 20px 50px}
  .ct-hero-title{font-size:36px}
  .ct-cards-row{grid-template-columns:1fr 1fr}
  .ct-card{padding:16px 12px;gap:10px}
  .ct-form-side,.ct-right-side{padding:28px 16px}
  .ct-social-row{flex-wrap:wrap}
  .ct-faq{flex-wrap:wrap}
  .ct-faq-link{margin-left:0}
}
</style>

<script>
var io=new IntersectionObserver(function(entries){
  entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('revealed');io.unobserve(e.target);}});
},{threshold:.12});
document.querySelectorAll('.reveal-left,.reveal-right').forEach(function(el){io.observe(el);});

/* Ripple on submit */
var btn=document.querySelector('.ct-btn');
if(btn){
  btn.addEventListener('click',function(e){
    var r=btn.getBoundingClientRect(),size=Math.max(r.width,r.height)*1.5;
    var rp=document.createElement('span');
    rp.style.cssText='position:absolute;border-radius:50%;background:rgba(255,255,255,.3);pointer-events:none;width:'+size+'px;height:'+size+'px;left:'+(e.clientX-r.left-size/2)+'px;top:'+(e.clientY-r.top-size/2)+'px;animation:ctRpl .65s linear forwards';
    btn.appendChild(rp);setTimeout(function(){rp.remove();},700);
  });
}
document.head.insertAdjacentHTML('beforeend','<style>@keyframes ctRpl{to{transform:scale(4);opacity:0}}</style>');
</script>

<?php include 'includes/footer.php'; ?>