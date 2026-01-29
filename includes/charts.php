<?php
require_once __DIR__ . '/../config/database.php';
header('Content-Type: application/json');

$pdo = db();
$uid = (int)($_GET['uid'] ?? 0);
$type = $_GET['type'] ?? '';

if ($type === 'category_pie') {
  $stmt = $pdo->prepare("SELECT c.name, COALESCE(SUM(e.amount),0) AS total
                         FROM categories c
                         LEFT JOIN expenses e ON e.category_id=c.id AND e.user_id=c.user_id
                         WHERE c.user_id=?
                         GROUP BY c.id ORDER BY total DESC");
  $stmt->execute([$uid]);
  echo json_encode($stmt->fetchAll());
  exit;
}
if ($type === 'monthly_bar') {
  $stmt = $pdo->prepare("SELECT DATE_FORMAT(date,'%Y-%m') AS ym, COALESCE(SUM(amount),0) total
                         FROM expenses WHERE user_id=? GROUP BY ym ORDER BY ym ASC LIMIT 12");
  $stmt->execute([$uid]);
  echo json_encode($stmt->fetchAll());
  exit;
}
echo json_encode([]);