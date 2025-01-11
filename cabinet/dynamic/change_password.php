<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";

class Mikrotik {
	private static $ip;

	public static function ChangePPPoEPassword($password) {
		if (!self::CheckKitAvailability()) {
			return;
		}

		require_once $_SESSION['path']."/cabinet/utils/MikrotikAPI/routeros_api.class.php";

		$API = new RouterosAPI();

		if ($API->connect(self::$ip, self::$login, self::$password)) {
			$API->comm("/interface/pppoe-client/set", array(
				"numbers" => "0",
				"password" => $password
			));
		}

		$API->disconnect();
	}

	private static function CheckKitAvailability() {
		$query = Database::Query($fetch=true, "SELECT ip FROM kits WHERE user = '{$_SESSION['username']}'");
		$answer = $query['ip'];

		if ($answer == 0 or !isset($answer))
			return false;

		return self::$ip = $answer;
	}
}

if (empty($_POST['old_password'])) {
	header("Location: ../control.php?pass_error=Требуется старый пароль");
	exit;
} else if (empty($_POST['new_password'])) {
	header("Location: ../control.php?pass_error=Требуется новый пароль");
	exit;
} else if ($_POST['new_password'] !== $_POST['new_password_confirm']) {
	header("Location: ../control.php?pass_error=Новые пароли не совпадают");
	exit;
} else {
	if (!($old_password = Validator::Validate($_POST['old_password'], "alnum")) ||
		!($new_password = Validator::Validate($_POST['new_password'], "alnum")) ||
		!($new_password_confirm = Validator::Validate($_POST['new_password_confirm'], "alnum"))) {

		header("Location: ../control.php?pass_error=Введенные данные некорректны");
		exit;
	}

	// If the entered passwords are more than 15 characters long, we return an error
	if (strlen($old_password) > 15 || strlen($new_password) > 15 || strlen($new_password_confirm) > 15) {
		header("Location: ../control.php?pass_error=Введенные пароли слишком длинные");
		exit;
	}

	$query = Database::Query($fetch=false, "SELECT password FROM users WHERE uid = '{$_SESSION['uid']}' AND password = '{$old_password}'");

	if ($query->rowCount() == 1) {
		Database::Query($fetch=true, "UPDATE users SET password = '{$new_password}' WHERE uid = '{$_SESSION['uid']}'");

		// Если у пользователя есть комплект Mikrotik, то меняем пароль для PPPoE соединения
		Mikrotik::ChangePPPoEPassword($new_password);

		header("Location: ../control.php?pass_success=Пароль изменен");
		exit;
	}

	header("Location: ../control.php?pass_error=Неправильный старый пароль");
	exit();
}