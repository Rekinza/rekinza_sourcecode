<?php


if(isset($_POST['sku_code']))
{ //check if form was submitted

	require_once '../../app/Mage.php';
	Mage::app();

	$sku_code = $_POST['sku_code'];
	$special_price = $_POST['special_price'];
	
	echo $sku_code."<br>";
	echo $special_price."<br>";

	$proxy = new SoapClient('http://magentohost/api/v2_soap/?wsdl'); // TODO : change url
	$sessionId = $proxy->login('thredshare', 'thredshare21'); // TODO : change login and pwd if necessary

	$result = $proxy->catalogProductSetSpecialPrice($sessionId, $sku_code, '1000', '', '','','');
	var_dump($result);
	mysql_close();
	
}



?>

<html>

	<head></head>
	<h1>SKU Configuration</h1>

	<div id ="inventory_panels">
	
			<fieldset>
				<form action = "sku_config.php" method = "POST">
					<h3>Update SKU Price</h3>			
					<input type = "text" name = "sku_code" placeholder ="Enter SKU code" required >
					<input type = "number" name = "special_price" placeholder ="Enter Special Price" required >
					<p class = "submit">
						<input type = "submit" value = "Submit">
					</p>			
				</form>
				<br>
				<a href = "..\backend_home.php"><button class ="panel_button">Home</button></a>

			</fieldset>
			
		</div>	

</html>

<style>
body
{
	background-color: #F0F0F0  ;
}

h1 
{
	text-align: center;
	color: #bb1515;
	font-family: 'Arial';
	background-color: #b7c3c2;
	width:50%;
	margin-left:auto;
	margin-right:auto;
}

span
{
	float:left;
	display:inline-block;
	width:49%;
}

h2
{
	text-align: center;
	color: #bb1515;
	font-family: 'Arial';
	background-color: #b7c3c2;
	width:50%;
	margin-left:auto;
	margin-right:auto;
}


div
{
	margin-left:auto;
	margin-right:auto;
	width:50%;
	text-align:center;
	line-height:2em;
}

legend
{
	font-size: 1.2em;
	background-color :#669999;
	color : white;
	width:100%;

}

.panel_button
{
	color: white;
	background: #bb1515;
	border: 2px outset #d7b9c9;
	font-size:1.1em;
	border-radius:7px;
} 

a
{
	text-decoration:none;
}

a:link 
{
	color: aquamarine;
	
}

a:visited
{
	color:white;
}

a:hover
{
	color:grey;
}


</style>