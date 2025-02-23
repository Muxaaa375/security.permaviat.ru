<?php
session_start();

if (!isset($_SESSION['session_token']) || !isset($_SESSION['token_expiry'])) {
    header("Location: login.php");
    exit();
}

if (time() > $_SESSION['token_expiry']) {
    session_destroy();
    header("Location: login.php?error=expired_token");
    exit();
}