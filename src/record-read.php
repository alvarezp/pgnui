<?php


function read_record_where($dbconn, $catalog, $schema, $table, $where) {
	return pg_fetch_array(pg_query($dbconn, "SELECT * FROM $schema.$table WHERE $where"), 0, PGSQL_ASSOC);
}

function read_record($dbconn, $catalog, $schema, $table, $rowid) {

	require_once("rowid.php");
	$where = rowid_to_where($rowid);

	return read_record_where($dbconn, $catalog, $schema, $table, $where);

}


function get_columns_as_keys($dbconn, $catalog, $schema, $table) {
	$fields = pg_query_params($dbconn, "SELECT * FROM information_schema.columns WHERE table_schema = $1 AND table_name = $2 ORDER BY ordinal_position;", array($schema, $table));

	if ($fields) {
		while ($field = pg_fetch_array($fields)) {

			if ($field[data_type] == "boolean") {
				require_once("html_control_checkbox.php");
				$pretty_columns[$field[column_name]][control] = new HtmlControlCheckbox;
			} else if ($field[data_type] == "character varying") {
				require_once("html_control_textbox.php");
				$pretty_columns[$field[column_name]][control] = new HtmlControlTextbox;
			} else if ($field[data_type] == "USER-DEFINED" && $field[udt_name] == "cryptmd5") {
				require_once("html_control_password.php");
				$pretty_columns[$field[column_name]][control] = new HtmlControlPassword;
			} else {
				require_once("html_control_textbox.php");
				$pretty_columns[$field[column_name]][control] = new HtmlControlTextbox;
			}

			$pretty_columns[$field[column_name]][pretty_name] = $field[column_name];
			$descriptions = pg_query_params($dbconn, "SELECT col_description('${schema}.${table}'::regclass, (SELECT ordinal_position FROM information_schema.columns WHERE table_schema = $1 AND table_name = $2 AND column_name = $3));", array($schema, $table, $field[column_name]));

			if ($descriptions) {
				$description = pg_fetch_array($descriptions);
				if ($description[0]) {
					$pretty_columns[$field[column_name]][pretty_name] = $description[0];
				}
			}

		}
	}

	if ($columns_str == "") $columns_str = "*";
	$rows = pg_query_params($dbconn, "SELECT $columns_str FROM ${schema}.${table};", array());

	return $pretty_columns;

}


?>
