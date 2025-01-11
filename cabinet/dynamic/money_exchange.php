<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";
require_once $_SESSION['path']."/cabinet/entities/UserInfo.php";

class MoneyExchange {
	private static $data_to_send = array("code" => "", "text" => "");
	private static $abonent_login;
	private static $sum;

	public static function Init() {
		if (!self::CheckInputData()) {
			self::SendData();
			return;
		}

		if (isset($_POST['get_fio'])) {
			self::GetAbonentFio();
			self::SendData();
			return;
		}

		self::Exchange();
		self::SendData();
	}

	private static function SendData() {
		echo json_encode(self::$data_to_send);
	}

	private static function CheckInputData() {
		if (empty($_POST['abonent_login'])) {
			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Требуется логин абонента";

			return false;
		}
		if (empty($_POST['money'])) {
			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Требуется сумма для перевода";

			return false;
		}
		if (!(self::$abonent_login = Validator::Validate($_POST['abonent_login'], "num")) ||
			!(self::$sum = Validator::Validate($_POST['money'], "cash"))) {

			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Введенные данные некорректны";

			return false;
		}

		if (self::$abonent_login == $_SESSION['username']) {
			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Вы не можете переводить деньги самому себе";

			return false;
		}

		if (self::$sum < 1 || strlen(self::$sum) > 6) {
			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Введенная сумма некорректна";

			return false;
		}

		if (self::$sum > UserDataCollector::$deposit) {
			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Недостаточно средств на балансе. На вашем счету: ".UserDataCollector::FormatDeposit()." рублей";

			return false;
		}

		return true;
	}

	private static function GetAbonentFio() {
		$data = Database::Query($fetch=true, "SELECT fio FROM users WHERE user = '".self::$abonent_login."'");
		if (!$data) {
			self::$data_to_send['code'] = "error";
			self::$data_to_send['text'] = "Пользователя с таким логином не существует";

			return;
		}

		self::$data_to_send['code'] = "success";
		self::$data_to_send['text'] = $data['fio'];
	}

	private static function Exchange() {
		$user_friend = Database::Query($fetch=true, "SELECT deposit, fio, uid FROM users WHERE user = '".self::$abonent_login."'");

		$user_deposit = sprintf("%.2f", UserDataCollector::$deposit - self::$sum);
		$user_friend_deposit = $user_friend['deposit'] + self::$sum;

		Database::Query($fetch=true, "UPDATE users SET deposit = {$user_deposit} WHERE user = '".$_SESSION['username']."'");
		Database::Query($fetch=true, "UPDATE users SET deposit = {$user_friend_deposit} WHERE user = '".self::$abonent_login."'");

		self::$data_to_send['code'] = "success";
		self::$data_to_send['text'] = "Перевод на сумму ".self::$sum." рублей абоненту ".$user_friend['fio']." прошел успешно";
		
		Database::Query($fetch=true, "CALL AdminWork(14, '".self::$abonent_login."', '".$_SESSION['username']."', '".$user_deposit."', '".$user_friend_deposit."', '".self::$sum."')");
	}
}


MoneyExchange::Init();

?>






