<?php
require_once "../cabinet/utils/session/session_init.php";

if(!isset($_POST['username']) || !isset($_POST['password'])) {
	header("Location: ../index.php");
	exit();
}
if (empty($_POST['username'])) {
	header("Location: ../index.php?error=Требуется логин");
	exit;
}
if (empty($_POST['password'])) {
	header("Location: ../index.php?error=Требуется пароль");
	exit;
} 

require_once "../cabinet/database/database.php";
require_once "../cabinet/utils/validator/validator.php";

if (!($username = Validator::Validate($_POST['username'], "alnum")) || 
	!($password = Validator::Validate($_POST['password'], "alnum"))) {
	header("Location: ../index.php?error=Ошибка");
	exit;
}

$query = Database::Query($fetch=false, "SELECT `user`, `password`, `fio`, `uid`, `blocked`  FROM users WHERE `user` = '$username' AND `password` = '$password' LIMIT 1");

if ($query->rowCount() == 1) {
	$row = $query->fetch();

	if ($row['blocked'] == '1') {
		header("Location: ../index.php?error=Ваш аккаунт заблокирован");
		exit;
	}

	if ($row['user'] === $username && $row['password'] === $password) {
		$_SESSION['username'] = $row['user'];
		$_SESSION['password'] = $row['password'];
		$_SESSION['fio'] = $row['fio'];
		$_SESSION['uid'] = $row['uid'];
		$_SESSION['path'] = "/usr/local/www/apache24/data/";

		header("Location: ../cabinet/");
		exit;
	}
}  else {
	header("Location: ../index.php?error=Неправильный логин или пароль");
	exit;
}
?>
