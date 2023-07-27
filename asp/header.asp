<LINK rel="stylesheet" type="text/css" href="style.css">
<?php

$server= "localhost";
$user= "calendar";
$database= "calendar";

/* Accessing MYSQL-Server */

MYSQL_CONNECT($server, $user, $password) or die ( "<H3>Server unreachable</H3>");
MYSQL_SELECT_DB($database) or die ( "<H3>Database non existent</H3>");
?>


