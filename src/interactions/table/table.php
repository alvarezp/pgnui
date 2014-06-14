<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once("session.php");

$catalog = $_SESSION['database'];
$schema = $_GET['schema'];
$table = $_GET['table'];
$sort = isset($_GET['sort']) ? $_GET['sort'] : "";

require_once("func_table.php");

require_once("func_sort_to_order.php");

$table_where = get_table_where($dbconn, $catalog, $schema, $table);
$table_rows = get_table_rows_where_order($dbconn, $catalog, $schema, $table, $table_where, sort_to_order($sort));

if ($table_rows == FALSE) {
	$table_rows = array();
}

$table_columns = get_all_columns($dbconn, $catalog, $schema, $table);
$ref_constraints = columns_ref_constraint($dbconn, $catalog, $schema, $table);

require_once("func_get_table_list.php");

$tables = get_table_list($dbconn);

$thistable_parameterstring = "schema=" . urlencode($schema) . "&table=" . urlencode($table);

require_once("func_global_parameters.php");

$columns = get_global_parameters_options($dbconn);

if ($columns !== NULL) {
	$global_parameters_friendly_comma_list = get_current_global_parameters_friendly_comma_list($dbconn);
}

function remove_column_from_sort($sort, $column) {
	$s = ";" . trim($sort, ";") . ";";
	if ($s == ";;") {
		return array("", "", "", FALSE, 0);
	}
	$search = ";" . $column . ":";
	$search_len = strlen($search);
	$col_location = strpos($s, $search);
	if ($col_location === FALSE) {
		return array($sort, "", "", FALSE, 0);
	}
	$col_location = $col_location + 1;
	$col_length = $search_len - 2;
	$order_location = $col_location + $col_length + 1;
	$order_length = strpos($s, ";", $col_location + $col_length + 1) - ($col_location + $col_length + 1);
	$return_new = trim(substr($s, 0, $col_location) . substr($s, $order_location + $order_length + 1), ";");
	$return_column = trim(substr($s, $col_location, $col_length), ";:");
	$return_order = trim(substr($s, $order_location, $order_length), ";:");

	return array($return_new, $return_column, $return_order, $col_location == 1, substr_count($sort, ";", 0, $col_location));
}

function invert_sort_direction($string) {
	return $string == "asc" ? "desc" : "asc";
}

function original_or_asc($string) {
	return $string == "" ? "asc" : $string;
}

foreach ($table_columns as $k => $c) {
	# sort=col1:asc;col2:desc
	$cleaned_sort = remove_column_from_sort($sort, $c['column_name']);
	if ($cleaned_sort[3]) {
		$new_direction = invert_sort_direction($cleaned_sort[2]);
	} else {
		$new_direction = original_or_asc($cleaned_sort[2]);
	}
	$table_columns[$k]['new_sortparam'] =  trim($c['column_name'] . ':' . $new_direction . ';' . $cleaned_sort[0], ";");
	$table_columns[$k]['sort_state_dir'] = $cleaned_sort[2];
	$table_columns[$k]['sort_state_order'] = $cleaned_sort[4] + 1;
	$table_columns[$k]['sort_state_html'] = $cleaned_sort[2] == "desc" ? "&nbsp;<span class='sub'>" . $table_columns[$k]['sort_state_order'] . "</span>" : ($cleaned_sort[2] == "asc" ? "&nbsp;<span class='sup'>" . $table_columns[$k]['sort_state_order'] . "</span>" : "");
}

$table_pretty_name = get_best_name_for_table($dbconn, $schema, $table);

require_once("record-read.php");

$pretty_columns = get_columns_as_keys($dbconn, $catalog, $schema, $table);

require_once("func_privileges.php");
$can_insert = has_table_privilege($dbconn, $schema . "." . $table, 'INSERT');
$can_update = has_table_privilege($dbconn, $schema . "." . $table, 'UPDATE');
$can_select = has_table_privilege($dbconn, $schema . "." . $table, 'SELECT');
$can_delete = has_table_privilege($dbconn, $schema . "." . $table, 'DELETE');

