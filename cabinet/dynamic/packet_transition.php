<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";

require_once $_SESSION['path']."/cabinet/entities/Packets.php";

class PacketTransition {
	private static $data_to_send = array("check" => "");
	private static $user_info;
	private static $gid_post;

	public static function Init() {
		self::CheckInputData();
		self::CollectUserInfo();
		self::DoTransition();
	}

	public static function SendData() {
		echo json_encode(self::$data_to_send);
	}

	private static function CheckInputData() {
		if (!(self::$gid_post = Validator::validate($_POST['gid'], "packet"))) {
			self::$data_to_send['check'] = "error";

			echo json_encode(self::$data_to_send);
			exit;
		}

		if (!isset(Packets::$PACKETS[self::$gid_post]) || Packets::$PACKETS[self::$gid_post]['prefix'] != "pub") {
			self::$data_to_send['check'] = "error";

			echo json_encode(self::$data_to_send);
			exit;
		}
	}

	private static function CollectUserInfo() {
		self::$user_info = DataBase::Query($fetch=true, "SELECT gid, regular_payment FROM users WHERE uid = '{$_SESSION['uid']}'");
	}

	private static function DoTransition() {
		if (self::$user_info['gid'] == 16 || self::$user_info['regular_payment'] == 0) {
			if (self::$gid_post == 16) {
				Database::Query($fetch=true, "UPDATE users SET gid = ".self::$gid_post.", bill_traff_auto_on = 0, bill_traffic_on = 0, regular_payment = 0, control_unlim = 0 WHERE uid = '{$_SESSION['uid']}'");
			} else {
				$used_traffic = Database::Query($fetch=true, "SELECT SUM(in_bytes + out_bytes) AS used_traffic FROM actions WHERE user = '{$_SESSION['username']}' AND (start_time BETWEEN '".Database::CurrentDate()."' and now())");

				if (!$used_traffic['used_traffic'])
					$used_traffic['used_traffic'] = 0;

				$control_unlim_flag = Packets::IsPacketNightUnlim(self::$gid_post) ? 1 : 0;

				Database::Query($fetch=true, "UPDATE users SET gid = ".self::$gid_post.", bill_traff_auto_on = 1, bill_traffic_on = 0, regular_payment = 0, control_unlim = {$control_unlim_flag}, added_traffic = {$used_traffic['used_traffic']} WHERE uid = '{$_SESSION['uid']}'");
			}
		} else {
			self::$data_to_send['check'] = "false";
		}
	}
}


PacketTransition::Init();
PacketTransition::SendData();

?>