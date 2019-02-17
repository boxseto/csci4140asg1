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
    echo "<p>Hi, " . $_COOKIE["name"] . "!</p><br>";
    echo "<a href=\"logout.php\">LOGOUT</a><br>";
}else{
    foreach($_COOKIE as $key => $value){setcookie($key, '', 1);}
    echo "<p>Hi, Guest!</p><br>";
    echo "<a href=\"login.php\">LOGIN</a><br>";
}
$db = parse_url(getenv("DATABASE_URL"));
$dbpath = ltrim($db["path"], "/");
$conn = new PDO("pgsql:" . sprintf(
    "host=%s;port=%s;user=%s;password=%s;dbname=%s",
    $db["host"],
    $db["port"],
    $db["user"],
    $db["pass"],
    $dbpath
    ));
$q = "SELECT mode FROM account WHERE username=?";
$sql = $conn->prepare($q);
$sql->execute([$_COOKIE["name"]]);
$arr = $sql->fetchAll(PDO::FETCH_ASSOC);
if($arr){
    $row = $arr[0];
    if($row['mode'] == 1){echo '<a href="admin.php">System Initialization</a><br>'; }
}
?>

<!--IMAGE DISPLAY-->
<h3>Photo Galley</h3>
<?php
if(isset($_SESSION['mode'])){
    $q = 'SELECT COUNT(*) FROM image WHERE temp=0';
}else{
    $q = "SELECT COUNT(*) FROM image WHERE temp=0 AND access=\'public\'";
}
$sql = $conn->prepare($q);
$sql->execute();
$arr = $sql->fetchAll();
if($arr){
    $row = $arr[0];
    $totalrow = $row[0];
    if($totalrow == 0){echo '<p>No photo exist.</p><br>';}
}

$totalpages = ceil((isset($totalrow) ? $totalrow : 0) / 8);

if (isset($_GET['current']) && is_numeric($_GET['current'])) {
    $currentpage = $_GET['current'];
}else{
    $currentpage = 1;
}
if ($currentpage > $totalpages) {$currentpage = $totalpages;}
if ($currentpage < 1) {$currentpage = 1;}


$q = "SELECT name FROM image WHERE temp=0 ORDER BY time DESC LIMIT " . ($currentpage-1)*8 . ", 8";
$sql = $conn->prepare($q);
$sql->execute();
$tempcount = 0;
while($row = $sql->fetch(PDO::FETCH_ASSOC)){
  echo "<img src=\"img/upload/" . $row['name'] . "\"><br>";
  $tempcount += 1;
}

echo "print page number: totalrow:". (isset($totalrow) ? $totalrow : 0) . "    totalpages: $totalpages     currentpage:$currentpage";


if ($currentpage > 1) {
    echo " <a href='index.php?current=". $currentpage-1 ."'> a< </a> ";
}
echo "    for loop start    ";
for ($i=($currentpage-3); $i < (($currentpage+3)+1); $i++) {
    echo "    $i";
    if (($i > 0) && ($i <= $totalpages)) {
        echo "has to be printed:";
        if ($i == $currentpage) {
            echo "currentpage";
            echo " [<b>" . $i . "s</b>] ";
        } else {
            echo "otherpages";
            echo " <a href='index.php?current=" . $i . "'>d" . $i . "</a> ";
        }
    }
    echo "    ";
}
echo "    for loop end    ";
if ($currentpage != $totalpages) {
    echo "    print next page    ";
    //echo " <a href='index.php?current=" . $currentpage+1 . "'> f> </a> ";
    echo " <a href='index.php?current=" . ($currentpage+1) . "'> f> </a> ";
}
?>

<!--UPload-->
<?php
if($_COOKIE['logged'] != 'true' && isset($_SESSION['mode'])){
    echo '<!--';
}
?>
<br>
<h3>Upload photo</h3>
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
if($_COOKIE['logged'] != 'true' && isset($_SESSION['mode']) ){
    echo '-->';
}
?>
</body>
</html>
