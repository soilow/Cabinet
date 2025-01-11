<?php

require_once "utils/session/session_check.php";
require_once "database/database.php";

require_once "page_renders/header.php";
require_once "page_renders/footer.php";

require_once "entities/UserInfo.php";
require_once "entities/Warnings.php";

// Класс собирает и формирует информацию о тарифе пользователя, остатке трафика, скорости ночного безлимита
class UserPacketManager {
	public static $packet;
	public static $night_unlim_availability;
	public static $price_per_mb;
	public static $fixed_cost;
	public static $month_traffic_limit;
	public static $night_unlim;
	public static $speed_night_unlim;
	public static $remaining_traffic;
	public static $color_for_display;

	public static function Init() {
		$data = Database::Query($fetch=true, 'SELECT packet, night_unlim, price_per_mb, fixed_cost, month_traffic_limit, night_unlim, speed_night_unlim FROM packets WHERE gid = '.UserDataCollector::$gid.'');

		self::$packet = $data['packet'];
		self::$night_unlim_availability = $data['night_unlim'];
		self::$price_per_mb = $data['price_per_mb'];
		self::$fixed_cost = $data['fixed_cost'];
		self::$month_traffic_limit = $data['month_traffic_limit'];
		self::$night_unlim = $data['night_unlim'];
		self::$speed_night_unlim = self::FormatUnlimSpeed($data['speed_night_unlim']);

		// Ставим остаток трафика равным нулю, если он заблокирован
		if (UserDataCollector::$blocked == 1 || (UserDataCollector::$regular_payment == 0 && UserDataCollector::$gid != 16)) {
			self::$remaining_traffic = 0;
		}
		// Иначе выводим остаток трафика
		else {
			$data2 = Database::Query($fetch=false, 'CALL RemainingUserTraffic('.$_SESSION['uid'].', @traffic)');
			$data3 = Database::Query($fetch=true, 'SELECT @traffic as traffic');

			self::$remaining_traffic = $data3['traffic'];
		}

		self::CheckRegularPayment();
	}

	private static function CheckRegularPayment() {
		if (self::$fixed_cost > 0 && UserDataCollector::$regular_payment == 0) {
			if (UserDataCollector::$deposit >= self::$fixed_cost) {
				Warnings::AddInfoWarning("Тариф оплачен. Подключитесь к интернету - деньги за тариф снимутся автоматически");
			} else {
				if (UserDataCollector::$deposit < 0) {
					$to_pay = ceil(self::$fixed_cost + abs(UserDataCollector::$deposit));
				} else {
					$to_pay = sprintf("%.0f", self::$fixed_cost);
				}

				Warnings::AddErrorWarning("Тариф не оплачен. Отправьте СМС на номер 900 с сообщением «Перевод ... ".$to_pay."», оплатите через мобильный банк или пополните счет через терминалы оплаты");
			}

			self::$color_for_display = "disable";
		} else {
			self::$color_for_display = "main-color";
		}
	}

	private static function FormatUnlimSpeed($speed) {
		if ($speed == "0") {
			return "0";
		}

		preg_match("/Mikrotik-Rate-Limit=*([^\n]*),/", $speed, $temp);
		$temp = str_replace('k', '', $temp[1]);
		$temp = str_replace('M', '000', $temp);

		// Меняем входящую и исходящую скорость местами
		$temp2 = explode('/', $temp);

		return $temp2[1].'/'.$temp2[0];
	}
}

// Класс собирает статистику по трафику за сегодня
class StatisticDataCollector {
	public static $in_bytes;
	public static $out_bytes;
	public static $unlim_statistic;
	public static $summary;

	public static function Init() {
		$statistic_data = Database::Query($fetch=true, 'SELECT SUM(in_bytes) AS in_bytes, SUM(out_bytes) AS out_bytes, SUM(CASE WHEN actions.address_list = 2 THEN in_bytes + out_bytes ELSE 0 END) AS unlim FROM actions WHERE user = "'.$_SESSION['username'].'" AND year(start_time) = year(now()) AND month(start_time) = month(now()) AND day(start_time) = day(now())');

		self::$in_bytes = $statistic_data['in_bytes'] / Database::BytesToMb();
		self::$out_bytes = $statistic_data['out_bytes'] / Database::BytesToMb();
		self::$unlim_statistic = $statistic_data['unlim'] / Database::BytesToMb();
		self::$summary = (self::$in_bytes + self::$out_bytes);
	}
}

