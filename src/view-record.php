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
$rowid = $_GET[rowid]; # string for record should be "(field1,field2...)(value1,value2)"

require_once("record-read.php");

$record = read_record($dbconn, $catalog, $schema, $table, $rowid);
$pretty_columns = get_columns_as_keys($dbconn, $catalog, $schema, $table);


require_once("func_get_table_list.php");

$tables = get_table_list($dbconn);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

<p> | 
<? foreach((array) $tables as $t): ?>
	<a href="table.php?<? print $t[parameterstring]; ?>"><? print $t[pretty_name]; ?></a> | 
<? endforeach ?>
</p>

<!--<pre>
<? print_r ($record); ?>
<? print_r ($pretty_columns); ?>
</pre>
-->

	<!-- ROW START -->
<?		foreach ($record as $key => $column): ?>
	<!-- COLUMN START -->
	<div>
	<?		$pretty_columns[$key][control]->set_value_from_sql($column); ?>
	<?		print $pretty_columns[$key][pretty_name] . ": ". $pretty_columns[$key][control]->get_html_static("data[0][columns][" . $key . "]"); ?>
	</div>
	<!-- COLUMN END -->
<?		endforeach; ?>
	<!-- ROW END -->

</body>

</html>
