<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';



use Dompdf\Dompdf;

$pdo = db();
$user = current_user($pdo);
$uid  = (int)$user['id'];

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$cat  = (int)($_GET['category_id'] ?? 0);

$sql = "SELECT e.date, c.name AS category, e.payment_mode, e.amount, e.notes
        FROM expenses e LEFT JOIN categories c ON c.id=e.category_id
        WHERE e.user_id=? AND e.date BETWEEN ? AND ?";
$params = [$uid,$from,$to];
if ($cat) { 
  $sql .= " AND e.category_id=?"; 
  $params[] = $cat; 
}
$sql .= " ORDER BY e.date ASC";
$stmt = $pdo->prepare($sql); 
$stmt->execute($params);
$rows = $stmt->fetchAll();

$total = array_sum(array_column($rows,'amount'));

// Build HTML with inline CSS for Dompdf
$html = "
<style>
  body { font-family: DejaVu Sans, sans-serif; font-size: 12px; }
  h2 { text-align: center; margin-bottom: 10px; }
  table { width: 100%; border-collapse: collapse; margin-top: 10px; }
  th, td { border: 1px solid #333; padding: 6px; text-align: left; }
  th { background: #f2f2f2; }
  .total-row th { text-align: right; }
</style>
<h2>Expense Report</h2>
<p>Period: {$from} to {$to}</p>
<table>
  <thead>
    <tr>
      <th>Date</th>
      <th>Category</th>
      <th>Payment</th>
      <th>Amount</th>
      <th>Notes</th>
    </tr>
  </thead>
  <tbody>";
foreach ($rows as $r) {
  $html .= "<tr>
    <td>".sanitize($r['date'])."</td>
    <td>".sanitize($r['category'] ?? '—')."</td>
    <td>".sanitize($r['payment_mode'])."</td>
    <td>".number_format($r['amount'],2)."</td>
    <td>".sanitize($r['notes'])."</td>
  </tr>";
}
$html .= "<tr class='total-row'>
    <th colspan='3'>Total</th>
    <th>".number_format($total,2)."</th>
    <th></th>
  </tr>
  </tbody>
</table>";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("expense_report.pdf", ["Attachment" => true]);
exit;

