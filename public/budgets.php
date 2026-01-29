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

  $year   = (int)$_POST['year'];
  $month  = (int)$_POST['month'];
  $amount = (float)$_POST['amount'];

  if ($year < 2000 || $month < 1 || $month > 12 || $amount <= 0) {

    $popupData = [
      'error',
      'Invalid Input',
      'Please enter a valid year, month and amount.'
    ];

  } else {

    $pdo->prepare(
      "INSERT INTO budgets (user_id, month, month_num, amount)
       VALUES (?,?,?,?)
       ON DUPLICATE KEY UPDATE amount=VALUES(amount)"
    )->execute([$uid,$year,$month,$amount]);

    $popupData = [
      'success',
      'Budget Saved',
      'Monthly budget saved successfully.'
    ];
  }
}

/* FETCH BUDGETS */
$budgets = $pdo->prepare(
  "SELECT * FROM budgets
   WHERE user_id=?
   ORDER BY month DESC, month_num DESC
   LIMIT 12"
);
$budgets->execute([$uid]);
?>

<div class="container my-4">
  <div class="row g-4">

    <!-- SET BUDGET -->
    <div class="col-md-6">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient mb-3">💰 Set Monthly Budget</h3>

          <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf_token()?>">

            <div class="mb-3">
              <label class="form-label fw-bold text-info">Year</label>
              <input class="form-control rounded-pill"
                     type="number"
                     name="year"
                     value="<?=date('Y')?>"
                     required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-success">Month</label>
              <input class="form-control rounded-pill"
                     type="number"
                     name="month"
                     min="1" max="12"
                     value="<?=date('n')?>"
                     required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-warning">Amount</label>
              <input class="form-control rounded-pill"
                     type="number"
                     step="0.01"
                     name="amount"
                     required>
            </div>

            <button class="btn btn-gradient w-100 btn-lg">
              Save Budget
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- RECENT BUDGETS -->
    <div class="col-md-6">
      <div class="card shadow-3d border-0 rounded-4" style="height:80vh;">
        <div class="card-body d-flex flex-column">
          <h3 class="text-gradient mb-3">📜 Recent Budgets</h3>

          <div class="table-responsive" style="overflow-y:auto; max-height:70vh;">
            <table class="table table-striped table-hover align-middle shadow-sm">
              <thead class="table-dark">
                <tr>
                  <th>Year</th>
                  <th>Month</th>
                  <th>Amount</th>
                </tr>
              </thead>
              <tbody>

              <?php foreach ($budgets as $b): ?>
                <tr>
                  <td class="fw-bold"><?=$b['month']?></td>
                  <td><?=$b['month_num']?></td>
                  <td class="text-primary fw-bold">
                    <?=number_format($b['amount'],2)?>
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

<!-- SWEETALERT POPUP -->
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
