<?php
session_start();
require_once __DIR__ . '/../../config/database.php';

/* Disable caching */
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if (empty($_SESSION['admin_id'])) {
  header('Location: actions.php');
  exit;
}

$pdo = db();

/* ===== SEARCH LOGIC ===== */
$search = trim($_GET['q'] ?? '');

if ($search !== '') {
  $stmt = $pdo->prepare(
    "SELECT id, name, email, created_at
     FROM users
     WHERE name LIKE ? OR email LIKE ?
     ORDER BY created_at DESC
     LIMIT 100"
  );
  $like = "%{$search}%";
  $stmt->execute([$like, $like]);
  $users = $stmt->fetchAll();
} else {
  $users = $pdo->query(
    "SELECT id, name, email, created_at
     FROM users
     ORDER BY created_at DESC
     LIMIT 100"
  )->fetchAll();
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Users</title>
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
      padding: 1rem;
      color: #fff;
    }
    footer {
      background: linear-gradient(90deg,#444,#222);
      color: #eee;
      text-align: center;
      padding: 12px;
      margin-top: auto;
    }
    .btn-gradient {
      background: linear-gradient(90deg, #43cea2, #185a9d);
      color: #fff;
      border-radius: 30px;
      box-shadow: 0 6px #0d3c61;
    }
    .btn-logout {
      background: linear-gradient(90deg, #ff512f, #dd2476);
      color: #fff !important;
      border-radius: 30px;
      padding: 8px 20px;
      font-weight: 600;
    }
  </style>
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark fixed-top">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="index.php">
      <i class="bi bi-people-fill"></i> Admin Panel
    </a>
    <div class="ms-auto">
      <button class="btn btn-logout" onclick="confirmLogout()">
        <i class="bi bi-box-arrow-right"></i> Logout
      </button>
    </div>
  </div>
</nav>

<div class="container py-5" style="margin-top:100px;">
  <div class="glass-card shadow-lg">

    <!-- HEADER + SEARCH -->
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">
        <i class="bi bi-person-lines-fill"></i> Users
      </h3>

      <form class="d-flex" method="get" action="users.php">
        <input class="form-control me-2 rounded-pill"
               type="search"
               name="q"
               value="<?=htmlspecialchars($search)?>"
               placeholder="Search users..."
               aria-label="Search">
        <button class="btn btn-gradient rounded-pill">
          <i class="bi bi-search"></i>
        </button>
      </form>
    </div>

    <!-- USERS TABLE -->
    <div class="table-responsive">
      <table class="table table-striped table-hover align-middle bg-white text-dark rounded">
        <thead class="table-dark">
          <tr>
            <th>ID</th>
            <th>Name</th>
            <th>Email</th>
            <th>Joined</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>

        <?php if ($users): ?>
          <?php foreach ($users as $u): ?>
            <tr>
              <td><?=htmlspecialchars($u['id'])?></td>
              <td><?=htmlspecialchars($u['name'])?></td>
              <td><?=htmlspecialchars($u['email'])?></td>
              <td><?=htmlspecialchars($u['created_at'])?></td>
              <td>
                <button class="btn btn-sm btn-warning me-1" onclick="showRestriction()">
                  <i class="bi bi-pencil-square"></i> Edit
                </button>

                <form method="post" action="actions.php" class="d-inline">
                  <input type="hidden" name="action" value="delete_user">
                  <input type="hidden" name="id" value="<?=$u['id']?>">
                  <button type="button"
                          class="btn btn-sm btn-danger"
                          onclick="confirmDelete(this)">
                    <i class="bi bi-trash"></i> Delete
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="text-center text-muted fw-bold">
              No users found
            </td>
          </tr>
        <?php endif; ?>

        </tbody>
      </table>
    </div>

    <!-- PAGINATION (UNCHANGED – static) -->
    <nav aria-label="User pagination">
      <ul class="pagination justify-content-center mt-3">
        <li class="page-item disabled"><a class="page-link">Previous</a></li>
        <li class="page-item active"><a class="page-link">1</a></li>
        <li class="page-item"><a class="page-link" href="#">2</a></li>
        <li class="page-item"><a class="page-link" href="#">3</a></li>
        <li class="page-item"><a class="page-link" href="#">Next</a></li>
      </ul>
    </nav>

  </div>
</div>

<footer>
  © All rights reserved by Vikas 2026
</footer>

<!-- Restriction Modal -->
<div class="modal fade" id="editRestrictionModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-warning">
        <h5 class="modal-title">
          <i class="bi bi-exclamation-triangle"></i> Permission Restricted
        </h5>
        <button class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        Admin cannot edit user details.<br>
        You can only delete.
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
function showRestriction() {
  new bootstrap.Modal(
    document.getElementById('editRestrictionModal')
  ).show();
}

function confirmDelete(btn) {
  Swal.fire({
    title: 'Delete User?',
    text: 'This action cannot be undone!',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#d33',
    confirmButtonText: 'Yes, Delete',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      btn.closest('form').submit();
    }
  });
}

function confirmLogout() {
  Swal.fire({
    title: 'Logout?',
    text: 'Do you want to logout from admin panel?',
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Yes, Logout',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "actions.php?action=logout";
    }
  });
}
</script>

</body>
</html>
