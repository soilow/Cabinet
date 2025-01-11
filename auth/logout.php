<?php
require_once "../cabinet/utils/session/session_init.php";

session_destroy();

header("Location: /");
exit;

?>