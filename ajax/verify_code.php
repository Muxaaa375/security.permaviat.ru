<?php
session_start();
include("../settings/connect_datebase.php");

// Проверяем входные данные
if (!isset($_POST['code']) || !isset($_SESSION['temp_user_id'])) {
    echo "error_no_code";
    exit();
}

$inputCode = trim($_POST['code']);
$storedCode = $_SESSION['auth_code'] ?? null;

// Сравниваем введенный код с сохраненным
if ($inputCode !== (string)$storedCode) {
    echo "error_invalid_code";
    exit();
}

// Генерируем новый токен и время истечения
$user_id = $_SESSION['temp_user_id'];
$new_session_token = bin2hex(random_bytes(32));
$token_expiry = time() + 3600; // Токен действителен 1 час

// Обновляем токен и время истечения в БД
$stmt = $mysqli->prepare("UPDATE users SET session_token = ?, token_expiry = ? WHERE id = ?");
if (!$stmt) {
    error_log("Ошибка подготовки запроса: " . $mysqli->error);
    echo "error_database";
    exit();
}

$stmt->bind_param("sis", $new_session_token, $token_expiry, $user_id);
if (!$stmt->execute()) {
    error_log("Ошибка выполнения запроса: " . $stmt->error);
    echo "error_database";
    exit();
}

// Обновляем сессию
$_SESSION['user_id'] = $user_id;
$_SESSION['session_token'] = $new_session_token;
$_SESSION['token_expiry'] = $token_expiry;

// Очищаем временные данные
unset($_SESSION['auth_code'], $_SESSION['temp_user_id']);

// Возвращаем успешный ответ
echo "success";
?>