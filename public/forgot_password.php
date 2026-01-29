<?php
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/../includes/flash.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../vendor/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer-master/src/SMTP.php';

csrf_check();
$pdo = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
    $stmt->execute([$email]);
    $u = $stmt->fetch();

    if ($u) {
        // Generate token + expiry
        $token   = bin2hex(random_bytes(32));
        $expires = (new DateTime('+30 minutes'))->format('Y-m-d H:i:s');

        $pdo->prepare("UPDATE users SET reset_token=?, reset_expires=? WHERE id=?")
            ->execute([$token, $expires, $u['id']]);

        // Reset link
        $resetLink = sprintf(
            "%s/reset_password.php?token=%s",
            "http://localhost/college%20project/public",
            $token
        );

        // Send email via PHPMailer
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'yourgmail@gmail.com';   // 🔴 Replace with your Gmail
            $mail->Password   = 'your-app-password';     // 🔴 Replace with Gmail App Password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('yourgmail@gmail.com', 'Expense Management System');
            $mail->addAddress($email);

            $mail->isHTML(true);
            $mail->Subject = 'Password Reset Request';
            $mail->Body = "
                <h3>Password Reset</h3>
                <p>Click the link below to reset your password:</p>
                <p><a href='$resetLink'>$resetLink</a></p>
                <p>This link will expire in 30 minutes.</p>
            ";

            $mail->send();
        } catch (Exception $e) {
            // silently fail in production
        }
    }

    // Always show same message (security best practice)
    flash('success', 'If the email exists, a password reset link has been sent.');
    header('Location: forgot_password.php');
    exit;
}
?>

<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Forgot Password | Expense Management System</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

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
    /* Hover effect only on project name */
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
  </div>
</nav>

<div class="container d-flex justify-content-center align-items-center" style="min-height:80vh; margin-top:80px;">
  <div class="card shadow-3d p-4 border-0 rounded-4" style="max-width:400px; width:100%;">
    <h3 class="text-center mb-4 text-gradient">Forgot Password</h3>
    <?php flash_show(); ?>
    <form method="post">
      <input type="hidden" name="csrf" value="<?= csrf_token() ?>">

      <div class="mb-3">
        <label class="form-label fw-bold text-info">Email</label>
        <input class="form-control rounded-pill" type="email" name="email" placeholder="Enter your email address" required>
      </div>

      <button class="btn btn-gradient w-100 btn-lg" type="submit">Send Reset Link</button>
    </form>
    <p class="text-center mt-3 mb-0">
      <a href="login.php" class="fw-bold text-primary">Back to Login</a>
    </p>
  </div>
</div>

<footer>
  © All rights reserved by Vikas 2026
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>