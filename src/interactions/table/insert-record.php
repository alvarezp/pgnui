<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

$catalog = $_SESSION['database'];
$schema = $_GET['schema'];
$table = $_GET['table'];

require_once("record-read.php");

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

<h1>Menu</h1>

<p> | 
<? foreach((array) $tables as $t): ?>
	<a href="table.php?<? print $t['parameterstring']; ?>"><? print $t['pretty_name']; ?></a> | 
<? endforeach ?>
</p>

<h1>Insert new record</h1>

<form action="submit-new-record.php" method="POST">
	<input type="hidden" name="catalog" value="<? print $catalog; ?>"></input>
	<input type="hidden" name="schema" value="<? print $schema; ?>"></input>
	<input type="hidden" name="table" value="<? print $table; ?>"></input>
	<!-- ROW START -->
	<input type="hidden" name="data[0][rowid]" value=""></input>
<?		foreach ($pretty_columns as $key => $column): ?>
	<!-- COLUMN START -->
	<div>
	<?		print $pretty_columns[$key]['pretty_name'] . ": ". $pretty_columns[$key]['control']->get_html_editable("data[0][columns][" . $key . "]"); ?>
	</div>
	<!-- COLUMN END -->
<?		endforeach; ?>
	<!-- ROW END -->
	<input type="submit">
</form>

</body>

</html>
