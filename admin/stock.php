<?php
$page_title = 'Stock';
require_once 'includes/auth.php';

// Same subfolder fix as products.php
$subfolder = trim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(__DIR__))
), '/');
$base_url = '/' . $subfolder . '/';

// Image URL helper — same as products.php
function stockImg($path, $base_url) {
    if(empty($path)) return '';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

$msg = '';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['update_stock'])) {
    foreach($_POST['stocks'] as $pid => $qty) {
        $pid = (int)$pid;
        $qty = max(0, (int)$qty);
        $conn->query("UPDATE products SET stock=$qty WHERE product_id=$pid");
    }
    $msg = 'Stock updated successfully!';
}

$prods = $conn->query("
    SELECT p.*, c.cat_name
    FROM products p
    LEFT JOIN categories c ON p.cat_id = c.cat_id
    ORDER BY p.stock ASC, p.product_name ASC
");
?>

<?php if($msg): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>

<div class="panel">
  <div class="panel-header">
    <h3>
      <i class="fas fa-warehouse" style="color:var(--purple);margin-right:8px;"></i>
      Stock Management
    </h3>
  </div>

  <form method="POST">
  <div style="overflow-x:auto;">
  <table class="dash-table">
    <thead>
      <tr>
        <th>Image</th>
        <th>Product</th>
        <th>Category</th>
        <th>Current Stock</th>
        <th>Update Stock</th>
      </tr>
    </thead>
    <tbody>
      <?php if($prods && $prods->num_rows > 0):
        while($p = $prods->fetch_assoc()):
          $imgSrc = stockImg($p['product_image'] ?? '', $base_url);
          $pid    = $p['product_id'];
      ?>
      <tr>

        <!-- IMAGE — same fix as products.php -->
        <td>
          <?php if($imgSrc): ?>
            <img src="<?php echo htmlspecialchars($imgSrc); ?>"
                 style="width:54px;height:44px;object-fit:cover;
                        border:1px solid var(--border);border-radius:4px;display:block;"
                 onerror="this.style.display='none';
                          this.nextElementSibling.style.display='flex';"/>
            <div style="display:none;width:54px;height:44px;background:#f0ebf8;
                        border:1px solid var(--border);border-radius:4px;
                        align-items:center;justify-content:center;">
              <i class="fas fa-box" style="color:#9B7BC0;font-size:18px;"></i>
            </div>
          <?php else: ?>
            <div style="width:54px;height:44px;background:#f0ebf8;
                        border:1px solid var(--border);border-radius:4px;
                        display:flex;align-items:center;justify-content:center;">
              <i class="fas fa-box" style="color:#9B7BC0;font-size:18px;"></i>
            </div>
          <?php endif; ?>
        </td>

        <!-- PRODUCT NAME + CODE -->
        <td>
          <strong style="font-size:13px;">
            <?php echo htmlspecialchars($p['product_name']); ?>
          </strong><br/>
          <span style="font-size:11px;color:var(--gray);">
            <?php echo htmlspecialchars($p['product_code'] ?? ''); ?>
          </span>
        </td>

        <!-- CATEGORY -->
        <td style="font-size:12px;color:var(--gray);">
          <?php echo htmlspecialchars($p['cat_name'] ?? ''); ?>
        </td>

        <!-- CURRENT STOCK with color + label -->
        <td>
          <span style="font-weight:700;font-size:16px;color:<?php
            echo $p['stock'] == 0
                ? 'var(--red)'
                : ($p['stock'] < 5 ? 'var(--orange)' : 'var(--green)');
          ?>;">
            <?php echo (int)$p['stock']; ?>
          </span>
          <?php if($p['stock'] == 0): ?>
            <span style="font-size:10px;font-weight:600;letter-spacing:.08em;
                         color:var(--red);margin-left:6px;">OUT OF STOCK</span>
          <?php elseif($p['stock'] < 5): ?>
            <span style="font-size:10px;font-weight:600;letter-spacing:.08em;
                         color:var(--orange);margin-left:6px;">LOW</span>
          <?php endif; ?>
        </td>

        <!-- UPDATE INPUT -->
        <td>
          <input type="number"
                 name="stocks[<?php echo $pid; ?>]"
                 value="<?php echo (int)$p['stock']; ?>"
                 min="0"
                 class="form-control"
                 style="width:90px;padding:6px 10px;"/>
        </td>

      </tr>
      <?php endwhile; else: ?>
      <tr>
        <td colspan="5" style="text-align:center;padding:40px;color:var(--gray);">
          No products found
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
  </div>

  <div style="padding:16px 20px;border-top:1px solid var(--border);">
    <button type="submit" name="update_stock" class="btn btn-purple">
      <i class="fas fa-save"></i> Save All Stock Updates
    </button>
  </div>
  </form>
</div>

<?php require_once 'includes/footer.php'; ?>