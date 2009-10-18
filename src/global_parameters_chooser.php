<?

session_start();

if ($_SESSION['connstr'] == "") {
	header("Location: index.php");
	break;
}

$dbconn = pg_connect($_SESSION['connstr']);

require_once("global_parameters.php");

$columns = get_global_parameters_options($dbconn);

if ($columns === NULL) {
	header ('Location: mainmenu.php');
};

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN"
	"http://www.w3.org/TR/html4/strict.dtd">

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

	<p>Please choose the global variables:</p>
	<form action="global_parameters_send.php" method="post">

<?	foreach ($columns as $catalog => $c):
		foreach ($c as $schema => $s):
			foreach ($s as $table => $t):
				foreach ($t as $column => $col): ?>
		<p>
			<label for="<? print $column; ?>">
			<? print "$schema.$table.$column:"; ?> 
			</label>
			<select name="globals<? print "[$catalog][$schema][$table][$column]"; ?>">
				<option value="">(All)</option>
<?					foreach ($col[values] as $x => $v): ?>
				<option value="<? print $v; ?>"><? print $v; ?></option>
<?					endforeach; ?>
			</select>
		</p>
<?				endforeach;
			endforeach;
		endforeach;
	endforeach; ?>

		<input type="submit">

	</form>

</body>

</html>










