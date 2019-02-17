<?php
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
        $q = "Update image set temp = 3 WHERE name=?";
        $sql = $conn->prepare($q);
        $sql->execute([$_COOKIE['filename']]);
?>