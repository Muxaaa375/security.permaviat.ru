<?php
session_start();
if (!isset($_POST['code'])) {
    echo "error_no_code";
    exit;
}

$inputCode = trim($_POST['code']);
$storedCode = isset($_SESSION['auth_code']) ? $_SESSION['auth_code'] : null;
$authCodeTime = isset($_SESSION['auth_code_time']) ? $_SESSION['auth_code_time'] : null;

if ($storedCode === null || $authCodeTime === null) {
    echo "error_no_stored_code";
    exit;
}

$currentTime = time();
if ($currentTime - $authCodeTime > 1800) { // Код действителен 30 минут
    echo "error_expired_code";
    exit;
}

if ($inputCode !== (string)$storedCode) {
    echo "error_invalid_code";
    exit;
}

// Удаляем код из сессии и устанавливаем флаг авторизации
unset($_SESSION['auth_code']);
unset($_SESSION['auth_code_time']);
$_SESSION['authorized'] = true;

echo "success";
exit;
?>