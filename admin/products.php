<?php
$page_title = 'Products';
require_once 'includes/auth.php';

// ── Fix subfolder for Windows XAMPP ──
$subfolder = trim(str_replace(
    str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']),
    '',
    str_replace('\\', '/', dirname(__DIR__))
), '/');

$base_url = '/' . $subfolder . '/';
$msg = $err = '';

// ── DELETE ──
if(isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    // Delete local image file if exists
    $old = $conn->query("SELECT product_image FROM products WHERE product_id=$did")->fetch_assoc();
    if($old && !empty($old['product_image']) && strpos($old['product_image'],'http') !== 0) {
        $oldFile = dirname(__DIR__) . '/' . ltrim($old['product_image'], '/');
        if(file_exists($oldFile)) unlink($oldFile);
    }
    $conn->query("DELETE FROM products WHERE product_id=$did");
    $msg = 'Product deleted.';
}

// ── SAVE ──
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_product'])) {
    $pid      = (int)($_POST['product_id'] ?? 0);
    $name     = clean($conn, $_POST['product_name'] ?? '');
    $cat      = (int)($_POST['cat_id'] ?? 0);
    $price    = (float)($_POST['price'] ?? 0);
    $stock    = (int)($_POST['stock'] ?? 0);
    $desc     = clean($conn, $_POST['description'] ?? '');
    $code     = clean($conn, $_POST['product_code'] ?? '');
    $warranty = isset($_POST['has_warranty']) ? 1 : 0;
    $isnew    = isset($_POST['is_new']) ? 1 : 0;
    $img      = clean($conn, $_POST['product_image'] ?? '');

    // ── Local file upload ──
    if(!empty($_FILES['product_image_file']['name'])) {
        $allowed = ['image/jpeg','image/png','image/gif','image/webp'];
        $ftype   = mime_content_type($_FILES['product_image_file']['tmp_name']);
        if(in_array($ftype, $allowed) && $_FILES['product_image_file']['size'] <= 5*1024*1024) {
            $upload_dir = dirname(__DIR__) . '/uploads/products/';
            if(!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            $ext      = strtolower(pathinfo($_FILES['product_image_file']['name'], PATHINFO_EXTENSION));
            $filename = 'prod_' . time() . '_' . mt_rand(100,999) . '.' . $ext;
            if(move_uploaded_file($_FILES['product_image_file']['tmp_name'], $upload_dir.$filename)) {
                // Delete old local image if updating
                if($pid) {
                    $old = $conn->query("SELECT product_image FROM products WHERE product_id=$pid")->fetch_assoc();
                    if($old && !empty($old['product_image']) && strpos($old['product_image'],'http') !== 0) {
                        $oldFile = dirname(__DIR__) . '/' . ltrim($old['product_image'],'/');
                        if(file_exists($oldFile)) unlink($oldFile);
                    }
                }
                $img = 'uploads/products/' . $filename;
            } else {
                $err = 'Upload failed. Check permissions on /uploads/products/';
            }
        } else {
            $err = 'Invalid file or too large. Max 5MB, JPG/PNG/GIF/WEBP only.';
        }
    }

    if(!$name || !$price) {
        $err = 'Product name and price are required.';
    }

    if(!$err) {
        $img_safe = clean($conn, $img);
        if($pid) {
            $conn->query("UPDATE products SET
                product_name='$name', cat_id=$cat, price=$price, stock=$stock,
                description='$desc', product_image='$img_safe', product_code='$code',
                has_warranty=$warranty, is_new=$isnew
                WHERE product_id=$pid");
            $msg = 'Product updated!';
        } else {
            $conn->query("INSERT INTO products
                (product_code,product_name,cat_id,price,stock,description,product_image,has_warranty,is_new,created_at)
                VALUES ('$code','$name',$cat,$price,$stock,'$desc','$img_safe',$warranty,$isnew,NOW())");
            $msg = 'Product added!';
        }
    }
}

// ── EDIT MODE ──
$edit = null;
if(isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $er  = $conn->query("SELECT * FROM products WHERE product_id=$eid");
    if($er) $edit = $er->fetch_assoc();
}

// ── IMAGE HELPER ──
function adminProdImg($path, $base_url) {
    if(empty($path)) return '';
    if(strpos($path, 'http') === 0) return $path;
    return $base_url . ltrim($path, '/');
}

// ── CATEGORIES ──
$cats_r = $conn->query("SELECT * FROM categories ORDER BY cat_name");
$cats = [];
if($cats_r) while($row = $cats_r->fetch_assoc()) $cats[] = $row;

// ── PRODUCTS LIST ──
$search = clean($conn, $_GET['q'] ?? '');
$where  = $search ? "WHERE p.product_name LIKE '%$search%'" : '';
$page   = max(1, (int)($_GET['page'] ?? 1));
$per    = 12;
$off    = ($page - 1) * $per;
$total  = $conn->query("SELECT COUNT(*) c FROM products p $where")->fetch_assoc()['c'];
$pages  = ceil($total / $per);
$prods  = $conn->query("SELECT p.*, c.cat_name FROM products p
                        LEFT JOIN categories c ON p.cat_id=c.cat_id
                        $where ORDER BY p.created_at DESC
                        LIMIT $per OFFSET $off");

// ── PREVIEW SRC for edit form ──
$prevSrc = '';
if(!empty($edit['product_image'])) {
    $prevSrc = adminProdImg($edit['product_image'], $base_url);
}
?>

<?php if($msg): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>
<?php if($err): ?>
<div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $err; ?></div>
<?php endif; ?>

<div class="grid-2" style="align-items:start;">

<!-- ── ADD / EDIT FORM ── -->
<div class="panel" style="position:sticky;top:80px;">
  <div class="panel-header">
    <h3>
      <i class="fas fa-<?php echo $edit?'edit':'plus'; ?>"
         style="color:var(--purple);margin-right:8px;"></i>
      <?php echo $edit ? 'Edit Product' : 'Add New Product'; ?>
    </h3>
    <?php if($edit): ?>
      <a href="products.php" class="btn btn-sm btn-outline-purple">+ New</a>
    <?php endif; ?>
  </div>
  <div class="panel-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="product_id"   value="<?php echo $edit['product_id'] ?? 0; ?>"/>
      <input type="hidden" name="save_product" value="1"/>

      <div class="form-row">
        <div class="form-group">
          <label>Product Name *</label>
          <input class="form-control" type="text" name="product_name" required
                 value="<?php echo htmlspecialchars($edit['product_name'] ?? ''); ?>"/>
        </div>
        <div class="form-group">
          <label>Product Code</label>
          <input class="form-control" type="text" name="product_code" placeholder="GA00001"
                 value="<?php echo htmlspecialchars($edit['product_code'] ?? ''); ?>"/>
        </div>
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Category</label>
          <select class="form-control" name="cat_id">
            <option value="">Select Category</option>
            <?php foreach($cats as $c): ?>
              <option value="<?php echo $c['cat_id']; ?>"
                <?php echo (isset($edit['cat_id']) && $edit['cat_id']==$c['cat_id']) ? 'selected' : ''; ?>>
                <?php echo htmlspecialchars($c['cat_name']); ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="form-group">
          <label>Price (Rs.) *</label>
          <input class="form-control" type="number" name="price" step="0.01" required
                 value="<?php echo $edit['price'] ?? ''; ?>"/>
        </div>
      </div>

      <div class="form-group">
        <label>Stock Quantity</label>
        <input class="form-control" type="number" name="stock" min="0"
               value="<?php echo $edit['stock'] ?? 0; ?>"/>
      </div>

      <div class="form-group">
        <label>Description</label>
        <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($edit['description'] ?? ''); ?></textarea>
      </div>

      <!-- ── IMAGE SOURCE ── -->
      <div class="form-group">
        <label style="margin-bottom:6px;display:block;">Image Source</label>
        <div style="display:flex;gap:16px;margin-bottom:10px;">
          <label style="cursor:pointer;display:flex;align-items:center;gap:6px;">
            <input type="radio" name="img_source" value="url" id="img_url_radio"
                   onchange="toggleImgSource('url')"/> 🌐 Online URL
          </label>
          <label style="cursor:pointer;display:flex;align-items:center;gap:6px;">
            <input type="radio" name="img_source" value="local" id="img_local_radio"
                   onchange="toggleImgSource('local')"/> 📁 Upload from device
          </label>
        </div>

        <div id="img_url_wrap">
          <input class="form-control" type="text" name="product_image" id="prod_image_url"
                 placeholder="https://example.com/image.jpg"
                 value="<?php echo htmlspecialchars($edit['product_image'] ?? ''); ?>"/>
          <small style="color:var(--gray);font-size:11px;">Paste any image URL from the web.</small>
        </div>

        <div id="img_local_wrap" style="display:none;">
          <input class="form-control" type="file" name="product_image_file" id="prod_image_file"
                 accept="image/jpeg,image/png,image/gif,image/webp"
                 onchange="previewProdImg(this)"/>
          <small style="color:#856404;font-size:11px;">Max 5MB — JPG, PNG, GIF, WEBP only.</small>
        </div>
      </div>

      <!-- ── PREVIEW ── -->
      <div id="prod_preview_wrap"
           style="margin-bottom:16px;<?php echo empty($prevSrc) ? 'display:none;' : ''; ?>">
        <img id="prod_preview" src="<?php echo htmlspecialchars($prevSrc); ?>"
             style="width:100%;height:140px;object-fit:cover;
                    border:1px solid var(--border);border-radius:6px;"
             onerror="this.onerror=null;this.style.display='none'"/>
      </div>

      <div style="display:flex;gap:20px;margin-bottom:18px;">
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
          <input type="checkbox" name="is_new" value="1"
                 <?php echo !empty($edit['is_new']) ? 'checked' : ''; ?>
                 style="accent-color:var(--purple);"/>
          Mark as New
        </label>
        <label style="display:flex;align-items:center;gap:8px;font-size:13px;cursor:pointer;">
          <input type="checkbox" name="has_warranty" value="1"
                 <?php echo !empty($edit['has_warranty']) ? 'checked' : ''; ?>
                 style="accent-color:var(--purple);"/>
          Has Warranty
        </label>
      </div>

      <button type="submit" class="btn btn-purple" style="width:100%;justify-content:center;">
        <i class="fas fa-save"></i>
        <?php echo $edit ? 'Update Product' : 'Add Product'; ?>
      </button>
    </form>
  </div>
</div>

<!-- ── PRODUCTS LIST ── -->
<div>
  <div class="panel">
    <div class="panel-header">
      <h3>All Products (<?php echo $total; ?>)</h3>
      <form method="GET" style="display:flex;gap:8px;">
        <input class="form-control" style="width:200px;" type="text" name="q"
               value="<?php echo htmlspecialchars($search); ?>" placeholder="Search..."/>
        <button type="submit" class="btn btn-sm btn-purple">
          <i class="fas fa-search"></i>
        </button>
      </form>
    </div>
    <div style="overflow-x:auto;">
      <table class="dash-table">
        <thead>
          <tr>
            <th>Image</th>
            <th>Product</th>
            <th>Category</th>
            <th>Price</th>
            <th>Stock</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php if($prods && $prods->num_rows > 0):
            while($p = $prods->fetch_assoc()):
              $thumbSrc = adminProdImg($p['product_image'] ?? '', $base_url);
          ?>
          <tr>
            <td>
              <?php if($thumbSrc): ?>
                <img src="<?php echo htmlspecialchars($thumbSrc); ?>"
                     style="width:54px;height:42px;object-fit:cover;
                            border:1px solid var(--border);border-radius:4px;"
                     onerror="this.onerror=null;this.style.display='none';
                              this.nextElementSibling.style.display='flex'"/>
                <div style="display:none;width:54px;height:42px;background:#f0f0f0;
                            border-radius:4px;align-items:center;justify-content:center;">
                  <i class="fas fa-image" style="color:#ccc;font-size:14px;"></i>
                </div>
              <?php else: ?>
                <div style="width:54px;height:42px;background:#f0f0f0;border-radius:4px;
                            display:flex;align-items:center;justify-content:center;">
                  <i class="fas fa-image" style="color:#ccc;font-size:14px;"></i>
                </div>
              <?php endif; ?>
            </td>
            <td>
              <strong style="font-size:13px;">
                <?php echo htmlspecialchars($p['product_name']); ?>
              </strong><br/>
              <span style="font-size:11px;color:var(--gray);">
                <?php echo htmlspecialchars($p['product_code'] ?? ''); ?>
              </span>
            </td>
            <td style="font-size:12px;color:var(--gray);">
              <?php echo htmlspecialchars($p['cat_name'] ?? ''); ?>
            </td>
            <td style="font-weight:600;">
              Rs. <?php echo number_format($p['price']); ?>
            </td>
            <td>
              <span style="font-weight:700;color:<?php
                echo $p['stock'] == 0
                    ? 'var(--red)'
                    : ($p['stock'] < 5 ? 'var(--orange)' : 'var(--green)');
              ?>;">
                <?php echo (int)$p['stock']; ?>
              </span>
            </td>
            <td style="display:flex;gap:6px;">
              <a href="products.php?edit=<?php echo $p['product_id']; ?>"
                 class="btn btn-sm btn-icon btn-outline-purple" title="Edit">
                <i class="fas fa-edit"></i>
              </a>
              <a href="products.php?delete=<?php echo $p['product_id']; ?>"
                 class="btn btn-sm btn-icon btn-red confirm-delete" title="Delete">
                <i class="fas fa-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; else: ?>
          <tr>
            <td colspan="6"
                style="text-align:center;padding:40px;color:var(--gray);">
              No products found
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>

    <?php if($pages > 1): ?>
    <div style="padding:12px 16px;" class="pagination">
      <?php for($i = 1; $i <= $pages; $i++): ?>
        <a href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>"
           class="<?php echo $i == $page ? 'active' : ''; ?>">
          <?php echo $i; ?>
        </a>
      <?php endfor; ?>
    </div>
    <?php endif; ?>
  </div>
</div>

</div><!-- grid-2 -->

<script>
window.addEventListener('DOMContentLoaded', function() {
    const urlVal = document.getElementById('prod_image_url')?.value || '';
    if(urlVal && !urlVal.startsWith('http')) {
        document.getElementById('img_local_radio').checked = true;
        toggleImgSource('local');
    } else {
        document.getElementById('img_url_radio').checked = true;
        toggleImgSource('url');
    }
    document.getElementById('prod_image_url')?.addEventListener('input', function() {
        updateProdPreview(this.value);
    });
});

function toggleImgSource(src) {
    document.getElementById('img_url_wrap').style.display   = src === 'url'   ? 'block' : 'none';
    document.getElementById('img_local_wrap').style.display = src === 'local' ? 'block' : 'none';
    if(src === 'url') {
        document.getElementById('prod_image_file').value = '';
        updateProdPreview(document.getElementById('prod_image_url').value);
    } else {
        updateProdPreview('');
    }
}

function previewProdImg(input) {
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => updateProdPreview(e.target.result);
        reader.readAsDataURL(input.files[0]);
    }
}

function updateProdPreview(src) {
    const wrap = document.getElementById('prod_preview_wrap');
    const img  = document.getElementById('prod_preview');
    if(src) { img.src = src; wrap.style.display = 'block'; }
    else    { wrap.style.display = 'none'; }
}
</script>

<?php require_once 'includes/footer.php'; ?>