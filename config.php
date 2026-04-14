<?php

// Mention which platform this PHP scripts are running on
$OSEV = 'win'; // uncomment if Windows OS
# $OSEV = 'unix'; // uncomment if Unix OS

// Site URL
$SURL = '192.168.0.102';

/* Database credentials. Assuming you are running MySQL
server with default setting (user 'root' with no password) */
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'logindb');
 
/* Attempt to connect to MySQL database */
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
 
// Check connection
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
