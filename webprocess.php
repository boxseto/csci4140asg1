<?php
session_start();
$function = htmlspecialchars($_REQUEST["function"]);
if($function = "login"){login_chk();}

function login_chk(){
    $user = htmlspecialchars($_REQUEST["username"]);
    $pass = htmlspecialchars($_REQUEST["password"]);
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
    //$conn = new mysqli("localhost", "user", "user", "CSCI4140");
    $q = 'SELECT mode FROM account WHERE user=? AND pass=?';
    $sql = $conn->prepare($q);
    $sql->bind_param('ss', $user, $pass);
    $sql->execute();
    $sql->bind_result($result);
    $counter = 0;
    while ($sql->fetch()){
        $counter += 1;
    //if($result->num_rows > 0){             //cannot use get_result()
        //$row = $result->fetch_assoc();     //cannot use get_result()
        //$mode = $row['mode'];              //cannot use get_result()
        $mode = $result;
        setcookie('logged', 'true', NULL, NULL, NULL, NULL, TRUE);  
        if($mode == 0){
            $_SESSION['mode'] = 0;
            setcookie('name', $user, NULL, NULL, NULL, NULL, TRUE);  
        }
        else if($mode == 1){
            setcookie('name', $user, NULL, NULL, NULL, NULL, TRUE);  
            $_SESSION['mode'] = 1;
        }
        $sql->close();
        $conn->close();
        header('Location: index.php');
    }
    if($counter == 0){
    //}else{                                 //cannot use get_result()
        $_SESSION['error'] = 'INCORRECT PASSWORD OR USERNAME.';
        header('Location: index.php');
    }
    
}

?>
