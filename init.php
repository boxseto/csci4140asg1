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
$q = "Drop table image";
$conn->query($q);
$q = "DROP table account";
$conn->query($q);
$q = "create table image (id SERIAL, name varchar(50), creator varchar(50), access varchar(10), time TIME, temp int not null default 1, primary key (id))";
$conn->query($q);
$q = "create table account (username varchar(20), pass varchar(20), mode int)";
$conn->query($q);
$q = "Insert into account (username, pass, mode) values ('admin', 'minda123', 1)";
$sql = $conn->prepare($q);
$sql->execute();
$q = "Insert into account (username, pass, mode) values ('Alice', 'csci4140', 0)";
$sql = $conn->prepare($q);
$sql->execute();


//clear bucket
require('vendor/autoload.php');
$s3 = new Aws\S3\S3Client([
    'version'  => '2006-03-01',
    'region'   => 'ap-southeast-1',
]);
$bucket = getenv('S3_BUCKET');

try {
    $results = $s3->getPaginator('ListObjects', [
        'Bucket' => $bucket
    ]);

    foreach ($results as $result) {
        foreach ($result['Contents'] as $object) {
          $s3->deleteObject([
             'Bucket' => $bucket,
             'Key'    => $object['Key']
             ]);
        }
    }
} catch (S3Exception $e) {
    echo $e->getMessage() . PHP_EOL;
}


header('Location: finish.php');

?>
