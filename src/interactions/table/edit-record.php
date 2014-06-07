<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once("session.php");

$catalog = $_SESSION['database'];
$schema = $_GET['schema'];
$table = $_GET['table'];
$rowid = $_GET['rowid']; # string for record should be "(field1,field2...)(value1,value2)"

require_once("record-read.php");

$record = read_record($dbconn, $catalog, $schema, $table, $rowid);
$pretty_columns = get_columns_as_keys($dbconn, $catalog, $schema, $table);


require_once("func_get_table_list.php");
require_once("func_table.php");

$tables = get_table_list($dbconn);

$children = get_tables_referencing_this($dbconn, $catalog, $schema, $table);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

<?php include("menu.php"); ?>

<!--<pre>
<?php print_r ($record); ?>
<?php print_r ($pretty_columns); ?>
</pre>
-->

<form action="submit-record.php" method="POST">
	<input type="hidden" name="catalog" value="<?= $catalog ?>"></input>
	<input type="hidden" name="schema" value="<?= $schema ?>"></input>
	<input type="hidden" name="table" value="<?= $table ?>"></input>
	<!-- ROW START -->
	<input type="hidden" name="data[0][rowid]" value="<?= $rowid ?>"></input>
<?php		foreach ($record as $key => $column): ?>
	<!-- COLUMN START -->
	<div>
<?php		$pretty_columns[$key]['control']->set_value_from_sql($column); ?>
		<label class="update" for="data[0][columns][<?= $key ?>]"><?= $pretty_columns[$key]['pretty_name'] ?>: </label><?= $pretty_columns[$key]['control']->get_html_editable("data[0][columns][" . $key . "]"); ?>
	</div>
	<!-- COLUMN END -->
<?php		endforeach; ?>
	<!-- ROW END -->
	<input type="submit">

<?php
	if (($children !== FALSE) && (count($children) >= 2)):
		$prevtable = NULL;
		foreach ($children as $c => $column):
			$thistable = get_best_name_for_table($dbconn, $column["rc_schema"], $column["rc_table"]);
			$thistablelen = strlen($thistable);
			if ($prevtable == NULL) {
				$prevtable = $thistable;
				$prevtablelen = $thistablelen;
				$lowestlen = $thistablelen;
				$highestlen = $thistablelen;
				continue;
			}
			if ($thistablelen < $lowestlen) {
				$lowestlen = $thistablelen;
			}
			for ($x = 0; $x < $lowestlen; $x++) {
				if ($thistable[$thistablelen-$x] != $prevtable[$prevtablelen-$x]) {
					break;
				} else {
					if ($thistable[$thistablelen-$x] == " ") {
						$lastword = $x;
					}
				}
			}
			if ($x < $lowestlen) {
				$lowestlen = $x;
			}

			if ($thistablelen > $highestlen) {
				$highest = $thistablelen;
			}
			for ($x = 0; $x < $highestlen; $x++) {
				if ($thistable[$x] != $prevtable[$x]) {
					break;
				} else {
					if ($thistable[$x] == " ") {
						$firstword = $x;
					}
				}
			}
			if ($x > $highest) {
				$lowestlen = $x;
			}
			
			$prevtable = $thistable;
			$prevtablelen = $thistablelen;
		endforeach;
	endif;
?>


<?php	if (($children !== FALSE) && (count($children) > 0)): ?>
	<p>For this record, add a new:
<?php		foreach ($children as $c => $column): ?>
	<a href="insert-record.php?schema=<?= $column["rc_schema"] ?>&table=<?= $column["rc_table"] ?>"><?= substr(get_best_name_for_table($dbconn, $column["rc_schema"], $column["rc_table"]),$firstword,-$lastword) ?></a></a> |
<?php		endforeach; ?>
	</p>
<?php	endif; ?>

	
</form>

</body>

</html>
