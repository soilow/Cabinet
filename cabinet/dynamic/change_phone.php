<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";

if (empty($_POST['old_phone'])) {
	header("Location: ../control.php?phone_error=Требуется старый телефон");
	exit;
} else if (empty($_POST['new_phone'])) {
	header("Location: ../control.php?phone_error=Требуется новый телефон");
	exit;
} else {
	if (!($old_phone = Validator::Validate($_POST['old_phone'], "num")) ||
		!($new_phone = Validator::Validate($_POST['new_phone'], "num"))) {
		header("Location: ../control.php?phone_error=Введенные данные некорректны");
		exit;
	}

	// If the lenght of the new and old phones is not eaual to 10, we return an error
	if (strlen($old_phone) != 10 || strlen($new_phone) != 10) {
		header("Location: ../control.php?phone_error=Ошибка. Введите номер телефона без 7 или 8");
		exit;
	}

	// If the old and new phones are the same, then return an error
	if ($old_phone == $new_phone) {
		header("Location: ../control.php?phone_error=Старый и новый телефоны одинаковы");
		exit;
	}

	$query = Database::Query($fetch=false, "SELECT phone FROM users WHERE uid = '{$_SESSION['uid']}' AND phone = '{$old_phone}'");

	if ($query->rowCount() == 1) {
		Database::Query($fetch=true, "UPDATE users SET phone = '{$new_phone}' WHERE uid = '{$_SESSION['uid']}'");

		header("Location: ../control.php?phone_success=Номер телефона изменен");
		exit;
	} else {
		header("Location: ../control.php?phone_error=Неправильный старый телефон");
		exit;
	}
}