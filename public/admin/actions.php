<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

$pdo = db();
$popupData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  if ($action === 'login') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id,password_hash FROM admin WHERE email=?");
    $stmt->execute([$email]);
    $a = $stmt->fetch();

    if ($a && password_verify($pass, $a['password_hash'])) {
      $_SESSION['admin_id'] = (int)$a['id'];
      $popupData = ['success','Login Successful','Welcome Admin! Redirecting…'];
    } else {
      $popupData = ['error','Login Failed','Invalid admin email or password.'];
    }
  }

  /* user delete logic untouched (popup not needed here) */
  if ($action === 'delete_user' && !empty($_SESSION['admin_id'])) {
    $id = (int)$_POST['id'];
    $pdo->prepare("DELETE FROM users WHERE id=?")->execute([$id]);
    header('Location: users.php');
    exit;
  }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Admin Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS & Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      background: linear-gradient(135deg, #1f1c2c, #928dab);
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      font-family: 'Segoe UI', sans-serif;
    }
    .glass-card {
      background: rgba(255,255,255,0.08);
      backdrop-filter: blur(12px);
      border-radius: 20px;
      border: 1px solid rgba(255,255,255,0.2);
      padding: 2rem;
    }
    .form-control {
      border-radius: 30px;
      padding: 0.75rem 1rem;
      font-weight: 500;
    }
    .btn-gradient {
      background: linear-gradient(90deg, #43cea2, #185a9d);
      color: #fff;
      border: none;
      border-radius: 30px;
      box-shadow: 0 6px #0d3c61;
    }
    .btn-gradient:hover {
      background: linear-gradient(90deg, #185a9d, #43cea2);
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
<body class="d-flex flex-column">

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold mx-auto mx-lg-0" href="actions.php">
      <i class="bi bi-shield-lock"></i> Admin Panel
    </a>
  </div>
</nav>

<!-- Login Card -->
<div class="container d-flex align-items-center justify-content-center flex-grow-1"
     style="margin-top:100px; margin-bottom:40px;">
  <div class="col-12 col-sm-10 col-md-8 col-lg-5">
    <div class="glass-card shadow-lg w-100">
      <h3 class="text-center mb-4 text-light">
        <i class="bi bi-lock-fill"></i> Admin Login
      </h3>

      <!-- ❌ old error alert removed -->

      <form method="post">
        <input type="hidden" name="action" value="login">

        <div class="mb-3">
          <label class="form-label text-light fw-bold">Email</label>
          <input class="form-control"
                 type="email"
                 name="email"
                 placeholder="Enter admin email"
                 required>
        </div>

        <div class="mb-3">
          <label class="form-label text-light fw-bold">Password</label>
          <input class="form-control"
                 type="password"
                 name="password"
                 placeholder="Enter admin password"
                 required>
        </div>

        <div class="d-grid">
          <button class="btn btn-gradient btn-lg">🚀 Login</button>
        </div>
      </form>
    </div>
  </div>
</div>

<footer>
  © All rights reserved by Vikas 2026
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($popupData): ?>
<script>
Swal.fire({
  icon: "<?=$popupData[0]?>",
  title: "<?=$popupData[1]?>",
  text: "<?=$popupData[2]?>",
  confirmButtonText: "OK",
  allowOutsideClick: false
}).then(() => {
  <?php if ($popupData[0] === 'success'): ?>
    window.location.href = "index.php";
  <?php endif; ?>
});
</script>
<?php endif; ?>

</body>
</html>
