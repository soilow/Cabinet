<?php

require_once "/usr/local/www/apache24/data/cabinet/database/database.php";

class Packets {
	public static $PACKETS = array();

	public static function Init() {
		$data = Database::Query($fetch=false, "SELECT gid, packet, prefix, month_traffic_limit, price_per_mb, fixed_cost, night_unlim, speed_night_unlim FROM packets ORDER BY month_traffic_limit");

		while ($packet = $data->fetch()) {
			$packet['month_traffic_limit'] /= Database::BytesToMb();
			$packet['fixed_cost'] = sprintf("%.0f", $packet['fixed_cost']);

			// Convert night unlim speed if there is one
			if ($packet['speed_night_unlim'] != "0")
				$packet['speed_night_unlim'] = self::ConvertNightUnlimSpeed($packet['speed_night_unlim']);

			self::$PACKETS[$packet['gid']] = $packet;
		}
	}

	public static function IsPacketNightUnlim($gid) {
		if (self::$PACKETS[$gid]['night_unlim'] == 0) {
			return false;
		}

		return true;
	}

	public static function IsSessionNightUnlim($address_list) {
		return $address_list == 1 ? 0 : 1;
	}

	private static function ConvertNightUnlimSpeed($speed) {
		if ($speed != "0") {
			preg_match("/Mikrotik-Rate-Limit=*([^\n]*),/", $speed, $temp);
			$temp = str_replace('k', '', $temp[1]);
			$temp = str_replace('M', '000', $temp);

			// Меняем входящую и исходящую скорость местами
			$temp2 = explode('/', $temp);

			return $temp2[1].'/'.$temp2[0];
		}
	}
}

Packets::Init();

?>