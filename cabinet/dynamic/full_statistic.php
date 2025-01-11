<?php

require_once "../utils/session/session_check.php";
require_once $_SESSION['path']."/cabinet/database/database.php";
require_once $_SESSION['path']."/cabinet/utils/validator/validator.php";
require_once $_SESSION['path']."/cabinet/entities/Packets.php";

class PostData {
	public static $data_to_send = array("pages" => "", "brief" => "", "detailed" => "");
	public static $from;
	public static $to;

	public static function Init() {
		self::CheckInputData();
	}

	private static function CheckInputData() {
		if (!(self::$from = Validator::Validate($_POST['from'], "sql_date")) ||
			!(self::$to = Validator::Validate($_POST['to'], "sql_date"))) {

			echo "Ошибка";
			exit;
		}
	}
}

class Pagination {
	private static $page = 1;
	private static $number_of_pages;
	public static $rows_per_page = 20;
	public static $page_sql;

	private static $pointer;

	public static function Init() {
		self::CheckInputData();
		self::SetPageSql();
		self::GetNumberOfPages();
		self::RenderBeginOfPagination();
		self::RenderMiddleOfPagination();
		self::RenderEndOfPagination();
	}

	private static function CheckInputData() {
		if (isset($_POST['page']) && !self::$page = Validator::Validate($_POST['page'], "num")) {

			echo "Ошибка";
			exit;
		}
	}

	private static function SetPageSql() {
		self::$page_sql = (self::$page-1) * self::$rows_per_page;
	}

	private static function GetNumberOfPages() {
		$data = Database::Query($fetch=false, "SELECT gid FROM actions WHERE user = '{$_SESSION['username']}' AND start_time BETWEEN '".PostData::$from."' AND '".PostData::$to."'");

		self::$number_of_pages = ceil($data->rowCount() / self::$rows_per_page);
	}

	private static function RenderBeginOfPagination() {
		if (self::$page - 5 >= 0) {
			self::$pointer = self::$page - 2;
			PostData::$data_to_send['pages'] .= '<a data-page="1">1</a>';
			PostData::$data_to_send['pages'] .= '<p>...</p>';
		}
	}

	private static function RenderMiddleOfPagination() {
		for (; self::$pointer <= self::$page + 2 && self::$pointer <= self::$number_of_pages; self::$pointer++) {
			if (self::$pointer == self::$page)
				PostData::$data_to_send['pages'] .= '<a class="pagination_active">'.self::$pointer.'</a>';
			else
				PostData::$data_to_send['pages'] .= '<a data-page="'.self::$pointer.'">'.self::$pointer.'</a>';
		}
	}

	private static function RenderEndOfPagination() {
		// Если страниц больше, чем 10
		if (self::$pointer != self::$number_of_pages + 1) {
			PostData::$data_to_send['pages'] .= '<p>...</p>';
			PostData::$data_to_send['pages'] .= '<a data-page="'.self::$number_of_pages.'">'.self::$number_of_pages.'</a>';
		}
	}
}

class BriefStatistic {
	public static function Render() {
		$brief = Database::Query($fetch=true, "SELECT COUNT(gid) AS quantity, SUM(in_bytes) AS in_bytes, SUM(out_bytes) AS out_bytes FROM actions WHERE user = '{$_SESSION['username']}' AND start_time BETWEEN '".PostData::$from."' AND '".PostData::$to."'");

		$brief['in_bytes'] = sprintf("%.0f", $brief['in_bytes'] / Database::BytesToMb());
		$brief['out_bytes'] = sprintf("%.0f", $brief['out_bytes'] / Database::BytesToMb());
		$total_bytes = $brief['in_bytes'] + $brief['out_bytes'];

		PostData::$data_to_send['brief'] .= '
			<tr>
				<td>'.$brief['quantity'].'</td>
				<td>'.number_format($brief['out_bytes'], 0, '.', ' ').'мб</td>
				<td>'.number_format($brief['in_bytes'], 0, '.', ' ').'мб</td>
				<td>'.number_format($total_bytes, 0, '.', ' ').'мб</td>
			</tr>
		';
	}
}

class DetailedStatistic {
	public static function Render() {
		$detailed = Database::Query($fetch=false, "SELECT gid, start_time, stop_time, time_on, in_bytes, out_bytes, call_from, address_list FROM actions WHERE user = '{$_SESSION['username']}' AND start_time BETWEEN '".PostData::$from."' AND '".PostData::$to."' LIMIT ".Pagination::$page_sql.", ".Pagination::$rows_per_page);

		while ($session = $detailed->fetch()) {
			// Formating data
			$session['time_on'] = date("G:i:s", mktime(0, 0, $session['time_on']));
			$session['in_bytes'] = $session['in_bytes'] / Database::BytesToMb();
			$session['out_bytes'] = $session['out_bytes'] / Database::BytesToMb();
			$total_bytes = $session['in_bytes'] + $session['out_bytes'];

			// Selecting session class
			if ($session['stop_time'] == "0000-00-00 00:00:00")
				PostData::$data_to_send['detailed'] .= '<tr class="active_session">';
			else if (Packets::IsPacketNightUnlim($session['gid']) && 
					 Packets::IsSessionNightUnlim($session['address_list']))

				PostData::$data_to_send['detailed'] .= '<tr class="unlim_session">';
			else
				PostData::$data_to_send['detailed'] .= '<tr>';

			// Adding start time
			PostData::$data_to_send['detailed'] .= '<td>'.$session['start_time'].'</td>';

			// If sesssion is active, print "Активно"
			if ($session['stop_time'] == "0000-00-00 00:00:00")
				PostData::$data_to_send['detailed'] .= '<td>Активно</td>';
			else
				PostData::$data_to_send['detailed'] .= '<td>'.$session['stop_time'].'</td>';

			PostData::$data_to_send['detailed'] .= '
				<td>'.$session['time_on'].'</td>
				<td>'.sprintf("%.2f", $session['out_bytes']).'</td>
				<td>'.sprintf("%.2f", $session['in_bytes']).'</td>
				<td>'.sprintf("%.2f", $total_bytes).'</td>
				<td>'.$session['call_from'].'</td>
			';
			PostData::$data_to_send['detailed'] .= '</tr>';
		}
	}
}


PostData::Init();
Pagination::Init();
BriefStatistic::Render();
DetailedStatistic::Render();

echo json_encode(PostData::$data_to_send);

?>