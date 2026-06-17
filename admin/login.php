<?php
session_start();
if(isset($_SESSION['admin_id'])) { header('Location: index.php'); exit; }

require_once '../includes/db.php';
$error = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $user = clean($conn, $_POST['username'] ?? '');
    $pass = $_POST['password'] ?? '';

    $stmt = $conn->prepare("SELECT * FROM admin WHERE username=? AND is_active=1 LIMIT 1");
    if($stmt) {
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if($row) {
            $ok = false;
            if(password_verify($pass, $row['password']))  $ok = true;
            elseif(md5($pass) === $row['password'])       $ok = true;
            elseif($pass === $row['password'])            $ok = true;
            if($ok) {
                $_SESSION['admin_id']   = $row['admin_id'];
                $_SESSION['admin_name'] = $row['full_name'];
                $_SESSION['admin_role'] = $row['role'];
                header('Location: index.php'); exit;
            }
        }
    }
    $error = 'Invalid username or password. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width,initial-scale=1"/>
<title>Admin Login — Arts Store</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<style>
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}

body {
  font-family: 'Inter', sans-serif;
  min-height: 100vh;
  display: flex;
  background: #0f0c1d;
}

/* ── LEFT PANEL ── */
.left-panel {
  width: 55%;
  position: relative;
  overflow: hidden;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 50px 60px;
  background: linear-gradient(145deg, #1a0a3d 0%, #2d1b69 40%, #4a2c8f 70%, #6b42c8 100%);
}

/* animated blobs */
.blob {
  position: absolute;
  border-radius: 50%;
  filter: blur(80px);
  opacity: 0.35;
  animation: drift 8s ease-in-out infinite;
}
.blob-1 { width:380px;height:380px;background:#7B5EA7;top:-80px;left:-80px;animation-delay:0s; }
.blob-2 { width:300px;height:300px;background:#a855f7;bottom:100px;right:-60px;animation-delay:3s; }
.blob-3 { width:200px;height:200px;background:#ec4899;bottom:-40px;left:30%;animation-delay:5s; }

@keyframes drift {
  0%,100% { transform: translate(0,0) scale(1); }
  33%     { transform: translate(20px,-20px) scale(1.05); }
  66%     { transform: translate(-15px,15px) scale(0.97); }
}

/* grid overlay */
.left-panel::before {
  content:'';
  position:absolute;
  inset:0;
  background-image:
    linear-gradient(rgba(255,255,255,.04) 1px,transparent 1px),
    linear-gradient(90deg,rgba(255,255,255,.04) 1px,transparent 1px);
  background-size:50px 50px;
}

.left-top { position:relative;z-index:2; }
.left-logo {
  display:inline-flex;
  align-items:center;
  gap:12px;
}
.left-logo-icon {
  width:46px;height:46px;
  background:rgba(255,255,255,.12);
  border:1px solid rgba(255,255,255,.2);
  border-radius:12px;
  display:flex;align-items:center;justify-content:center;
  font-size:20px;color:#fff;
  backdrop-filter:blur(10px);
}
.left-logo-text {
  font-family:'Playfair Display',serif;
  font-size:22px;
  color:#fff;
  font-weight:700;
  letter-spacing:0.05em;
}

.left-main {
  position:relative;
  z-index:2;
}
.left-main h1 {
  font-family:'Playfair Display',serif;
  font-size:52px;
  font-weight:700;
  color:#fff;
  line-height:1.1;
  margin-bottom:20px;
}
.left-main h1 span {
  color:#c084fc;
}
.left-main p {
  font-size:15px;
  font-weight:300;
  color:rgba(255,255,255,.65);
  line-height:1.8;
  max-width:380px;
  margin-bottom:40px;
}

/* stats row */
.stats-row {
  display:flex;
  gap:32px;
}
.stat-box {
  border-left:2px solid rgba(255,255,255,.2);
  padding-left:16px;
}
.stat-num {
  font-size:26px;
  font-weight:600;
  color:#fff;
  display:block;
  line-height:1;
  margin-bottom:4px;
}
.stat-lbl {
  font-size:11px;
  font-weight:400;
  letter-spacing:.12em;
  text-transform:uppercase;
  color:rgba(255,255,255,.45);
}

.left-bottom {
  position:relative;
  z-index:2;
  display:flex;
  align-items:center;
  gap:10px;
  font-size:12px;
  color:rgba(255,255,255,.35);
}
.left-bottom a {
  color:rgba(255,255,255,.5);
  text-decoration:none;
  transition:color .2s;
}
.left-bottom a:hover { color:#fff; }

/* ── RIGHT PANEL ── */
.right-panel {
  width:45%;
  background:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  padding:60px 50px;
}

.login-box {
  width:100%;
  max-width:380px;
}

.login-box-header {
  margin-bottom:40px;
}
.login-box-header h2 {
  font-size:26px;
  font-weight:600;
  color:#1a1a1a;
  margin-bottom:8px;
}
.login-box-header p {
  font-size:14px;
  font-weight:300;
  color:#888;
}

/* ── FORM ── */
.field {
  margin-bottom:20px;
}
.field label {
  display:block;
  font-size:11px;
  font-weight:600;
  letter-spacing:.12em;
  text-transform:uppercase;
  color:#444;
  margin-bottom:8px;
}
.input-wrap {
  position:relative;
}
.input-wrap .icon {
  position:absolute;
  left:14px;top:50%;
  transform:translateY(-50%);
  color:#bbb;
  font-size:14px;
  transition:color .2s;
}
.input-wrap input {
  width:100%;
  padding:13px 14px 13px 42px;
  border:1.5px solid #e8e8e8;
  border-radius:8px;
  font-family:'Inter',sans-serif;
  font-size:14px;
  color:#1a1a1a;
  background:#fafafa;
  outline:none;
  transition:border-color .2s,background .2s;
}
.input-wrap input:focus {
  border-color:#7B5EA7;
  background:#fff;
}
.input-wrap input:focus + .icon,
.input-wrap:focus-within .icon { color:#7B5EA7; }

/* toggle password */
.eye-btn {
  position:absolute;
  right:13px;top:50%;
  transform:translateY(-50%);
  background:none;border:none;
  color:#bbb;font-size:14px;
  cursor:pointer;padding:4px;
  transition:color .2s;
}
.eye-btn:hover { color:#7B5EA7; }

/* error */
.error-msg {
  display:flex;
  align-items:center;
  gap:10px;
  background:#fef2f2;
  border:1px solid #fecaca;
  border-left:4px solid #ef4444;
  border-radius:8px;
  padding:12px 16px;
  font-size:13px;
  color:#b91c1c;
  margin-bottom:24px;
}

/* submit */
.btn-login {
  width:100%;
  padding:14px;
  background:linear-gradient(135deg,#7B5EA7,#9b59b6);
  color:#fff;
  border:none;
  border-radius:8px;
  font-family:'Inter',sans-serif;
  font-size:13px;
  font-weight:600;
  letter-spacing:.1em;
  text-transform:uppercase;
  cursor:pointer;
  display:flex;align-items:center;justify-content:center;gap:10px;
  transition:all .25s;
  box-shadow:0 4px 20px rgba(123,94,167,.35);
  margin-top:8px;
}
.btn-login:hover {
  transform:translateY(-2px);
  box-shadow:0 8px 28px rgba(123,94,167,.45);
}
.btn-login:active { transform:translateY(0); }

/* divider */
.divider {
  display:flex;align-items:center;gap:12px;
  margin:28px 0;
  color:#ccc;font-size:12px;
}
.divider::before,.divider::after {
  content:'';flex:1;height:1px;background:#e8e8e8;
}

/* back link */
.back-link {
  display:flex;align-items:center;justify-content:center;gap:8px;
  font-size:13px;color:#888;
  text-decoration:none;
  transition:color .2s;
}
.back-link:hover { color:#7B5EA7; }

/* role badges */
.role-badges {
  display:flex;gap:10px;flex-wrap:wrap;
  margin-top:32px;padding-top:28px;
  border-top:1px solid #f0f0f0;
}
.role-badge {
  display:inline-flex;align-items:center;gap:6px;
  padding:5px 12px;
  border-radius:20px;
  font-size:11px;font-weight:600;letter-spacing:.08em;
  text-decoration:none;
  transition:all .2s;
}
.rb-admin {
  background:#f0ebf8;color:#7B5EA7;border:1px solid #d4c8ee;
}
.rb-staff {
  background:#e8f5ee;color:#1e7e45;border:1px solid #b8dfc8;
}
.rb-admin:hover { background:#7B5EA7;color:#fff; }
.rb-staff:hover { background:#1e7e45;color:#fff; }

/* responsive */
@media(max-width:900px) {
  .left-panel { display:none; }
  .right-panel { width:100%;padding:40px 24px; }
}
</style>
</head>
<body>

<!-- ── LEFT PANEL ── -->
<div class="left-panel">
  <div class="blob blob-1"></div>
  <div class="blob blob-2"></div>
  <div class="blob blob-3"></div>

  <div class="left-top">
    <div class="left-logo">
      <div class="left-logo-icon"><i class="fas fa-store"></i></div>
      <span class="left-logo-text">Arts Store</span>
    </div>
  </div>

  <div class="left-main">
    <h1>Welcome to<br/>the <span>Admin</span><br/>Dashboard</h1>
    <p>Manage your products, orders, employees, stock and reports — all from one place.</p>
    <div class="stats-row">
      <?php
      // Live stats
      $o = $conn->query("SELECT COUNT(*) c FROM orders")->fetch_assoc()['c']        ?? 0;
      $p = $conn->query("SELECT COUNT(*) c FROM products")->fetch_assoc()['c']      ?? 0;
      $c = $conn->query("SELECT COUNT(*) c FROM customers")->fetch_assoc()['c']     ?? 0;
      ?>
      <div class="stat-box">
        <span class="stat-num"><?php echo $o; ?></span>
        <span class="stat-lbl">Total Orders</span>
      </div>
      <div class="stat-box">
        <span class="stat-num"><?php echo $p; ?></span>
        <span class="stat-lbl">Products</span>
      </div>
      <div class="stat-box">
        <span class="stat-num"><?php echo $c; ?></span>
        <span class="stat-lbl">Customers</span>
      </div>
    </div>
  </div>

  <div class="left-bottom">
    <i class="fas fa-lock" style="font-size:11px;"></i>
    <span>Secure admin access only &nbsp;·&nbsp;</span>
    <a href="../index.php">View Website →</a>
  </div>
</div>

<!-- ── RIGHT PANEL ── -->
<div class="right-panel">
  <div class="login-box">

    <div class="login-box-header">
      <h2>Sign in to Dashboard</h2>
      <p>Enter your admin credentials to continue</p>
    </div>

    <?php if($error): ?>
    <div class="error-msg">
      <i class="fas fa-exclamation-circle"></i>
      <?php echo $error; ?>
    </div>
    <?php endif; ?>

    <form method="POST">

      <div class="field">
        <label>Username</label>
        <div class="input-wrap">
          <i class="fas fa-user icon"></i>
          <input type="text" name="username" placeholder="Enter username"
                 value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>" required/>
        </div>
      </div>

      <div class="field">
        <label>Password</label>
        <div class="input-wrap">
          <i class="fas fa-lock icon"></i>
          <input type="password" name="password" id="passInput" placeholder="Enter password" required/>
          <button type="button" class="eye-btn" onclick="togglePass()">
            <i class="fas fa-eye" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn-login">
        <i class="fas fa-sign-in-alt"></i>
        Login to Dashboard
      </button>
    </form>

    <div class="divider">or access</div>

    <a href="../index.php" class="back-link">
      <i class="fas fa-arrow-left" style="font-size:12px;"></i>
      Back to Website
    </a>

    <!-- Role shortcuts -->
    <div class="role-badges">
      <span style="font-size:11px;color:#aaa;align-self:center;">Other portals:</span>
      <a href="../employee/login.php" class="role-badge rb-staff">
        <i class="fas fa-user-tie"></i> Staff Login
      </a>
      <a href="../login.php" class="role-badge rb-admin" style="background:#fff3e0;color:#c0580c;border-color:#fcd9b0;">
        <i class="fas fa-user"></i> Customer Login
      </a>
    </div>

  </div>
</div>

<script>
function togglePass() {
  const inp  = document.getElementById('passInput');
  const icon = document.getElementById('eyeIcon');
  if(inp.type === 'password') {
    inp.type = 'text';
    icon.className = 'fas fa-eye-slash';
  } else {
    inp.type = 'password';
    icon.className = 'fas fa-eye';
  }
}
</script>

</body>
</html>