// Класс формирует таблицу "Онлайн"
class OnlineTable {
	private static $connections_queue = array();
	private static $number_of_active_connections;

	public static function Init() {
		if (!self::CheckOnlineConnections()) {
			return;
		}

		self::FormatConnectionsQueue();
	}

	// Выводим все подключения
	public static function PrintConnections() {
		$to_print = '';

		foreach (self::$connections_queue as $connection)
			$to_print .= $connection;

		return $to_print;
	}

	public static function GetActiveConnections() {
		return self::$number_of_active_connections;
	}

	// Формируем очередь из соединений и форматируем вывыод
	private static function FormatConnectionsQueue() {
		// Вставляем в таблицу первым домашний Wi-Fi, если он есть
		self::InsertHomeKitConnection();

		// Собираем информацию о всех активных подключениях под этим аккаунтом
		$online = Database::Query($fetch=false, "SELECT call_from, in_bytes, out_bytes FROM online WHERE user = '{$_SESSION['username']}'");

		// Вносим в таблицу все подключения
		while ($row = $online->fetch()) {
			// Если мак подключения равен маку домашнего Wi-Fi, то скипаем его
			if ($row['call_from'] == UserDataCollector::$mac_of_users_homekit) {
				continue;
			}

			$traffic = sprintf("%.2f", ($row['in_bytes'] + $row['out_bytes']) / Database::BytesToMb());

			// Получаем имя хоста
			$host_query = Database::Query($fetch=true, "SELECT host FROM wi_fi_clients WHERE mac = '{$row['call_from']}'");
			$host = $host_query['host'];

			// Выбираем иконку для подключения
			$icon = self::SelectIconForConnection($host);

			if ($host == NULL) {
				self::$connections_queue[] = '
					<tr>
						<td class="device"><i class="las la-question"></i>Неизвестное устройство</td>
						<td>'.$row['call_from'].'</td>
						<td>'.$traffic.'мб</td>
					</tr>
				';
			} else {
				self::$connections_queue[] = '
					<tr>
						<td class="device">'.$icon.''.$host.'</td>
						<td>'.$row['call_from'].'</td>
						<td>'.$traffic.'мб</td>
					</tr>
				';
			}
		}
	}

	// Получаем информацию о домашнем комплекте пользователя
	private static function InsertHomeKitConnection() {
		if (UserDataCollector::$is_user_has_homekit) {
			$traffic = Database::Query($fetch=true, 'SELECT in_bytes, out_bytes FROM online WHERE call_from = "'.UserDataCollector::$mac_of_users_homekit.'"');

			// Если домашний Wi-Fi не в онлайне
			if (!$traffic) {
				return;
			}

			$traffic_format = sprintf("%.2f", ($traffic['in_bytes'] + $traffic['out_bytes']) / Database::BytesToMb());

			self::$connections_queue[] = '
				<tr>
					<td class="device"><i class="las la-wifi"></i>Домашний Wi-Fi</td>
					<td>'.UserDataCollector::$mac_of_users_homekit.'</td>
					<td>'.$traffic_format.'мб</td>
				</tr>
			';
		}
	}

	private static function SelectIconForConnection($host) {
		$icon = '<i class="las la-mobile"></i>';

		// Tablets
		if (preg_match("/(tab|pad)/i", $host)) {
			$icon = '<i class="las la-tablet"></i>';
		}
		// Desktops and laptops
		if (preg_match("/(desktop|laptop|book|pc|pk|win|comp|mbp)/i", $host)) {
			$icon = '<i class="las la-desktop"></i>';
		}

		return $icon;
	}

	private static function CheckOnlineConnections() {
		$connections = Database::Query($fetch=true, 'SELECT COUNT(gid) as counts FROM online WHERE user = "'.$_SESSION['username'].'"');

		if ($connections['counts'] == 0) {
			return false;
		}

		self::$number_of_active_connections = $connections['counts'];

		return true;
	}
}

// Основной класс, который занимается рендерингом и отрисовкой страницы. Также запускает в работу все остальные классы
class DashboardRender {
	private static $output = '';

