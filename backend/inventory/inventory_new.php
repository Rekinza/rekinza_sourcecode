<html>
	<head>
		<script src="jquery-1.11.1.js"></script>
		<body>
			<h1>Inventory Panel</h1>

			<!------------Input new inventory details here------------>
			
			<div id = "inventory_insert_form">
				
				<form action = "inventory_insert.php" method = "POST">
				<fieldset>
					<legend>Insert new inventory</legend>
					Pickup ID:
					<input type="number" name="pickup_id" id ="pickup_id" onkeypress ='validate(event)' required>
					<br>
					Product Name:
					<input type="text" name="product_name" id ="product_name" required>
					<br>
					Customer Email ID:
					<input type="text" name="customer_email_id" id ="customer_email_id" required>
					<br>
					Type:
					<?php

						include 'db_config.php';

						$get = mysql_query("SELECT entity_name FROM sku_code_mapping where type='type'");
						$option = '<option value="" disabled="disabled" selected="selected">Select Type</option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['entity_name'].'">'.$row['entity_name'].'</option>';
						}
					?>
					<select name="type" id = "type" required>
						<?php echo $option ?>
					</select>
					<br>
					Sub-Type:
					<select name="sub_type" id ="sub_type" required>
					</select>
					<br>
					Category:
					<input type="text" name="category" id ="category">
					<br>
					Sub-Category:
					<input type="text" name="sub_category" id ="sub_category">
					<br>
					Brand:
					<?php

						$get = mysql_query("SELECT entity_name FROM sku_code_mapping where type ='brand' ORDER BY entity_name ");
						$option = '';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['entity_name'].'">'.$row['entity_name'].'</option>';
						}
					?>
					<select name="brand" id ="brand" required>
						<?php echo $option ?>
					</select>
					<br>
					Color:
					<?php

						$get = mysql_query("SELECT * FROM colors where 1");
						$option = '';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['type'].'">'.$row['type'].'</option>';
						}
					?>
					<select name="color[]" id ="color" multiple required>
						<?php echo $option ?>
					</select>
					<br>
					Special Instr.:
					<input type="text" name="special_instr" id ="special_instr">
					<br>
					<!-- for quantity, default value 1 -->
					Quantity:
					<input type="text" name="quantity" id="quantity" value="1" required>
					<br>
					-----------Fill below if QC done--------------
					<br>
					Quality Check Owner:
					<?php

						$get = mysql_query("SELECT * FROM qc_owner where 1");
						$option = '<option value = ""></option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['owner'].'">'.$row['owner'].'</option>';
						}
					?>
					<select name="qc_owner" id ="qc_owner" required>
						<?php echo $option ?>
					</select>	
					<br>
					Quality Check Status:
					<?php

						$get = mysql_query("SELECT * FROM qc_status where 1");
						$option = '<option value = ""></option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['status'].'">'.$row['status'].'</option>';
						}
					?>
					<select name="qc_status" id ="qc_status" onChange="changeTextBox();" required>
						<?php echo $option ?>
					</select>			
					</br>
					<input type = "text" name ="rejection_reason" id ="rejection_reason" placeholder ="Enter rejection reason" hidden>
					<br>
					<input type = "text" name ="maybe_reason" id ="maybe_reason" placeholder ="Enter maybe reason" hidden>
					<br>
					Condition:
					
					<?php

						$get = mysql_query("SELECT * FROM `condition` WHERE 1");
						$option = '<option value="" disabled="disabled" selected="selected">Select Condition</option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['type'].'">'.$row['type'].'</option>';
						}
					?>
					<select name="condition" id = "condition" onchange ="changeConditionTextBox();" hidden>
					<?php echo $option ?>
					</select>
					</br>
					<div id = "new_with_tag_prices" hidden>
						<br>
							<input type="number" name="retail_value" id ="retail_value" value ="" placeholder = "Retail Value" onkeypress ='validate(event)'>
						<br>
							<input type="number" name="suggested_price" id ="suggested_price" value ="" placeholder = "Suggested Price" onkeypress ='validate(event)' >
					</div>
					
					<input type = "text" name ="gently_used_comments" id ="gently_used_comments" placeholder ="Gently Used Comments" hidden>
					</br>

					<p class = "submit">
						<input type = "submit" value = "Insert">
					</p>
					</fieldset>
				</form>
				
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


form
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
/*************Fill sub-type based on type******************/

$(document).ready(
	function()
	{
		$('#type').change(
			function()
			{
				$.ajax({
				type:'POST',
				url:'inventory_sub_type_from_type.php',
				data:
				{
					'type':$('#type').val()
				},
			
				success:function(message)
				{
					$('#sub_type').html(message);
				}
				});
			
			}
		);		
	}
);


/*************Fill customer_email_id based on pickup_id******************/

$(document).ready(
	function()
	{
		$('#pickup_id').change(
			function()
			{
				$.ajax({
				type:'POST',
				url:'inventory_cust_email_id_from_pickup_id.php',
				data:
				{
					'pickup_id':$('#pickup_id').val()
				},
			
				success:function(message)
				{
					$('#customer_email_id').val(message);
				}
				});
			
			}
		);		
	}
);



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


/********************* Display rejection reason box only if QC status selected is rejected*********************/
function changeTextBox() {
    var comp = document.getElementById('qc_status');
    if(comp.value=='rejected')
	{   document.getElementById('rejection_reason').hidden=false;
		document.getElementById('maybe_reason').hidden=true;
		document.getElementById('gently_used').hidden=true;
	}
    else if (comp.value =='maybe')
	{       document.getElementById('rejection_reason').hidden=true;
			document.getElementById('maybe_reason').hidden=false;
			document.getElementById('gently_used').hidden=true;
	}
    else
	{       document.getElementById('rejection_reason').hidden=true;
			document.getElementById('maybe_reason').hidden=true;
			document.getElementById('condition').hidden=false;
	}

	}

/********************* Display Gently Used box only if condition is gently used*********************/
function changeConditionTextBox() {
    var comp = document.getElementById('condition');
    if(comp.value=='gently used')
	{   
		document.getElementById('gently_used_comments').hidden=false;
		document.getElementById('new_with_tag_prices').hidden=true;
	}

    else
	{
		document.getElementById('gently_used_comments').hidden=true;
		if(comp.value=='new with tag')
		{
			document.getElementById('new_with_tag_prices').hidden=false;			
		}
		else
		{
			document.getElementById('new_with_tag_prices').hidden=true;
		}
	}

}

$(window).bind("pageshow", function() 
{
  document.getElementById('rejection_reason').value='';
  document.getElementById('maybe_reason').value='';
  document.getElementById('retail_value').value='';
  document.getElementById('suggested_price').value='';
  document.getElementById('gently_used_comments').value='';
});

</script>