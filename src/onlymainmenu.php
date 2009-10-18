<?php

session_start();

$dbconn = pg_connect($_SESSION['connstr']);

require_once("mainmenu.php");

$tables = get_table_list($dbconn);

?>

<html>

<body>

<p> | 
<? foreach((array) $tables as $t): ?>
	<a href="table3.php?<? print $t[parameterstring]; ?>"><? print $t[pretty_name]; ?></a> | 
<? endforeach ?>
</p>

</body>

</html>
