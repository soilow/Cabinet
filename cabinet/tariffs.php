<?php
require_once "utils/session/session_check.php";
require_once "database/database.php";

require_once "page_renders/header.php";
require_once "page_renders/footer.php";

require_once "entities/Packets.php";

class TariffsRender {
	private static $output = '';
	public static $users_gid;

	public static function Init() {
		self::CurrentUserPacket();
		TariffFormating::Init();
	}

	public static function Render() {
		self::$output .= self::ContainerRender();
		self::$output .= self::TableRender();
		self::$output .= self::PopupRender();
		self::$output .= self::JqueryCode();

		echo self::$output;
	}

	private static function CurrentUserPacket() {
		$data = Database::Query($fetch=true, "SELECT gid FROM users WHERE uid = {$_SESSION['uid']}");
		self::$users_gid = $data['gid'];
	}

	private static function ContainerRender() {
		return '
		<main>
			<div class="main_container">
				<div class="table_cell_heading">
					<h4>Тарифы</h4>
				</div>
				<div class="scrollable">';
	}

	private static function TableRender() {
		$to_print = '';

		$to_print = '
		<table class="common_table selectible">
						<thead>
							<tr>
								<th>Тариф</th>
								<th>Макс. скорость<sup>1</sup><br>(кбит/сек)</th>
								<th>Стоимость<br>(руб/месяц)</th>
								<th>Объём трафика<br>(мб)</th>
								<th>1 мб доп. трафика<br>(руб)</th>
								<th>Ночной безлимит<sup>2</sup><br>(кбит/сек)</th>
							</tr>
						</thead>
						<tbody>';

		foreach (TariffFormating::$packets_queue as $packet) {
			$to_print .= $packet;
		}

		$to_print .= '
						</tbody>
					</table>
				</div>
				<div class="table_end">
					<h6><sup>1</sup> Скорости указанные на всех тарифах являются максимальными. Реальная скорость интернета зависит от загруженности сети, а также от естественных условий распространения радиоволн</h6>
					<h6><sup>2</sup> Ночной безлимит действует с 04:00 до 10:00 часов (Камчатское время)</h6>
				</div>
			</div>
		</main>';

		return $to_print;
	}

	private static function PopupRender() {
		return '
			<div class="popup_container">
				<div class="popup_window popup_available">
					<i class="las la-exchange-alt"></i>
					<h3 style="margin-top: 10px;">Переход</h3>
					<h5>Вы действительно хотите перейти на тариф <span class="popup_show_packet"></span>?</h5>
					<div class="popup_buttons">
						<input class="cancel_button secondary_button" type="submit" value="Отмена">
						<input class="accept_button primary_button" type="submit" value="Перейти">
					</div>
				</div>
				<div class="popup_window popup_unavailable">
					<i class="las la-exclamation-triangle"></i>
					<h5>Переход возможен только со следующего месяца</h5>
					<input class="cancel_button primary_button" type="submit" value="Закрыть">
				</div>
			</div>
		';
	}

	private static function JqueryCode(){
		return '<script src="frontend/script/packets.js?id='.filectime("frontend/script/packets.js").'"></script>';
	}
}

class TariffFormating {
	public static $packets_queue = array();
	private static $packets;

	public static function Init() {
		foreach (Packets::$PACKETS as $packet) {
			if ($packet['prefix'] != "pub")
				continue;

			$packet_html = '';

			// Row
			if ($packet['gid'] == TariffsRender::$users_gid)
				$packet_html .= '<tr class="packet_active">';
			else
				$packet_html .= '<tr data-gid="'.$packet['gid'].'" data-packet="'.$packet['packet'].'">';

			// Packet name and speed
			$packet_html .= '
				<td data-label="Тариф">'.$packet['packet'].'</td>
				<td data-label="Скорость">20000/10000</td>
			';

			// Cost and month limit
			if ($packet['fixed_cost'] == 0 and $packet['month_traffic_limit'] == 0) {
				$packet_html .= '<td data-label="Стоимость">—</td>';
				$packet_html .= '<td data-label="Трафик">—</td>';
			} else {
				$packet_html .= '<td data-label="Стоимость">'.sprintf("%.0f", $packet['fixed_cost']).'</td>';
				$packet_html .= '<td data-label="Трафик">'.$packet['month_traffic_limit'].'</td>';
			}

			// Price per MB
			$packet_html .= '<td data-label="1 мб доп. трафика">'.sprintf("%.2f", $packet['price_per_mb']).'</td>';

			// Night unlim speed
			if ($packet['speed_night_unlim'] == 0)
				$packet_html .= '<td data-label="Ночной безлимит">—</td>';
			else
				$packet_html .= '<td data-label="Ночной безлимит">'.$packet['speed_night_unlim'].'</td>';

			array_push(self::$packets_queue, $packet_html);
		}
	}
}


TariffsRender::Init();

echo Header::Render("Тарифы", basename(__FILE__));
echo TariffsRender::Render();
echo Footer::Render();

?>