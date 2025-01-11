<?php

class Header {
	private static $output = '';
	private static $greeting = '';

	public static function Render($title, $row_name) {
		self::SetTimeGreetings();

		self::$output .= self::Head($title);
		self::$output .= self::Sidebar($row_name);
		self::$output .= self::DarkeringBackground();
		self::$output .= self::Greetings();

		return self::$output;
	}

	private static function SetTimeGreetings() {
		$morning = "Доброе утро";
		$afternoon = "Добрый день";
		$evening = "Добрый вечер";
		$night = "Доброй ночи";

		$hours = date("H");

		if ($hours >= 04 && $hours < 10) {self::$greeting = $morning; }
		else if ($hours >= 10 && $hours < 16) {self::$greeting = $afternoon; }
		else if ($hours >= 16 && $hours < 22) {self::$greeting = $evening; }
		else if ($hours >= 22 || $hours < 04) {self::$greeting = $night; }
	}

	private static function Head($title) {
		return '
		<!DOCTYPE html>
		<html>
		<head>
			<title>' . $title . '</title>
			<meta charset="UTF-8">
			<meta name="format-detection" content="telephone=no">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			
			<!-- Icons -->
			<link rel="stylesheet" href="frontend/icons/1.3.0/css/line-awesome.min.css">
			<!-- Flatpickr -->
			<link rel="stylesheet" href="utils/Flatpickr/flatpickr.min.css">
			<!-- Favicons -->
			<link rel="shortcut icon" href="media/favicon_ico.ico">
			<link rel="icon" type="image/icon" href="media/favicon.png" sizes="192x192">
			<link rel="apple-touch-icon" sizes="180x180" href="media/favicon_apple.png">
			<!-- CSS -->
			<link rel="stylesheet" href="frontend/css/style.css?id=' . filectime("frontend/css/style.css") . '">

			<!-- jQuery -->
			<script src="utils/jQuery/jquery-3.6.0.min.js"></script> 
			<!-- jQuery code -->
			<script src="frontend/script/jquery.js?id=' . filectime("frontend/script/jquery.js") . '"></script>
		</head>';
	}

	private static function Sidebar($file_name) {
		// Стоит задача выделить текущий каталог в sidebar отдельным цветом
		// Поэтому мы возьмем название текущего файла без расширения
		// И в массиве по этому файлу присвоим sidebar_item_active
		// Чтобы там где нужно вставить sidebar_item_active, а там где
		// Не надо выводить пустую строку
		$array = array(
			"index" => "",
			"tariffs" => "",
			"statistic" => "",
			"control" => "",
		);

		// Получаем имя файла, с которого была вызвана функция и обрезаем ".php"
		$catalog_name = strtok($file_name, '.');
		$array[$catalog_name] = "sidebar_item_active";

		return '
			<!-- Sidebar -->
			<nav class="sidebar">
				<div class="logo">
					<img src="media/logo2.svg"alt="Logo">
				</div>
				<ul class="sidebar_links">
					<li class="sidebar_item ' . $array["index"] . '">
						<a href="index.php">
							<i class="las la-wallet"></i>
							<span class="sidebar_item_text">Интернет</span>
						</a>
					</li>
					<li class="sidebar_item ' . $array["tariffs"] . '">
						<a href="tariffs.php">
							<i class="las la-exchange-alt"></i>
							<span class="sidebar_item_text">Тарифы</span>
						</a>
					</li>
					<li class="sidebar_item ' . $array["statistic"] . '">
						<a href="statistic.php">
							<i class="las la-history"></i>
							<span class="sidebar_item_text">Статистика</span>
						</a>
					</li>
					<li class="sidebar_item ' . $array["control"] . '">
						<a href="control.php">
							<i class="las la-tools"></i>
							<span class="sidebar_item_text">Управление</span>
						</a>
					</li>
					<li class="sidebar_item">
						<a href="../auth/logout.php">
							<i class="las la-door-open"></i>
							<span class="sidebar_item_text">Выход</span>
						</a>
					</li>
				</ul>
			</nav>
		';
	}

	private static function DarkeringBackground() {
		return '
		<div class="background"></div>';
	}

	private static function Greetings() {
		return '
		<div class="greetings">
			<div>
				<h2 class="main-color">'.$_SESSION['username'].'</h1>
				<h4>'.self::$greeting.', '.$_SESSION['fio'].'</h2>
			</div>
			<div class="burger">
				<div class="line1"></div>
				<div class="line2"></div>
				<div class="line3"></div>
			</div>
		</div>';
	}
}

?>