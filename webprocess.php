<?php
session_start();
$function = htmpspecialchars($_REQUEST["function"]);
if($function = "login"){login_chk();}

function login_chk(){
    $user = htmlspecialchars($_REQUEST["username"]);
    $pass = htmlspecialchars($_REQUEST["password"]);
    $conn = new mysqli("localhost", "user", "user", "CSCI4140");
    $q = 'SELECT mode FROM account WHERE user=? AND pass=?';
    $sql = $conn->prepare($q2);
    $sql->bind_param('ss', $user, $pass);
    $sql->execute();
    $result = $sql->get_result();
    if($result->num_rows > 0){
        $row = $result->fetch_assoc();
        $mode = $row['mode'];
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
    }else{
        $_SESSION['error'] = 'INCORRECT PASSWORD OR USERNAME.';
        header('Location: index.php');
    }
}

?>
