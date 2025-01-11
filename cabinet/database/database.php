<?php

class Database {
	private static $driver = "mysql";
	private static $charset = "utf8";
	private static $options = [PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION,
							   PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC];

	private static $pdo;

	private static $byte_to_mb = 0;
	private static $current_date;

	public static function Init() {
		try {
			$settings = self::$driver . ":host=" . self::$host . ";dbname=" . self::$db_name . ";charset=" . self::$charset;
			self::$pdo = new PDO($settings, self::$user, self::$password, self::$options);
		} catch (PDOException $e) {
			die($e->getMessage());
		}

		$bytes = self::Query($fetch=true, "SELECT mbyte FROM conf");
		self::$byte_to_mb = $bytes['mbyte'];

		self::$current_date = date("Y-m-d G:i:s", mktime(0, 0, 0, date('m'), 1, date('Y')));
	}

	/*
	 * \brief: Отправляем запрос в базу данных
	 * \param [boolean]: $fetch; Выполнить ли fetch
	 * \param [string]: $sql; Запрос
	 * \return $query() [PDO object]: Результат
	 */
	public static function Query($fetch, $sql) {
		$query = self::$pdo->prepare($sql);
		$query->execute();

		// Проверяем запрос на ошибки
		self::CheckQueryError($query);

		if ($fetch)
			$query = $query->fetch();

		return $query;
	}

	public static function BytesToMb() {
		return self::$byte_to_mb;
	}

	public static function CurrentDate() {
		return self::$current_date;
	}

	/*
	 * \brief: Проверяем запрос к базе данных на ошибки
	 * \param (PDO object): $query; Сам запрос
	 * \return true: Если запрос прошел без ошибок - true
	 */
	private static function CheckQueryError($query) {
		$error_info = $query->errorInfo();
		if ($error_info[0] !== PDO::ERR_NONE) {
			echo $error_info[2];
			exit;
		}

		return true;
	}
}

Database::Init();
?>