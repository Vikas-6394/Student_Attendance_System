<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';

csrf_check();

$pdo = db();
$popupData = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name  = trim($_POST['name'] ?? '');
  $email = strtolower(trim($_POST['email'] ?? ''));
  $pass  = $_POST['password'] ?? '';

  if (!$name || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($pass) < 6) {
    $popupData = ['error','Invalid Input','Please enter valid details and a strong password (min 6 characters).'];
  } else {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      $popupData = ['warning','Already Registered','This email is already registered. Please login.'];
    } else {
      $hash = password_hash($pass, PASSWORD_BCRYPT);
      $pdo->prepare(
        "INSERT INTO users (name,email,password_hash) VALUES (?,?,?)"
      )->execute([$name,$email,$hash]);

      $user_id = (int)$pdo->lastInsertId();

      // Default categories
      $defaults = ['Food','Travel','Shopping','Rent','Bills','Health','Education'];
      $ins = $pdo->prepare("INSERT INTO categories (user_id,name,icon) VALUES (?,?,?)");
      foreach ($defaults as $d) {
        $ins->execute([$user_id,$d,strtolower($d)]);
      }

      $popupData = ['success','Registration Successful','Your account has been created. Please login.'];
    }
  }
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Register | Expense Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <style>
    body {
      background: linear-gradient(135deg, #f0f8ff, #e6f7ff);
      margin: 0;
      padding-bottom: 60px;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
    }
    .shadow-3d {
      box-shadow: 0 15px 25px rgba(0,0,0,0.2),
                  0 5px 10px rgba(0,0,0,0.15);
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    .shadow-3d:hover {
      transform: scale(1.02);
      box-shadow: 0 20px 40px rgba(0,0,0,0.25);
    }
    .text-gradient {
      background: linear-gradient(90deg, #ff6a00, #ee0979);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      font-weight: bold;
    }
    .btn-gradient {
      background: linear-gradient(90deg, #43cea2, #185a9d);
      color: #fff;
      border: none;
      border-radius: 30px;
      box-shadow: 0 6px #0d3c61;
    }
    footer {
      background: linear-gradient(90deg,#555,#333);
      color: #eee;
      text-align: center;
      padding: 12px;
      position: fixed;
      bottom: 0;
      left: 0;
      width: 100%;
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
    <a class="navbar-brand fw-bold" href="index.php">Expense Management System</a>
    <div class="ms-auto">
      <a class="btn btn-outline-light rounded-pill px-3" href="login.php">Sign In</a>
    </div>
  </div>
</nav>

<div class="container d-flex justify-content-center align-items-center"
     style="min-height:80vh; margin-top:80px;">
  <div class="card shadow-3d p-4 border-0 rounded-4"
       style="max-width:420px; width:100%;">
    <h3 class="text-center mb-4 text-gradient">Create Account</h3>

    <!-- ❌ flash_show removed -->

    <form method="post">
      <input type="hidden" name="csrf" value="<?=csrf_token()?>">

      <div class="mb-3">
        <label class="form-label fw-bold text-info">Name</label>
        <input class="form-control rounded-pill"
               name="name"
               placeholder="Enter your full name" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold text-success">Email</label>
        <input class="form-control rounded-pill"
               type="email"
               name="email"
               placeholder="Enter your email address" required>
      </div>

      <div class="mb-3">
        <label class="form-label fw-bold text-warning">
          Password (min 6 chars)
        </label>
        <input class="form-control rounded-pill"
               type="password"
               name="password"
               placeholder="Create a strong password" required>
      </div>

      <button class="btn btn-gradient w-100 btn-lg">🚀 Register</button>
    </form>

    <p class="text-center mt-3 mb-0">
      Already have an account?
      <a href="login.php" class="fw-bold text-primary">Login</a>
    </p>
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
    window.location.href = "login.php";
  <?php endif; ?>
});
</script>
<?php endif; ?>

</body>
</html>
