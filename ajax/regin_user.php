<?php
session_start();
include("../settings/connect_datebase.php");

$login = trim($_POST['login']);
$password = $_POST['password'];

function isValidPassword($password) {
    if (strlen($password) <= 8) {
        return "Пароль должен содержать более 8 символов.";
    }
    if (!preg_match('/[A-Z]/', $password)) {
        return "Пароль должен содержать хотя бы одну заглавную букву.";
    }
    if (!preg_match('/[a-z]/', $password)) {
        return "Пароль должен содержать хотя бы одну строчную букву.";
    }
    if (!preg_match('/\d/', $password)) {
        return "Пароль должен содержать хотя бы одну цифру.";
    }
    if (!preg_match('/[@$!%*?&#^()_+\-=\[\]{};:\'"\\\\|,.<>\/]/', $password)) {
        return "Пароль должен содержать хотя бы один специальный символ.";
    }
    return true;
}
$passwordCheck = isValidPassword($password);
if ($passwordCheck !== true) {
    echo "error: " . $passwordCheck;
    exit;
}

$stmt = $mysqli->prepare("SELECT * FROM `users` WHERE `login` = ?");
$stmt->bind_param("s", $login);
$stmt->execute();
$query_user = $stmt->get_result();
$id = -1;

if ($query_user->num_rows > 0) {
    echo $id;
} else {
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("INSERT INTO `users` (`login`, `password`, `roll`) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $login, $hashed_password);
    $stmt->execute();

    $stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `login` = ?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    if ($id != -1) $_SESSION['user'] = $id;
    echo $id;
}
?>
