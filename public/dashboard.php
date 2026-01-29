<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

$pdo = db();
$user = current_user($pdo);
$uid = (int)$user['id'];

$tot_income  = $pdo->query("SELECT COALESCE(SUM(amount),0) s FROM income WHERE user_id=$uid")->fetch()['s'];
$tot_expense = $pdo->query("SELECT COALESCE(SUM(amount),0) s FROM expenses WHERE user_id=$uid")->fetch()['s'];
$balance = $tot_income - $tot_expense;

$month = (int)date('n');
$year  = (int)date('Y');

$exp_stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) s FROM expenses WHERE user_id=? AND YEAR(date)=? AND MONTH(date)=?");
$exp_stmt->execute([$uid,$year,$month]);
$month_expense = $exp_stmt->fetch()['s'];

$budget_stmt = $pdo->prepare("SELECT amount FROM budgets WHERE user_id=? AND month=? AND month_num=?");
$budget_stmt->execute([$uid,$year,$month]);
$budget = $budget_stmt->fetch()['amount'] ?? 0;

$usage = $budget ? min(100, round(($month_expense/$budget)*100)) : 0;
?>

<div class="container my-4">

  <!-- SUMMARY -->
  <div class="row g-4 mb-4">
    <div class="col-md-4">
      <div class="card shadow-3d border-0 rounded-4 text-center">
        <div class="card-body">
          <h5 class="text-gradient">Total Income</h5>
          <p class="display-6 text-success fw-bold">
            <?=currency_symbol($user['currency']).' '.number_format($tot_income,2)?>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-3d border-0 rounded-4 text-center">
        <div class="card-body">
          <h5 class="text-gradient">Total Expense</h5>
          <p class="display-6 text-danger fw-bold">
            <?=currency_symbol($user['currency']).' '.number_format($tot_expense,2)?>
          </p>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-3d border-0 rounded-4 text-center">
        <div class="card-body">
          <h5 class="text-gradient">Balance</h5>
          <p class="display-6 text-primary fw-bold">
            <?=currency_symbol($user['currency']).' '.number_format($balance,2)?>
          </p>
        </div>
      </div>
    </div>
  </div>

  <!-- MONTHLY SUMMARY -->
  <div class="row mb-4">
    <div class="col">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient">📊 Monthly Summary</h3>

          <p class="fw-bold">
            Month: <?=$year?> / <?=$month?> |
            Expense: <?=number_format($month_expense,2)?> |
            Budget: <?=number_format($budget,2)?>
          </p>

          <div class="progress mb-3" style="height:22px;">
            <div class="progress-bar bg-warning fw-bold" style="width:<?=$usage?>%">
              <?=$usage?>%
            </div>
          </div>

          <?php if ($budget && $month_expense > $budget): ?>
            <div class="alert alert-danger shadow-sm">⚠ Budget exceeded</div>
          <?php elseif ($budget): ?>
            <div class="alert alert-success shadow-sm">✅ Budget under control</div>
          <?php else: ?>
            <div class="alert alert-warning shadow-sm">ℹ No budget set</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <!-- CHARTS -->
  <div class="row g-4 mb-4">
    <div class="col-md-6">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient">Expense by Category</h3>
          <canvas id="pieCategory"></canvas>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient">Monthly Expenses</h3>
          <canvas id="barMonthly"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- RECENT TRANSACTIONS -->
  <div class="row">
    <div class="col">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient">📜 Recent Transactions</h3>

          <div class="table-responsive" style="max-height:55vh; overflow-y:auto;">
            <table class="table table-striped table-hover align-middle shadow-sm">
              <thead class="table-dark">
                <tr>
                  <th>Date</th>
                  <th>Type</th>
                  <th>Amount</th>
                  <th>Notes</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $tx = $pdo->prepare(
                  "(SELECT date,'Expense' t,amount,notes FROM expenses WHERE user_id=?)
                   UNION ALL
                   (SELECT date,'Income' t,amount,notes FROM income WHERE user_id=?)
                   ORDER BY date DESC LIMIT 10"
                );
                $tx->execute([$uid,$uid]);

                foreach ($tx as $r):
                ?>
                <tr>
                  <td><?=sanitize($r['date'])?></td>
                  <td><?=sanitize($r['t'])?></td>
                  <td class="fw-bold"><?=currency_symbol($user['currency']).' '.number_format($r['amount'],2)?></td>
                  <td><?=sanitize($r['notes'])?></td>
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
style="background:linear-gradient(90deg,#555,#333);color:#eee;position:fixed;bottom:0;left:0;width:100%;">
  © All rights reserved by Vikas 2026
</footer>

<script src="../assets/js/charts.js"></script>
<script>
initCategoryPie(<?=$uid?>);
initMonthlyBar(<?=$uid?>);
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
