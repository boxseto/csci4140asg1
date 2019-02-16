<?php
session_start();
unset($_SESSION['mode']);
unset($_SESSION['error']);
session_destroy();
header("location: index.php");
?>
