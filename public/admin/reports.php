<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

if (empty($_SESSION['admin_id'])) {
  header('Location: actions.php'); 
  exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Reports</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
    body {
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      font-family: 'Segoe UI', sans-serif;
    }
    .btn-report {
      background: linear-gradient(90deg, #43cea2, #185a9d);
      color: #fff !important;
      border: none;
      border-radius: 30px;
      padding: 12px 25px;
      font-weight: 600;
      box-shadow: 0 6px rgba(0,0,0,0.3);
      transition: all 0.3s ease;
    }
    .btn-report:hover {
      background: linear-gradient(90deg, #185a9d, #43cea2);
      transform: translateY(-2px);
      box-shadow: 0 8px rgba(0,0,0,0.4);
    }
    .btn-logout {
      background: linear-gradient(90deg, #ff512f, #dd2476);
      color: #fff !important;
      border: none;
      border-radius: 30px;
      padding: 8px 20px;
      font-weight: 600;
      box-shadow: 0 6px rgba(0,0,0,0.3);
      transition: all 0.3s ease;
    }
    .btn-logout:hover {
      background: linear-gradient(90deg, #dd2476, #ff512f);
      transform: translateY(-2px);
      box-shadow: 0 8px rgba(0,0,0,0.4);
    }
    footer {
      background: linear-gradient(90deg,#444,#222);
      color: #eee;
      text-align: center;
      padding: 12px;
      margin-top: auto;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="#">
       <i class="bi bi-people-fill"></i> Admin Panel
    </a>
    <div class="ms-auto">
      <a href="actions.php?action=logout" class="btn btn-logout">
        <i class="bi bi-box-arrow-right"></i> Logout
      </a>
    </div>
  </div>
</nav>

<!-- Main Content -->
<div class="container text-center" style="margin-top:120px;">
  <h1 class="text-light mb-4">📊 Admin System Reports</h1>
  <p class="text-light mb-5">Click below to generate and download the latest system report as PDF.</p>
  <a href="reports_pdf.php" class="btn btn-report btn-lg">
    <i class="bi bi-file-earmark-bar-graph"></i> Generate PDF Report
  </a>
</div>

<!-- Footer -->
<footer>
  © All rights reserved by Vikas 2026
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>