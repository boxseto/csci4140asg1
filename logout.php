<?php
session_start();
unset($_SESSION['mode']);
unset($_SESSION['error']);
session_destroy();
foreach($_COOKIE as $key => $value){setcookie($key, '', 1);}
header("location: index.php");
?>
