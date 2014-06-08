<?php

session_start();

// FIXME: Sanitize input values.
$username=$_POST['username'];
$password=$_POST['password'];
$database=$_POST['catalog'];

if ($database == "") {
	$database = $username;
}

// FIXME: Sanitize input values.
$dbconn=pg_connect("host=localhost port=5432 dbname=$database user=$username password=$password");

if (!$dbconn) {
	print "error\n";
	die("error connecting to the db\n");
}

$_SESSION['connstr'] = "host=localhost port=5432 dbname=$database user=$username password=$password";
$_SESSION['username'] = $username;
$_SESSION['database'] = $database;

/* Create a user schema. */
/* FIXME: It should not re-create the schema if it already exists. */
pg_query($dbconn, "CREATE SCHEMA AUTHORIZATION " . pg_escape_string($username));
pg_query($dbconn, "CREATE TABLE " . pg_escape_string($username) . ".___pgnui_settings (name varchar, parameter varchar, value varchar);");

header("Location: global_parameters_chooser.php");

?>

