<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Login';

if(isset($_SESSION['customer_id'])) {
    header('Location: my-account.php');
    exit;
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($conn, $_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if(empty($email) || empty($password)) {
        $error = 'Please fill in all fields.';
    } else {
        $stmt = $conn->prepare("SELECT * FROM customers WHERE email = ? LIMIT 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res  = $stmt->get_result();
        $user = $res->fetch_assoc();

        if($user && password_verify($password, $user['password'])) {
            $_SESSION['customer_id']    = $user['customer_id'];
            $_SESSION['customer_name']  = $user['full_name'];
            $_SESSION['customer_email'] = $user['email'];
            header('Location: my-account.php');
            exit;
        } else {
            $error = 'Invalid email or password. Please try again.';
        }
    }
}

$total_products  = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c'] ?? 0;
$total_cats      = $conn->query("SELECT COUNT(*) c FROM categories")->fetch_assoc()['c'] ?? 0;
$total_customers = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Sign In — Arts Store</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:       #1c1510;
  --ink-soft:  #6b5f57;
  --cream:     #faf6f1;
  --warm:      #f0e8de;
  --gold:      #b8860b;
  --gold-lt:   #d4a843;
  --rust:      #a0522d;
  --border:    #e2d9d0;
  --white:     #ffffff;
}

html, body {
  height: 100%;
}

body {
  font-family: 'DM Sans', sans-serif;
  min-height: 100vh;
  display: flex;
  background: var(--cream);
  overflow: hidden;
  height: 100vh;
}

/* ══ LEFT PANEL ══ */
.left-panel {
  width: 52%;
  min-height: 100vh;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 52px 64px;
  background: var(--ink);
  flex-shrink: 0;
}

/* Layered texture */
.left-panel::before {
  content: '';
  position: absolute;
  inset: 0;
  background:
    radial-gradient(ellipse 60% 50% at 80% 20%, rgba(180,120,40,.18) 0%, transparent 60%),
    radial-gradient(ellipse 50% 60% at 10% 85%, rgba(120,60,20,.22) 0%, transparent 55%);
}

/* Dot grid */
.left-panel::after {
  content: '';
  position: absolute;
  inset: 0;
  background-image: radial-gradient(circle, rgba(255,255,255,.06) 1px, transparent 1px);
  background-size: 32px 32px;
}

/* Decorative vertical line */
.deco-line {
  position: absolute;
  right: 0;
  top: 0;
  bottom: 0;
  width: 1px;
  background: linear-gradient(to bottom, transparent, rgba(212,168,67,.4) 30%, rgba(212,168,67,.4) 70%, transparent);
  z-index: 10;
}

/* Gold accent bar top */
.accent-bar {
  position: absolute;
  top: 0;
  left: 64px;
  right: 64px;
  height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold-lt), transparent);
}

.left-top {
  position: relative;
  z-index: 3;
}

.brand {
  display: flex;
  align-items: center;
  gap: 14px;
}
.brand-mark {
  width: 44px;
  height: 44px;
  border: 1px solid rgba(212,168,67,.5);
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 18px;
  color: var(--gold-lt);
  position: relative;
}
.brand-mark::before {
  content: '';
  position: absolute;
  inset: 3px;
  border: 1px solid rgba(212,168,67,.25);
  border-radius: 1px;
}
.brand-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 20px;
  font-weight: 600;
  color: var(--white);
  letter-spacing: .12em;
  text-transform: uppercase;
}

.left-main {
  position: relative;
  z-index: 3;
}

.left-eyebrow {
  font-size: 10px;
  font-weight: 500;
  letter-spacing: .22em;
  text-transform: uppercase;
  color: var(--gold-lt);
  margin-bottom: 20px;
  display: flex;
  align-items: center;
  gap: 10px;
}
.left-eyebrow::before {
  content: '';
  width: 28px;
  height: 1px;
  background: var(--gold-lt);
}

.left-main h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 58px;
  font-weight: 700;
  color: var(--white);
  line-height: 1.0;
  margin-bottom: 22px;
}
.left-main h1 em {
  font-style: italic;
  font-weight: 400;
  color: var(--gold-lt);
}

.left-main p {
  font-size: 14px;
  font-weight: 300;
  color: rgba(255,255,255,.5);
  line-height: 1.9;
  max-width: 340px;
  margin-bottom: 44px;
}



