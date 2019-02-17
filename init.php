<?php
session_start();
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
echo "drop 1            ";
$q = "Drop table image";
$conn->query($q);
echo "drop 2            ";
$q = "DROP table account";
$conn->query($q);
echo "create 1            ";
$q = "create table image (id SERIAL, name varchar(50), creator varchar(50), access varchar(10), time TIME, temp int not null default 1, primary key (id))";
$conn->query($q);
echo "create 2            ";
$q = "create table account (username varchar(20), pass varchar(20), mode int)";
$conn->query($q);
echo "insert 2            ";
$q = "Insert into account (username, pass, mode) values (\'admin\', \'minda123\', 1)";
$sql = $conn->prepare($q);
$sql->execute();
echo "insert 2            ";
$q = "Insert into account (username, pass, mode) values (\'Alice\', \'csci4140\', 0)";
$sql = $conn->prepare($q);
$sql->execute();

/*
$files = glob('img/upload/*');
foreach($files as $file){
  if(is_file($file)){
    unlink($file);
  }
}

$files = glob('img/temp/*');
foreach($files as $file){
  if(is_file($file)){
    unlink($file);
  }
}
*/


//header('Location: finish.php');

?>
