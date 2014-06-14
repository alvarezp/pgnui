<?php

session_start();
session_destroy();

session_start();
?>

<!DOCTYPE html>

<html>

<head>
	<link rel="stylesheet" href="default_style.css" type="text/css">
</head>

<body>

<div class="index header" id="index_header">
</div>

<div class="index header" id="index_header">
	<form class="index login" id="login_form" action="login.php" method="post">
		<p>
			<label class="login" for="username">Username:</label><input class="login" type="text" name="username"><br/>
			<label class="login" for="password">Password:</label><input class="login" type="password" name="password"><br/>
			<label class="login" for="catalog">Database:</label><input class="login" type="text" name="catalog"><br/>
			<input class="login" type="submit" value="Login">
		</p>
	</form>
</div>

<div class="index footer" id="index_footer">
</div>

</body>
</html>