.feat-list {
  margin-top: 32px;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
.feat-item {
  display: flex;
  align-items: center;
  gap: 12px;
  font-size: 13px;
  color: rgba(255,255,255,.55);
}
.feat-icon {
  width: 28px;
  height: 28px;
  border: 1px solid rgba(212,168,67,.3);
  border-radius: 2px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 11px;
  color: var(--gold-lt);
  flex-shrink: 0;
}

.left-bottom {
  position: relative;
  z-index: 3;
  display: flex;
  align-items: center;
  justify-content: space-between;
}
.left-bottom-copy {
  font-size: 11px;
  color: rgba(255,255,255,.25);
  letter-spacing: .05em;
}
.left-bottom a {
  font-size: 11px;
  color: rgba(255,255,255,.4);
  text-decoration: none;
  display: flex;
  align-items: center;
  gap: 6px;
  transition: color .2s;
}
.left-bottom a:hover { color: var(--gold-lt); }

/* ══ RIGHT PANEL ══ */
.right-panel {
  width: 48%;
  min-height: 100vh;
  background: var(--cream);
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 60px 56px;
  position: relative;
  overflow: hidden;
}

/* Subtle corner ornament */
.right-panel::before {
  content: '';
  position: absolute;
  top: 40px;
  right: 40px;
  width: 60px;
  height: 60px;
  border-top: 1px solid var(--border);
  border-right: 1px solid var(--border);
}
.right-panel::after {
  content: '';
  position: absolute;
  bottom: 40px;
  left: 40px;
  width: 60px;
  height: 60px;
  border-bottom: 1px solid var(--border);
  border-left: 1px solid var(--border);
}

.login-box {
  width: 100%;
  max-width: 360px;
  animation: fadeUp .6s ease both;
}

@keyframes fadeUp {
  from { opacity: 0; transform: translateY(24px); }
  to   { opacity: 1; transform: translateY(0); }
}

.login-header {
  margin-bottom: 38px;
}
.login-header-tag {
  font-size: 10px;
  font-weight: 500;
  letter-spacing: .2em;
  text-transform: uppercase;
  color: var(--gold);
  margin-bottom: 12px;
  display: flex;
  align-items: center;
  gap: 8px;
}
.login-header-tag::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}
.login-header h2 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 36px;
  font-weight: 700;
  color: var(--ink);
  line-height: 1.1;
  margin-bottom: 8px;
}
.login-header p {
  font-size: 13px;
  color: var(--ink-soft);
  font-weight: 300;
}

/* Error */
.error-msg {
  display: flex;
  align-items: flex-start;
  gap: 10px;
  background: #fff5f5;
  border: 1px solid #f5c6c6;
  border-left: 3px solid #d9534f;
  border-radius: 2px;
  padding: 12px 14px;
  font-size: 13px;
  color: #8b1a1a;
  margin-bottom: 24px;
  animation: shake .4s ease;
}
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25%     { transform: translateX(-4px); }
  75%     { transform: translateX(4px); }
}

/* Field */
.field {
  margin-bottom: 18px;
}
.field label {
  display: block;
  font-size: 10px;
  font-weight: 600;
  letter-spacing: .18em;
  text-transform: uppercase;
  color: var(--ink);
  margin-bottom: 8px;
}
.input-wrap {
  position: relative;
}
.input-wrap .icon {
  position: absolute;
  left: 14px;
  top: 50%;
  transform: translateY(-50%);
  color: #c8bdb5;
  font-size: 13px;
  pointer-events: none;
  transition: color .2s;
}
.input-wrap input {
  width: 100%;
  padding: 13px 14px 13px 40px;
  border: 1px solid var(--border);
  border-radius: 2px;
  font-family: 'DM Sans', sans-serif;
  font-size: 14px;
  color: var(--ink);
  background: var(--white);
  outline: none;
  transition: border-color .2s, box-shadow .2s;
}
.input-wrap input:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(184,134,11,.1);
}
.input-wrap:focus-within .icon { color: var(--gold); }

.eye-btn {
  position: absolute;
  right: 12px;
  top: 50%;
  transform: translateY(-50%);
  background: none;
  border: none;
  color: #c8bdb5;
  font-size: 13px;
  cursor: pointer;
  padding: 4px;
  transition: color .2s;
}
.eye-btn:hover { color: var(--gold); }

