<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

csrf_check();
$pdo  = db();
$user = current_user($pdo);
$uid  = (int)$user['id'];

$popupData = null;

/* ===== HANDLE FORM ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  /* ADD CATEGORY */
  if ($action === 'add') {
    $name = trim($_POST['name']);

    if ($name === '') {
      $popupData = ['error','Invalid Input','Category name is required.'];
    } else {
      $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM categories WHERE user_id=? AND name=?"
      );
      $stmt->execute([$uid,$name]);

      if ($stmt->fetchColumn() > 0) {
        $popupData = ['warning','Already Exists',"Category \"$name\" already exists."];
      } else {
        $pdo->prepare(
          "INSERT INTO categories (user_id,name,icon) VALUES (?,?,?)"
        )->execute([$uid,$name,'tag']);

        $popupData = ['success','Category Added','Category added successfully.'];
      }
    }
  }

  /* EDIT CATEGORY */
  if ($action === 'edit') {
    $id   = (int)$_POST['id'];
    $name = trim($_POST['name']);

    $stmt = $pdo->prepare(
      "SELECT COUNT(*) FROM categories WHERE user_id=? AND name=? AND id<>?"
    );
    $stmt->execute([$uid,$name,$id]);

    if ($stmt->fetchColumn() > 0) {
      $popupData = ['warning','Already Exists',"Category \"$name\" already exists."];
    } else {
      $pdo->prepare(
        "UPDATE categories SET name=? WHERE id=? AND user_id=?"
      )->execute([$name,$id,$uid]);

      $popupData = ['success','Category Updated','Category updated successfully.'];
    }
  }

  /* DELETE CATEGORY */
  if ($action === 'delete') {
    $id = (int)$_POST['id'];

    $pdo->prepare(
      "DELETE FROM categories WHERE id=? AND user_id=?"
    )->execute([$id,$uid]);

    $popupData = ['success','Category Deleted','Category deleted successfully.'];
  }
}

/* FETCH CATEGORIES */
$cats = $pdo->prepare(
  "SELECT * FROM categories WHERE user_id=? ORDER BY name"
);
$cats->execute([$uid]);
?>

<div class="container my-4">
  <div class="row g-4">

    <!-- ADD CATEGORY -->
    <div class="col-md-4">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient mb-3">➕ Add Category</h3>

          <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf_token()?>">
            <input type="hidden" name="action" value="add">

            <div class="mb-3">
              <label class="form-label fw-bold text-info">Category Name</label>
              <input class="form-control rounded-pill" name="name" required>
            </div>

            <button class="btn btn-gradient w-100 btn-lg">Add</button>
          </form>
        </div>
      </div>
    </div>

    <!-- CATEGORY LIST -->
    <div class="col-md-8">
      <div class="card shadow-3d border-0 rounded-4" style="height:80vh;">
        <div class="card-body d-flex flex-column">
          <h3 class="text-gradient mb-3">📂 Your Categories</h3>

          <div class="table-responsive" style="overflow-y:auto; max-height:70vh;">
            <table class="table table-striped table-hover align-middle shadow-sm">
              <thead class="table-dark">
                <tr>
                  <th>Name</th>
                  <th style="width:160px;">Actions</th>
                </tr>
              </thead>
              <tbody>

              <?php foreach ($cats as $c): ?>
                <tr>
                  <td class="fw-bold"><?=sanitize($c['name'])?></td>
                  <td>
                    <!-- EDIT -->
                    <button class="btn btn-sm btn-outline-primary me-1"
                            data-bs-toggle="collapse"
                            data-bs-target="#edit<?=$c['id']?>">
                      ✏️
                    </button>

                    <!-- DELETE (SweetAlert Confirm) -->
                    <button class="btn btn-sm btn-danger"
                            onclick="confirmDelete(<?=$c['id']?>)">
                      🗑
                    </button>

                    <!-- HIDDEN DELETE FORM -->
                    <form id="deleteForm<?=$c['id']?>" method="post" class="d-none">
                      <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?=$c['id']?>">
                    </form>

                    <!-- EDIT FORM -->
                    <div class="collapse mt-2" id="edit<?=$c['id']?>">
                      <div class="card card-body p-2 shadow-sm">
                        <form method="post">
                          <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                          <input type="hidden" name="action" value="edit">
                          <input type="hidden" name="id" value="<?=$c['id']?>">

                          <input class="form-control rounded-pill mb-2"
                                 name="name"
                                 value="<?=sanitize($c['name'])?>" required>

                          <button class="btn btn-gradient w-100 btn-sm">Save</button>
                        </form>
                      </div>
                    </div>

                  </td>
                </tr>
              <?php endforeach; ?>

              </tbody>
            </table>
          </div>

        </div>
      </div>
    </div>

  </div>
</div>

<footer class="text-center py-3 mt-auto"
style="background:linear-gradient(90deg,#555,#333);
       color:#eee;
       position:fixed;
       bottom:0; left:0; width:100%;">
  © All rights reserved by Vikas 2026
</footer>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<!-- SUCCESS / ERROR POPUP -->
<?php if ($popupData): ?>
<script>
document.addEventListener("DOMContentLoaded", function () {
  Swal.fire({
    icon: "<?=$popupData[0]?>",
    title: "<?=$popupData[1]?>",
    text: "<?=$popupData[2]?>",
    confirmButtonText: "OK",
    allowOutsideClick: false
  });
});
</script>
<?php endif; ?>

<!-- DELETE CONFIRM POPUP -->
<script>
function confirmDelete(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This category will be permanently deleted!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    cancelButtonColor: '#3085d6',
    confirmButtonText: 'Yes, delete it!'
  }).then((result) => {
    if (result.isConfirmed) {
      document.getElementById('deleteForm' + id).submit();
    }
  });
}
</script>
