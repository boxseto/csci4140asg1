<?php
session_start();
echo "<p>final image</p><br>";
echo '<img src="'.$_COOKIE['filepath'].'"><br>';
echo "<p>link</p><br>";
echo $_COOKIE['filepath'];
?>
<br>
<a href="index.php">index</a>
