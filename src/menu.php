<?php
require_once("func_database.php");

$dbname = get_best_name_for_this_database($dbconn);
?>

<header>
	<h1><span><?=$dbname?></span></h1>

	<nav>
		<h2>Relations</h2>
		<ul>
<?php foreach((array) $tables as $t): ?>
			<li><a href="/enter.php?<?= $t['parameterstring'] ?>"><?= $t['pretty_name'] ?></a></li>
<?php endforeach ?>
		</ul>
	</nav>

</header>
