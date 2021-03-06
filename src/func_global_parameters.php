<?php

function get_global_parameters_options($dbconn) {

	$columns_global_parameters = pg_query_params($dbconn, "SELECT DISTINCT * FROM ___pgnui_column_reference_tree.global_parameters()", array());

	$columns = array();

	while ($g = pg_fetch_array($columns_global_parameters)):
		$columns[$g['catalog_name']][$g['schema_name']][$g['table_name']][$g['column_name']]['values'] = array();

		$values = pg_query_params($dbconn, "SELECT DISTINCT $g[column_name] FROM $g[schema_name].$g[table_name];", array());

		$x = 0;
		while ($v = pg_fetch_array($values)):
			$columns[$g['catalog_name']][$g['schema_name']][$g['table_name']][$g['column_name']]['values'][$x] = $v[0];
			$x++;
		endwhile;
	endwhile;

	return $columns;

}

function get_current_global_parameters($dbconn) {

	$t = pg_query_params($dbconn, "SELECT ___pgnui_functions.if_table_exists($1, $2, $3)", array($_SESSION['database'], $_SESSION['username'], '___pgnui_global_parameters_values'));
	$r = pg_fetch_result($t, 0, 0);

	if ($r != 't') {
		pg_query($dbconn, "CREATE TABLE ___pgnui_global_parameters_values (LIKE ___pgnui_user_settings_skel.global_parameters_values INCLUDING CONSTRAINTS)");
	}

	$columns_global_parameters = pg_query($dbconn, "SELECT * FROM ___pgnui_global_parameters_values;");

	return pg_fetch_all($columns_global_parameters);

}

function get_current_global_parameters_friendly_comma_list($dbconn) {

	$gp = get_current_global_parameters($dbconn);

	$ret = "";

	$count = 0;
	if (is_array($gp)) {
		foreach($gp as $param):
			if ($count == 0) {
				$ret = $ret . $param['value'];
			} else {
				$ret = $ret . ", " . $param['value'];
			}
			$count++;
		endforeach;
	}

	return $ret;

}


?>
