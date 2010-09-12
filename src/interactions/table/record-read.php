<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');


function read_record_where($dbconn, $catalog, $schema, $table, $where) {
	return pg_fetch_array(pg_query($dbconn, "SELECT * FROM $schema.$table WHERE $where"), 0, PGSQL_ASSOC);
}

function read_record($dbconn, $catalog, $schema, $table, $rowid) {

	require_once("func_rowid.php");
	$where = rowid_to_where($rowid);

	return read_record_where($dbconn, $catalog, $schema, $table, $where);

}


function get_columns_as_keys($dbconn, $catalog, $schema, $table) {
	$fields = pg_query_params($dbconn, "SELECT * FROM information_schema.columns WHERE table_schema = $1 AND table_name = $2 ORDER BY is_nullable ASC, ordinal_position ASC;", array($schema, $table));

	if ($fields) {
		while ($field = pg_fetch_array($fields)) {

			require_once("func_table.php");

			$refs = pg_fetch_all(pg_query_params("SELECT c_name, rc_table, rc_column, ref_column, ref_schema, ref_table FROM ___pgnui_column_reference_tree.column_reference_tree WHERE rc_catalog = $1 AND rc_schema = $2 AND rc_table = $3 AND rc_column = $4", array($catalog, $schema, $table, $field['column_name'])));

			$r = $refs[0];

			if ($field['data_type'] == "boolean") {
				require_once("html_control_checkbox.php");
				$pretty_columns[$field['column_name']]['control'] = new HtmlControlCheckbox;
			} else if ($field['data_type'] == "character varying") {
				require_once("html_control_textbox.php");
				$pretty_columns[$field['column_name']]['control'] = new HtmlControlTextbox;
			} else if ($field['data_type'] == "USER-DEFINED" && $field['udt_name'] == "cryptmd5") {
				require_once("html_control_password.php");
				$pretty_columns[$field['column_name']]['control'] = new HtmlControlPassword;
			} else {
				require_once("html_control_textbox.php");
				$pretty_columns[$field['column_name']]['control'] = new HtmlControlTextbox;
			}

			if (count($r) > 0) {
				require_once("html_control_dropdown.php");
				$pretty_columns[$field['column_name']]['control'] = new HtmlControlDropdown;
				$values = pg_fetch_all(pg_query("SELECT " . $r['ref_column'] . " FROM " . $r['ref_schema'] . "." . $r['ref_table'] . " " . get_table_where($dbconn, $catalog, $r['ref_schema'], $r['ref_table']) . ";"));
				$pretty_columns[$field['column_name']]['control']->set_option_list($values);
			}

			$pretty_columns[$field['column_name']]['pretty_name'] = $field['column_name'];
			$descriptions = pg_query_params($dbconn, "SELECT col_description('${schema}.${table}'::regclass, (SELECT ordinal_position FROM information_schema.columns WHERE table_schema = $1 AND table_name = $2 AND column_name = $3));", array($schema, $table, $field['column_name']));

			if ($descriptions) {
				$description = pg_fetch_array($descriptions);
				if ($description[0]) {
					$pretty_columns[$field['column_name']]['pretty_name'] = $description[0];
				}
			}

			$pretty_columns[$field['column_name']]['required'] = $field['is_nullable'];

		}
	}

	/*if ($columns_str == "") */
	$columns_str = "*";
	$rows = pg_query_params($dbconn, "SELECT $columns_str FROM ${schema}.${table};", array());

	return $pretty_columns;

}


?>
