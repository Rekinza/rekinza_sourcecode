<html>
	<head>
		<script src="jquery-1.11.1.js"></script>
		<body>
			<h1>Inventory Update Panel</h1>

			<!------------Input new inventory details here----------->
			
			<div id = "inventory_search_form">
				
				<form action = "inventory_search_route.php" method = "POST">
				<fieldset>
					<legend>Enter search criteria</legend>
					Acceptance Status:
					<?php

						include 'db_config.php';

						$get = mysql_query("SELECT status FROM qc_status WHERE 1");
						$option = '<option value="" disabled="disabled" selected="selected">Select status</option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['status'].'">'.$row['status'].'</option>';
						}
					?>
					<select name="qc_status" id = "qc_status">
						<?php echo $option ?>
					</select>
					<br>
					Inventory data status:
					<select name ="inventory_data_status" id = "inventory_data_status">
						<option value = "Not complete">Not complete</option>
						<option value = "Complete">Complete</option>
					</select>
					<br>
					
					<p class = "submit">
						<input type = "submit" value = "Search">
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
</script>