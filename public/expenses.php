<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

csrf_check();
$pdo = db();
$user = current_user($pdo);
$uid = (int)$user['id'];

$popupData = null;

/* ===== HANDLE FORM ===== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  /* ADD EXPENSE */
  if ($action === 'add') {
    $date = $_POST['date'];
    $amount = (float)$_POST['amount'];
    $category_id = (int)$_POST['category_id'];
    $pm = $_POST['payment_mode'];
    $notes = trim($_POST['notes'] ?? '');

    if ($amount <= 0) {
      $popupData = ['error','Invalid Amount','Amount must be greater than 0.'];
    } else {
      $pdo->prepare(
        "INSERT INTO expenses (user_id, category_id, date, amount, payment_mode, notes)
         VALUES (?,?,?,?,?,?)"
      )->execute([$uid,$category_id,$date,$amount,$pm,$notes]);

      $popupData = ['success','Expense Added','Expense added successfully.'];
    }
  }

  /* EDIT EXPENSE */
  if ($action === 'edit') {
    $id = (int)$_POST['id'];
    $date = $_POST['date'];
    $amount = (float)$_POST['amount'];
    $category_id = (int)$_POST['category_id'];
    $pm = $_POST['payment_mode'];
    $notes = trim($_POST['notes'] ?? '');

    $pdo->prepare(
      "UPDATE expenses
       SET date=?, amount=?, category_id=?, payment_mode=?, notes=?
       WHERE id=? AND user_id=?"
    )->execute([$date,$amount,$category_id,$pm,$notes,$id,$uid]);

    $popupData = ['success','Expense Updated','Expense updated successfully.'];
  }

  /* DELETE EXPENSE */
  if ($action === 'delete') {
    $id = (int)$_POST['id'];

    $pdo->prepare(
      "DELETE FROM expenses WHERE id=? AND user_id=?"
    )->execute([$id,$uid]);

    $popupData = ['success','Expense Deleted','Expense deleted successfully.'];
  }
}

/* FETCH DATA */
$categories = $pdo->prepare(
  "SELECT id,name FROM categories WHERE user_id=? ORDER BY name"
);
$categories->execute([$uid]);

$expenses = $pdo->prepare(
  "SELECT e.*, c.name AS category
   FROM expenses e
   LEFT JOIN categories c ON c.id=e.category_id
   WHERE e.user_id=?
   ORDER BY e.date DESC, e.id DESC
   LIMIT 50"
);
$expenses->execute([$uid]);
?>

<div class="container my-4">

  <div class="row">

    <!-- ADD EXPENSE -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient">➕ Add Expense</h3>

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
              <label class="form-label fw-bold text-warning">Category</label>
              <select name="category_id" class="form-select rounded-pill" required>
                <?php foreach ($categories as $c): ?>
                  <option value="<?=$c['id']?>"><?=sanitize($c['name'])?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-primary">Payment Mode</label>
              <select name="payment_mode" class="form-select rounded-pill" required>
                <option>Cash</option>
                <option>UPI</option>
                <option>Card</option>
                <option>Bank</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-danger">Notes</label>
              <input class="form-control rounded-pill" name="notes">
            </div>

            <button class="btn btn-gradient w-100 btn-lg">Add</button>
          </form>
        </div>
      </div>
    </div>

    <!-- RECENT EXPENSES -->
    <div class="col-md-6 mb-4">
      <div class="card shadow-3d border-0 rounded-4" style="height:75vh;">
        <div class="card-body d-flex flex-column">
          <h3 class="text-gradient">📜 Recent Expenses</h3>

          <div class="table-responsive" style="overflow-y:auto; max-height:85vh;">
            <table class="table table-striped table-hover align-middle shadow-sm">
              <thead class="table-dark">
                <tr>
                  <th>Date</th>
                  <th>Category</th>
                  <th>Payment</th>
                  <th>Amount</th>
                  <th>Notes</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($expenses as $e): ?>
                <tr>
                  <td><?=sanitize($e['date'])?></td>
                  <td><?=sanitize($e['category'] ?? '—')?></td>
                  <td><?=sanitize($e['payment_mode'])?></td>
                  <td class="text-danger fw-bold"><?=number_format($e['amount'],2)?></td>
                  <td><?=sanitize($e['notes'])?></td>
                  <td>
                    <button class="btn btn-sm btn-danger"
                            onclick="confirmDelete(<?=$e['id']?>)">
                      🗑
                    </button>

                    <form id="deleteForm<?=$e['id']?>" method="post" class="d-none">
                      <input type="hidden" name="csrf" value="<?=csrf_token()?>">
                      <input type="hidden" name="action" value="delete">
                      <input type="hidden" name="id" value="<?=$e['id']?>">
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

<!-- DELETE CONFIRM -->
<script>
function confirmDelete(id) {
  Swal.fire({
    title: 'Are you sure?',
    text: 'This expense will be permanently deleted!',
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
