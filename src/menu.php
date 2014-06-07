<?php
require_once("func_database.php");

$dbname = get_best_name_for_this_database($dbconn);
?>

<h1><?=$dbname?></h1>

<p> | 
<?php foreach((array) $tables as $t): ?>
	<a href="/enter.php?<?= $t['parameterstring'] ?>"><?= $t['pretty_name'] ?></a> | 
<?php endforeach ?>
</p>

