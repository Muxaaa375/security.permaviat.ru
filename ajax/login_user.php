<?php
session_start();
if (!isset($_COOKIE[session_name()])) {
    echo "error: cookies_required";
    exit;
}
include __DIR__ . '/../settings/connect_datebase.php';

// Подключаем файлы PHPMailer из локальной директории, которая находится на уровень выше
require __DIR__ . '/../PHPMailer-master/src/PHPMailer.php';
require __DIR__ . '/../PHPMailer-master/src/SMTP.php';
require __DIR__ . '/../PHPMailer-master/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$N_DAYS = 30;
$MAX_DISTANCE = 50;

$login     = trim($_POST['login']);
$password  = $_POST['password'];
$latitude  = isset($_POST['latitude']) ? floatval($_POST['latitude']) : null;
$longitude = isset($_POST['longitude']) ? floatval($_POST['longitude']) : null;

// Проверяем пользователя в базе по email (поле login)
$query = $mysqli->prepare("SELECT id, password, password_changed_at, last_latitude, last_longitude FROM users WHERE login = ?");
if (!$query) {
    die("Ошибка подготовки запроса: " . $mysqli->error); 
}
$query->bind_param("s", $login);
$query->execute();
$query->bind_result($id, $hashed_password, $password_changed_at, $last_lat, $last_lon);
$query->fetch();
$query->close();

if (!$id || !password_verify($password, $hashed_password)) {
    echo "error";
    exit;
}
// Проверяем, истёк ли срок действия пароля
$today        = new DateTime();
$passwordDate = new DateTime($password_changed_at);
$interval     = $today->diff($passwordDate)->days;
if ($interval >= $N_DAYS) {
    $_SESSION['user'] = $id;
    echo "expired";
    exit;
}
// Функция для вычисления расстояния между координатами (в км)
function getDistance($lat1, $lon1, $lat2, $lon2) {
    $R    = 6371;
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon2 - $lon1);
    $a    = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c    = 2 * atan2(sqrt($a), sqrt(1 - $a));
    return $R * $c;
}

$verification_needed = false;
if (!is_null($last_lat) && !is_null($last_lon) && !is_null($latitude) && !is_null($longitude)) {
    $distance = getDistance($latitude, $longitude, $last_lat, $last_lon);
    if ($distance > $MAX_DISTANCE) {
        $verification_needed = true;
    }
}
session_regenerate_id(true);
$new_session_token = session_id();

$_SESSION['user']          = $id;
$_SESSION['session_token'] = $new_session_token;

// Обновляем координаты и session_token в базе для данного пользователя
if (!is_null($latitude) && !is_null($longitude) && $latitude !== 0.0 && $longitude !== 0.0) {
    $query = $mysqli->prepare("UPDATE users SET last_latitude = ?, last_longitude = ?, session_token = ? WHERE id = ?");
    $query->bind_param("ddsi", $latitude, $longitude, $new_session_token, $id);
    $query->execute();
    $query->close();
}

if ($verification_needed) {
        $code = rand(100000, 999999);
    $_SESSION['auth_code']    = $code;
    $_SESSION['pending_user'] = $id;
    
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.yandex.ru';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'Hoze-Doze@yandex.ru';
        $mail->Password   = 'wknismlzsruqcpyo';
        $mail->SMTPSecure = 'ssl';
        $mail->Port       = 465;
    
        $mail->CharSet    = 'UTF-8';
        $mail->Encoding   = 'base64';
    
        $mail->setFrom('Hoze-Doze@yandex.ru', 'Админ');
        $mail->addAddress($login);
    
        $mail->isHTML(true);
        $mail->Subject = 'Код авторизации';
        $mail->Body    = 'Ваш код авторизации: <b>' . $code . '</b>';
        $mail->AltBody = 'Ваш код авторизации: ' . $code;
    
        $mail->send();
    
        echo "code_required";
    } catch (Exception $e) {
        echo "error: " . $mail->ErrorInfo;
    }
} else {
    echo "success";
}
?>
