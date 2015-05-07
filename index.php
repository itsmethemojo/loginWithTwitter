<?php

include realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'vendor/mvc-core/autoloader.php';
include realpath(dirname(__FILE__)).DIRECTORY_SEPARATOR.'autoloader.php';

if(isset($_POST["action"])){
    switch ($_POST["action"]){
        case "logout" :
            $login->actionLogout();
  
    }
}

$login->actionLogin();

?>
