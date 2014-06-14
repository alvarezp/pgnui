<?php

require_once("session.php");

require_once("func_get_table_list.php");

$tables = get_table_list($dbconn);

?>

<!DOCTYPE html>

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

<?php include("menu.php"); ?>

</body>

</html>

