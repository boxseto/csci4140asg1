<?php
$db = parse_url(getenv("DATABASE_URL"));
$dbpath = ltrim($db["path"], "/");
$conn = new PDO("pgsql:" . sprintf(
    "host=%s;port=%s;user=%s;password=%s;dbname=%s"
    $db["host"],
    $db["port"],
    $db["user"],
    $db["pass"],
    $dbpath
    ));
//$conn = new mysqli("localhost", 'user', 'user', 'CSCI4140');
$q = "Drop table image";
$conn->query($q);
$q = "DROP table account";
$conn->query($q);
$q = "create table image (id int not null auto_increment, name varchar(50), creator varchar(50), access varchar(10), time TIME, temp int not null default 1, primary key (id))";
$conn->query($q);
$q = "create table account (user varchar(20), pass varchar(20), mode int)";
$conn->query($q);
$q = "Insert into account (user, pass, mode) values (\"admin\", \"minda123\", 1)";
$conn->query($q);
$q = "Insert into account (user, pass, mode) values (\"user\", \"csci4140\", 0)";
$conn->query($q);
$conn->close();

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

header('Location: finish.php');

?>
