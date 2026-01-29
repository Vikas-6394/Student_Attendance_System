<?php
session_start();
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/helpers.php';

if (empty($_SESSION['admin_id'])) {
  header('Location: actions.php');
  exit;
}

$pdo = db();

/* ===== STATS ===== */
$tot_users    = $pdo->query("SELECT COUNT(*) c FROM users")->fetch()['c'];
$tot_expenses = $pdo->query("SELECT COUNT(*) c FROM expenses")->fetch()['c'];
$sum_expenses = $pdo->query("SELECT COALESCE(SUM(amount),0) s FROM expenses")->fetch()['s'];

/* ===== REAL RECENT ACTIVITY ===== */
$recent_user = $pdo->query(
  "SELECT name, created_at FROM users ORDER BY created_at DESC LIMIT 1"
)->fetch();

$recent_expense = $pdo->query(
  "SELECT amount, date FROM expenses ORDER BY date DESC LIMIT 1"
)->fetch();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Admin Dashboard</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<meta name="viewport" content="width=device-width, initial-scale=1">

<style>
body{
  background:linear-gradient(135deg,#1f1c2c,#928dab);
  min-height:100vh;
  display:flex;
  flex-direction:column;
  font-family:'Segoe UI',sans-serif;
}
.glass-card{
  background:rgba(255,255,255,0.08);
  backdrop-filter:blur(12px);
  border-radius:20px;
  border:1px solid rgba(255,255,255,0.2);
  padding:2rem;
  color:#fff;
}
.glass-card:hover{
  transform:scale(1.03);
}
.stat-users{color:#00eaff}
.stat-expenses{color:#ffeb3b}
.stat-sum{color:#ff5722}
footer{
  background:linear-gradient(90deg,#444,#222);
  color:#eee;
  text-align:center;
  padding:12px;
  margin-top:auto;
}
.btn-logout{
  background:linear-gradient(90deg,#ff512f,#dd2476);
  color:#fff;
  border-radius:30px;
  padding:8px 20px;
  font-weight:600;
}
</style>
</head>

<body>

<!-- NAVBAR -->
<nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="dashboard.php">
      <i class="bi bi-people-fill"></i> Admin Panel
    </a>
    <button onclick="confirmLogout()" class="btn btn-logout">
      <i class="bi bi-box-arrow-right"></i> Logout
    </button>
  </div>
</nav>

<div class="container py-5" style="margin-top:100px;">
<h1 class="text-center text-light mb-4">Admin Dashboard</h1>

<!-- STATS -->
<div class="row g-4">
  <div class="col-md-4">
    <div class="glass-card text-center">
      <h5>Total Users</h5>
      <p class="stat-users fs-2"><?= $tot_users ?></p>
    </div>
  </div>

  <div class="col-md-4">
    <div class="glass-card text-center">
      <h5>Total Expenses</h5>
      <p class="stat-expenses fs-2"><?= $tot_expenses ?></p>
    </div>
  </div>

  <div class="col-md-4">
    <div class="glass-card text-center">
      <h5>Sum of Expenses</h5>
      <p class="stat-sum fs-2"><?= number_format($sum_expenses,2) ?></p>
    </div>
  </div>
</div>

<!-- ACTIONS -->
<div class="text-center mt-5">
  <a href="users.php" class="btn btn-success btn-lg me-2">
    <i class="bi bi-person-gear"></i> Manage Users
  </a>
  <a href="reports.php" class="btn btn-outline-light btn-lg">
    <i class="bi bi-bar-chart"></i> Reports
  </a>
</div>

<!-- REAL RECENT ACTIVITY -->
<div class="mt-5">
  <div class="glass-card">
    <h5><i class="bi bi-clock-history"></i> Recent Activity</h5>
    <ul class="list-group mt-3">

      <?php if ($recent_user): ?>
      <li class="list-group-item">
        🧑 New user <b><?=sanitize($recent_user['name'])?></b>
        registered on <?=sanitize($recent_user['created_at'])?>
      </li>
      <?php endif; ?>

      <?php if ($recent_expense): ?>
      <li class="list-group-item">
        💰 Expense of ₹<?=number_format($recent_expense['amount'],2)?>
        added on <?=sanitize($recent_expense['date'])?>
      </li>
      <?php endif; ?>

      <?php if (!$recent_user && !$recent_expense): ?>
      <li class="list-group-item text-muted">
        No recent activity found.
      </li>
      <?php endif; ?>

    </ul>
  </div>
</div>

</div>

<footer>
© All rights reserved by Vikas 2026
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function confirmLogout(){
  Swal.fire({
    title:'Logout?',
    text:'Are you sure you want to logout?',
    icon:'warning',
    showCancelButton:true,
    confirmButtonColor:'#d33',
    confirmButtonText:'Yes, Logout',
    cancelButtonText:'Cancel'
  }).then((r)=>{
    if(r.isConfirmed){
      window.location='actions.php?action=logout';
    }
  });
}
</script>

</body>
</html>
