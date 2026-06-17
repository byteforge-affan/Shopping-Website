change_password.php                                                                                                                                                                       <?php
session_start();
require_once 'includes/db.php';
$page_title = 'Change Password';

if(!isset($_SESSION['customer_id'])) { header('Location: login.php'); exit; }

$cid = (int)$_SESSION['customer_id'];
$msg = $err = '';

if($_SERVER['REQUEST_METHOD']==='POST') {
    $cur  = $_POST['current_password'] ?? '';
    $new  = $_POST['new_password']     ?? '';
    $conf = $_POST['confirm_password'] ?? '';
    $row  = $conn->query("SELECT password FROM customers WHERE customer_id=$cid")->fetch_assoc();

    if(!password_verify($cur, $row['password']))     $err = 'Current password is incorrect.';
    elseif($new !== $conf)                           $err = 'New passwords do not match.';
    elseif(strlen($new) < 6)                        $err = 'Password must be at least 6 characters.';
    else {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $conn->query("UPDATE customers SET password='$hash' WHERE customer_id=$cid");
        $msg = 'Password updated successfully!';
    }
}
?>
<?php include 'includes/header.php'; ?>

<div class="page-hero"><h1>Change Password</h1></div>

<section style="padding:70px 40px;background:#F8F8FC;min-height:60vh;display:flex;align-items:center;justify-content:center;">
  <div style="background:#fff;border:1px solid #EBEBF0;border-radius:12px;padding:40px 48px;max-width:460px;width:100%;box-shadow:0 2px 20px rgba(123,94,167,.08);">

    <div style="text-align:center;margin-bottom:32px;">
      <div style="width:60px;height:60px;background:#F5F0FF;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:22px;color:#7B5EA7;">
        <i class="fas fa-lock"></i>
      </div>
      <h2 style="font-family:'Playfair Display',serif;font-size:24px;margin-bottom:6px;">Change Password</h2>
      <p style="font-size:13px;color:#888;">Keep your account secure with a strong password</p>
    </div>

    <?php if($msg): ?>
    <div style="display:flex;align-items:center;gap:10px;background:#F0FDF4;color:#15803D;border:1px solid #BBF7D0;border-radius:8px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
      <i class="fas fa-check-circle"></i> <?php echo $msg; ?>
    </div>
    <?php endif; ?>
    <?php if($err): ?>
    <div style="display:flex;align-items:center;gap:10px;background:#FEF2F2;color:#B91C1C;border:1px solid #FECACA;border-radius:8px;padding:12px 16px;font-size:13px;margin-bottom:20px;">
      <i class="fas fa-exclamation-circle"></i> <?php echo $err; ?>
    </div>
    <?php endif; ?>

    <form method="POST">
      <?php foreach([
        ['current_password','Current Password','Your existing password'],
        ['new_password',    'New Password',    'At least 6 characters'],
        ['confirm_password','Confirm Password','Repeat your new password'],
      ] as $f): ?>
      <div style="margin-bottom:18px;">
        <label style="display:block;font-size:11px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;color:#555;margin-bottom:7px;"><?php echo $f[1]; ?></label>
        <div style="position:relative;">
          <i class="fas fa-lock" style="position:absolute;left:14px;top:50%;transform:translateY(-50%);color:#bbb;font-size:13px;"></i>
          <input type="password" name="<?php echo $f[0]; ?>"
                 placeholder="<?php echo $f[2]; ?>"
                 required
                 style="width:100%;padding:12px 14px 12px 40px;border:1.5px solid #EBEBF0;border-radius:8px;font-family:'Josefin Sans',sans-serif;font-size:14px;outline:none;transition:border-color .2s;"
                 onfocus="this.style.borderColor='#7B5EA7'" onblur="this.style.borderColor='#EBEBF0'"/>
        </div>
      </div>
      <?php endforeach; ?>

      <button type="submit"
              style="width:100%;padding:13px;background:linear-gradient(135deg,#7B5EA7,#9b59b6);color:#fff;border:none;border-radius:8px;font-family:'Josefin Sans',sans-serif;font-size:13px;font-weight:600;letter-spacing:.1em;text-transform:uppercase;cursor:pointer;margin-top:6px;transition:all .25s;box-shadow:0 4px 16px rgba(123,94,167,.3);"
              onmouseover="this.style.transform='translateY(-2px)'"
              onmouseout="this.style.transform='translateY(0)'">
        <i class="fas fa-save" style="margin-right:8px;"></i>Update Password
      </button>
    </form>

    <div style="text-align:center;margin-top:24px;">
      <a href="my-account.php" style="font-size:13px;color:#7B5EA7;text-decoration:none;">
        <i class="fas fa-arrow-left" style="font-size:11px;margin-right:6px;"></i>Back to My Account
      </a>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>