	public static function Init() {
		UserPacketManager::Init();
		StatisticDataCollector::Init();
		OnlineTable::Init();
	}

	public static function Render() {
		self::$output .= self::RenderWarnings();
		self::$output .= self::RenderDeposit();
		self::$output .= self::RenderCredit();
		self::$output .= self::RenderPacket();
		self::$output .= self::RenderStatistic();
		self::$output .= self::RenderNightUnlim();
		self::$output .= self::RenderOnlineTable();
		self::$output .= self::RenderPopupWindow();

		return self::$output;
	}

	private static function RenderWarnings() {
		$to_print = '';

		if (empty(Warnings::$warnings_queue)) {
			return '';
		}

		$to_print .= '<div class="warnings">';

		foreach (Warnings::$warnings_queue as $warning) {
			$to_print .= '<div class="warning '.$warning['type'].'">';

			switch ($warning['type']) {
				case 'warning_error':
					$to_print .= '<i class="las la-exclamation-circle"></i>';
					break;

				case 'warning_info':
					$to_print .= '<i class="las la-info-circle"></i>';
					break;

				default:
					break;
			}

			$to_print .= '
					<h5>'.$warning['message'].'</h5>
				</div>
			';
		}

		$to_print .= '</div>';

		return $to_print;
	}

	private static function RenderDeposit() {
		return '
		<main class="grid">
			<div class="finance">
				<div>
					<h5 class="money">Баланс</h5>
					<h1 class="important">'.UserDataCollector::FormatDeposit().'</h1>
				</div>
				<div class="finance_icon">
					<i class="las la-ruble-sign"></i>
				</div>
			</div>';
	}

	private static function RenderCredit() {
		if (UserDataCollector::$credit > 0) {
			return '
			<div class="finance">
				<div>
					<h5 class="money">Задолженность</h5>
					<h1 class="important credit">'.UserDataCollector::FormatCredit().'</h1>
				</div>
				<div class="finance_icon">
					<i class="las la-receipt credit"></i>
				</div>
			</div>';
		} else {
			return '
			<div class="finance debt">
				<div>
					<h5 class="money">Задолженность</h5>
					<h1 class="important disable">0.00</h1>
				</div>
				<div class="finance_icon">
					<i class="las la-receipt disable"></i>
				</div>
			</div>';
		}
	}

	private static function RenderPacket() {
		return '
		<div class="finance">
			<div>
				<h5 class="money">Тариф</h5>
				<h1 class="important">' . UserPacketManager::$packet . '</h1>
				<h5>Остаток<span class="traffic_words"> трафика</span>: <span class="traffic '.UserPacketManager::$color_for_display.'">'.number_format(UserPacketManager::$remaining_traffic, 0, ".", " ").' мб</span></h5>
			</div>
			<div class="finance_icon">
				<i class="las la-folder '.UserPacketManager::$color_for_display.'"></i>
			</div>
		</div>';
	}

	private static function RenderStatistic() {
		return '
		<div class="statistic">
			<div class="cell_heading">
				<h4>Статистика</h4>
				<div class="cell_heading_submenu">
					<button class="dropdown_button">
						<i class="las la-calendar submenu_icon"></i>
						<h5>День</h5>
						<i class="las la-angle-down submenu_icon"></i>
					</button>
					<ul class="dropdown_list">
						<li class="dropdown_list_item dropdown_item_active" data-value="day">День</li>
						<li class="dropdown_list_item" data-value="month">Месяц</li>
						<li class="dropdown_list_item" data-value="year">Год</li>
					</ul>
					<input type="text" class="dropdown_input_hidden">
				</div>
			</div>
			<div class="statistic_data">
				<div>
					<i class="las la-long-arrow-alt-down"></i>
					<div class="data_container main-color">
						<h1 class="dynamic_data">'.number_format(StatisticDataCollector::$out_bytes, 0, '.', ' ').'</h1>
						<h1>мб</h1>
					</div>
					<h6>Входящий трафик</h6>
				</div>
				<div>
					<i class="las la-long-arrow-alt-up"></i>
					<div class="data_container main-color">
						<h1 class="dynamic_data">'.number_format(StatisticDataCollector::$in_bytes, 0, '.', ' ').'</h1>
						<h1>мб</h1>
					</div>
					<h6>Исходящий трафик</h6>
				</div>
				<div>
					<i class="las la-cloud-showers-heavy"></i>
					<div class="data_container main-color">
						<h1 class="dynamic_data">'.number_format(StatisticDataCollector::$unlim_statistic, 0, '.', ' ').'</h1>
						<h1>мб</h1>
					</div>
					<h6>Ночной трафик</h6>
				</div>
				<div>
					<i class="las la-sync"></i>
					<div class="data_container main-color">
						<h1 class="dynamic_data">'.number_format(StatisticDataCollector::$summary, 0, '.', ' ').'</h1>
						<h1>мб</h1>
					</div>
					<h6>Скачено всего</h6>
				</div>
			</div>
		</div>';
	}

