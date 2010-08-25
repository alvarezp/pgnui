<?php

$default_interaction = "table";

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

require_once("func_get_table_list.php");

$schema = $_GET['schema'];
$table = $_GET['table'];

/* Al seleccionar una relación, en lugar de ir directo a table.php será
ahora a este archivo (enter.php) para que se consiga el nombre de la vista
que el usuario prefiere para la relación y usarla. */

$interaction = $default_interaction;

$res = pg_query_params($dbconn, "SELECT value FROM ___pgnui_settings WHERE name = 'last_table_interaction' AND parameter = $1;", array($schema . "." . $table));

if ($res) {
	$res_a = pg_fetch_all($res);
	print $res_a;
	print_r ($res_a);
	if ($res_a[0][0] != "") {
		$interaction = $res_a[0][0];
	}
}

header("Location: interactions/$interaction/$interaction.php?schema=$schema&table=$table");

?>

