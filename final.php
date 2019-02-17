<?php
session_start();
echo "<p>final image</p><br>";
echo '<img src="'.$_COOKIE['filepath'].'"><br>';
echo "<p>link</p>";
echo $_COOKIE['filepath'];
setcookie($_COOKIE['filename'], '', 1);
setcookie($_COOKIE['effect'], '', 1);
setcookie($_COOKIE['filepath'], '', 1);
setcookie($_COOKIE['filetype'], '', 1);
setcookie($_COOKIE['lasteffect'], '', 1);
?>
<br>
<a href="index.php">index</a>
