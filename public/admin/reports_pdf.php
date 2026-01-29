<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../vendor/autoload.php'; // Dompdf autoload

use Dompdf\Dompdf;

if (empty($_SESSION['admin_id'])) {
  header('Location: actions.php'); 
  exit;
}

$pdo = db();

// Month names
$months = [1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'May',6=>'Jun',7=>'Jul',8=>'Aug',9=>'Sep',10=>'Oct',11=>'Nov',12=>'Dec'];

// Fetch data
$users = $pdo->query("SELECT id, name, email FROM users")->fetchAll();
$budgets = $pdo->query("SELECT user_id, month, month_num, amount FROM budgets")->fetchAll();
$tot_users    = $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
$tot_expenses = $pdo->query("SELECT COUNT(*) c FROM expenses")->fetch()['c'];
$sum_expenses = $pdo->query("SELECT COALESCE(SUM(amount),0) s FROM expenses")->fetch()['s'];

// Build HTML for PDF
$html = "
<h1 style='text-align:center;'>Admin System Report</h1>
<hr>
<h3>Summary</h3>
<p><strong>Total Users:</strong> {$tot_users}</p>
<p><strong>Total Expenses:</strong> {$tot_expenses}</p>
<p><strong>Sum of Expenses:</strong> Rs. ".number_format($sum_expenses,2)."</p>

<h3>User Details</h3>
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<tr><th>ID</th><th>Name</th><th>Email</th></tr>";
foreach ($users as $u) {
    $html .= "<tr>
                <td>{$u['id']}</td>
                <td>{$u['name']}</td>
                <td>{$u['email']}</td>
              </tr>";
}
$html .= "</table><br><br>";

$html .= "<h3>Budgets</h3>
<table border='1' cellspacing='0' cellpadding='5' width='100%'>
<tr><th>User ID</th><th>Year</th><th>Month</th><th>Amount</th></tr>";
foreach ($budgets as $b) {
    $monthName = $months[$b['month_num']] ?? $b['month_num'];
    $html .= "<tr>
                <td>{$b['user_id']}</td>
                <td>{$b['month']}</td>
                <td>{$monthName}</td>
                <td>Rs. {$b['amount']}</td>
              </tr>";
}
$html .= "</table>";

// Generate PDF
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream("system_report.pdf", ["Attachment" => true]); // force download
?>