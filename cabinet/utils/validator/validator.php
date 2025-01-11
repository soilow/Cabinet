<?php
require_once "/usr/local/www/apache24/data/cabinet/database/database.php";
require_once "/usr/local/www/apache24/data/cabinet/entities/Packets.php";

class Validator {
	public static function Validate($data, $type, $lenght = 128) {
		// Is it set
		if (!isset($data))
			return false;

		// Variable type checking
		switch ($type) {
			case 'num':
				if (!ctype_digit($data))
					return false;

				break;
				
			case "alnum":
				if (!ctype_alnum($data))
					return false;

				break;

			case "money":
				// Пропускаем только цифры и знак -
				if (preg_match("/-?\d*\.\d*/", $data))
					return false;

				break;

			case "cash":
				if (!preg_match("/^[1-9]\d*$/", $data))
					return false;			

				break;

			case "flag_num":
				if (!ctype_alnum($data))
					return -1;

				if ($data != "0" and $data != "1")
					return -1;

				break;

			case "flag_bool":
				if (!ctype_alnum($data))
					return false;

				if ($data != "true" and $data != "false")
					return false;

				break;

			case "packet":
				// Если тариф не задан
				if ($data == "none" or $data == "all")
					break;

				if (!ctype_digit($data))
					return false;

				// Есть ли тариф с таким gid'ом, как $data
				if (!array_key_exists($data, Packets::$PACKETS))
					return false;

				break;

			case "sql_date":
				if (!preg_match("/^[0-9]{4}-(0[0-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1]) ([0-1][0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9])$/", $data))
					return false;

				break;

			case "any":
				if (preg_match("/[']/", $data))
					return false;

				break;

			default:
				break;
		}

		// Проверка длины строки
		if (strlen($data) > $lenght)
			return false;

		$data = trim($data);
		$data = stripcslashes($data);
		$data = htmlspecialchars($data);


		return $data;

		if (self::filter_out_garbage($data))
			return false;
		else
			return $data;

		return $data;
	}

	private static function filter_out_garbage($garbage = '') {
		if (preg_match('/[^\x{80}-\x{F7} a-z0-9@_.\'-:]/i', $garbage)) {
			return true;
		}
		if (preg_match('/[\x{80}-\x{A0}'.          // Non-printable ISO-8859-1 + NBSP
	                 '\x{AD}'.                 // Soft-hyphen  Мягкий дефис
	                  '\x{2000}-\x{200F}'.      // Various space characters  Различные пробельные символы
	                  '\x{2028}-\x{202F}'.      // Bidirectional text overrides  Двунаправленный перекрывающийся текст
	                  '\x{205F}-\x{206F}'.      // Various text hinting characters
	                  '\x{FEFF}'.               // Byte order mark
	                  '\x{FF01}-\x{FF60}'.      // Full-width latin
	                  '\x{FFF9}-\x{FFFD}'.      // Replacement characters  Замена символов
	                  '\x{0}-\x{1F}]/u',        // NULL byte and control characters  Нуль байт и управляющие символы
	                  $garbage)) {
			return true;
		}
	}
}

?>