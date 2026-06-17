<?php
$page_title = 'Change Password';
require_once 'includes/auth.php';
$msg=$err='';
if($_SERVER['REQUEST_METHOD']==='POST') {
    $cur  = $_POST['current_password']??'';
    $new  = $_POST['new_password']??'';
    $conf = $_POST['confirm_password']??'';
    $eid  = (int)$_SESSION['emp_id'];
    $row  = $conn->query("SELECT password FROM admin WHERE admin_id=$eid")->fetch_assoc();
    if(!password_verify($cur, $row['password'])) { $err='Current password is incorrect.'; }
    elseif($new !== $conf) { $err='New passwords do not match.'; }
    elseif(strlen($new)<6) { $err='Password must be at least 6 characters.'; }
    else {
        $hash=password_hash($new,PASSWORD_DEFAULT);
        $conn->query("UPDATE admin SET password='$hash' WHERE admin_id=$eid");
        $msg='Password updated successfully!';
    }
}
?>
<?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>
<?php if($err):  ?><div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $err; ?></div><?php endif; ?>
<div class="panel" style="max-width:480px;">
  <div class="panel-header"><h3><i class="fas fa-lock" style="color:var(--purple);margin-right:8px;"></i>Change My Password</h3></div>
  <div class="panel-body">
    <form method="POST">
      <div class="form-group"><label>Current Password</label><input class="form-control" type="password" name="current_password" required/></div>
      <div class="form-group"><label>New Password</label><input class="form-control" type="password" name="new_password" required/></div>
      <div class="form-group"><label>Confirm New Password</label><input class="form-control" type="password" name="confirm_password" required/></div>
      <button type="submit" class="btn btn-green"><i class="fas fa-save"></i> Update Password</button>
    </form>
    <p style="font-size:12px;color:var(--gray);margin-top:16px;">
      <i class="fas fa-info-circle"></i> Only you can change your password. Contact admin for username changes.
    </p>
  </div>
</div>
<?php echo '</div></div></div></body></html>'; ?>
