<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";

if (($unlim_state = Validator::validate($_POST['unlim'], "flag_num")) == -1) {
	echo "Ошибка";
	exit;
}

// Send query
Database::Query($fetch=false, "UPDATE users SET control_unlim = {$unlim_state} WHERE uid = '{$_SESSION['uid']}'");
?>