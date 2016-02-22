<?php

$host = "localhost";
$user="root";
$password="harsh";
$database="magento";

mysql_connect($host,$user,$password);
@mysql_select_db($database) or die( "Unable to select DB");

?>