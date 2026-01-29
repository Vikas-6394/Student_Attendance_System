<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';

if (!empty($_SESSION['user_id'])) {
  header('Location: dashboard.php');
  exit;
}

csrf_check();

$popupData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass  = $_POST['password'] ?? '';

  if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $pass === '') {
    $popupData = ['error','Invalid Input','Please enter a valid email and password.'];
  } else {
    $pdo = db();
    $stmt = $pdo->prepare("SELECT id, password_hash FROM users WHERE email=? LIMIT 1");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u && password_verify($pass, $u['password_hash'])) {
      session_regenerate_id(true);
      $_SESSION['user_id'] = (int)$u['id'];

      $popupData = ['success','Login Successful','Welcome back! Redirecting to dashboard…'];
    } else {
      $popupData = ['error','Login Failed','Invalid email or password.'];
    }
  }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Login | Expense Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      background: linear-gradient(135deg, #fdfcfb, #e2d1c3);
      margin: 0;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .card {
      border-radius: 20px;
      background: #ffffff;
      box-shadow: 0 15px 25px rgba(0,0,0,0.3),
                  0 5px 10px rgba(0,0,0,0.2);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .card:hover {
      transform: scale(1.03);
      box-shadow: 0 20px 40px rgba(0,0,0,0.35);
    }

    .form-control {
      font-size: 1rem;
      font-weight: 500;
      color: #000;
      background-color: #fff !important;
      border-radius: 30px;
      padding: 0.75rem 1rem;
    }

    .btn-gradient {
      background: linear-gradient(90deg, #43cea2, #185a9d);
      color: #fff;
      border: none;
      border-radius: 30px;
      box-shadow: 0 6px #0d3c61;
      transition: all 0.2s ease;
    }
    .btn-gradient:active {
      box-shadow: 0 2px #0d3c61;
      transform: translateY(4px);
    }
    .btn-gradient:hover {
      background: linear-gradient(90deg, #185a9d, #43cea2);
      color: #fff;
    }

    footer {
      background: linear-gradient(90deg,#444,#222);
      color: #eee;
      text-align: center;
      padding: 12px;
      margin-top: auto;
    }

    .navbar-brand {
      transition: color 0.3s ease;
    }
    .navbar-brand:hover {
      color: #ffc107 !important;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-primary fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="login.php">Expense Management System</a>
  </div>
</nav>

<div class="container d-flex justify-content-center align-items-start pt-5 flex-grow-1" style="margin-top:80px;">
  <div class="col-11 col-sm-8 col-md-6 col-lg-4">
    <h2 class="text-center mb-4 fw-bold text-dark">Login</h2>
    <div class="card border-0">
      <div class="card-body p-4">
        <h4 class="text-center mb-3">Welcome Back 👋</h4>
        <p class="text-center text-muted mb-4">Log in to manage your finances</p>

        <!-- ❌ flash_show removed -->

        <form method="post" novalidate>
          <input type="hidden" name="csrf" value="<?=csrf_token()?>">

          <div class="mb-3">
            <label class="form-label fw-bold text-info">Email</label>
            <input type="email" name="email" class="form-control border-info"
                   placeholder="Enter your email address" required>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold text-warning">Password</label>
            <input type="password" name="password" class="form-control border-warning"
                   placeholder="Enter your password" required>
          </div>

          <button class="btn btn-gradient w-100 btn-lg" type="submit">🚀 Login</button>
        </form>

        <div class="text-center mt-3">
          <a href="forgot_password.php" class="text-decoration-none">Forgot password?</a>
          <span class="mx-2 text-muted">|</span>
          <a href="register.php" class="text-decoration-none">Create an account</a>
        </div>
      </div>
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
    window.location.href = "dashboard.php";
  <?php endif; ?>
});
</script>
<?php endif; ?>

</body>
</html>
