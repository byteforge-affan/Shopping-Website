<?php
session_start();
require_once 'includes/db.php';
$page_title = 'Register';

if(isset($_SESSION['customer_id'])) {
    header('Location: my-account.php');
    exit;
}

$error   = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name     = clean($conn, $_POST['full_name']  ?? '');
    $email    = clean($conn, $_POST['email']       ?? '');
    $phone    = clean($conn, $_POST['phone']       ?? '');
    $address  = clean($conn, $_POST['address']     ?? '');
    $city     = clean($conn, $_POST['city']        ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    if(!$name || !$email || !$password || !$phone) {
        $error = 'Please fill all required fields.';
    } elseif($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif(strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $chk = $conn->prepare("SELECT customer_id FROM customers WHERE email=?");
        $chk->bind_param("s", $email);
        $chk->execute();
        $chk->store_result();

        if($chk->num_rows > 0) {
            $error = 'This email is already registered. <a href="login.php">Login instead?</a>';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO customers (full_name, email, phone, address, city, password, created_at) VALUES (?,?,?,?,?,?,NOW())");
            $stmt->bind_param("ssssss", $name, $email, $phone, $address, $city, $hash);
           if($stmt->execute()) {
    header('Location: login.php');
    exit;
} else {
    $error = 'Something went wrong. Please try again.';
}
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Create Account — Arts Store</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,600;0,700;1,400;1,600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
  --ink:      #1c1510;
  --ink-soft: #6b5f57;
  --cream:    #faf6f1;
  --gold:     #b8860b;
  --gold-lt:  #d4a843;
  --border:   #e2d9d0;
  --white:    #ffffff;
}

/* ── ROOT LAYOUT ── */
html, body {
  height: 100%;
  overflow: hidden;
}
body {
  font-family: 'DM Sans', sans-serif;
  display: flex;
  background: var(--cream);
}

/* ══ LEFT PANEL ══ */
.left-panel {
  width: 40%;
  flex-shrink: 0;
  height: 100vh;
  overflow: hidden;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 52px 56px;
  background: var(--ink);
}
.left-panel::before {
  content: ''; position: absolute; inset: 0;
  background:
    radial-gradient(ellipse 70% 50% at 100% 10%, rgba(180,120,40,.2) 0%, transparent 55%),
    radial-gradient(ellipse 60% 70% at 0% 90%,  rgba(120,60,20,.25)  0%, transparent 55%);
}
.left-panel::after {
  content: ''; position: absolute; inset: 0;
  background-image: radial-gradient(circle, rgba(255,255,255,.05) 1px, transparent 1px);
  background-size: 28px 28px;
}
.deco-line {
  position: absolute; right: 0; top: 0; bottom: 0; width: 1px;
  background: linear-gradient(to bottom, transparent, rgba(212,168,67,.35) 30%, rgba(212,168,67,.35) 70%, transparent);
  z-index: 10;
}
.accent-bar {
  position: absolute; top: 0; left: 56px; right: 56px; height: 2px;
  background: linear-gradient(90deg, transparent, var(--gold-lt), transparent);
}
.left-top  { position: relative; z-index: 3; }
.left-main { position: relative; z-index: 3; }
.left-bottom {
  position: relative; z-index: 3;
  display: flex; align-items: center; justify-content: space-between;
}
.brand { display: flex; align-items: center; gap: 13px; }
.brand-mark {
  width: 42px; height: 42px;
  border: 1px solid rgba(212,168,67,.45); border-radius: 2px;
  display: flex; align-items: center; justify-content: center;
  font-size: 17px; color: var(--gold-lt); position: relative;
}
.brand-mark::before {
  content: ''; position: absolute; inset: 3px;
  border: 1px solid rgba(212,168,67,.2); border-radius: 1px;
}
.brand-name {
  font-family: 'Cormorant Garamond', serif;
  font-size: 19px; font-weight: 600; color: var(--white);
  letter-spacing: .12em; text-transform: uppercase;
}
.left-eyebrow {
  font-size: 10px; font-weight: 500; letter-spacing: .22em;
  text-transform: uppercase; color: var(--gold-lt);
  margin-bottom: 18px; display: flex; align-items: center; gap: 10px;
}
.left-eyebrow::before { content: ''; width: 24px; height: 1px; background: var(--gold-lt); }
.left-main h1 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 50px; font-weight: 700; color: var(--white);
  line-height: 1.0; margin-bottom: 20px;
}
.left-main h1 em { font-style: italic; font-weight: 400; color: var(--gold-lt); }
.left-main p {
  font-size: 13px; font-weight: 300; color: rgba(255,255,255,.48);
  line-height: 1.9; max-width: 300px;
}
.left-bottom-copy { font-size: 11px; color: rgba(255,255,255,.22); }
.left-bottom a {
  font-size: 11px; color: rgba(255,255,255,.38); text-decoration: none;
  display: flex; align-items: center; gap: 6px; transition: color .2s;
}
.left-bottom a:hover { color: var(--gold-lt); }

/* ══ RIGHT PANEL — invisible scroll ══ */
.right-panel {
  flex: 1;
  height: 100vh;
  overflow-y: scroll;
  overflow-x: hidden;
  background: var(--cream);
  position: relative;
}

/* corner ornament — fixed so it doesn't scroll away */
.right-panel::before {
  content: '';
  position: fixed;
  top: 40px; right: 40px;
  width: 50px; height: 50px;
  border-top: 1px solid var(--border);
  border-right: 1px solid var(--border);
  pointer-events: none; z-index: 1;
}

/* ── FORM WRAPPER — padding does the centering work ── */
.register-box {
  width: 100%;
  max-width: 480px;
  /* horizontal centering via auto margins, vertical via padding */
  margin: 0 auto;
  padding: 60px 0 80px;
  animation: fadeUp .6s ease both;
}
@keyframes fadeUp {
  from { opacity: 0; transform: translateY(20px); }
  to   { opacity: 1; transform: translateY(0); }
}

/* give the right panel inner padding on sides */
.right-panel { padding: 0 56px; }

.reg-header { margin-bottom: 36px; }
.reg-header-tag {
  font-size: 10px; font-weight: 500; letter-spacing: .2em;
  text-transform: uppercase; color: var(--gold);
  margin-bottom: 12px; display: flex; align-items: center; gap: 8px;
}
.reg-header-tag::after { content: ''; flex: 1; height: 1px; background: var(--border); }
.reg-header h2 {
  font-family: 'Cormorant Garamond', serif;
  font-size: 36px; font-weight: 700; color: var(--ink); margin-bottom: 8px;
}
.reg-header p { font-size: 13px; color: var(--ink-soft); font-weight: 300; }

.section-title {
  font-size: 10px; font-weight: 600; letter-spacing: .18em;
  text-transform: uppercase; color: var(--ink);
  padding-bottom: 10px; border-bottom: 1px solid var(--border);
  margin-bottom: 18px; margin-top: 28px;
  display: flex; align-items: center; gap: 10px;
}
.section-title i { color: var(--gold); }
.section-title:first-of-type { margin-top: 0; }

.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.field { margin-bottom: 16px; }
.field label {
  display: block; font-size: 10px; font-weight: 600;
  letter-spacing: .16em; text-transform: uppercase;
  color: var(--ink); margin-bottom: 7px;
}
.field label .req { color: var(--gold); margin-left: 2px; }

.input-wrap { position: relative; }
.input-wrap .icon {
  position: absolute; left: 13px; top: 50%; transform: translateY(-50%);
  color: #c8bdb5; font-size: 12px; pointer-events: none; transition: color .2s;
}
.input-wrap input,
.input-wrap select {
  width: 100%; padding: 12px 14px 12px 38px;
  border: 1px solid var(--border); border-radius: 2px;
  font-family: 'DM Sans', sans-serif; font-size: 13px;
  color: var(--ink); background: var(--white); outline: none;
  transition: border-color .2s, box-shadow .2s; appearance: none;
}
.input-wrap input:focus,
.input-wrap select:focus {
  border-color: var(--gold);
  box-shadow: 0 0 0 3px rgba(184,134,11,.09);
}
.input-wrap:focus-within .icon { color: var(--gold); }
.input-wrap.select-wrap::after {
  content: '\f078'; font-family: 'Font Awesome 6 Free'; font-weight: 900;
  font-size: 10px; position: absolute; right: 13px; top: 50%;
  transform: translateY(-50%); color: #c8bdb5; pointer-events: none;
}
.input-wrap select { padding-right: 36px; cursor: pointer; }

.eye-btn {
  position: absolute; right: 11px; top: 50%; transform: translateY(-50%);
  background: none; border: none; color: #c8bdb5; font-size: 12px;
  cursor: pointer; padding: 4px; transition: color .2s;
}
.eye-btn:hover { color: var(--gold); }

.pw-strength { margin-top: 6px; display: flex; gap: 4px; align-items: center; }
.pw-bar { flex: 1; height: 3px; background: var(--border); border-radius: 2px; transition: background .3s; }
.pw-bar.active-weak   { background: #e05c5c; }
.pw-bar.active-fair   { background: #e0a050; }
.pw-bar.active-strong { background: #4caf7c; }
.pw-label { font-size: 10px; color: var(--ink-soft); margin-left: 6px; min-width: 40px; }

.alert {
  display: flex; align-items: flex-start; gap: 10px;
  padding: 12px 14px; border-radius: 2px; font-size: 13px; margin-bottom: 22px;
}
.alert a { font-weight: 600; }
.alert-error {
  background: #fff5f5; border: 1px solid #f5c6c6; border-left: 3px solid #d9534f;
  color: #8b1a1a; animation: shake .4s ease;
}
.alert-error a { color: #8b1a1a; }
.alert-success {
  background: #f3fdf7; border: 1px solid #b8dfc8; border-left: 3px solid #4caf7c;
  color: #1a5c35;
}
.alert-success a { color: #1a5c35; }
@keyframes shake {
  0%,100% { transform: translateX(0); }
  25%     { transform: translateX(-4px); }
  75%     { transform: translateX(4px); }
}

.btn-submit {
  width: 100%; margin-top: 10px; padding: 14px 20px;
  background: var(--ink); color: var(--white);
  border: none; border-radius: 2px;
  font-family: 'DM Sans', sans-serif; font-size: 11px; font-weight: 600;
  letter-spacing: .2em; text-transform: uppercase; cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  transition: background .25s, transform .15s; position: relative; overflow: hidden;
}
.btn-submit::before {
  content: ''; position: absolute; inset: 0;
  background: linear-gradient(135deg, transparent 40%, rgba(212,168,67,.12) 100%);
  opacity: 0; transition: opacity .3s;
}
.btn-submit:hover { background: #2e261f; }
.btn-submit:hover::before { opacity: 1; }
.btn-submit:active { transform: scale(.99); }

.reg-footer { margin-top: 20px; text-align: center; font-size: 13px; color: var(--ink-soft); }
.reg-footer a { color: var(--gold); font-weight: 600; text-decoration: none; }
.reg-footer a:hover { text-decoration: underline; }

/* ── RESPONSIVE ── */
@media (max-width: 1200px) {
  .left-panel { padding: 44px 40px; }
  .left-main h1 { font-size: 42px; }
  .right-panel { padding: 0 44px; }
}
@media (max-width: 960px) {
  .left-panel { width: 36%; padding: 40px 32px; }
  .left-main h1 { font-size: 36px; }
  .right-panel { padding: 0 32px; }
}
@media (max-width: 860px) {
  html, body { overflow: auto; height: auto; }
  body { display: block; }
  .left-panel { display: none; }
  .right-panel {
    height: auto; overflow: visible;
    padding: 0 28px;
  }
  .right-panel::before { display: none; }
  .register-box { max-width: 520px; padding: 48px 0 80px; }
  .form-row { grid-template-columns: 1fr; gap: 0; }
}
@media (max-width: 480px) {
  .right-panel { padding: 0 16px; }
  .reg-header h2 { font-size: 30px; }
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
    <div class="left-eyebrow">New member</div>
    <h1>Join Our<br/><em>Curated</em><br/>Community</h1>
    <p>Create your account in minutes and start exploring our collection of handpicked products.</p>
  </div>

  <div class="left-bottom">
    <span class="left-bottom-copy">© <?php echo date('Y'); ?> Arts Store</span>
    <a href="index.php"><i class="fas fa-store" style="font-size:10px;"></i> Browse Store</a>
  </div>
</div>

<!-- RIGHT PANEL -->
<div class="right-panel">
  <div class="register-box">

    <div class="reg-header">
      <div class="reg-header-tag">Create Account</div>
      <h2>Register</h2>
      <p>Already have an account? <a href="login.php" style="color:var(--gold);font-weight:600;">Sign in instead</a></p>
    </div>

    <?php if($error): ?>
    <div class="alert alert-error">
      <i class="fas fa-exclamation-circle" style="margin-top:1px;flex-shrink:0;"></i>
      <span><?php echo $error; ?></span>
    </div>
    <?php endif; ?>

    <?php if($success): ?>
    <div class="alert alert-success">
      <i class="fas fa-check-circle" style="margin-top:1px;flex-shrink:0;"></i>
      <span><?php echo $success; ?></span>
    </div>
    <?php endif; ?>

    <form method="POST">

      <div class="section-title"><i class="fas fa-user"></i> Personal Information</div>

      <div class="form-row">
        <div class="field">
          <label>Full Name <span class="req">*</span></label>
          <div class="input-wrap">
            <i class="fas fa-user icon"></i>
            <input type="text" name="full_name" placeholder="Your full name"
                   value="<?php echo htmlspecialchars($_POST['full_name'] ?? ''); ?>" required/>
          </div>
        </div>
        <div class="field">
          <label>Phone Number <span class="req">*</span></label>
          <div class="input-wrap">
            <i class="fas fa-phone icon"></i>
            <input type="text" name="phone" placeholder="03XX-XXXXXXX"
                   value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>" required/>
          </div>
        </div>
      </div>

      <div class="field">
        <label>Email Address <span class="req">*</span></label>
        <div class="input-wrap">
          <i class="fas fa-envelope icon"></i>
          <input type="email" name="email" placeholder="your@email.com"
                 value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required/>
        </div>
      </div>

      <div class="section-title"><i class="fas fa-map-marker-alt"></i> Delivery Details</div>

      <div class="field">
        <label>Street Address</label>
        <div class="input-wrap">
          <i class="fas fa-home icon"></i>
          <input type="text" name="address" placeholder="House / Street / Area"
                 value="<?php echo htmlspecialchars($_POST['address'] ?? ''); ?>"/>
        </div>
      </div>

      <div class="field">
        <label>City</label>
        <div class="input-wrap select-wrap">
          <i class="fas fa-city icon"></i>
          <select name="city">
            <option value="">Select your city</option>
            <?php foreach(['Karachi','Lahore','Islamabad','Rawalpindi','Faisalabad','Multan','Peshawar','Quetta'] as $c): ?>
              <option <?php echo (isset($_POST['city']) && $_POST['city']==$c) ? 'selected' : ''; ?>>
                <?php echo $c; ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>

      <div class="section-title"><i class="fas fa-shield-alt"></i> Account Security</div>

      <div class="form-row">
        <div class="field">
          <label>Password <span class="req">*</span></label>
          <div class="input-wrap">
            <i class="fas fa-lock icon"></i>
            <input type="password" name="password" id="passInput"
                   placeholder="Min 6 characters" required
                   oninput="checkStrength(this.value)"/>
            <button type="button" class="eye-btn" onclick="togglePass('passInput','eyeIcon1')">
              <i class="fas fa-eye" id="eyeIcon1"></i>
            </button>
          </div>
          <div class="pw-strength">
            <div class="pw-bar" id="bar1"></div>
            <div class="pw-bar" id="bar2"></div>
            <div class="pw-bar" id="bar3"></div>
            <span class="pw-label" id="pwLabel"></span>
          </div>
        </div>
        <div class="field">
          <label>Confirm Password <span class="req">*</span></label>
          <div class="input-wrap">
            <i class="fas fa-lock icon"></i>
            <input type="password" name="confirm_password" id="confirmInput"
                   placeholder="Repeat password" required/>
            <button type="button" class="eye-btn" onclick="togglePass('confirmInput','eyeIcon2')">
              <i class="fas fa-eye" id="eyeIcon2"></i>
            </button>
          </div>
        </div>
      </div>

      <button type="submit" class="btn-submit">
        <i class="fas fa-user-plus"></i> Create My Account
      </button>
    </form>

    <div class="reg-footer">
      Already registered? <a href="login.php">Sign in here</a>
    </div>

  </div>
</div>

<script>
function togglePass(inputId, iconId) {
  const inp  = document.getElementById(inputId);
  const icon = document.getElementById(iconId);
  inp.type = inp.type === 'password' ? 'text' : 'password';
  icon.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}

function checkStrength(val) {
  const bars  = [document.getElementById('bar1'), document.getElementById('bar2'), document.getElementById('bar3')];
  const label = document.getElementById('pwLabel');
  bars.forEach(b => b.className = 'pw-bar');
  if(!val) { label.textContent = ''; return; }
  let score = 0;
  if(val.length >= 6)  score++;
  if(val.length >= 10) score++;
  if(/[A-Z]/.test(val) && /[0-9!@#$%^&*]/.test(val)) score++;
  const levels = [
    { cls: 'active-weak',   label: 'Weak' },
    { cls: 'active-fair',   label: 'Fair' },
    { cls: 'active-strong', label: 'Strong' },
  ];
  const lvl = levels[score - 1] || levels[0];
  for(let i = 0; i < score; i++) bars[i].classList.add(lvl.cls);
  label.textContent = lvl.label;
  label.style.color = score === 1 ? '#e05c5c' : score === 2 ? '#e0a050' : '#4caf7c';
}
</script>
</body>
</html>