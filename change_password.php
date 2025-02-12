<?php
session_start();
include("./settings/connect_datebase.php");

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $new_password = $_POST['new_password'];
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
    
    $user_id = $_SESSION['user'];
    $query = $mysqli->prepare("UPDATE users SET password=?, password_changed_at=NOW() WHERE id=?");
    $query->bind_param("si", $hashed_password, $user_id);
    
    if ($query->execute()) {
        echo "success";
        unset($_SESSION['user']);
        session_destroy();
        exit;
    } else {
        echo "error";
    }
}
?>

<html>
<head>
    <meta charset="utf-8">
    <title>Смена пароля</title>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <h2>Смена пароля</h2>
    <input type="password" id="new_password" placeholder="Введите новый пароль"/>
    <button onclick="changePassword()">Сменить пароль</button>

    <script>
        function changePassword() {
            let new_password = document.getElementById("new_password").value;

            $.ajax({
                url: "change_password.php",
                type: "POST",
                data: { new_password: new_password },
                success: function(response) {
                    if (response.trim() === "success") {
                        alert("Пароль успешно изменён! Войдите снова.");
                        window.location.href = "login.php";
                    } else {
                        alert("Ошибка смены пароля!");
                    }
                }
            });
        }
    </script>
</body>
</html>
