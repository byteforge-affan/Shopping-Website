<?php
$page_title = 'Customers';
require_once 'includes/auth.php';

$search = clean($conn, $_GET['q'] ?? '');
$where  = $search ? "WHERE full_name LIKE '%$search%' OR email LIKE '%$search%'" : '';
$page   = max(1,(int)($_GET['page']??1)); $per=15; $off=($page-1)*$per;
$total  = $conn->query("SELECT COUNT(*) c FROM customers $where")->fetch_assoc()['c'];
$pages  = ceil($total/$per);
$custs  = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM orders o WHERE o.customer_id=c.customer_id) as order_count,
                         (SELECT SUM(total) FROM orders o WHERE o.customer_id=c.customer_id AND o.status='delivered') as total_spent
                         FROM customers c $where ORDER BY c.created_at DESC LIMIT $per OFFSET $off");
?>
<div class="panel">
  <div class="panel-header">
    <h3>All Customers (<?php echo $total; ?>)</h3>
    <form method="GET" style="display:flex;gap:8px;">
      <input class="form-control" style="width:220px;" type="text" name="q"
             value="<?php echo htmlspecialchars($search); ?>" placeholder="Search name or email..."/>
      <button type="submit" class="btn btn-sm btn-purple"><i class="fas fa-search"></i></button>
    </form>
  </div>
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead>
      <tr><th>#</th><th>Customer</th><th>Phone</th><th>City</th><th>Orders</th><th>Total Spent</th><th>Joined</th></tr>
    </thead>
    <tbody>
      <?php if($custs && $custs->num_rows>0):
        $i=$off+1; while($c=$custs->fetch_assoc()): ?>
      <tr>
        <td style="color:var(--gray);"><?php echo $i++; ?></td>
        <td>
          <div style="display:flex;align-items:center;gap:10px;">
            <div style="width:34px;height:34px;background:var(--purple-light);border-radius:50%;
                        display:flex;align-items:center;justify-content:center;font-size:13px;
                        font-weight:600;color:var(--purple);">
              <?php echo strtoupper(substr($c['full_name'],0,1)); ?>
            </div>
            <div>
              <p style="font-size:13px;"><?php echo htmlspecialchars($c['full_name']); ?></p>
              <p style="font-size:11px;color:var(--gray);"><?php echo htmlspecialchars($c['email']); ?></p>
            </div>
          </div>
        </td>
        <td style="font-size:13px;"><?php echo htmlspecialchars($c['phone']??'—'); ?></td>
        <td style="font-size:13px;"><?php echo htmlspecialchars($c['city']??'—'); ?></td>
        <td style="font-weight:600;color:var(--purple);"><?php echo $c['order_count']; ?></td>
        <td style="font-weight:600;">Rs. <?php echo number_format($c['total_spent']??0); ?></td>
        <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y',strtotime($c['created_at'])); ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="7" style="text-align:center;padding:40px;color:var(--gray);">No customers found</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>
  <?php if($pages>1): ?>
  <div style="padding:12px 16px;" class="pagination">
    <?php for($p=1;$p<=$pages;$p++): ?>
      <a href="?page=<?php echo $p; ?>&q=<?php echo urlencode($search); ?>"
         class="<?php echo $p==$page?'active':''; ?>"><?php echo $p; ?></a>
    <?php endfor; ?>
  </div>
  <?php endif; ?>
</div>
<?php require_once 'includes/footer.php'; ?>
