<?php
$page_title = 'Categories';
require_once 'includes/auth.php';

$msg = $err = '';

// ── Correct paths: admin is one level deep, uploads must go to project ROOT ──
$project_root = dirname(__DIR__);                    // e.g. C:/xampp/htdocs/arts_store
$project_url  = dirname(dirname($_SERVER['PHP_SELF'])); // e.g. /arts_store
$upload_rel   = 'uploads/categories/';              // relative path saved to DB
$upload_abs   = $project_root . '/' . $upload_rel;  // absolute path for file operations

// ── Image preview helper (for admin display only) ──
function adminImgSrc($path, $project_url) {
    if(empty($path)) return '';
    if(strpos($path, 'http') === 0) return $path;
    return $project_url . '/' . ltrim($path, '/');
}

// ── DELETE ──
if(isset($_GET['delete'])) {
    $did = (int)$_GET['delete'];
    $chk = $conn->query("SELECT COUNT(*) c FROM products WHERE cat_id=$did")->fetch_assoc()['c'];
    if($chk > 0) {
        $err = 'Cannot delete: this category has products. Remove products first.';
    } else {
        // Delete local image file if exists
        $old = $conn->query("SELECT cat_image FROM categories WHERE cat_id=$did")->fetch_assoc();
        if($old && !empty($old['cat_image']) && strpos($old['cat_image'], 'http') !== 0) {
            $oldFile = $project_root . '/' . ltrim($old['cat_image'], '/');
            if(file_exists($oldFile)) unlink($oldFile);
        }
        $conn->query("DELETE FROM categories WHERE cat_id=$did");
        $msg = 'Category deleted.';
    }
}

// ── SAVE ──
if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_cat'])) {
    $cid  = (int)($_POST['cat_id'] ?? 0);
    $name = clean($conn, $_POST['cat_name'] ?? '');
    $slug = clean($conn, strtolower(preg_replace('/[^a-z0-9]+/', '-', strtolower($_POST['cat_name'] ?? ''))));
    $desc = clean($conn, $_POST['cat_desc'] ?? '');
    $img  = clean($conn, $_POST['cat_image'] ?? ''); // URL or keep existing

    // ── Handle local file upload ──
    if(!empty($_FILES['cat_image_file']['name'])) {
        $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $ftype   = mime_content_type($_FILES['cat_image_file']['tmp_name']);

        if(!in_array($ftype, $allowed)) {
            $err = 'Invalid image type. Allowed: JPG, PNG, GIF, WEBP.';
        } elseif($_FILES['cat_image_file']['size'] > 5 * 1024 * 1024) {
            $err = 'Image too large. Max 5MB.';
        } else {
            // Create folder if not exists
            if(!is_dir($upload_abs)) mkdir($upload_abs, 0755, true);

            $ext      = strtolower(pathinfo($_FILES['cat_image_file']['name'], PATHINFO_EXTENSION));
            $filename = 'cat_' . time() . '_' . mt_rand(100, 999) . '.' . $ext;
            $destAbs  = $upload_abs . $filename; // full server path for move_uploaded_file

            if(move_uploaded_file($_FILES['cat_image_file']['tmp_name'], $destAbs)) {
                // Delete old local file if updating
                if($cid) {
                    $old = $conn->query("SELECT cat_image FROM categories WHERE cat_id=$cid")->fetch_assoc();
                    if($old && !empty($old['cat_image']) && strpos($old['cat_image'], 'http') !== 0) {
                        $oldFile = $project_root . '/' . ltrim($old['cat_image'], '/');
                        if(file_exists($oldFile)) unlink($oldFile);
                    }
                }
                // ✅ Save ONLY relative path to DB — never the full server path
                $img = $upload_rel . $filename;
            } else {
                $err = 'Upload failed. Check permissions on /uploads/categories/';
            }
        }
    }

    if(!$name && !$err) $err = 'Category name is required.';

    if(!$err) {
        $img_safe = clean($conn, $img);
        if($cid) {
            $conn->query("UPDATE categories SET cat_name='$name',cat_slug='$slug',cat_desc='$desc',cat_image='$img_safe' WHERE cat_id=$cid");
            $msg = 'Category updated!';
        } else {
            $conn->query("INSERT INTO categories (cat_name,cat_slug,cat_desc,cat_image) VALUES ('$name','$slug','$desc','$img_safe')");
            $msg = 'Category added!';
        }
    }
}

// ── EDIT ──
$edit = null;
if(isset($_GET['edit'])) {
    $eid = (int)$_GET['edit'];
    $er  = $conn->query("SELECT * FROM categories WHERE cat_id=$eid");
    if($er) $edit = $er->fetch_assoc();
}

// ── LIST ──
$cats = $conn->query("SELECT c.*, (SELECT COUNT(*) FROM products p WHERE p.cat_id=c.cat_id) as prod_count FROM categories c ORDER BY c.cat_id");
?>

<?php if($msg): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $msg; ?></div>
<?php endif; ?>
<?php if($err): ?>
<div class="alert alert-error"><i class="fas fa-times-circle"></i> <?php echo $err; ?></div>
<?php endif; ?>

<div class="grid-2" style="align-items:start;">

