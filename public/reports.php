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

// Filters
$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$cat  = (int)($_GET['category_id'] ?? 0);

// Build query
$sql = "SELECT e.date, c.name AS category, e.payment_mode, e.amount, e.notes
        FROM expenses e
        LEFT JOIN categories c ON c.id = e.category_id
        WHERE e.user_id = ? AND e.date BETWEEN ? AND ?";
$params = [$uid, $from, $to];

if ($cat) {
  $sql .= " AND e.category_id = ?";
  $params[] = $cat;
}

$sql .= " ORDER BY e.date ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$rows = $stmt->fetchAll();
$total = 0;
foreach ($rows as $r) {
  $total += $r['amount'];
}

// Categories for filter
$cats = $pdo->prepare(
  "SELECT id, name FROM categories WHERE user_id=? ORDER BY name"
);
$cats->execute([$uid]);
?>

<div class="container my-4">

  <div class="card shadow-3d border-0 rounded-4">
    <div class="card-body">

      <h3 class="text-gradient mb-3">📊 Expense Report</h3>

      <!-- FILTER FORM -->
      <form method="get" class="row g-3 mb-3">
        <div class="col-12 col-md-3">
          <label class="form-label fw-bold text-info">From</label>
          <input type="date"
                 name="from"
                 value="<?=sanitize($from)?>"
                 class="form-control rounded-pill">
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label fw-bold text-success">To</label>
          <input type="date"
                 name="to"
                 value="<?=sanitize($to)?>"
                 class="form-control rounded-pill">
        </div>

        <div class="col-12 col-md-3">
          <label class="form-label fw-bold text-warning">Category</label>
          <select name="category_id" class="form-select rounded-pill">
            <option value="0">All</option>
            <?php foreach ($cats as $c): ?>
              <option value="<?=$c['id']?>"
                <?= $cat == $c['id'] ? 'selected' : '' ?>>
                <?=sanitize($c['name'])?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-12 col-md-3 d-grid">
          <button class="btn btn-gradient">🔍 Filter</button>
        </div>
      </form>

      <!-- EXPORT / PRINT -->
      <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-outline-secondary rounded-pill flex-fill"
           href="export_pdf.php?from=<?=urlencode($from)?>&to=<?=urlencode($to)?>&category_id=<?=$cat?>">
          📄 Export PDF
        </a>

        <a class="btn btn-outline-secondary rounded-pill flex-fill"
           href="export_excel.php?from=<?=urlencode($from)?>&to=<?=urlencode($to)?>&category_id=<?=$cat?>">
          📊 Export Excel
        </a>

        <button class="btn btn-outline-primary rounded-pill flex-fill"
                onclick="window.print()">
          🖨 Print
        </button>
      </div>

      <!-- RESULTS -->
      <div class="table-responsive" style="max-height:70vh; overflow-y:auto;">
        <table class="table table-striped table-hover align-middle shadow-sm">
          <thead class="table-dark">
            <tr>
              <th>Date</th>
              <th>Category</th>
              <th>Payment</th>
              <th>Amount</th>
              <th>Notes</th>
            </tr>
          </thead>
          <tbody>

          <?php if ($rows): ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?=sanitize($r['date'])?></td>
                <td><?=sanitize($r['category'] ?? '—')?></td>
                <td><?=sanitize($r['payment_mode'])?></td>
                <td class="text-danger fw-bold">
                  <?=number_format($r['amount'],2)?>
                </td>
                <td><?=sanitize($r['notes'])?></td>
              </tr>
            <?php endforeach; ?>

            <tr class="table-info fw-bold">
              <td colspan="3" class="text-end">Total</td>
              <td class="text-primary"><?=number_format($total,2)?></td>
              <td></td>
            </tr>

          <?php else: ?>
            <tr>
              <td colspan="5" class="text-center text-muted">
                No expenses found for this filter.
              </td>
            </tr>
          <?php endif; ?>

          </tbody>
        </table>
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
