<?php

session_start();

// FIXME: Sanitize input values.
$username=$_POST['username'];
$password=$_POST['password'];
$database=$_POST['catalog'];

// FIXME: Sanitize input values.
$dbconn=pg_connect("host=localhost port=5432 dbname=$database user=$username password=$password");

if (!$dbconn) {
	print "error\n";
	die("error connecting to the db\n");
}

$_SESSION['connstr'] = "host=localhost port=5432 dbname=$database user=$username password=$password";
$_SESSION['username'] = $_POST['username'];
$_SESSION['database'] = $_POST['catalog'];

header("Location: global_parameters_chooser.php");

?>

