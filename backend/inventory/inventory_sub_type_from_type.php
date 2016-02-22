<?php

include 'db_config.php';

$type =$_POST['type'];

$get = mysql_query("SELECT entity_name from sku_code_mapping WHERE type = 'sub-type' AND parent = '".$type."' ");

$option = '';
while($row = mysql_fetch_assoc($get))
{
  $option .= '<option value = "'.$row['entity_name'].'">'.$row['entity_name'].'</option>';
}
mysql_close();

	echo $option;


	
	
?>
