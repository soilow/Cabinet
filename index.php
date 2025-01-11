<?php
require_once "cabinet/utils/session/session_init.php";

// Если у пользователя есть сессия
if (isset($_SESSION['username']) && isset($_SESSION['password'])) {
	// Вход через HotSpot
	if (isset($_GET['username']) && isset($_GET['password'])) {
		// Исключаем вход в лк по старой сессии, чтобы в кабинете появилась информация только по введенным данным через HotSpot
		if ($_SESSION['username'] == $_GET['username'] && 
			$_SESSION['password'] == $_GET['password']) {
			header("Location: cabinet/");
			exit;
		} else {
			session_unset();
		}
	} else {
		// Вход по сессии
		header("Location: cabinet/");
		exit;
	}
}

class RenderPage {
	public static function Header() {
		return '
		<!DOCTYPE html>
		<html lang="ru">
		<head>
			<meta charset="UTF-8">
			<meta name="viewport" content="width=device-width, initial-scale=1.0">
			<title>Авторизация</title>

			<!-- CSS -->
			<link rel="stylesheet" type="text/css" href="auth/style/style.css?id='.filectime("auth/style/style.css").'">
			<!-- Favicons -->
			<link rel="shortcut icon" href="cabinet/media/favicon_ico.ico">
			<link rel="icon" type="image/icon" href="cabinet/media/favicon.png" sizes="192x192">
			<link rel="apple-touch-icon" sizes="180x180" href="cabinet/media/favicon_apple.png">
		</head>
		<body>
			<form class="login_form" name="login" action="auth/login.php" method="POST">
				<input type="hidden" name="username" />
				<input type="hidden" name="password" />
				<input type="hidden" name="hotspot_flag" />
			</form>';
	}

	public static function LoginViaHotspot($username, $password) {
		return '
		<script>
			document.login.username.value = "' . htmlspecialchars($username) . '";
			document.login.password.value = "' . htmlspecialchars($password) . '";
			document.login.hotspot_flag.value = "1";
			document.login.submit();

			close();
		</script>
		';
	}

	public static function Form($error = '') {
		$error_html = $error ? '<div class="error-info"><p>' . htmlspecialchars($error) . '</p></div>' : '';

		return '
		<div class="wrapper">
				<div class="login-container">
					<img src="cabinet/media/logo.svg" alt="" class="logo">
					<form action="auth/login.php" method="POST" id="auth">
					' . $error_html . '
					<div class="inputs">
						<input type="tel" name="username" placeholder="Логин" class="input">
						<input type="password" name="password" placeholder="Пароль" class="input">
					</div>
				</form>
				<input type="submit" class="button" value="Войти" form="auth">
			</div>
		</div>
		';
	}

	public static function Footer() {
		return '
		</body>
		</html>';
	}
}

// Рендер страницы
echo RenderPage::Header();

if (isset($_GET['username']) && isset($_GET['password'])) {
	echo RenderPage::LoginViaHotspot($_GET['username'], $_GET['password']);
}

echo RenderPage::Form(isset($_GET['error']) ? $_GET['error'] : '');
echo RenderPage::Footer();

?>