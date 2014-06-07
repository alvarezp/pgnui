<?php

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: /index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

?>
