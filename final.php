<?php
session_start();
echo "<p>final image</p><br>";
echo "<img src='img/upload/". $_COOKIE['filename'] ."'><br>";
echo "<p>link</p><br>";
echo $_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']."/img/upload".$_COOKIE['filename'];
?>
<br>
<a href="index.php">index</a>
