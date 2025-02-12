<?php
session_start();
include("../settings/connect_datebase.php");

$login = trim($_POST['login']);
$password = $_POST['password'];

function isValidPassword($password) {
    return preg_match('/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{9,}$/', $password);
}

if (!isValidPassword($password)) {
    echo "error: invalid_password";
    exit;
}

$query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='" . $mysqli->real_escape_string($login) . "'");
$id = -1;

if ($query_user->fetch_row()) {
    echo $id;
} else {
    // Хешируем пароль
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $mysqli->prepare("INSERT INTO `users` (`login`, `password`, `roll`) VALUES (?, ?, 0)");
    $stmt->bind_param("ss", $login, $hashed_password);
    $stmt->execute();

    $stmt = $mysqli->prepare("SELECT `id` FROM `users` WHERE `login`=?");
    $stmt->bind_param("s", $login);
    $stmt->execute();
    $stmt->bind_result($id);
    $stmt->fetch();
    $stmt->close();

    if ($id != -1) $_SESSION['user'] = $id;
    echo $id;
}
?>
