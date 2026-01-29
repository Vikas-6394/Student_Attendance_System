<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/header.php';

csrf_check();
$pdo = db();
$user = current_user($pdo);
$uid = (int)$user['id'];

$swal = null; // popup controller

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $action = $_POST['action'] ?? '';

  /* ===== PROFILE UPDATE ===== */
  if ($action === 'profile') {
    $pdo->prepare(
      "UPDATE users SET name=?, currency=?, theme=? WHERE id=?"
    )->execute([
      trim($_POST['name']),
      $_POST['currency'],
      $_POST['theme'],
      $uid
    ]);

    $swal = [
      'icon' => 'success',
      'title' => 'Profile Updated!',
      'text' => 'Your profile has been updated successfully.',
      'redirect' => 'dashboard.php'
    ];
  }

  /* ===== PASSWORD CHANGE ===== */
  if ($action === 'password') {
    $current = $_POST['current'] ?? '';
    $new     = $_POST['new'] ?? '';

    $stmt = $pdo->prepare("SELECT password_hash FROM users WHERE id=?");
    $stmt->execute([$uid]);
    $row = $stmt->fetch();

    if (!$row || !password_verify($current, $row['password_hash'])) {
      $swal = [
        'icon' => 'error',
        'title' => 'Wrong Password',
        'text' => 'Current password is incorrect.',
        'redirect' => 'settings.php'
      ];
    } elseif (strlen($new) < 6) {
      $swal = [
        'icon' => 'warning',
        'title' => 'Weak Password',
        'text' => 'New password must be at least 6 characters.',
        'redirect' => 'settings.php'
      ];
    } else {
      $pdo->prepare(
        "UPDATE users SET password_hash=? WHERE id=?"
      )->execute([password_hash($new, PASSWORD_BCRYPT),$uid]);

      $swal = [
        'icon' => 'success',
        'title' => 'Password Changed!',
        'text' => 'Your password has been changed successfully.',
        'redirect' => 'dashboard.php'
      ];
    }
  }
}
?>

<div class="container my-4">
  <div class="row g-4">

    <!-- PROFILE -->
    <div class="col-md-6">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient mb-3">👤 Profile Settings</h3>

          <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf_token()?>">
            <input type="hidden" name="action" value="profile">

            <div class="mb-3">
              <label class="form-label fw-bold text-info">Name</label>
              <input class="form-control rounded-pill"
                     name="name"
                     value="<?=sanitize($user['name'])?>" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-success">Currency</label>
              <select name="currency" class="form-select rounded-pill">
                <option value="INR" <?=$user['currency']=='INR'?'selected':''?>>₹ INR</option>
                <option value="USD" <?=$user['currency']=='USD'?'selected':''?>>$ USD</option>
                <option value="EUR" <?=$user['currency']=='EUR'?'selected':''?>>€ EUR</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-warning">Theme</label>
              <select name="theme" class="form-select rounded-pill">
                <option value="light" <?=$user['theme']=='light'?'selected':''?>>Light</option>
                <option value="dark" <?=$user['theme']=='dark'?'selected':''?>>Dark</option>
              </select>
            </div>

            <button class="btn btn-gradient w-100 btn-lg">
              💾 Save Profile
            </button>
          </form>
        </div>
      </div>
    </div>

    <!-- PASSWORD -->
    <div class="col-md-6">
      <div class="card shadow-3d border-0 rounded-4">
        <div class="card-body">
          <h3 class="text-gradient mb-3">🔐 Change Password</h3>

          <form method="post">
            <input type="hidden" name="csrf" value="<?=csrf_token()?>">
            <input type="hidden" name="action" value="password">

            <div class="mb-3">
              <label class="form-label fw-bold text-danger">Current Password</label>
              <input class="form-control rounded-pill" type="password" name="current" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold text-primary">New Password</label>
              <input class="form-control rounded-pill" type="password" name="new" required>
            </div>

            <button class="btn btn-warning w-100 btn-lg shadow-sm">
              🔑 Change Password
            </button>
          </form>
        </div>
      </div>
    </div>

  </div>
</div>

<footer class="text-center py-3 mt-auto"
style="background:linear-gradient(90deg,#555,#333);
       color:#eee;
       position:fixed;
       bottom:0; left:0; width:100%;">
  © All rights reserved by Vikas 2026
</footer>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

<?php if ($swal): ?>
<script>
Swal.fire({
  icon: '<?=$swal['icon']?>',
  title: '<?=$swal['title']?>',
  text: '<?=$swal['text']?>',
  confirmButtonText: 'OK',
  allowOutsideClick: false
}).then(() => {
  window.location.href = '<?=$swal['redirect']?>';
});
</script>
<?php endif; ?>
