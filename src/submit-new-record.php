<?php

session_start();

$dbconn = pg_connect($_SESSION['connstr']);

$catalog = $_SESSION['database'];
$schema = $_POST[schema];
$table = $_POST[table];
$data = $_POST[data];

require_once ("record-read.php");

$pretty_columns = get_columns_as_keys($dbconn, $catalog, $schema, $table);

$provided_columns = array();
$provided_params = array();
$provided_values = array();


pg_query("BEGIN;");

foreach($data as $row):
	$rowid=$row[rowid];
	foreach($row[columns] as $key => $col):
		$change = $pretty_columns[$key][control]->get_sql_update_from_diff($col[bef], $col[aft]);
		if ($change[change] == "yes") {
			$next = $next + 1;
			array_push($provided_columns, $key);
			array_push($provided_params, "$" . $next);
			array_push($provided_values, $change[value]);
		}
	endforeach;
endforeach;

pg_query_params("INSERT INTO $schema.$table (" . join($provided_columns, ",") . ") VALUES (" . join($provided_params, ",") . ")", $provided_values);

pg_query("COMMIT;");

header("Location: table.php?schema=$schema&table=$table");

#pg_query_params($dbconn, "UPDATE $schema.$table SET $key = $1 WHERE " . rowid_to_where($rowid), array($change[value]));
?>