	private static function RenderNightUnlim() {
		if (UserPacketManager::$night_unlim_availability) {
			if (UserDataCollector::$regular_payment == 0) {
				return '
					<div class="night_unlim unavailable">
						<i class="las la-cloud-moon disable"></i>
						<h3>Ночной безлимит</h3>
						<h5 class="light info">Ночной безлимит станет доступен после оплаты тарифа</span></h5>
					</div>
				';
			} else if (UserDataCollector::$control_unlim == 0) {
				return '
				<div class="night_unlim">
					<div>
						<i class="las la-cloud-moon disable"></i>
						<h3>Ночной безлимит</h3>
						<h5 class="light info">Отключено</h5>
					</div>
					<input class="accept_button primary_button" type="submit" value="Включить" id="control_unlim" data-action="1">
				</div>
				';
			} else {
				return '
					<div class="night_unlim">
						<div>
							<i class="las la-cloud-moon"></i>
							<h3>Ночной безлимит</h3>
							<h5 class="light info">Доступно с 4:00 по 10:00<br>Скорость: '.UserPacketManager::$speed_night_unlim.' Кб/c</h5>
						</div>
						<input class="accept_button primary_button" type="submit" value="Отключить" id="control_unlim" data-action="0">
					</div>
				';
			}
		} else {
			return '
				<div class="night_unlim unavailable">
					<i class="las la-cloud-moon disable"></i>
					<h3>Ночной безлимит</h3>
					<h5 class="light info">Доступно на тарифах:<br><span>Астра 2700, Астра 3600, Астра 5200</span></h5>
				</div>
			';
		}
	}

	private static function RenderOnlineTable() {
		$to_print = '';

		$to_print .= '
		<div class="online">
			<div class="cell_heading">
				<h4>Онлайн</h4>
				<div class="online_explanation">
					<p>?</p>
				</div>
				<div class="online_explanation_window">
					<p>Таблица "Онлайн" показывает кто прямо сейчас подключен под вашим аккаунтом. В таблице показано имя устройства, его MAC-адрес и сколько было скачано трафика с этого устройства</p>
				</div>
			</div>';

		if (OnlineTable::GetActiveConnections()) {
			$to_print .= '
				<div class="scrollable">
					<table class="online_table">
						<thead>
							<th>Устройство</th>
							<th>MAC</th>
							<th>Скачено</th>
						</thead>
						<tbody>
			';

			$to_print .= OnlineTable::PrintConnections();

			$to_print .= '
						</tbody>
					</table>
				</div>
			';
		} else {
			$to_print .= '
			<div class="online_table_empty">
				<i class="las la-link"></i>
				<h5 class="light">Нет активных подключений</h5>
			</div>';
		}


		$to_print .= '
		</div>';

		$to_print .= '
		</main>';

		return $to_print;
	}

	private static function RenderPopupWindow() {
		return '
		<div class="popup_container">
			<div class="popup_window popup_available">
				<i class="las la-question-circle"></i>
				<h3>Подтверждение</h3>
				<h5>Вы действительно хотите <span class="unlim_disable">отключить</span><span class="unlim_enable">включить</span> ночной безлимит?</h5>
				<div class="popup_buttons">
					<input class="cancel_button secondary_button" type="submit" value="Отмена">
					<input class="accept_button primary_button" id="unlim_button" type="submit" value="Включить">
				</div>
			</div>
		</div>';
	}
}


DashboardRender::Init();

echo Header::Render("Личный кабинет", basename(__FILE__));
echo DashboardRender::Render();
echo Footer::Render();