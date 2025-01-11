<?php

require_once "utils/session/session_check.php";
require_once "database/database.php";

require_once "page_renders/header.php";
require_once "page_renders/footer.php";

class ControlRender {
	private static $output = '';

	public static function Render() {
		self::$output .= self::MainContainerOpenRender();
		self::$output .= self::PasswordChangingRender();
		self::$output .= self::PhoneChangingRender();
		self::$output .= self::MoneyExchangeRender();
		self::$output .= self::MainContainerCloseRender();
		self::$output .= self::RenderPopupWindow();
		self::$output .= self::JqueryCode();

		echo self::$output;
	}

	private static function MainContainerOpenRender() {
		return '
		<main class="grid control_grid">
			<div>';
	}

	private static function PasswordChangingRender() {
		$to_print = '';

		$to_print .= '
			<div class="cell_heading">
				<h4>Изменить пароль</h4>
			</div>
			<div class="control_warnings">';

		if (isset($_GET['pass_error']))
			$to_print .= '
				<div class="warning warning_error">
					<i class="las la-exclamation-circle"></i>
					<h5>'.$_GET['pass_error'].'</h5>
				</div>';
		else if (isset($_GET['pass_success']))
			$to_print .= '
				<div class="warning warning_success">
					<i class="las la-exclamation-circle"></i>
					<h5>'.$_GET['pass_success'].'</h5>
				</div>';

		$to_print .= '
					<div class="warning control_warning">
						<i class="las la-exclamation-circle"></i>
						<h5>Пароль может состоять только из букв латинского алфавита и цифр. Максимальная длина пароля — 15 символов</h5>
					</div>
				</div>
				<form action="dynamic/change_password.php" method="POST" class="control_form" id="password_form">
					<div>
						<h5>Старый пароль</h5>
						<input type="password" name="old_password" class="input_type" onkeypress="realtime_validate(event, \'alnum\')">
					</div>
					<div>
						<h5>Новый пароль</h5>
						<input type="password" name="new_password" class="input_type" onkeypress="realtime_validate(event, \'alnum\')">
					</div>
					<div>
						<h5>Повторите новый пароль</h5>
						<input type="password" name="new_password_confirm" class="input_type" onkeypress="realtime_validate(event, \'alnum\')">
					</div>
				</form>
				<input type="submit" value="Сохранить" class="accept_button primary_button" form="password_form">
			</div>';

		return $to_print;
	}

	private static function PhoneChangingRender() {
		$to_print = '';

		$to_print .= '
			<div>
				<div class="cell_heading">
					<h4>Изменить номер телефона</h4>
				</div>
				<div class="control_warnings">';

		if (isset($_GET['phone_error']))
			$to_print .= '
				<div class="warning warning_error">
					<i class="las la-exclamation-circle"></i>
					<h5>'.$_GET['phone_error'].'</h5>
				</div>';
		else if (isset($_GET['phone_success']))
			$to_print .= '
				<div class="warning warning_success">
					<i class="las la-exclamation-circle"></i>
					<h5>'.$_GET['phone_success'].'</h5>
				</div>';

		$to_print .= '
					<div class="warning control_warning">
						<i class="las la-exclamation-circle"></i>
						<h5>Изменится номер, на который приходят смс с текущим балансом. Логин останется прежним</h5>
					</div>
				</div>
				<form action="dynamic/change_phone.php" method="POST" class="control_form" id="phone_form">
					<div>
						<h5>Старый телефон</h5>
						<div class="input_type fake_input">
							<div class="country_code">
								<span>+7</span>
							</div>
							<input type="tel" name="old_phone" class="input_type num_input" onkeypress="realtime_validate(event, \'num\')">
						</div>
					</div>
					<div>
						<h5>Новый телефон</h5>
						<div class="input_type fake_input">
							<div class="country_code">
								<span>+7</span>
							</div>
							<input type="tel" name="new_phone" class="input_type num_input" onkeypress="realtime_validate(event, \'num\')">
						</div>
					</div>
				</form>
				<input type="submit" value="Сохранить" class="accept_button primary_button" form="phone_form">
			</div>';

		return $to_print;
	}

	private static function MoneyExchangeRender() {
		$to_print = '';

		$to_print .= '
		<div class="money_exchange_container">
			<div class="cell_heading">
				<h4>Перевести деньги</h4>
			</div>
			<div class="control_warnings">';


		$to_print .= '
			<div class="warning warning_error money_error_warning" style="display: none">
				<i class="las la-exclamation-circle"></i>
				<h5 class="money_error_text"></h5>
			</div>';

		$to_print .= '
			<div class="warning warning_success money_success_warning" style="display: none">
				<i class="las la-exclamation-circle"></i>
				<h5 class="money_success_text"></h5>
			</div>';

		$to_print .= '
				<div class="warning control_warning">
					<i class="las la-exclamation-circle"></i>
					<h5>Вы можете перевести все средства с вашего депозита или часть другому абоненту. Логин абонента должен быть номером без 8 или +7. Сумма должна быть целым неотрицательным числом
					<br>
					<br>
					Пример:</h5>
				</div>
			</div>';

		$to_print .= '
			<div class="control_form">
				<div>
					<h5>Логин абонента</h5>
					<input type="tel" name="abonent_login" class="input_type" onkeypress="realtime_validate(event, \'num\')">
				</div>
				<div>
					<h5>Сумма</h5>
					<input type="tel" id="money_input" name="money" class="input_type" onkeypress="realtime_validate(event, \'num\')">
				</div>
			</div>
			<input type="submit" value="Перевести" class="primary_button money_exchange_button">';

		$to_print .= '
		</div>';

		return $to_print;
	}

	private static function RenderPopupWindow() {
		return '
		<div class="popup_container">
			<div class="popup_window popup_available">
				<i class="las la-ruble-sign"></i>
				<h3>Перевод</h3>
				<h5>Перевод на сумму <span class="span_summa"></span> рублей абоненту <span class="span_fio"></span></h5>
				<div class="popup_buttons">
					<input class="cancel_button secondary_button" type="submit" value="Отмена">
					<input class="accept_button primary_button" id="money_accept_button" type="submit" value="Перевести">
				</div>
			</div>
		</div>';
	}

	private static function MainContainerCloseRender() {
		return '			
			</div>
		</main>';
	}

	private static function JqueryCode() {
		return '<script src="frontend/script/money.js?id='.filectime("frontend/script/money.js").'"></script>';
	}
}



echo Header::Render("Управление", basename(__FILE__));
echo ControlRender::Render();
echo Footer::Render($validator_flag=1);

?>