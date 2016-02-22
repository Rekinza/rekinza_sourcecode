`<?php

if(isset($_POST['mark_upload']))
{
	include 'db_config.php';
    $checkbox = $_POST['checkbox'];
	for($i=0;$i<count($checkbox);$i++){
		
		$mark_upload_id = $checkbox[$i];
		$sql = "UPDATE inventory SET upload_status = 'uploaded' WHERE sku_name ='$mark_upload_id' ";
		$result = mysql_query($sql);
		}
	
	if($result =='TRUE')
	{
		echo "Marked uploaded<br>";
	}

mysql_close();

}



include 'db_config.php';
$query = "SELECT * FROM inventory WHERE upload_status != 'uploaded' AND ( (qc_status = 'accepted' AND size != '' AND measurements != '') OR (qc_status ='rejected'))";
$result = mysql_query($query);

	
$numresult = mysql_numrows($result);

if ( $numresult > 0 )
{
	?>
	<head>
		<script src="jquery-1.11.1.js"></script>
		<script src="FileSaver.js"></script>
	<head>
	
	<body>
	<div id ="inventory_table">
		<h1>Inventory Details</h1>
		
		<button id="btnExport" onclick="fnExcelReport();" > Export To Excel </button>
		
		<table id="report_table">
			<th>S. No</th>
			<th><input type ="checkbox" onchange ="checkAll(this)" name ="chk">Select All</th>
			<th>SKU Code</th>
			<th>Pickup Id</th>
			<th>Email ID</th>
			<th>Product Name</th>
			<th>Type</th>
			<th>Sub Type</th>
			<th>QC Status</th>
			<th>Condition</th>
			<th>Gently Used Comments</th>
			<th>Brand</th>
			<th>Color</th>
			<th>Material</th>
			<th>Measurements</th>
			<th>Size</th>
			<th>Retail Value</th>
			<th>Suggested Price</th>
			<th>Special</th>
			<th>Rejection Reason</th>
			<th>Upload Status</th>

			<form action = 'inventory_pending_upload.php' method = 'POST'>
	<?php
	$i = 0;
	while ( $i < $numresult )
	{
			$sku_name = mysql_result($result,$i,'sku_name');
			$customer_email_id = mysql_result($result,$i,'customer_email_id');
			$product_name= mysql_result($result,$i,'product_name');
			$type= mysql_result($result,$i,'type');
			$sub_type= mysql_result($result,$i,'sub_type');
			$qc_status= mysql_result($result,$i,'qc_status');
			$condition= mysql_result($result,$i,'condition');
			$gently_used_comments= mysql_result($result,$i,'gently_used_comments');
			$brand = mysql_result($result,$i,'brand');
			$color= mysql_result($result,$i,'color');
			$material = mysql_result($result,$i,'material');
			$measurements = mysql_result($result,$i,'measurements');
			$size = mysql_result($result,$i,'size');
			$retail_value = mysql_result($result,$i,'retail_value');
			$suggested_price = mysql_result($result,$i,'suggested_price');
			$special = mysql_result($result,$i,'special');
			$rejection_reason = mysql_result($result,$i,'rejection_reason');
			$upload_status = mysql_result($result,$i,'upload_status');
			$pickup_id = mysql_result($result,$i,'pickup_id');
		
		?>
		
		

		<tr>
			<td> <?php echo $i+1 ?></td>
			<td><input type = "checkbox" name = "checkbox[]" value = "<?php echo $sku_name?>"> </td>
			<td><?php echo $sku_name; ?></td>
			<td><?php echo $pickup_id?></td>
			<td><?php echo $customer_email_id ;?></td>
			<td ><?php echo $product_name;?></td>
			<td ><?php echo $type;?></td>
			<td ><?php echo $sub_type;?></td>
			<td ><?php echo $qc_status;?></td>
			<td ><?php echo $condition;?></td>
			<td ><?php echo $gently_used_comments;?></td>
			<td><?php echo $brand; ?></td>
			<td><?php echo $color; ?></td>
			<td><?php echo $material?></td>
 			<td><?php echo $measurements?></td>
			<td><?php echo $size ?></td>
			<td><?php echo $retail_value ?></td>
			<td><?php echo $suggested_price ?></td>
			<td><?php echo $special ?></td>
			<td><?php echo $rejection_reason ?></td>
			<td><?php echo $upload_status ?></td>
			

		</tr>
						
	<?php
	$i++;
	}
	
?>
	</table>
	<p class ="submit">
	<input type = "Submit" id = "mark_upload" name ="mark_upload" value = "Mark uploaded">
	</p>
	</form>
	<br><br>
	
	
	</div>
	</body>
<?php
}
else
{
	echo "No results found";

}


?>
<style>

body
{
	background-color: #F9FFFB;
}

#seller_pickup_id
{
	width:1%;
	text-align:center;
	
}

h1
{
	background-color: #E3E0FA;
	text-align:center;
	width:40%;
	margin-left:auto;
	margin-right:auto;
	margin-bottom:2em;
	font-family: 'Century Schoolbook';
		

}

table
	{
		margin-left: auto;
		margin-right: auto;
		font-color: #d3d3d3;
		background-color: #ADD8E6;
	
	}
	
th
	{
		background-color: #C0C0C0;
		font-family: 'Georgia';
		font-size:1.2em;
	}
	
td
	{
		text-align : center;
	}
	
	
tr:nth-child(odd)
	{
			background-color: #EAF1FB;
			font-size:1.1em;
	}

tr:nth-child(even)
	{
			background-color: #CEDEF4;
			font-size:1.1em;
	}

tr.highlight   
	{    
		background-color: #063774;   
		color: White;   
	}  

textarea
	{
		height:120px;
	}
	
#btnExport
	{
		display:block;
		margin-left: auto;
		margin-right: auto;
		margin-bottom:2em;
		height: 35px;
		color: white;
		border-radius: 10px;
		text-shadow: 0 1px 1px rgba(0, 0, 0, 0.2);
		background: rgb(202, 60, 60); /* this is maroon */
	}	

button
{
		width:20%;
}

p{
	
	text-align:center;
}
.submit input
{
	color: white;
	background: #bb1515;
	border: 2px outset #d7b9c9;
	font-size:1.1em;
	border-radius:7px;
} 

</style>

<script>

function fnExcelReport()
{
	var tab_text="<table><tr>";
    var textRange;
    tab = document.getElementById('report_table'); // id of actual table on your page

	console.log(tab.rows.length);
    for(j = 0 ; j < tab.rows.length ; j++) 
    {   
        tab_text=tab_text+tab.rows[j].innerHTML;
        tab_text=tab_text+"</tr><tr>";
    }

    tab_text = tab_text+"</tr></table>";

	var txt = new Blob([tab_text], {type: "text/plain;charset=utf-8"});
	saveAs(txt,"SKUs_For_Upload.xls");
}

/********************* Validate numeric entry in form field*********************/
function validate(evt) 
{
	var theEvent = evt || window.event;
	var key = theEvent.keyCode || theEvent.which;
	key = String.fromCharCode( key );
	var regex = /[0-9]|\./;
	if( !regex.test(key) ) 
	{
		theEvent.returnValue = false;
		if(theEvent.preventDefault) theEvent.preventDefault();
	}
}

 function checkAll(ele) {
     var checkboxes = document.getElementsByName('checkbox[]');
     if (ele.checked) {
         for (var i = 0; i < checkboxes.length; i++) {
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = true;
             }
         }
     } else {
         for (var i = 0; i < checkboxes.length; i++) {
             console.log(i)
             if (checkboxes[i].type == 'checkbox') {
                 checkboxes[i].checked = false;
             }
         }
     }
 }
</script>