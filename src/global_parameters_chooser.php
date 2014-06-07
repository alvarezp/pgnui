<?php

require_once("session.php");

require_once("func_global_parameters.php");

$columns = get_global_parameters_options($dbconn);

if (count($columns) == 0) {
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

<?php	foreach ($columns as $catalog => $c):
		foreach ($c as $schema => $s):
			foreach ($s as $table => $t):
				foreach ($t as $column => $col): ?>
		<p>
			<label for="<?= $column ?>">
			<?= "$schema.$table.$column:" ?> 
			</label>
			<select name="globals<?= "[$catalog][$schema][$table][$column]" ?>">
				<option value="">(All)</option>
<?php					foreach ($col['values'] as $x => $v): ?>
				<option value="<?= $v ?>"><?= $v ?></option>
<?php					endforeach; ?>
			</select>
		</p>
<?php				endforeach;
			endforeach;
		endforeach;
	endforeach; ?>

		<input type="submit">

	</form>

</body>

</html>










