<?php

require_once "utils/session/session_check.php";
require_once "database/database.php";

require_once "page_renders/header.php";
require_once "page_renders/footer.php";

class StatisticRender {
	private static $output = '';

	public static function Render() {
		self::$output .= self::MainContainerRender();
		self::$output .= self::LoadingWindowRender();
		self::$output .= self::ModulesRender();

		echo self::$output;
	}

	private static function MainContainerRender() {
		return '
		<main>
			<div class="main_container">
				<div class="cell_heading statistic_heading">
					<h4>Подключения</h4>
					<div class="cell_heading_submenu">
						<input class="date" placeholder="Выберите дату...">
					</div>
				</div>
				<div class="brief_table">
					<div>
						<h5 class="table_heading">Общая информация</h5>
					</div>
					<div class="scrollable">
						<table class="common_table statistic_table">
							<thead>
								<th>Подключений</th>
								<th>Входящий трафик</th>
								<th>Исходящий трафик</th>
								<th>Суммарный трафик</th>
							</thead>
							<tbody id="brief-container">
							</tbody>
						</table>
					</div>
				</div>
				<div class="detailed_table">
					<div>
						<h5 class="table_heading">Подробная информация</h5>
					</div>
					<div class="scrollable">
						<table class="common_table statistic_table">
							<thead>
								<th>Подключение</th>
								<th>Отключение</th>
								<th>Время</th>
								<th>Входящий трафик</th>
								<th>Исходящий трафик</th>
								<th>Суммарный трафик</th>
								<th>IP и MAC</th>
							</thead>
							<tbody id="detailed-container">
							</tbody>
						</table>
					</div>
				</div>
				<div class="statistic_table_footer table_end">
					<div class="pagination_container">
					</div>
					<div class="statistic_table_explanation">
						<div>
							<div class="square expanation_unlim"></div>
							<p> - Подключения в ночной безлимит</p>
						</div>
						<div>
							<div class="square expanation_active"></div>
							<p> - Активные подключения</p>
						</div>
					</div>
				</div>
			</div>
		</main>';
	}

	private static function LoadingWindowRender() {
		return '
		<div class="loader_container">
			<div class="loader"></div>
			<h3>Загрузка...</h3>
		</div>';
	}

	private static function ModulesRender() {
		return '
		<!-- jQuery code -->
		<script src="frontend/script/statistic.js?id='.filectime("frontend/script/statistic.js").'"></script>
		<!-- Flatpickr module -->
		<script src="frontend/script/flatpickr.js"></script>
		<!-- Localization -->
		<script src="frontend/script/flatpickr_ru.js"></script>
		<!-- Converting to PHP date format module -->
		<script src="frontend/script/php-date-formatter.min.js"></script>';
	}
}


echo Header::Render("Статистика", basename(__FILE__));
echo StatisticRender::Render();
echo Footer::Render();

?>