/* Submit */
.btn-login {
  width: 100%;
  margin-top: 8px;
  padding: 14px 20px;
  background: var(--ink);
  color: var(--white);
  border: none;
  border-radius: 2px;
  font-family: 'DM Sans', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .2em;
  text-transform: uppercase;
  cursor: pointer;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 10px;
  transition: background .25s, transform .15s;
  position: relative;
  overflow: hidden;
}
.btn-login::before {
  content: '';
  position: absolute;
  inset: 0;
  background: linear-gradient(135deg, transparent 40%, rgba(212,168,67,.15) 100%);
  opacity: 0;
  transition: opacity .3s;
}
.btn-login:hover { background: #2e261f; }
.btn-login:hover::before { opacity: 1; }
.btn-login:active { transform: scale(.99); }

/* Divider */
.divider {
  display: flex;
  align-items: center;
  gap: 12px;
  margin: 28px 0;
  font-size: 10px;
  letter-spacing: .15em;
  text-transform: uppercase;
  color: #c8bdb5;
}
.divider::before, .divider::after {
  content: '';
  flex: 1;
  height: 1px;
  background: var(--border);
}

/* Register box */
.register-cta {
  border: 1px solid var(--border);
  border-radius: 2px;
  padding: 20px 22px;
  background: var(--white);
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 16px;
}
.register-cta p {
  font-size: 13px;
  color: var(--ink-soft);
  line-height: 1.5;
}
.register-cta p strong {
  display: block;
  color: var(--ink);
  font-size: 14px;
  margin-bottom: 2px;
}
.btn-register {
  flex-shrink: 0;
  padding: 10px 20px;
  border: 1px solid var(--ink);
  border-radius: 2px;
  font-family: 'DM Sans', sans-serif;
  font-size: 11px;
  font-weight: 600;
  letter-spacing: .15em;
  text-transform: uppercase;
  color: var(--ink);
  text-decoration: none;
  transition: all .2s;
  white-space: nowrap;
}
.btn-register:hover {
  background: var(--ink);
  color: var(--white);
}

.forgot-link {
  display: block;
  text-align: right;
  margin-top: 8px;
  font-size: 12px;
  color: var(--ink-soft);
  text-decoration: none;
  transition: color .2s;
}
.forgot-link:hover { color: var(--gold); }

@media (max-width: 1200px) {
  .left-panel { padding: 44px 44px; }
  .left-main h1 { font-size: 48px; }
  .right-panel { padding: 52px 44px; }
}

@media (max-width: 960px) {
  .left-panel { width: 46%; padding: 40px 36px; }
  .left-main h1 { font-size: 40px; }
  .left-main p { font-size: 13px; }
  .right-panel { width: 54%; padding: 44px 36px; }
}

@media (max-width: 860px) {
  body { overflow-y: auto; display: block; }
  .left-panel { display: none; }
  .right-panel {
    width: 100%;
    min-height: 100vh;
    padding: 48px 24px;
    overflow-y: visible;
  }
  .right-panel::before, .right-panel::after { display: none; }
  .login-box { max-width: 420px; margin: 0 auto; }
}

@media (max-width: 480px) {
  .right-panel { padding: 36px 20px; }
  .login-header h2 { font-size: 30px; }
  .register-cta { flex-direction: column; align-items: flex-start; gap: 12px; }
  .btn-register { width: 100%; text-align: center; justify-content: center; display: flex; }
}
</style>
</head>
<body>

<!-- LEFT PANEL -->
<div class="left-panel">
  <div class="deco-line"></div>
  <div class="accent-bar"></div>

  <div class="left-top">
    <div class="brand">
      <div class="brand-mark"><i class="fas fa-feather-alt"></i></div>
      <span class="brand-name">Arts Store</span>
    </div>
  </div>

  <div class="left-main">
    <div class="left-eyebrow">Welcome back</div>
    <h1>Where Art<br/>Meets <em>Everyday</em><br/>Elegance</h1>
    <p>Your curated destination for gifts, stationery, beauty essentials and thoughtfully crafted goods.</p>

    <div class="feat-list">
      <div class="feat-item">
        <div class="feat-icon"><i class="fas fa-truck"></i></div>
        Free delivery on orders over Rs. 5,000
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fas fa-undo"></i></div>
        Hassle-free 7-day returns
      </div>
      <div class="feat-item">
        <div class="feat-icon"><i class="fas fa-gift"></i></div>
        Complimentary gift wrapping available
      </div>
    </div>
  </div>

  <div class="left-bottom">
    <span class="left-bottom-copy">© <?php echo date('Y'); ?> Arts Store</span>
    <a href="index.php"><i class="fas fa-store" style="font-size:10px;"></i> Browse Store</a>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <div class="login-box">

    <div class="login-header">
      <div class="login-header-tag">Customer Portal</div>
      <h2>Sign In</h2>
      <p>Access your orders, wishlist and account details</p>
    </div>

    <?php if($error): ?>
    <div class="error-msg">
      <i class="fas fa-exclamation-circle" style="margin-top:1px;flex-shrink:0;"></i>
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <div class="field">
        <label>Email Address</label>
        <div class="input-wrap">
          <i class="fas fa-envelope icon"></i>
          <input type="email" name="email" placeholder="your@email.com"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required/>
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock icon"></i>
          <input type="password" name="password" id="passInput" placeholder="Enter your password" required/>
          <button type="button" class="eye-btn" onclick="togglePass()">
            <i class="fas fa-eye" id="eyeIcon"></i>
          </button>
        </div>
        <a href="change-password.php" class="forgot-link">Forgot password?</a>
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i> Sign In to My Account
      </button>
    </form>

    <div class="divider">New here?</div>

    <div class="register-cta">
      <p>
        <strong>Create an Account</strong>
        Join thousands of happy customers
      </p>
      <a href="register.php" class="btn-register">Register</a>
    </div>

  </div>
</div>

<script>
function togglePass() {
  const inp  = document.getElementById('passInput');
  const icon = document.getElementById('eyeIcon');
  inp.type = inp.type === 'password' ? 'text' : 'password';
  icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
</script>
</body>
</html>
