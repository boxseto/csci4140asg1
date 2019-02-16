<?php
session_start();
$function = htmlspecialchars($_REQUEST["function"]);
if($function = "login"){login_chk();}
else{header("Location: index.php");}

function login_chk(){
    $user = htmlspecialchars($_REQUEST["username"]);
    $pass = htmlspecialchars($_REQUEST["password"]);
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
    $q = 'SELECT mode FROM account WHERE username=? AND pass=?';
    $sql = $conn->prepare($q);
    $sql->execute([$user, $pass]);
    $counter = 0;
    while ($row = $sql->fetch(PDO::FETCH_ASSOC)){
        $counter += 1;
        $mode = $row['mode'];
        setcookie('logged', 'true', time()+60*60*24*30 , "/");
        if($mode == 0){
            $_SESSION['mode'] = 0;
            setcookie('name', $user, time()+60*60*24*30 , "/");
        }
        else if($mode == 1){
            setcookie('name', $user, time()+60*60*24*30 , "/");  
            $_SESSION['mode'] = 1;
        }
        header('Location: index.php');
    }
    if($counter == 0){
        $_SESSION['error'] = 'INCORRECT PASSWORD OR USERNAME.';
        header('Location: index.php');
    }

}

?>
