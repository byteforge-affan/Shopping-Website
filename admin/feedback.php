<?php $page_title='Feedback'; require_once 'includes/auth.php';
$feedbacks = $conn->query("SELECT * FROM feedback ORDER BY created_at DESC"); ?>
<div class="panel">
  <div class="panel-header"><h3>Customer Feedback</h3></div>
  <table class="dash-table">
    <thead><tr><th>#</th><th>Email</th><th>Subject</th><th>Message</th><th>Date</th></tr></thead>
    <tbody>
      <?php if($feedbacks&&$feedbacks->num_rows>0): $i=1; while($f=$feedbacks->fetch_assoc()): ?>
      <tr>
        <td style="color:var(--gray);"><?php echo $i++; ?></td>
        <td><?php echo htmlspecialchars($f['email']); ?></td>
        <td><?php echo htmlspecialchars($f['subject']??'—'); ?></td>
        <td style="max-width:300px;font-size:12px;color:#555;"><?php echo htmlspecialchars(substr($f['message'],0,120)).'...'; ?></td>
        <td style="font-size:12px;color:var(--gray);"><?php echo date('d M Y',strtotime($f['created_at'])); ?></td>
      </tr>
      <?php endwhile; else: ?>
      <tr><td colspan="5" style="text-align:center;padding:40px;color:var(--gray);">No feedback yet</td></tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
<?php require_once 'includes/footer.php'; ?>
