<?php


include 'db_config.php';

$pickup_id =$_POST['pickup_id'];
$product_name=$_POST['product_name'];
$cust_email_id=$_POST['customer_email_id'];
$type=$_POST['type'];
$sub_type=$_POST['sub_type'];
$category=$_POST['category'];
$sub_category=$_POST['sub_category'];
$brand =$_POST['brand'];
$color =$_POST['color'];
$condition =$_POST['condition'];
$gently_used_comments =$_POST['gently_used_comments'];
$special_instr =$_POST['special_instr'];
$qc_owner=$_POST['qc_owner'];
$qc_status=$_POST['qc_status'];
$rejection_reason =$_POST['rejection_reason'];
$maybe_reason =$_POST['maybe_reason'];
$retail_value =$_POST['retail_value'];
$suggested_price =$_POST['suggested_price'];
$quantity = $_POST['quantity'];

/***************Get product type ********************/

$query = "SELECT code from sku_code_mapping WHERE entity_name = '".$type."' ";
$result = mysql_query($query); 

$type_code = mysql_result($result,0,'code');


/***************Get product sub-type ********************/

$query = "SELECT code from sku_code_mapping WHERE entity_name = '".$sub_type."' ";
$result = mysql_query($query); 

$sub_type_code = mysql_result($result,0,'code');

/***************Get product brand ********************/

$query = "SELECT code from sku_code_mapping WHERE entity_name = '".$brand."' ";
$result = mysql_query($query); 

$brand_code = mysql_result($result,0,'code');


$sku_code = $type_code.'-'.$sub_type_code.'-'.$brand_code ;



/****************Search last added item with same type, sub type and brand combination******************/

$query = "SELECT sku_name from inventory WHERE sku_name LIKE '".$sku_code."%' ";

$result = mysql_query($query); 

$numresult = mysql_numrows($result);

if ($numresult == 0 )
{
	$sku_code = $sku_code.'-01';
}

else
{	
	$j = 0;
	$max_index = 0;

	while ($j < $numresult)
	{
		$sku_name = mysql_result($result,$j,'sku_name');
		$sku_name_array = explode("-",$sku_name);
		
		$sku_index = $sku_name_array[3];		
		if ($sku_index >$max_index)
		{
			$max_index = $sku_index;
		}
		$j++;

	}
	$max_index = $max_index + 1;

	if ($max_index <10)
	{	
		$sku_code = $sku_code.'-0'.$max_index;
	}
	else
	{	
		$sku_code = $sku_code.'-'.$max_index;
	}
}

echo $sku_code."<br>";

/**************Convert color array into a single string*******************/

for($i=0;$i<count($color) - 1;$i++){
	
	$color_string .= $color[$i];
	$color_string .= ",";
}

$color_string .= $color[$i];


/*****************************************/

$query = "INSERT INTO `inventory` VALUES ('','$sku_code','$cust_email_id','$type','$sub_type','$category','$sub_category','$brand','$color_string','$condition','$gently_used_comments','$pickup_id','$product_name','$special_instr','$qc_owner','$qc_status','','','','$suggested_price','$retail_value','','','','$quantity','$rejection_reason','','$maybe_reason')";
$result = mysql_query($query);

echo mysql_error();

if ($result == 'TRUE')
{
	echo 'Record inserted successfully';
}
else
{
	echo 'Record insertion failed';
}
mysql_close();

?>


<html>
	<br><br>
			<a href = "inventory_new.php"><button class ="panel_button">Insert New Inventory</button></a>
	<br><br>

</html>

<style>
.panel_button
{
	color: white;
	background: #bb1515;
	border: 2px outset #d7b9c9;
	font-size:1.1em;
	border-radius:7px;
} 
</style>