<?php

if(isset($_POST['brand']))
{ //check if form was submitted

	include 'db_config.php';

	$brand = $_POST['brand'];
	$code = $_POST['code'];
	
	$brand = trim($brand);
	$code = trim($code);
	$code = strtoupper($code);   // In case code is in lower case

	$query = "SELECT entity_name FROM sku_code_mapping WHERE entity_name ='$brand'";
	$result = mysql_query($query);
	$numresult = mysql_numrows($result);
	
	if ($numresult > 0)
	{
		echo 'Brand already exists';
	}
	
	else
	{
		$query = "SELECT entity_name FROM sku_code_mapping WHERE code ='$code'";
		$result = mysql_query($query);
		$numresult = mysql_numrows($result);
		if ($numresult > 0)
		{
			echo 'Code already exists';
		}
		else
		{
			$query = "INSERT into sku_code_mapping values (NULL,'$brand','brand','','$code')"; 
			$result = mysql_query($query);
			if ($result == TRUE)
			{
				echo "Brand ".$brand." with code ".$code." added successfully";
			}
			else
			{
				echo "Record addition failed";
			}	
		}
	}

	mysql_close();
	
}
?>

<html>

	<head></head>
	<h1>Brand Configuration</h1>

	<div id ="inventory_panels">
	
			<fieldset>
				<form action = "brands_config.php" method = "POST">
				<h3>Add New Brand</h3>			
				<input type = "text" name = "brand" placeholder ="Enter brand name" required >
					<input type = "text" name = "code" placeholder = "Enter brand code" required>
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