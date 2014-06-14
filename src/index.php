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

<header>
	<h1><span>pgnui</span></h1>
</header>

<div class="index header" id="index_header">
	<form class="index login" id="login_form" action="login.php" method="post">
		<table>
			<tr><td><label class="login" for="username">Username:</label></td><td><input class="login" type="text" name="username"></td></tr>
			<tr><td><label class="login" for="password">Password:</label></td><td><input class="login" type="password" name="password"></td></tr>
			<tr><td><label class="login" for="catalog">Database:</label></td><td><input class="login" type="text" name="catalog"></td></tr>
			<tr><td><input class="login" type="submit" value="Login">
		</table>
	</form>
</div>

<div class="index footer" id="index_footer">
</div>

</body>
</html>

