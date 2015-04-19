<?php

include 'vendor/mvc-core/autoloader.php';
include 'autoloader.php';

if(isset($_POST["action"])){
    switch ($_POST["action"]){
        case "logout" :
            $login->actionLogout();
  
    }
}

$login->actionLogin();

?>
<form action="?" METHOD="POST"><input type="submit" value="logout"/><input type="hidden" name="action" value="logout"></form>