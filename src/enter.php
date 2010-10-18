<?php

require_once("session.php");

require_once("func_get_table_list.php");

$schema = $_GET['schema'];
$table = $_GET['table'];

/* Al seleccionar una relación, en lugar de ir directo a table.php será
ahora a este archivo (enter.php) para que se consiga el nombre de la vista
que el usuario prefiere para la relación y usarla. */

$allowed_interactions = array();

$dir = "interactions/";

if (is_dir($dir)) {
    if ($dh = opendir($dir)) {
        while (($file = readdir($dh)) !== false) {
            if (is_dir($dir . $file) && substr($file, 0, 1) != ".") {
				include($dir . $file . "/check.php");
				$checkfunc = $file . "_check";
				$it_fits = $checkfunc($schema, $table);
				if ($it_fits) {
					array_push($allowed_interactions, $file);
				}
			}
        }
        closedir($dh);
    }
}

if (count($allowed_interactions) == 0) {
	print ("FATAL ERROR: No available interactions found $shema.$table!");
	return;
} else {
	$default_interaction = $allowed_interactions[0];
}


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

