<?php

set_include_path(get_include_path() . PATH_SEPARATOR . '../../');

require_once("session.php");

$catalog = $_SESSION['database'];
$schema = $_POST['schema'];
$table = $_POST['table'];
$data = $_POST['data'];

$confirmation = $_POST['confirmation'];
$rowid = $_POST['rowid'];

if ($confirmation == "Yes") {

	pg_query("BEGIN;");

	require_once("func_rowid.php");
	pg_query($dbconn, "DELETE FROM $schema.$table WHERE " . rowid_to_where($rowid));

	pg_query("COMMIT;");

}

header("Location: table.php?schema=$schema&table=$table");

?>

