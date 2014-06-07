<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

function get_table_where($dbconn, $catalog, $schema, $table, $prepend = 'WHERE ') {

	$columns_global_parameters = pg_query_params($dbconn, "SELECT * FROM ___pgnui_column_reference_tree.table_column_globals($1, $2, $3);", array($catalog, $schema, $table));

	$where = '(';
	while ($gc = pg_fetch_array($columns_global_parameters)):

		if ($_SESSION['globals'][$gc['dep_catalog_name']][$gc['dep_schema_name']][$gc['dep_table_name']][$gc['dep_column_name']] != "") {
			if ($where != '(') $where .= ' AND ';
			$where = $where . '("' . $gc[column_name] . '" = \'' . $_SESSION['globals'][$gc['dep_catalog_name']][$gc['dep_schema_name']][$gc['dep_table_name']][$gc['dep_column_name']] . '\')';
		}
	endwhile;
	if ($where == '(') {
		$where = '';
	} else {
		$where = $prepend . $where . ')';
	}

	return $where;

}

function get_table_rows($dbconn, $catalog, $schema, $table) {

# RETURN: $columns
# 1. column_name
# 2. description

# RETURN: $rows
# 1. edit_link
# 2. columns:
#    a. value.

	$where = get_table_where($dbconn, $catalog, $schema, $table);

	$rows = pg_query_params($dbconn, "SELECT * FROM $schema.$table $where;", array());

	$all_rows = pg_fetch_all($rows);

	return $all_rows;

}

function get_table_rows_where($dbconn, $catalog, $schema, $table, $where) {

# RETURN: $columns
# 1. column_name
# 2. description

# RETURN: $rows
# 1. edit_link
# 2. columns:
#    a. value.

	$rows = pg_query_params($dbconn, "SELECT * FROM $schema.$table $where;", array());

	$all_rows = pg_fetch_all($rows);

	return $all_rows;

}

function get_all_columns($dbconn, $catalog, $schema, $table) {
	$all_columns = pg_query_params($dbconn, "SELECT c.table_catalog, c.table_schema, c.table_name, c.column_name, c.ordinal_position, pk.ordinal_position AS position_in_pk, col_description('${schema}.${table}'::regclass, (SELECT ordinal_position FROM information_schema.columns AS lcolumns WHERE table_schema = $1 AND table_name = $2 AND column_name = c.column_name)) AS description FROM information_schema.columns AS c LEFT JOIN ___pgnui_column_reference_tree.primary_keys AS pk USING (table_catalog, table_schema, table_name, column_name) WHERE c.table_schema = $1 AND c.table_name = $2 ORDER BY c.ordinal_position", array($schema, $table));

	return pg_fetch_all($all_columns);

}

function columns_ref_constraint($dbconn, $catalog, $schema, $table) {
	$columns_ref_constraint = pg_query_params($dbconn, "SELECT * FROM ___pgnui_column_reference_tree.column_foreign_key_references_table($1, $2, $3) WHERE (rc_catalog, rc_schema, rc_table, rc_column) NOT IN (SELECT catalog_name, schema_name, table_name, column_name FROM ___pgnui_column_reference_tree.table_column_globals($1, $2, $3)) ORDER BY constraint_catalog, constraint_schema, constraint_name;", array($catalog, $schema, $table));

	return pg_fetch_all($columns_ref_constraint);
}

function get_tables_referencing_this($dbconn, $catalog, $schema, $table) {
	$columns_ref_constraint = pg_query_params($dbconn, "SELECT * FROM ___pgnui_column_reference_tree.column_foreign_key_references WHERE ref_catalog = $1 AND ref_schema = $2 AND ref_table = $3;", array($catalog, $schema, $table));

        return pg_fetch_all($columns_ref_constraint);

}

function get_tables_referenced_by_this($dbconn, $catalog, $schema, $table) {
	$columns_ref_constraint = pg_query_params($dbconn, "SELECT DISTINCT ref_schema, ref_table FROM ___pgnui_column_reference_tree.column_foreign_key_references WHERE rc_catalog = $1 AND rc_schema = $2 AND rc_table = $3;", array($catalog, $schema, $table));

        return pg_fetch_all($columns_ref_constraint);

}

?>


