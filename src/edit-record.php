<?php

session_start();

$dbconn = pg_connect($_SESSION['connstr']);

$catalog = $_SESSION['database'];
$schema = $_GET[schema];
$table = $_GET[table];
$rowid = $_GET[rowid]; # string for record should be "(field1,field2...)(value1,value2)"

print "<pre>" . $rowid . "</pre>";

require_once("record-read.php");

$record = read_record($dbconn, $catalog, $schema, $table, $rowid);
$pretty_columns = get_columns_as_keys($dbconn, $catalog, $schema, $table);


require_once("mainmenu.php");

$tables = get_table_list($dbconn);

?>

<p> | 
<? foreach((array) $tables as $t): ?>
	<a href="table3.php?<? print $t[parameterstring]; ?>"><? print $t[pretty_name]; ?></a> | 
<? endforeach ?>
</p>

<!--<pre>
<? print_r ($record); ?>
<? print_r ($pretty_columns); ?>
</pre>
-->

<form action="submit-record.php" method="POST">
	<input type="hidden" name="catalog" value="<? print $catalog; ?>"></input>
	<input type="hidden" name="schema" value="<? print $schema; ?>"></input>
	<input type="hidden" name="table" value="<? print $table; ?>"></input>
	<!-- ROW START -->
	<input type="hidden" name="data[0][rowid]" value="<? print $rowid; ?>"></input>
<?		foreach ($record as $key => $column): ?>
	<!-- COLUMN START -->
	<div>
	<?		$pretty_columns[$key][control]->set_value_from_sql($column); ?>
	<?		print $pretty_columns[$key][pretty_name] . ": ". $pretty_columns[$key][control]->get_html_editable("data[0][columns][" . $key . "]"); ?>
	</div>
	<!-- COLUMN END -->
<?		endforeach; ?>
	<!-- ROW END -->
	<input type="submit">
</form>

