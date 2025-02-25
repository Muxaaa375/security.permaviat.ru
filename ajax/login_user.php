<?php
session_start();
header('Content-Type: application/json');
include(__DIR__ . "/../settings/connect_datebase.php");
require __DIR__ . "/../recaptcha/autoload.php"; 
error_reporting(E_ALL);
ini_set('display_errors', 1);
ob_clean();
$secretKey = "6LcDReAqAAAAAH5U_HqwhzqcOiNffQ00drK0qL8B";
if (!isset($_POST['g-recaptcha-response']) || empty($_POST['g-recaptcha-response'])) {
    echo json_encode(["status" => "error", "message" => "Ошибка: reCAPTCHA не была отправлена."]);
    exit;
}
$response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secretKey&response=" . $_POST['g-recaptcha-response'] . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
$responseKeys = json_decode($response, true);

if (!$responseKeys["success"]) {
    echo json_encode(["status" => "error", "message" => "Ошибка: reCAPTCHA не пройдена."]);
    exit;
}

$login = trim($_POST['login'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($login) || empty($password)) {
    echo json_encode(["status" => "error", "message" => "Ошибка: Заполните все поля."]);
    exit;
}

$result = $mysqli->query("SHOW COLUMNS FROM users LIKE 'role'");
$roleExists = $result->num_rows > 0;
$result->free();


if ($roleExists) {
    $stmt = $mysqli->prepare("SELECT id, password, role FROM users WHERE login = ?");
} else {
    $stmt = $mysqli->prepare("SELECT id, password FROM users WHERE login = ?");
}

if (!$stmt) {
    error_log("Ошибка запроса: " . $mysqli->error);
    echo json_encode(["status" => "error", "message" => "Ошибка базы данных."]);
    exit;
}

$stmt->bind_param("s", $login);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows === 1) {
    if ($roleExists) {
        $stmt->bind_result($id, $hashed_password, $role);
    } else {
        $stmt->bind_result($id, $hashed_password);
        $role = 0; 
    }

    $stmt->fetch();

   
    if (strlen($hashed_password) < 60) {
        $isPasswordCorrect = ($password === $hashed_password); 
    } else {
        $isPasswordCorrect = password_verify($password, $hashed_password); 
    }

    if ($isPasswordCorrect) {
        $_SESSION['user'] = $id;
        $redirect_url = ($role == 1) ? "admin.php" : "user.php";
        echo json_encode(["status" => "success", "redirect" => $redirect_url]);
    } else {
        echo json_encode(["status" => "error", "message" => "Ошибка: Неверный логин или пароль."]);
    }
} else {
    echo json_encode(["status" => "error", "message" => "Ошибка: Пользователь не найден."]);
}

$stmt->close();
$mysqli->close();
?>
