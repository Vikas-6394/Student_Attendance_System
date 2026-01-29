<?php
// public/admin/header.php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once dirname(__DIR__, 2) . '/config/database.php';
require_once dirname(__DIR__, 2) . '/includes/helpers.php';
require_once dirname(__DIR__, 2) . '/includes/auth.php';
require_once dirname(__DIR__, 2) . '/includes/flash.php';

$pdo  = db();
$user = current_user($pdo);
if (!$user || ($user['role'] ?? 'user') !== 'admin') {
  header('Location: ../login.php'); exit;
}

$theme = $user['theme'] ?? 'dark';
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Panel | Expense Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="../../assets/css/style.css">
  <?php if ($theme === 'dark'): ?>
    <link rel="stylesheet" href="../../assets/css/dark.css">
  <?php endif; ?>
</head>
<body class="bg-dark text-light">

<nav class="navbar navbar-expand-lg navbar-dark" style="background-color:#111827;">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">Admin Panel</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="index.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link" href="users.php">Manage Users</a></li>
        <li class="nav-item"><a class="nav-link" href="../login.php">Manage Expense</a></li>
        <li class="nav-item"><a class="nav-link" href="reports.php">Reports</a></li>
      </ul>
      <div class="d-flex align-items-center">
        <span class="text-muted me-3"><?=sanitize($user['name'])?></span>
        <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
      </div>
    </div>
  </div>
</nav>

<div class="container my-4">
  <?php flash_show(); ?>