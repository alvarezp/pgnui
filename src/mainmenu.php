<?php

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

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
	<a href="/enter.php?<? print $t['parameterstring']; ?>"><? print $t['pretty_name']; ?></a> | 
<? endforeach ?>
</p>

</body>

</html>
