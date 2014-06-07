<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once("session.php");

$catalog = $_SESSION['database'];
$schema = $_GET['schema'];
$table = $_GET['table'];

require_once("func_table.php");

$table_where = get_table_where($dbconn, $catalog, $schema, $table);
$table_rows = get_table_rows_where($dbconn, $catalog, $schema, $table, $table_where);

if ($table_rows == FALSE) {
	$table_rows = array();
}

$table_columns = get_all_columns($dbconn, $catalog, $schema, $table);
$ref_constraints = columns_ref_constraint($dbconn, $catalog, $schema, $table);

require_once("func_get_table_list.php");

$tables = get_table_list($dbconn);

require_once("func_global_parameters.php");

$columns = get_global_parameters_options($dbconn);

if ($columns !== NULL) {
	$global_parameters_friendly_comma_list = get_current_global_parameters_friendly_comma_list($dbconn);
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
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

<?php if ($columns !== NULL): ?>

<?php 		if ($table_where !== ""): ?>

	<div class="global_parameters_show" id="global_parameters_show">
	<p>Only showing records relevant to <span class="global_parameters_list"><?= $global_parameters_friendly_comma_list ?></span>. <a href="global_parameters_chooser.php">[ Change ]</a></p>
	</div>

<?php 		else: ?>

	<div class="global_parameters_show" id="global_parameters_show">
	<p>Not limiting records by relevance. <a href="global_parameters_chooser.php">[ Change ]</a></p>
	</div>

<?php 		endif; ?>

<?php endif; ?>

<?php include("menu.php"); ?>

<h1><?= $table_pretty_name ?></h1>


<?php if ($can_select): ?>
<table>
	<thead>
		<tr>
<?php 	foreach((array) $table_columns as $c): ?>
			<th><?php print $c['description'] == "" ? $c['column_name'] : $c['description']; ?></th>
<?php 	endforeach; ?>
			<th></th>
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

<?php if (count($parents) > 0): ?>
<h1>Referenced tables</h1>
<?php 	foreach($parents as $p): ?>
	<p><a href="/enter.php?<?= $t['parameterstring'] ?>"><?= $t['pretty_name'] ?></a></p>
<?php	endforeach; ?>
<?php endif ?>
</body>

</html>


