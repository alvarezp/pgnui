<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once("session.php");

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

<? include("menu.php"); ?>

<h1>Insert new record</h1>

<form action="submit-new-record.php" method="POST">
	<input type="hidden" name="catalog" value="<?= $catalog ?>"></input>
	<input type="hidden" name="schema" value="<?= $schema ?>"></input>
	<input type="hidden" name="table" value="<?= $table ?>"></input>
	<!-- ROW START -->
	<input type="hidden" name="data[0][rowid]" value=""></input>
<?		foreach ($pretty_columns as $key => $column): ?>
	<!-- COLUMN START -->
	<div>
		<label class="insert" for="data[0][columns][<?= $key ?>]"><?= $pretty_columns[$key]['pretty_name'] ?>: </label><?=$pretty_columns[$key]['control']->get_html_editable("data[0][columns][" . $key . "]"); ?>
	</div>
	<!-- COLUMN END -->
<?		endforeach; ?>
	<!-- ROW END -->
	<input type="submit">
</form>

</body>

</html>
