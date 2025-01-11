<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";

if (!($action = Validator::Validate($_POST['action'], "alnum"))) {
	echo "Ошибка";
	exit;
}

$output = array();

// Send query
$traffic_sql = "SELECT SUM(in_bytes) AS in_bytes, SUM(out_bytes) AS out_bytes, SUM(CASE WHEN actions.address_list = 2 THEN in_bytes + out_bytes ELSE 0 END) AS unlim FROM actions WHERE user = '{$_SESSION['username']}' AND year(start_time) = year(now())";

switch ($action) {
	case "day":
		$traffic_sql .= " AND month(start_time) = month(now()) AND day(start_time) = day(now())";
		break;
	case "month":
		$traffic_sql .= " AND month(start_time) = month(now())";
		break;
	case "year":
		break;
	default:
		echo "Ошибка";
		exit;
}

$traffic = Database::Query($fetch=true, $traffic_sql);

$output = [
	number_format($traffic['out_bytes'] / Database::BytesToMb(), 0, '.', ' '), 
	number_format($traffic['in_bytes'] / Database::BytesToMb(), 0, '.', ' '), 
	number_format($traffic['unlim'] / Database::BytesToMb(), 0, '.', ' '), 
	number_format(($traffic['in_bytes'] + $traffic['out_bytes']) / Database::BytesToMb(), 0, '.', ' ')
];

echo json_encode($output);

?>