<!-- ── FORM ── -->
<div class="panel" style="position:sticky;top:80px;">
  <div class="panel-header">
    <h3><?php echo $edit ? 'Edit Category' : 'Add Category'; ?></h3>
    <?php if($edit): ?>
      <a href="categories.php" class="btn btn-sm btn-outline-purple">+ New</a>
    <?php endif; ?>
  </div>
  <div class="panel-body">
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="cat_id"   value="<?php echo $edit['cat_id'] ?? 0; ?>"/>
      <input type="hidden" name="save_cat" value="1"/>

      <div class="form-group">
        <label>Category Name *</label>
        <input class="form-control" type="text" name="cat_name" required
               value="<?php echo htmlspecialchars($edit['cat_name'] ?? ''); ?>"/>
      </div>

      <div class="form-group">
        <label>Description</label>
        <input class="form-control" type="text" name="cat_desc"
               value="<?php echo htmlspecialchars($edit['cat_desc'] ?? ''); ?>"/>
      </div>

      <!-- Image Source Toggle -->
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

        <!-- URL input -->
        <div id="img_url_wrap">
          <input class="form-control" type="text" name="cat_image" id="cat_image_url"
                 placeholder="https://example.com/image.jpg"
                 value="<?php echo htmlspecialchars($edit['cat_image'] ?? ''); ?>"/>
          <small style="color:var(--gray);font-size:11px;">Paste any image URL from the web.</small>
        </div>

        <!-- File upload -->
        <div id="img_local_wrap" style="display:none;">
          <input class="form-control" type="file" name="cat_image_file" id="cat_image_file"
                 accept="image/jpeg,image/png,image/gif,image/webp"
                 onchange="previewLocal(this)"/>
          <small style="color:#856404;font-size:11px;">Max 5MB — JPG, PNG, GIF, WEBP only.</small>
        </div>
      </div>

      <!-- Preview -->
      <?php
        $previewSrc = !empty($edit['cat_image']) ? adminImgSrc($edit['cat_image'], $project_url) : '';
      ?>
      <div id="img_preview_wrap" style="margin-bottom:16px;<?php echo empty($previewSrc) ? 'display:none;' : ''; ?>">
        <img id="img_preview" src="<?php echo $previewSrc; ?>"
             style="width:100%;height:140px;object-fit:cover;border:1px solid var(--border);border-radius:6px;"
             onerror="this.style.display='none'"/>
      </div>

      <button type="submit" class="btn btn-purple" style="width:100%;justify-content:center;">
        <i class="fas fa-save"></i> <?php echo $edit ? 'Update Category' : 'Add Category'; ?>
      </button>
    </form>
  </div>
</div>

<!-- ── LIST ── -->
<div class="panel">
  <div class="panel-header"><h3>All Categories</h3></div>
  <table class="dash-table">
    <thead>
      <tr><th>Image</th><th>Name</th><th>Slug</th><th>Products</th><th>Actions</th></tr>
    </thead>
    <tbody>
      <?php if($cats && $cats->num_rows > 0): while($c = $cats->fetch_assoc()):
        $thumbSrc = adminImgSrc($c['cat_image'] ?? '', $project_url);
      ?>
      <tr>
        <td>
          <?php if($thumbSrc): ?>
          <img src="<?php echo $thumbSrc; ?>"
               style="width:54px;height:42px;object-fit:cover;border:1px solid var(--border);border-radius:4px;"
               onerror="this.style.display='none'"/>
          <?php else: ?>
          <div style="width:54px;height:42px;background:#f0f0f0;border-radius:4px;display:flex;align-items:center;justify-content:center;">
            <i class="fas fa-image" style="color:#ccc;"></i>
          </div>
          <?php endif; ?>
        </td>
        <td>
          <strong><?php echo htmlspecialchars($c['cat_name']); ?></strong><br/>
          <span style="font-size:11px;color:var(--gray);"><?php echo htmlspecialchars($c['cat_desc'] ?? ''); ?></span>
        </td>
        <td style="font-size:12px;color:var(--gray);"><?php echo htmlspecialchars($c['cat_slug']); ?></td>
        <td style="font-weight:600;color:var(--purple);"><?php echo (int)$c['prod_count']; ?></td>
        <td style="display:flex;gap:6px;">
          <a href="categories.php?edit=<?php echo $c['cat_id']; ?>"
             class="btn btn-sm btn-icon btn-outline-purple" title="Edit">
            <i class="fas fa-edit"></i>
          </a>
          <a href="categories.php?delete=<?php echo $c['cat_id']; ?>"
             class="btn btn-sm btn-icon btn-red confirm-delete" title="Delete">
            <i class="fas fa-trash"></i>
          </a>
        </td>
      </tr>
      <?php endwhile; else: ?>
      <tr>
        <td colspan="5" style="text-align:center;padding:30px;color:var(--gray);">
          No categories yet. Add your first one!
        </td>
      </tr>
      <?php endif; ?>
    </tbody>
  </table>
</div>
</div>

<script>
window.addEventListener('DOMContentLoaded', function () {
    const urlVal = document.getElementById('cat_image_url')?.value || '';
    if(urlVal && !urlVal.startsWith('http')) {
        document.getElementById('img_local_radio').checked = true;
        toggleImgSource('local');
    } else {
        document.getElementById('img_url_radio').checked = true;
        toggleImgSource('url');
    }
    document.getElementById('cat_image_url')?.addEventListener('input', function () {
        updatePreview(this.value);
    });
});

function toggleImgSource(src) {
    document.getElementById('img_url_wrap').style.display   = src === 'url'   ? 'block' : 'none';
    document.getElementById('img_local_wrap').style.display = src === 'local' ? 'block' : 'none';
    if(src === 'url') {
        document.getElementById('cat_image_file').value = '';
        updatePreview(document.getElementById('cat_image_url').value);
    } else {
        updatePreview('');
    }
}

function previewLocal(input) {
    if(input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => updatePreview(e.target.result);
        reader.readAsDataURL(input.files[0]);
    }
}

function updatePreview(src) {
    const wrap = document.getElementById('img_preview_wrap');
    const img  = document.getElementById('img_preview');
    if(src) { img.src = src; wrap.style.display = 'block'; }
    else    { wrap.style.display = 'none'; }
}
</script>

<?php require_once 'includes/footer.php'; ?>