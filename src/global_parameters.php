<?

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

function get_global_parameters_options($dbconn) {

	$columns_global_parameters = pg_query_params($dbconn, "SELECT DISTINCT * FROM ___pgnui_column_reference_tree.global_parameters()", array());

	while ($g = pg_fetch_array($columns_global_parameters)):
		$columns[$g[catalog_name]][$g[schema_name]][$g[table_name]][$g[column_name]][values] = array();

		$values = pg_query_params($dbconn, "SELECT DISTINCT $g[column_name] FROM $g[schema_name].$g[table_name];", array());

		$x = 0;
		while ($v = pg_fetch_array($values)):
			$columns[$g[catalog_name]][$g[schema_name]][$g[table_name]][$g[column_name]][values][$x] = $v[0];
			$x++;
		endwhile;
	endwhile;

	return $columns;

}

?>
