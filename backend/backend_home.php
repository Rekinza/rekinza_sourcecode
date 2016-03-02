<html>

	<head></head>
	<h1>Welcome to Rekinza !</h1>

	<div id ="inventory_panels">
		<fieldset>
			<legend>Inventory Panels</legend>
			<a href = "inventory\inventory_new.php"><button class ="panel_button">New Inventory</button></a>
			<br><br>
			<a href = "inventory\inventory_search.php"><button class ="panel_button">Search Inventory</button></a>
			<br><br>
			<a href = "inventory\inventory_pending_upload.php"><button class ="panel_button">Inventory Pending Upload</button></a>
			
			<br><br>
			<a href = "inventory\inventory_summary.php"><button class ="panel_button">Inventory Summary</button></a>
			<br><br>
			
			</fieldset>
			
			<fieldset>
				<legend>Search Panels</legend>
				<form action = "inventory\inventory_search_by_filter.php" method = "GET">
				<h3>Search SKU</h3>			
					<input type = "text" name = "sku_name">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "inventory\inventory_search_by_seller.php" method = "GET">
				<h3>Search by Seller</h3>			
					<input type = "text" name = "customer_email_id" placeholder ="Enter Email ID">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
			</fieldset>
			
			<fieldset>
				<legend>Control Panels</legend>
				<br>
				<a href = "config\brands_config.php"><button class ="panel_button">Brand Config</button></a>
				<br>
				<a href = "config\category\category_sorting_list.php"><button class ="panel_button">Category Config</button></a>
				<br>
				<a href = "config\sku_config.php"><button class ="panel_button">SKU Config</button></a>
				<br>
				<a href = "config\sql_strict_removal.php"><button class ="panel_button">Fix the Panel</button></a>
				<br>
				<a href = "config\cohort_analysis\cohort_analysis_input.html"><button class ="panel_button">Cohort Analysis</button></a>
				<br>
			</fieldset>
			
			<fieldset>
				<legend>Pick Up Panel</legend>
				<form action = "pickup\pickup_seller_summary.php" method = "POST">
				<h3>Search by Seller</h3>			
					<input type = "text" name = "customer_email_id" placeholder ="Enter Email ID">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "pickup\pickup_seller_summary.php" method = "POST">
				<h3>Search by Pick up Date Range</h3>			
					Start Date:<input type = "date" name = "pickup_start_date">
					End Date:<input type = "date" name = "pickup_end_date">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "pickup\pickup_seller_summary.php" method = "POST">
				<h3>Search by Waybill Number</h3>			
					<input type = "text" name = "waybill_number" placeholder ="Enter Waybill Number">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "pickup\pickup_seller_summary.php" method = "POST">
				<h3>Search by Pick up Status</h3>
					<select name ="status_search" id = "status_search">
					<?php
					//$row = array('requested','scheduled','received','acknowledged','processed','follow-up');
					$row = array('requested','scheduled','picked-up','received','processed','live','cancelled','follow-up');
					
					$option = "";
					for($j = 0; $j < 8; $j++)
					{
				
						$option .= '<option value = "'.$row[$j].'">'.$row[$j].'</option>';
							
					}
					?>
					<?php echo $option ?>
					</select>
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>
				</form>
				<form action = "pickup\pickup_seller_summary.php" method = "POST">
				<h3>Search by Return Dispatch Date Range</h3>			
					Start Date:<input type = "date" name = "return_dispatch_start_date">
					End Date:<input type = "date" name = "return_dispatch_end_date">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>
				</form>
				
				</form>
				
			</fieldset>

			<fieldset>
				<legend>Cash Out Panel</legend>
				<form action = "cashout\cashout_summary.php" method = "POST">
				<h3>Search by Seller</h3>			
					<input type = "text" name = "customer_email_id" placeholder ="Enter Email ID">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "cashout\cashout_summary.php" method = "POST">
				<h3>Search by Date Range</h3>			
					Start Date:<input type = "date" name = "start_date">
					End Date:<input type = "date" name = "end_date">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>
					
					<form action = "cashout\cashout_summary.php" method = "POST">
				<h3>Search by Status</h3>			
					<select name ="cashout_status" id = "cashout_status">
					<?php

						include 'db_config.php';
						
						$get = mysql_query("SELECT state FROM cashout_state");
						$option = '<option value="" disabled="disabled" selected="selected">Select Type</option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['state'].'">'.$row['state'].'</option>';
						}					
					?>
					<?php echo $option ?>
					</select>
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			

				</form>
			</fieldset>

			<fieldset>
				<legend>Returns Panel</legend>
				<form action = "returns\returns_list.php" method = "POST">
				<h3>Search by Customer</h3>			
					<input type = "text" name = "customer_email_id" placeholder ="Enter Email ID">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "returns\returns_list.php" method = "POST">
				<h3>Search by Date Range</h3>			
					Start Date:<input type = "date" name = "start_date">
					End Date:<input type = "date" name = "end_date">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "returns\returns_list.php" method = "POST">
				<h3>Search by Status</h3>			
					<select name ="return_status" id = "return_status">
					<?php
					include 'db_config.php'; 	//made a new db_config in the root itself
					$get = mysql_query("SELECT status FROM returns_status");
						$option = '<option value="" disabled="disabled" selected="selected">Select Type</option>';
						while($row = mysql_fetch_assoc($get))
						{
						  $option .= '<option value = "'.$row['status'].'">'.$row['status'].'</option>';
						}					
					?>
					<?php echo $option ?>
					</select>
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
				<form action = "returns\returns_list.php" method = "POST">
					<h3>Search by Waybill Number</h3>			
					<input type = "text" name = "waybill_number" placeholder ="Enter Waybill Number">
					<p class = "submit">
						<input type = "submit" value = "Search">
					</p>			
				</form>
				
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