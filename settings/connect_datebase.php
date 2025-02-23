<?php
	$mysqli = new mysqli('localhost', 'root', '', 'security');
	if ($mysqli->connect_error) {
		error_log("Database connection failed: " . $mysqli->connect_error);
		die("Connection error");
	}
	$mysqli->set_charset("utf8");
?>