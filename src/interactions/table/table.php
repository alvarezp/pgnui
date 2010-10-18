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


?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

<? if ($columns !== NULL): ?>

<? 		if ($table_where !== ""): ?>

	<div class="global_parameters_show" id="global_parameters_show">
	<p>Only showing records relevant to <span class="global_parameters_list"><?= $global_parameters_friendly_comma_list ?></span>. <a href="global_parameters_chooser.php">[ Change ]</a></p>
	</div>

<? 		else: ?>

	<div class="global_parameters_show" id="global_parameters_show">
	<p>Not limiting records by relevance. <a href="global_parameters_chooser.php">[ Change ]</a></p>
	</div>

<? 		endif; ?>

<? endif; ?>

<? include("menu.php"); ?>

<h1><?= $table_pretty_name ?></h1>


<? if ($can_select): ?>
<table>
	<thead>
		<tr>
<? 	foreach((array) $table_columns as $c): ?>
			<th><? print $c['description'] == "" ? $c['column_name'] : $c['description']; ?></th>
<? 	endforeach; ?>
			<th></th>
		</tr>
	</thead>

	<tbody>
<? 	foreach($table_rows as $r): ?>
		<tr>
<?		require_once("func_rowid.php"); ?>
<?		$row_id = record_columns_to_rowid($table_columns, (array) $r); ?>
<? 		foreach($r as $col_name => $d): ?>

<?		$pretty_columns[$col_name]['control']->set_value_from_sql($d); ?>

			<td><?= $pretty_columns[$col_name]['control']->get_html_static("data[$row_id][columns][" . $col_name . "]") ?></td>
<? 		endforeach; ?>

<?		if ($can_update): ?>
			<td><a href="edit-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">Mod</a></td>
<?		endif; ?>

<?		if ($can_select): ?>
			<td><a href="view-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">View</a></td>
<?		endif; ?>

<?		if ($can_delete): ?>
			<td><a href="delete-record.php?schema=<?= $schema ?>&table=<?= $table ?>&rowid=<?= $row_id ?>">Del</a></td>
<?		endif; ?>

		<tr>
<? 	endforeach; ?>
	</tbody>

</table>
<? endif; ?>

<? if ($can_insert): ?>
<p>
	<a href="insert-record.php?schema=<?= $schema ?>&table=<?= $table ?>">Ins</a>
</p>
<? endif; ?>

</body>

</html>


