<?php

require_once "/usr/local/www/apache24/data/cabinet/database/database.php";
require_once "/usr/local/www/apache24/data/cabinet/entities/Warnings.php";

// Класс собирает информацию о юзере
class UserDataCollector {
	public static $gid;
	public static $deposit;
	public static $credit;
	public static $money_for_next_mon;
	public static $blocked;
	public static $added_traffic;
	public static $regular_payment;
	public static $control_unlim;

	public static $is_user_has_homekit = 0;
	public static $mac_of_users_homekit = '';

	public static function Init() {
		$data = Database::Query($fetch=true, 'SELECT gid, deposit, credit, debt, money_for_next_mon, blocked, added_traffic, regular_payment, control_unlim FROM users WHERE uid = '.$_SESSION['uid'].'');

		$data2 = Database::Query($fetch=true, 'SELECT mac FROM kits WHERE user = "'.$_SESSION['username'].'" LIMIT 1');

		self::$gid = $data['gid'];
		self::$deposit = $data['deposit'];
		self::$credit = $data['credit'] + $data['debt'];
		self::$money_for_next_mon = $data['money_for_next_mon'];
		self::$blocked = $data['blocked'];
		self::$added_traffic = $data['added_traffic'];
		self::$regular_payment = $data['regular_payment'];
		self::$control_unlim = $data['control_unlim'];

		// Формируем варнинг, если юзер заблокирован
		if (self::$blocked == 1) {
			Warnings::AddErrorWarning("Ваш аккаунт заблокирован");
		}

		// Формируем варнинг, если юзер не всес абонентскую плату
		if (self::$money_for_next_mon > 0) {
			Warnings::AddInfoWarning(sprintf("%.0f", self::$money_for_next_mon)." рублей зачислены на следующий месяц");
		}

		// Проверяем наличие комплекта у пользователя
		if ($data2) {
			self::$is_user_has_homekit = 1;
			self::$mac_of_users_homekit = $data2['mac'];
		}
	}

	public static function FormatDeposit() {
		return number_format(sprintf("%.2f", self::$deposit), 2, '.', ' ');
	}

	public static function FormatCredit() {
		return number_format(sprintf("%.2f", self::$credit), 2, '.', ' ');
	}
}

UserDataCollector::Init();

?>