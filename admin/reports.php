<?php
$page_title = 'Reports';
require_once 'includes/auth.php';

// Revenue by month (last 6 months)
$monthly = $conn->query("
    SELECT DATE_FORMAT(created_at,'%b %Y') as month,
           COUNT(*) as orders,
           SUM(total) as revenue
    FROM orders
    WHERE status='delivered'
    AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at,'%Y-%m')
    ORDER BY created_at ASC
");
$months=[]; $revenues=[]; $order_counts=[];
if($monthly) while($r=$monthly->fetch_assoc()) {
    $months[]=$r['month']; $revenues[]=$r['revenue']; $order_counts[]=$r['orders'];
}

// Top products
$top_prods = $conn->query("
    SELECT p.product_name, SUM(oi.qty) as sold, SUM(oi.qty*oi.price) as revenue
    FROM order_items oi
    LEFT JOIN products p ON oi.product_id=p.product_id
    LEFT JOIN orders o ON oi.order_id=o.order_id
    WHERE o.status='delivered'
    GROUP BY oi.product_id ORDER BY sold DESC LIMIT 5
");

// Summary stats
$stats = $conn->query("SELECT
    COUNT(*) as total_orders,
    SUM(CASE WHEN status='delivered' THEN total ELSE 0 END) as total_revenue,
    SUM(CASE WHEN status='pending'   THEN 1 ELSE 0 END) as pending,
    SUM(CASE WHEN status='cancelled' THEN 1 ELSE 0 END) as cancelled
    FROM orders")->fetch_assoc();

// Orders by payment type
$by_payment = $conn->query("SELECT delivery_type, COUNT(*) c FROM orders GROUP BY delivery_type");
$pay_data = [1=>0, 2=>0, 3=>0];
if($by_payment) while($r=$by_payment->fetch_assoc()) $pay_data[$r['delivery_type']]=$r['c'];
?>

<!-- SUMMARY STATS -->
<div class="stat-grid" style="margin-bottom:24px;">
  <div class="stat-card"><div class="stat-icon purple"><i class="fas fa-shopping-bag"></i></div><div class="stat-info"><p>Total Orders</p><h3><?php echo $stats['total_orders']; ?></h3></div></div>
  <div class="stat-card"><div class="stat-icon green"><i class="fas fa-rupee-sign"></i></div><div class="stat-info"><p>Total Revenue</p><h3>Rs. <?php echo number_format($stats['total_revenue']??0); ?></h3><small>Delivered orders only</small></div></div>
  <div class="stat-card"><div class="stat-icon orange"><i class="fas fa-clock"></i></div><div class="stat-info"><p>Pending Orders</p><h3><?php echo $stats['pending']; ?></h3></div></div>
  <div class="stat-card"><div class="stat-icon red"><i class="fas fa-times-circle"></i></div><div class="stat-info"><p>Cancelled</p><h3><?php echo $stats['cancelled']; ?></h3></div></div>
</div>

<div class="grid-2">

<!-- TOP PRODUCTS -->
<div class="panel">
  <div class="panel-header"><h3><i class="fas fa-trophy" style="color:var(--orange);margin-right:8px;"></i>Top Selling Products</h3></div>
  <div style="padding:20px;">
    <?php if($top_prods && $top_prods->num_rows>0): $rank=1; while($p=$top_prods->fetch_assoc()): ?>
    <div style="display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #f5f5f5;">
      <div style="width:28px;height:28px;background:<?php echo $rank==1?'#f39c12':($rank==2?'#bdc3c7':'#cd7f32'); ?>;
                  border-radius:50%;display:flex;align-items:center;justify-content:center;
                  font-size:12px;font-weight:700;color:#fff;flex-shrink:0;">
        <?php echo $rank++; ?>
      </div>
      <div style="flex:1;">
        <p style="font-size:13px;font-weight:400;"><?php echo htmlspecialchars($p['product_name']); ?></p>
        <p style="font-size:11px;color:var(--gray);"><?php echo $p['sold']; ?> units sold</p>
      </div>
      <div style="text-align:right;">
        <p style="font-size:13px;font-weight:600;color:var(--purple);">Rs. <?php echo number_format($p['revenue']); ?></p>
      </div>
    </div>
    <?php endwhile; else: ?>
    <p style="text-align:center;color:var(--gray);padding:30px;">No sales data yet</p>
    <?php endif; ?>
  </div>
</div>

<!-- PAYMENT TYPE BREAKDOWN -->
<div class="panel">
  <div class="panel-header"><h3><i class="fas fa-credit-card" style="color:var(--blue);margin-right:8px;"></i>Orders by Payment Type</h3></div>
  <div style="padding:24px;">
    <?php
    $pay_labels = [1=>'Credit Card', 2=>'Cheque', 3=>'Cash on Delivery'];
    $pay_icons  = [1=>'fas fa-credit-card', 2=>'fas fa-money-check', 3=>'fas fa-hand-holding-usd'];
    $pay_colors = [1=>'var(--blue)', 2=>'var(--green)', 3=>'var(--orange)'];
    $total_pay  = array_sum($pay_data) ?: 1;
    foreach($pay_data as $type=>$count): $pct=round($count/$total_pay*100); ?>
    <div style="margin-bottom:20px;">
      <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px;">
        <span style="font-size:13px;display:flex;align-items:center;gap:8px;">
          <i class="<?php echo $pay_icons[$type]; ?>" style="color:<?php echo $pay_colors[$type]; ?>;"></i>
          <?php echo $pay_labels[$type]; ?>
        </span>
        <span style="font-size:12px;font-weight:600;"><?php echo $count; ?> orders (<?php echo $pct; ?>%)</span>
      </div>
      <div style="height:8px;background:#f0f0f0;border-radius:4px;overflow:hidden;">
        <div style="height:100%;width:<?php echo $pct; ?>%;background:<?php echo $pay_colors[$type]; ?>;border-radius:4px;transition:width .5s;"></div>
      </div>
    </div>
    <?php endforeach; ?>
  </div>

  <!-- ORDER STATUS BREAKDOWN -->
  <div style="border-top:1px solid var(--border);padding:24px;">
    <h4 style="font-size:13px;font-weight:600;margin-bottom:16px;">Order Status Breakdown</h4>
    <?php
    $status_r = $conn->query("SELECT status, COUNT(*) c FROM orders GROUP BY status");
    $status_data = [];
    if($status_r) while($r=$status_r->fetch_assoc()) $status_data[$r['status']]=$r['c'];
    $status_colors2=['pending'=>'var(--orange)','confirmed'=>'var(--blue)','dispatched'=>'#8e44ad','delivered'=>'var(--green)','cancelled'=>'var(--red)'];
    foreach($status_colors2 as $s=>$col): $c=$status_data[$s]??0; $pct2=round($c/$total_pay*100); ?>
    <div style="display:flex;align-items:center;gap:12px;margin-bottom:10px;">
      <span class="badge badge-<?php echo $s; ?>" style="min-width:90px;text-align:center;"><?php echo ucfirst($s); ?></span>
      <div style="flex:1;height:6px;background:#f0f0f0;border-radius:3px;">
        <div style="height:100%;width:<?php echo min($pct2,100); ?>%;background:<?php echo $col; ?>;border-radius:3px;"></div>
      </div>
      <span style="font-size:12px;font-weight:600;min-width:20px;"><?php echo $c; ?></span>
    </div>
    <?php endforeach; ?>
  </div>
</div>

</div><!-- grid-2 -->

<!-- MONTHLY REVENUE TABLE -->
<?php if(!empty($months)): ?>
<div class="panel" style="margin-top:0;">
  <div class="panel-header"><h3><i class="fas fa-chart-line" style="color:var(--green);margin-right:8px;"></i>Monthly Revenue (Last 6 Months)</h3></div>
  <table class="dash-table">
    <thead><tr><th>Month</th><th>Orders</th><th>Revenue</th></tr></thead>
    <tbody>
      <?php foreach($months as $i=>$m): ?>
      <tr>
        <td><?php echo $m; ?></td>
        <td style="font-weight:600;"><?php echo $order_counts[$i]; ?></td>
        <td style="font-weight:600;color:var(--purple);">Rs. <?php echo number_format($revenues[$i]); ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
