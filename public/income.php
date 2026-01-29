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

  /* ADD INCOME */
  if ($action === 'add') {
    $date   = $_POST['date'];
    $amount = (float)$_POST['amount'];
    $source = trim($_POST['source']);
    $notes  = trim($_POST['notes'] ?? '');

    if ($amount <= 0 || $source === '') {
      $popupData = ['error','Invalid Input','Please enter valid amount and source.'];
    } else {
      $pdo->prepare(
        "INSERT INTO income (user_id, source, date, amount, notes)
         VALUES (?,?,?,?,?)"
      )->execute([$uid,$source,$date,$amount,$notes]);

      $popupData = ['success','Income Added','Income added successfully.'];
    }
  }

  /* EDIT INCOME */
  if ($action === 'edit') {
    $id     = (int)$_POST['id'];
    $date   = $_POST['date'];
    $amount = (float)$_POST['amount'];
    $source = trim($_POST['source']);
    $notes  = trim($_POST['notes'] ?? '');

    if ($amount <= 0 || $source === '') {
      $popupData = ['error','Invalid Input','Please enter valid amount and source.'];
    } else {
      $pdo->prepare(
        "UPDATE income
         SET source=?, date=?, amount=?, notes=?
         WHERE id=? AND user_id=?"
      )->execute([$source,$date,$amount,$notes,$id,$uid]);

      $popupData = ['success','Income Updated','Income updated successfully.'];
    }
  }

  /* DELETE INCOME */
  if ($action === 'delete') {
    $id = (int)$_POST['id'];

    $pdo->prepare(
      "DELETE FROM income WHERE id=? AND user_id=?"
    )->execute([$id,$uid]);

    $popupData = ['success','Income Deleted','Income deleted successfully.'];
  }
}

/* FETCH INCOME */
$rows = $pdo->prepare(
  "SELECT * FROM income
   WHERE user_id=?
   ORDER BY date DESC, id DESC
   LIMIT 50"
);
$rows->execute([$uid]);
?>

<div class="container my-4">
  <div class="row g-4">

    <!-- ADD INCOME -->
    <div class="col-md-4">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient mb-3">➕ Add Income</h3>

          <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf_token()?>">
            <input type="hidden" name="action" value="add">

            <div class="mb-3">
              <label class="form-label fw-bold text-info">Date</label>
              <input class="form-control rounded-pill"
                     type="date" name="date"
                     value="<?=date('Y-m-d')?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-success">Amount</label>
              <input class="form-control rounded-pill"
                     type="number" step="0.01"
                     name="amount" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-warning">Source</label>
              <input class="form-control rounded-pill"
                     name="source"
                     placeholder="Salary, Freelance, etc."
                     required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-primary">Notes</label>
              <input class="form-control rounded-pill" name="notes">
            </div>

            <button class="btn btn-gradient w-100 btn-lg">Add</button>
          </form>
        </div>
      </div>
    </div>

    <!-- RECENT INCOME -->
    <div class="col-md-8">
      <div class="card shadow-3d border-0 rounded-4" style="height:80vh;">
        <div class="card-body d-flex flex-column">
          <h3 class="text-gradient mb-3">📜 Recent Income</h3>

          <div class="table-responsive" style="overflow-y:auto; max-height:70vh;">
            <table class="table table-striped table-hover align-middle shadow-sm">
              <thead class="table-dark">
                <tr>
                  <th>Date</th>
                  <th>Source</th>
                  <th>Amount</th>
                  <th>Notes</th>
                  <th style="width:220px;">Actions</th>
                </tr>
              </thead>
              <tbody>

              <?php foreach ($rows as $r): ?>
                <tr>
                  <td><?=sanitize($r['date'])?></td>
                  <td><?=sanitize($r['source'])?></td>
                  <td class="text-success fw-bold">
                    <?=number_format($r['amount'],2)?>
                  </td>
                  <td><?=sanitize($r['notes'])?></td>
                  <td>

                    <!-- EDIT -->
                    <details>
                      <summary class="text-primary fw-bold cursor-pointer">✏️ Edit</summary>

                      <form method="post" class="mt-2">
                        <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id" value="<?=$r['id']?>">

                        <input class="form-control rounded-pill mb-2"
                               type="date" name="date"
                               value="<?=$r['date']?>" required>

                        <input class="form-control rounded-pill mb-2"
                               type="number" step="0.01"
                               name="amount"
                               value="<?=$r['amount']?>" required>

                        <input class="form-control rounded-pill mb-2"
                               name="source"
                               value="<?=sanitize($r['source'])?>" required>

                        <input class="form-control rounded-pill mb-2"
                               name="notes"
                               value="<?=sanitize($r['notes'])?>">

                        <button class="btn btn-gradient w-100 btn-sm">
                          Save Changes
                        </button>
                      </form>
                    </details>

                    <!-- DELETE -->
                    <button class="btn btn-sm btn-danger w-100 mt-2"
                            onclick="confirmDelete(<?=$r['id']?>)">
                      🗑 Delete
                    </button>

                    <form id="deleteForm<?=$r['id']?>" method="post" class="d-none">
                      <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?=$r['id']?>">
                    </form>

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

<!-- ADD / EDIT / DELETE POPUP -->
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

<!-- DELETE CONFIRM -->
<script>
function confirmDelete(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This income record will be permanently deleted!',
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
