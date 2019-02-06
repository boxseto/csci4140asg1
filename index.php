<html>
<head>
<title>Web Instagram</title>
</head>
<body>
<!--ACCESS CONTROL-->
<?php
session_start();
if(isset($_SESSION['error'])) echo $_SESSION['error'];
$_SESSION['error'] = '';

if(isset($_SESSION['mode'])){
    echo "<p>Hi, " . $_SESSION["user"] . "!</p><br>";
    echo "<a href=\"logout.php\">LOGOUT</a><br>";
}else{
    echo "<p>Hi, Guest!</p><br>";
    echo "<a href=\"login.php\">LOGIN</a><br>";
}
?>

<!--IMAGE DISPLAY-->
<?php
$conn = new mysqli("localhost", "user", "user", "CSCI4140");
if(isset($_SESSION['mode'])){
    $q = 'SELECT * FROM image ORDER BY time DESC';
}else{
    $q = 'SELECT * FROM image WHERE access=\'public\' ORDER BY time DESC';
}
$sql = $conn->query($q);
if($sql->num_rows > 0){
    while($row = $result->fetch_assoc()){
        echo "<img src=\"img/upload/" . htmlspecialchars($row["name"]) . "\">"

    } 
}
?>

<!--UPload-->
<?php
if(!isset($_SESSION['mode'])){
    echo '<!--';
}
?>
<h3> Upload photo</h3><br>
<form method="POST" action="editor.php" ectype="multipart/form-data">
<p>Mode:</p> 
<select name="access">
<option value="public">Public</option>
<option value="private">Public</option>
</select>
<br>
<input type="file" name="image" required/>
<br>
<input type="submit" value="Upload" />
</form>
<?php
if(!isset($_SESSION['mode'])){
    echo '-->';
}
?>
</body>
</html>
