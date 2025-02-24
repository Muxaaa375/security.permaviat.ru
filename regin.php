<?php
	session_start();
	include("./settings/connect_datebase.php");
	
    if (isset($_SESSION['user']) && $_SESSION['user'] != -1) {
        $user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = " . $_SESSION['user']);
        while ($user_read = $user_query->fetch_row()) {
            if ($user_read[3] == 0) header("Location: user.php");
            else if ($user_read[3] == 1) header("Location: admin.php");
        }
    }
?>
<html>
	<head> 
		<meta charset="utf-8">
		<title> Регистрация </title>
		
		<script src="https://code.jquery.com/jquery-1.8.3.js"></script>
		<link rel="stylesheet" href="style.css">
	</head>
	<body>
		<div class="top-menu">
			<a href=#><img src = "img/logo1.png"/></a>
			<div class="name">
				<a href="index.php">
					<div class="subname">БЕЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
					Пермский авиационный техникум им. А. Д. Швецова
				</a>
			</div>
		</div>
		<div class="space"> </div>
		<div class="main">
			<div class="content">
			<div class="login">
            <div class="name">Регистрация</div>

            <div class="sub-name">Логин:</div>
            <input name="_login" type="text" placeholder="" onkeypress="return PressToEnter(event)"/>
            <div class="sub-name">Пароль:</div>
            <input name="_password" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>
            <div class="sub-name">Повторите пароль:</div>
            <input name="_passwordCopy" type="password" placeholder="" onkeypress="return PressToEnter(event)"/>

            <a href="login.php">Вернуться</a>
            <input type="button" class="button" value="Зарегистрироваться" onclick="RegIn()" style="margin-top: 0px;"/>
            <img src="img/loading.gif" class="loading" style="margin-top: 0px; display: none;"/>
        </div>
				
				<div class="footer">
					© КГАПОУ "Авиатехникум", 2020
					<a href=#>Конфиденциальность</a>
					<a href=#>Условия</a>
				</div>
			</div>
		</div>
		
		<script>
    var loading = document.getElementsByClassName("loading")[0];
    var button = document.getElementsByClassName("button")[0];

    function CheckPassword(value) {
        if (value.length <= 8) {
            alert("Пароль должен содержать более 8 символов.");
            return false;
        }
        if (!/[A-Z]/.test(value)) {
            alert("Пароль должен содержать хотя бы одну заглавную букву.");
            return false;
        }
        if (!/[a-z]/.test(value)) {
            alert("Пароль должен содержать хотя бы одну строчную букву.");
            return false;
        }
        if (!/\d/.test(value)) {
            alert("Пароль должен содержать хотя бы одну цифру.");
            return false;
        }
        if (!/[@$!%*?&#^()_+\-=\[\]{};':"\\|,.<>\/]/.test(value)) {
            alert("Пароль должен содержать хотя бы один специальный символ.");
            return false;
        }
        return true;
    }

    function RegIn() {
        var _login = document.getElementsByName("_login")[0].value.trim();
        var _password = document.getElementsByName("_password")[0].value;
        var _passwordCopy = document.getElementsByName("_passwordCopy")[0].value;

        if (_login === "") {
            alert("Введите логин.");
            return;
        }

        if (_password === "" || _passwordCopy === "") {
            alert("Введите пароль и его подтверждение.");
            return;
        }

        if (_password !== _passwordCopy) {
            alert("Пароли не совпадают.");
            return;
        }

        if (!CheckPassword(_password)) {
            return; 
        }

        loading.style.display = "block";
        button.classList.add("button_diactive");

        var data = new FormData();
        data.append("login", _login);
        data.append("password", _password);

        $.ajax({
            url: 'ajax/regin_user.php',
            type: 'POST',
            data: data,
            cache: false,
            dataType: 'text',
            processData: false,
            contentType: false,
            success: function (_data) {
                console.log("Ответ сервера:", _data);
                if (_data.startsWith("error: ")) {
                    var errorMsg = _data.split("error: ")[1];
                    alert(errorMsg);
                } else if (_data == -1) {
                    alert("Пользователь с таким логином уже существует.");
                } else {
                    location.reload();
                }
                loading.style.display = "none";
                button.classList.remove("button_diactive");
            },
            error: function () {
                alert('Ошибка соединения с сервером.');
                loading.style.display = "none";
                button.classList.remove("button_diactive");
            }
        });
    }

    function PressToEnter(e) {
        if (e.keyCode === 13) {
            RegIn();
        }
    }
			
		</script>
	</body>
</html>