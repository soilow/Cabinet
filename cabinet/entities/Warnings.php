<?php

// Варнинги, которыем могут показываться в лк юзера. Такие как: "Внесите плату", "Вы заблокированы" и т.д.
class Warnings {
	public static $warnings_queue = array();

	public static function AddErrorWarning($message) {
		array_push(self::$warnings_queue, array("type" => "warning_error", "message" => $message));
	}

	public static function AddInfoWarning($message) {
		array_push(self::$warnings_queue, array("type" => "warning_info", "message" => $message));
	}
}

?>