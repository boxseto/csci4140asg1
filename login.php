<?php session_start(); ?>
<html>
<head>
<title>login</title>
</head>
<body>
<form method="POST" action="webprocess.php">
    <h3>Login</h3>
    <input type="hidden" name="function" value="login">
    <input type="text" name="username" required/>
    <input type="password" name="password" required/>
    <input type="submit" value="Login"/>
</form>
</body>
</html>
