<?php
session_start();

if (!isset($_POST['code'])) {
    echo "error_no_code";
    exit;
}

$inputCode = trim($_POST['code']);
$storedCode = isset($_SESSION['auth_code']) ? $_SESSION['auth_code'] : null;

if ($storedCode === null) {
    echo "error_no_stored_code";
    exit;
}

if ($inputCode === (string)$storedCode) {
    unset($_SESSION['auth_code']);
    $_SESSION['authorized'] = true; // Пользователь авторизован
    echo "success";
} else {
    echo "error_invalid_code";
}
?>
