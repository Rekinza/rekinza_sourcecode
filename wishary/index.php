<?php
include_once "./config.php";
include_once "./include/mysql.class.php";
include_once "./include/common.class.php";
$finish = true;
if($DB->conn){
	echo "DB connect... success!<br/>";
}else{
	echo "DB connect... fail!!<br/>";
	$finish = false;
}
$php_version= phpversion();
echo  'php version : '.$php_version.'<br>';

{
	$query = "CREATE TABLE IF NOT EXISTS wishary_sdk_config (id INTEGER AUTO_INCREMENT, VERSION FLOAT, LAST_ACTION_TIME DATETIME, PERIOD INTEGER, PRIMARY KEY (`id`))";
	$result = $DB->query($query);
	$query = "SELECT ifnull(count(*), 0) as cnt FROM wishary_sdk_config";
	$rtns = $DB->query($query);
	$cnt = $rtns[0]['cnt'];
	if($cnt > 0){
		
	}else{
		$query = "INSERT INTO wishary_sdk_config(id, VERSION, LAST_ACTION_TIME, PERIOD) VALUES(1, '".SDK_VERSION."', '1970-01-01 00:00:00', '300')";
		$DB->query($query);
	}
	$query = "CREATE TABLE IF NOT EXISTS wishary_sdk_log (id INTEGER  AUTO_INCREMENT, VERSION FLOAT, ACTION_TIME DATETIME, ACTION_PERIOD INTEGER, IS_ERROR CHAR(1), RESULT_MSG VARCHAR(20), RESULT_DATA TEXT, PRIMARY KEY (`id`))";
	$DB->query($query);
}  
if($finish){ 
	echo "DB initialize... success!!<br/>";
}else{
	echo "DB connect... fail!!<br/>";
}

?>