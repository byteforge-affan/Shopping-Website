<?php
$page_title = 'Employees';
require_once 'includes/auth.php';

$msg = $err = '';

// DELETE
if(isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $conn->query("UPDATE admin SET is_active=0 WHERE admin_id=$did AND role='employee'");
    $msg = 'Employee deactivated.';
}

// ADD / EDIT employee
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_emp'])) {
    $eid      = (int)($_POST['emp_id'] ?? 0);
    $name     = clean($conn, $_POST['full_name']  ?? '');
    $username = clean($conn, $_POST['username']   ?? '');
    $email    = clean($conn, $_POST['email']      ?? '');
    $phone    = clean($conn, $_POST['phone']      ?? '');
    $pass     = $_POST['password'] ?? '';

    if(!$name || !$username || !$email) {
        $err = 'Name, username and email are required.';
    } elseif($eid) {
        // Update (password optional)
        if($pass) {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $conn->query("UPDATE admin SET full_name='$name',username='$username',email='$email',phone='$phone',password='$hash' WHERE admin_id=$eid");
        } else {
            $conn->query("UPDATE admin SET full_name='$name',username='$username',email='$email',phone='$phone' WHERE admin_id=$eid");
        }
        $msg = 'Employee updated!';
    } else {
        if(!$pass) { $err = 'Password is required for new employee.'; }
        else {
            $hash = password_hash($pass, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO admin (full_name,username,email,phone,password,role,is_active,created_at)
                          VALUES ('$name','$username','$email','$phone','$hash','employee',1,NOW())");
            $msg = 'Employee account created!';
        }
    }
}

// Edit mode
$edit = null;
if(isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $er  = $conn->query("SELECT * FROM admin WHERE admin_id=$eid");
    if($er) $edit = $er->fetch_assoc();
}

// List employees
$emps = $conn->query("SELECT * FROM admin WHERE role='employee' ORDER BY created_at DESC");
?>

<?php if($msg): ?><div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div><?php endif; ?>
<?php if($err): ?><div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $err; ?></div><?php endif; ?>

<div class="grid-2" style="align-items:start;">

<!-- FORM -->
<div class="panel" style="position:sticky;top:80px;">
  <div class="panel-header">
    <h3><i class="fas fa-user-plus" style="color:var(--purple);margin-right:8px;"></i>
      <?php echo $edit ? 'Edit Employee' : 'Add Employee'; ?>
    </h3>
    <?php if($edit): ?><a href="employees.php" class="btn btn-sm btn-outline-purple">+ New</a><?php endif; ?>
  </div>
  <div class="panel-body">
    <form method="POST">
      <input type="hidden" name="emp_id" value="<?php echo $edit['admin_id'] ?? 0; ?>"/>
      <input type="hidden" name="save_emp" value="1"/>

      <div class="form-group">
        <label>Full Name *</label>
        <input class="form-control" type="text" name="full_name" required
               value="<?php echo htmlspecialchars($edit['full_name'] ?? ''); ?>"/>
      </div>
      <div class="form-row">
        <div class="form-group">
          <label>Username *</label>
          <input class="form-control" type="text" name="username" required
                 value="<?php echo htmlspecialchars($edit['username'] ?? ''); ?>"/>
        </div>
        <div class="form-group">
          <label>Phone</label>
          <input class="form-control" type="text" name="phone"
                 value="<?php echo htmlspecialchars($edit['phone'] ?? ''); ?>"/>
        </div>
      </div>
      <div class="form-group">
        <label>Email *</label>
        <input class="form-control" type="email" name="email" required
               value="<?php echo htmlspecialchars($edit['email'] ?? ''); ?>"/>
      </div>
      <div class="form-group">
        <label>Password <?php echo $edit?'(leave blank to keep)':'*'; ?></label>
        <input class="form-control" type="password" name="password"
               <?php echo $edit?'':'required'; ?> placeholder="Min 6 characters"/>
      </div>

      <button type="submit" class="btn btn-purple" style="width:100%;justify-content:center;">
        <i class="fas fa-save"></i> <?php echo $edit ? 'Update Employee' : 'Create Employee'; ?>
      </button>

      <p style="font-size:11px;color:var(--gray);margin-top:12px;text-align:center;">
        <i class="fas fa-info-circle"></i>
        Only Admin can create employee accounts. Employees can only change their own password.
      </p>
    </form>
  </div>
</div>

<!-- EMPLOYEE LIST -->
<div class="panel">
  <div class="panel-header">
    <h3>All Employees</h3>
  </div>
  <table class="dash-table">
    <thead>
      <tr><th>Name</th><th>Username</th><th>Email</th><th>Status</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if($emps && $emps->num_rows > 0):
        while($e = $emps->fetch_assoc()): ?>
      <tr>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;background:var(--purple-light);border-radius:50%;
                        display:flex;align-items:center;justify-content:center;font-size:13px;
                        font-weight:600;color:var(--purple);">
              <?php echo strtoupper(substr($e['full_name'],0,1)); ?>
            </div>
            <?php echo htmlspecialchars($e['full_name']); ?>
          </div>
        </td>
        <td style="font-size:13px;font-weight:600;color:var(--purple);"><?php echo htmlspecialchars($e['username']); ?></td>
        <td style="font-size:12px;color:var(--gray);"><?php echo htmlspecialchars($e['email']); ?></td>
        <td>
          <span class="badge badge-<?php echo $e['is_active']?'active':'inactive'; ?>">
            <?php echo $e['is_active']?'Active':'Inactive'; ?>
          </span>
        </td>
        <td style="display:flex;gap:6px;">
          <a href="employees.php?edit=<?php echo $e['admin_id']; ?>" class="btn btn-sm btn-icon btn-outline-purple" title="Edit">
            <i class="fas fa-edit"></i>
          </a>
          <a href="employees.php?delete=<?php echo $e['admin_id']; ?>" class="btn btn-sm btn-icon btn-red confirm-delete" title="Deactivate">
            <i class="fas fa-ban"></i>
          </a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray);">No employees added yet</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>

</div>
<?php require_once 'includes/footer.php'; ?>
