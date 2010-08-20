<?php

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

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

