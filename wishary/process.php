<?php
include_once "./config.php";
include_once "./include/mysql.class.php";
include_once "./include/common.class.php";
$error = false;
$errMsg = "";
$resultData = "";
$version = SDK_VERSION;

if($DB->conn){

}else{
	$error = true;
	$errMsg = "DB Connection fail";
}

$period = 300;
$current_period = 300;
$config_id = 1;
$query = "SELECT NOW() as now ";
$rtns = $DB->query($query);
$now = $rtns[0]['now'];
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

if(!$error){
	
	
	$query = "SELECT id, VERSION, TIMESTAMPDIFF(SECOND, LAST_ACTION_TIME, NOW() ) as DIFF, PERIOD, LAST_ACTION_TIME FROM wishary_sdk_config ORDER BY id DESC Limit 1";
	$rtns = $DB->query($query);
	$diff = $rtns[0]['DIFF'];
	$version = $rtns[0]['VERSION'];
	$period = $rtns[0]['PERIOD'];
	$current_period = $rtns[0]['PERIOD'];
	$lastTime = $rtns[0]['LAST_ACTION_TIME'];
	$config_id = $rtns[0]['id'];
	
	if($period > 0){
		if($period < $diff){
			$query = "SELECT * FROM ".VIEW_TABLE." WHERE cdate > '".$lastTime."' AND cdate <= '".$now."' ORDER BY cdate ASC";

			$rtns = $DB->query($query);
			if(count($rtns) > 0){
				$dataArr = array();
				$row_cnt = count($rtns);
				for($i = 0; $i < $row_cnt; $i++){
					$uid = $rtns[$i]['uid'];
					if(!strpos($uid, UID_PREFIX)){
						$uid = UID_PREFIX.$uid;
					}
					$price = $rtns[$i]['price'];
					if($price == ""){
						$price = 0;
					}
					$price_unit = $rtns[$i]['price_unit'];
					if($price_unit == ""){
						$price_unit = "RS";
					}
					$category = $rtns[$i]['category'];
					$validCategory = $category;
					if(USE_CUSTOM_CATEGORY_MAPPER){
						if(CATEGORY_WAY == "MAP"){
							if(isset($categoryMap[$category])){
								$validCategory = $categoryMap[$category];
							}
						}else if(CATEGORY_WAY == "DICTIONARY"){
							foreach($categoryDictionary as $key => $val){
								if(is_array($val)){
									for($j = 0; $j < count($val); $j++){
										if($val[$j] == $category){
											$validCategory = $key;
											break;
										}
									}
								}
							}
						}else{
							$category = "0";
						}
					}
					$dataObj = array(
						"uid" => $uid,
						"cdate" => $rtns[$i]['cdate'],
						"category" => $validCategory,
						"name" => $rtns[$i]['name'],
						"price" => $price,
						"price_unit" => $price_unit,
						"description" => $rtns[$i]['description'],
						"pic_url" => $rtns[$i]['pic_url'],
						"product_url" => $rtns[$i]['product_url']
					);
					$dataArr[$i] = $dataObj;
				}
				$dataJson = json_encode($dataArr);
				$curl = curl_init();
				$params = array(
					"userid" => USERID,
					"apikey" => APIKEY,
					"site_url" => SITE_URL,
					"data" => $dataJson
				);

				$url = "http://207.182.158.186/sdkapi/putShopItems";
				curl_setopt($curl, CURLOPT_URL, $url);
				curl_setopt($curl, CURLOPT_HEADER,  0);
				curl_setopt($curl, CURLOPT_POST, 1);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
				curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 0);
		
				curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
				curl_setopt($curl, CURLOPT_TIMEOUT, 600);
				$result = curl_exec($curl);
				$apiRslt = json_decode($result, true);
				if(!isset($apiRslt["response"])){
					$error = true;
					$errMsg = "Network Error";
					$resultData = $result;
				}else if($apiRslt["response"] == "000"){
					$errMsg = "Sent ".$row_cnt." Items";
					$period = $apiRslt["period"] / 1000;
					$resultData = "000 | " . $apiRslt["period"];
				}else{
					$error = true;
					$errMsg = "API Error : ".$apiRslt["response"];
					$resultData = $result;
				}


			}else{
				$error = false;
				$errMsg = "No data to send";
			}
			
		}else{
			$error = false;
			$errMsg = "Skip by period";			
		}
	}else{
		$error = true;
		$errMsg = "Period error";
	}
}
$query = "UPDATE wishary_sdk_config SET LAST_ACTION_TIME = '".$now."', PERIOD = '".$period."' WHERE id = '".$config_id."'";
//$DB->query($query);
$resultData = str_replace("'", "''", $resultData);
$query = "INSERT INTO wishary_sdk_log(VERSION, ACTION_TIME, ACTION_PERIOD, IS_ERROR, RESULT_MSG, RESULT_DATA) VALUES('".$version."','".$now."','".$current_period."','".($error ? "1" : "0")."','".$errMsg."','".$resultData."')";
$DB->query($query);
echo "All Finish ".$errMsg;
?>