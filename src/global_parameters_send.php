<?

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

function save_global_parameters($dbconn, $globals) {

	$t = pg_query_params($dbconn, "SELECT ___pgnui_functions.if_table_exists($1, $2, $3)", array($_SESSION['database'], $_SESSION['username'], '___pgnui_global_parameters_values'));
	$r = pg_fetch_result($t, 0, 0);

	if ($r != 't') {
		pg_query($dbconn, "CREATE TABLE ___pgnui_global_parameters_values (LIKE ___pgnui_user_settings_skel.global_parameters_values INCLUDING CONSTRAINTS)");
	}

	foreach ($globals as $catalog => $c):
		foreach ($c as $schema => $s):
			foreach ($s as $table => $t):
				foreach ($t as $column => $value):
					$_SESSION['globals'][$catalog][$schema][$table][$column] = $value;
					$updated = pg_query_params($dbconn, "UPDATE ___pgnui_global_parameters_values SET value = $5 WHERE catalog_name = $1 AND schema_name = $2 AND table_name = $3 AND column_name = $4", array($catalog, $schema, $table, $column, $value));
					if (pg_num_rows($updated) == 0) {
						$selected = pg_query_params($dbconn, "SELECT * FROM ___pgnui_global_parameters_values WHERE catalog_name = $1 AND schema_name = $2 AND table_name = $3 AND column_name = $4", array($catalog, $schema, $table, $column));
						if (pg_num_rows($selected) == 0) {
							$inserted = pg_query_params($dbconn, "INSERT INTO ___pgnui_global_parameters_values (catalog_name, schema_name, table_name, column_name, value) VALUES ($1, $2, $3, $4, $5)", array($catalog, $schema, $table, $column, $value));
						}
					}

				endforeach;
			endforeach;
		endforeach;
	endforeach;

}

save_global_parameters($dbconn, $_POST[globals]);

header ('Location: mainmenu.php');

?>
