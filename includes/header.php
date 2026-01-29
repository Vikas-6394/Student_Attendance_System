<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';     // 🔴 current_user() yahin hota hai
require_once __DIR__ . '/../includes/helpers.php';

$user = null;
$theme = 'light';

if (!empty($_SESSION['user_id'])) {
    $user = current_user(db());
    $theme = $user['theme'] ?? 'light';
}

// Current page name
$currentPage = basename($_SERVER['SCRIPT_NAME'], '.php');
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Expense Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom CSS -->
  <link rel="stylesheet" href="../assets/css/style.css">
  <?php if ($theme === 'dark'): ?>
    <link rel="stylesheet" href="../assets/css/dark.css">
  <?php endif; ?>

  <!-- JS Libraries -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    .navbar-brand {
      color: #FFD700 !important;
      font-weight: 700;
      font-size: 1.4rem;
      text-transform: uppercase;
    }
    .navbar .nav-link.active::after {
      content: "";
      position: absolute;
      left: 0;
      bottom: -4px;
      width: 100%;
      height: 3px;
      background-color: #FFD700;
    }
  </style>
</head>

<body class="page-<?= $currentPage ?>">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Expense Management System</a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNav">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="mainNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link <?= $currentPage==='dashboard'?'active':'' ?>" href="dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage==='expenses'?'active':'' ?>" href="expenses.php">Expenses</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage==='income'?'active':'' ?>" href="income.php">Income</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage==='categories'?'active':'' ?>" href="categories.php">Categories</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage==='budgets'?'active':'' ?>" href="budgets.php">Budgets</a></li>
        <li class="nav-item"><a class="nav-link <?= $currentPage==='reports'?'active':'' ?>" href="reports.php">Reports</a></li>
      </ul>

      <div class="d-flex align-items-center">
        <?php if ($user): ?>
          <span class="text-white me-3">
            <?= sanitize($user['name']) ?> (<?= sanitize($user['currency']) ?>)
          </span>
          <a href="settings.php" class="btn btn-outline-light me-2">Settings</a>
          <a href="logout.php" class="btn btn-light">Logout</a>
        <?php else: ?>
          <a href="login.php" class="btn btn-light">Login</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>

<div class="container my-4">
<?php
require_once __DIR__ . '/flash.php';
flash_show();
?>
