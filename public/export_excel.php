<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$pdo = db();
$user = current_user($pdo);
$uid = (int)$user['id'];

$from = $_GET['from'] ?? date('Y-m-01');
$to   = $_GET['to']   ?? date('Y-m-d');
$cat  = (int)($_GET['category_id'] ?? 0);

$sql = "SELECT e.date, c.name AS category, e.payment_mode, e.amount, e.notes
        FROM expenses e LEFT JOIN categories c ON c.id=e.category_id
        WHERE e.user_id=? AND e.date BETWEEN ? AND ?";
$params = [$uid,$from,$to];
if ($cat) { $sql .= " AND e.category_id=?"; $params[] = $cat; }
$sql .= " ORDER BY e.date ASC";
$stmt = $pdo->prepare($sql); $stmt->execute($params);
$rows = $stmt->fetchAll();

$sheet = (new Spreadsheet())->getActiveSheet();
$sheet->fromArray(['Date','Category','Payment','Amount','Notes'], NULL, 'A1');
$i = 2;
foreach ($rows as $r) {
  $sheet->fromArray([$r['date'],$r['category'],$r['payment_mode'],$r['amount'],$r['notes']], NULL, "A{$i}");
  $i++;
}
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="expense_report.xlsx"');
$writer = new Xlsx($sheet->getParent());
$writer->save('php://output');