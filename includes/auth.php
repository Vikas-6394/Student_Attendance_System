<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header("Location: login.php");
        exit;
    }
}

function current_user(PDO $pdo) {

    if (empty($_SESSION['user_id'])) {
        return false;
    }

    $uid = (int)$_SESSION['user_id'];

    $stmt = $pdo->prepare(
        "SELECT * FROM users WHERE id = ? LIMIT 1"
    );
    $stmt->execute([$uid]);

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return is_array($user) ? $user : false;
}
