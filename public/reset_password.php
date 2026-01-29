<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

csrf_check();
$pdo = db();

$token = $_GET['token'] ?? '';

$stmt = $pdo->prepare("SELECT id, reset_expires FROM users WHERE reset_token=?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if (!$user) {
    flash('error', '❌ Invalid reset link.');
    header('Location: login.php');
    exit;
}

if (new DateTime() > new DateTime($user['reset_expires'])) {
    flash('error', '⚠ Reset link has expired.');
    header('Location: forgot_password.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';

    if (strlen($pass) < 6) {
        flash('error', 'Password must be at least 6 characters.');
    } else {
        $hash = password_hash($pass, PASSWORD_BCRYPT);

        $pdo->prepare(
            "UPDATE users 
             SET password_hash=?, reset_token=NULL, reset_expires=NULL 
             WHERE id=?"
        )->execute([$hash, $user['id']]);

        flash('success', '✅ Password updated successfully. Please login.');
        header('Location: login.php');
        exit;
    }
}
?>

<?php require_once __DIR__ . '/../includes/header.php'; ?>

<div class="container my-5">
  <!-- Flash messages -->
  <?php if (!empty($_SESSION['flash'])): ?>
    <?php foreach ($_SESSION['flash'] as $type => $msg): ?>
      <div class="alert alert-<?= $type==='error'?'danger':'success' ?> alert-dismissible fade show" role="alert">
        <?= $msg ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
    <?php endforeach; unset($_SESSION['flash']); ?>
  <?php endif; ?>

  <div class="card shadow-3d border-0 rounded-4 mx-auto" style="max-width:400px;">
    <div class="card-body">
      <h3 class="card-title text-gradient mb-3 text-center">🔑 Reset Password</h3>
      <form method="post">
        <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

        <div class="mb-3">
          <label class="form-label fw-bold text-info">New password</label>
          <input class="form-control rounded-pill" type="password" name="password" required>
        </div>

        <button class="btn btn-gradient w-100 btn-lg">Update</button>
      </form>
    </div>
  </div>
</div>

<!-- Custom Footer -->
<footer class="text-center py-3 mt-auto" style="background:linear-gradient(90deg,#444,#222); color:#eee; position:fixed; bottom:0; left:0; width:100%;">
  © All rights reserved by Vikas 2026
</footer>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<style>
body {
  background: linear-gradient(135deg, #fdfcfb, #e2d1c3);
  margin: 0;
  padding-bottom: 60px; /* space for fixed footer */
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
</style>