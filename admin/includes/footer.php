<?php // admin/includes/footer.php ?>
</div><!-- dash-content -->
</div><!-- dash-main -->
</div><!-- dash-layout -->
<script>
// Confirm delete
document.querySelectorAll('.confirm-delete').forEach(function(el){
  el.addEventListener('click', function(e){
    if(!confirm('Are you sure you want to delete this?')) e.preventDefault();
  });
});
</script>
</body>
</html>
