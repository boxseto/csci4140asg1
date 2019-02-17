<html>
<head>
<title>Web Instagram</title>
</head>
<body>
<!--ACCESS CONTROL-->
<?php
require('vendor/autoload.php');
$s3 = new Aws\S3\S3Client([
    'version'  => '2006-03-01',
    'region'   => 'ap-southeast-1',
]);
$bucket = getenv('S3_BUCKET');
session_start();
if(isset($_COOKIE['error'])) echo $_COOKIE['error'];
setcookie('error', '', 1);

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
if(isset($_COOKIE['logged'])){
    if($_COOKIE['logged'] == 'true'){
        $q = 'SELECT COUNT(*) FROM image WHERE temp=0';
    }else{
        $q = "SELECT COUNT(*) FROM image WHERE temp=0 AND access='public'";
    }
}else{
    $q = "SELECT COUNT(*) FROM image WHERE temp=0 AND access='public'";
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

if(isset($_COOKIE['logged'])){
    if($_COOKIE['logged'] == 'true'){
        $q = "SELECT name FROM image WHERE temp=0 ORDER BY time DESC LIMIT " . (($currentpage-1)*8) . ", 8";
    }else{
        $q = "SELECT name FROM image WHERE temp=0 AND access='public' ORDER BY time DESC LIMIT " . (($currentpage-1)*8) . ", 8";
    }
}else{
    $q = "SELECT name FROM image WHERE temp=0 AND access='public' ORDER BY time DESC LIMIT " . (($currentpage-1)*8) . ", 8";
}

$sql = $conn->prepare($q);
$sql->execute();
$tempcount = 0;
while($row = $sql->fetch(PDO::FETCH_ASSOC)){
  echo var_dump($row);
  $imagick = new \Imagick();
  $tmpurl = $s3-> getObjectUrl($bucket, $row['name']);
  $image = file_get_contents($tmpurl);
  $imagick -> readImageBlob($image);
  $imagick->resizeImage(1200,900,Imagick::FILTER_POINT,0);
  echo '<img src="data:image/' . $_COOKIE['filetype'] . ';base64,'.base64_encode($imagick->getImageBlob()).'"/>';
  $tempcount += 1;
}

if ($currentpage > 1) {
    echo " <a href='index.php?current=". $currentpage-1 ."'> < </a> ";
}

for ($i=($currentpage-3); $i < (($currentpage+3)+1); $i++) {
    if (($i > 0) && ($i <= $totalpages)) {
        if ($i == $currentpage) {
            echo " [<b>" . $i . "</b>] ";
        } else {
            echo " <a href='index.php?current=" . $i . "'>" . $i . "</a> ";
        }
    }
}
if ($currentpage < $totalpages) {
    echo " <a href='index.php?current=" . ($currentpage+1) . "'> > </a> ";
}
?>

<!--UPload-->
<?php
if(isset($_COOKIE['logged'])){
    if($_COOKIE['logged'] != 'true'){echo '<!--';}
}else{echo '<!--';}
?>
<br>
<h3>Upload photo</h3>
<form method="POST" action="editor.php" enctype="multipart/form-data">
<p>Mode:</p>
<select name="access">
<option value="public">Public</option>
<option value="private">Private</option>
</select>
<br>
<input type="file" name="image" required/>
<br>
<input type="submit" value="Upload" />
</form>
<?php
if(isset($_COOKIE['logged'])){
    if($_COOKIE['logged'] != 'true'){echo '-->';}
}else{echo '-->';}
?>
</body>
</html>