$parents_with_insert_privs = array();
$referenced_tables = get_tables_referenced_by_this($dbconn, $catalog, $schema, $table);
if ($referenced_tables != FALSE) foreach ($referenced_tables as $rt) {
	$rt_schema = $rt['ref_schema'];
	$rt_table = $rt['ref_table'];
	if (has_table_privilege($dbconn, $rt_schema . "." . $rt_table, 'INSERT')) {
		$parent_to_insert = array();
		$parent_to_insert['pretty_name'] = get_best_name_for_table($dbconn, $rt_schema, $rt_table);
		$parent_to_insert['schema'] = $rt_schema;
		$parent_to_insert['table'] = $rt_table;
		array_push($parents_with_insert_privs, $parent_to_insert);
	}
}

$parents = $parents_with_insert_privs;

?>
<!DOCTYPE html>

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
	<style type="text/css">
		.sup { font-size: 70%; vertical-align: top; }
		.sub { font-size: 70%; vertical-align: bottom; }
	</style>
</head>

<body>

<?php if (($columns !== NULL) and (count($columns) > 0)): ?>

<?php 		if ($table_where !== ""): ?>

	<div class="global_parameters_show" id="global_parameters_show">
	<p>Only showing records relevant to <span class="global_parameters_list"><?= $global_parameters_friendly_comma_list ?></span>. <a href="/global_parameters_chooser.php">[ Change ]</a></p>
	</div>

<?php 		else: ?>

	<div class="global_parameters_show" id="global_parameters_show">
	<p>Not limiting records by relevance. <a href="/global_parameters_chooser.php">[ Change ]</a></p>
	</div>

<?php 		endif; ?>

<?php endif; ?>

<?php include("menu.php"); ?>

<main class="table-interaction">

<h1><?= $table_pretty_name ?></h1>

<?php if ($can_select): ?>
	<table>
		<thead>
			<tr>
<?php 	foreach((array) $table_columns as $c): ?>
				<th><a href="?<?= $thistable_parameterstring . '&sort=' . $c['new_sortparam'] ?>"><?= $c['description'] == "" ? preg_replace("/_/", " ", ucfirst($c['column_name'])) : $c['description'] ?></a><?= $c['sort_state_html'] ?></th>
<?php 	endforeach; ?>
				<th colspan="4"><a href="?<?= $thistable_parameterstring ?>">(Clear sort)</a></th>
			</tr>
		</thead>

		<tbody>
<?php 	foreach($table_rows as $r): ?>
			<tr>
<?php		require_once("func_rowid.php"); ?>
<?php		$row_id = record_columns_to_rowid($table_columns, (array) $r); ?>
<?php 		foreach($r as $col_name => $d): ?>

<?php		$pretty_columns[$col_name]['control']->set_value_from_sql($d); ?>

				<td><?= $pretty_columns[$col_name]['control']->get_html_static("data[$row_id][columns][" . $col_name . "]") ?></td>
<?php 		endforeach; ?>

<?php		if ($can_update): ?>
				<td><a href="edit-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">Mod</a></td>
<?php		endif; ?>

<?php		if ($can_select): ?>
				<td><a href="view-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">View</a></td>
<?php		endif; ?>

<?php		if ($can_delete): ?>
				<td><a href="delete-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">Del</a></td>
<?php		endif; ?>

<?php		if ($can_delete): ?>
				<td><a href="duplicate-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">Sim</a></td>
<?php		endif; ?>

			<tr>
<?php 	endforeach; ?>
		</tbody>

	</table>
<?php endif; ?>

<?php if ($can_insert): ?>
	<p>
		<a href="insert-record.php?schema=<?= $schema ?>&table=<?= $table ?>">Ins</a>
	</p>
<?php endif; ?>

</main>

<?php if (count($parents) > 0): ?>
<h1>Referenced tables</h1>
<?php 	foreach($parents as $p): ?>
	<p><a href="/enter.php?schema=<?= $p['schema'] ?>&table=<?= $p['table'] ?>"><?= $p['pretty_name'] ?></a></p>
<?php	endforeach; ?>
<?php endif ?>
</body>

</html>


