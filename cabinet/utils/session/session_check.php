<?php
require_once "session_init.php";

if (!isset($_SESSION['username']) || !isset($_SESSION['password'])) {
	header("Location: /");
	exit; 
}

?>