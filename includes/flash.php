<?php
function flash(string $type, string $msg) {
  $_SESSION['flash'][$type][] = $msg;
}
function flash_show() {
  if (!empty($_SESSION['flash'])) {
    foreach ($_SESSION['flash'] as $type => $msgs) {
      foreach ($msgs as $m) {
        echo "<div class='alert {$type}'>{$m}</div>";
      }
    }
    unset($_SESSION['flash']);
  }
}