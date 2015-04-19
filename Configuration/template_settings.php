<?php

$dbConfig['MYSQL_HOST'] = 'localhost'; 
$dbConfig['MYSQL_USER'] = 'root';
$dbConfig['MYSQL_PASSWORD'] = 'root';
$dbConfig['MYSQL_DATABASENAME'] = 'login';
$dbConfig['MYSQL_TABLE_PREFIX'] = '';

define('LOGIN_ROOT_DIR', realpath(dirname(__FILE__).DIRECTORY_SEPARATOR."..").DIRECTORY_SEPARATOR);
define('EXTERNAL_LOGOUT_URL','https://twitter.com');


?>
