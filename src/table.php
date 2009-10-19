<?php

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

$catalog = $_SESSION['database'];
$schema = $_GET[schema];
$table = $_GET[table];

require_once("func_table.php");

$table_rows = get_table_rows($dbconn, $catalog, $schema, $table);

if ($table_rows == FALSE) {
	$table_rows = array();
}

$table_columns = get_all_columns($dbconn, $catalog, $schema, $table);
$ref_constraints = columns_ref_constraint($dbconn, $catalog, $schema, $table);

require_once("func_get_table_list.php");

$tables = get_table_list($dbconn);

require_once("func_global_parameters.php");

$global_parameters = get_global_parameters_options($dbconn);

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>


<? if ($blogal_parameters !== NULL): ?>

	<p>Currently working with:</p>
	<form action="global_parameters_send.php" method="post">

<?	foreach ($global_parameters as $catalog => $c):
		foreach ($c as $schema => $s):
			foreach ($s as $table => $t):
				foreach ($t as $column => $col): ?>
		<p>
			<label for="<? print $column; ?>">
			<? print "$schema.$table.$column:"; ?> 
			</label>
			<select name="globals<? print "[$catalog][$schema][$table][$column]"; ?>">
				<option value="">(All)</option>
<?					foreach ($col[values] as $x => $v): ?>
				<option value="<? print $v; ?>"><? print $v; ?></option>
<?					endforeach; ?>
			</select>
		</p>
<?				endforeach;
			endforeach;
		endforeach;
	endforeach; ?>

		<input type="submit" value="Change global parameters">

	</form>

<? endif; ?>

<p> | 
<? foreach((array) $tables as $t): ?>
	<a href="?<? print $t[parameterstring]; ?>"><? print $t[pretty_name]; ?></a> | 
<? endforeach ?>
</p>

<table>
	<thead>
		<tr>
<? foreach((array) $table_columns as $c): ?>
			<th><? print $c[description] == "" ? $c[column_name] : $c[description]; ?></th>
<? endforeach; ?>
			<th></th>
		</tr>
	</thead>

	<tbody>
<? foreach($table_rows as $r): ?>
		<tr>
<? 		foreach($r as $col_name => $d): ?>
			<td><? print $d; ?></td>
<? 		endforeach; ?>
<?		require_once("func_rowid.php"); ?>
<?		$row_id = record_columns_to_rowid($table_columns, (array) $r); ?>
			<td><a href="edit-record.php?schema=<? print $schema; ?>&table=<? print $table; ?>&rowid=<? print $row_id; ?>">Mod</a></td>
			<td><a href="view-record.php?schema=<? print $schema; ?>&table=<? print $table; ?>&rowid=<? print $row_id; ?>">View</a></td>
			<td><a href="delete-record.php?schema=<? print $schema; ?>&table=<? print $table; ?>&rowid=<? print $row_id; ?>">Del</a></td>
		<tr>
<? endforeach; ?>
	</tbody>

</table>

<p>
	<a href="insert-record.php?schema=<? print $schema; ?>&table=<? print $table; ?>">Ins</a>
</p>

</body>

</html>


