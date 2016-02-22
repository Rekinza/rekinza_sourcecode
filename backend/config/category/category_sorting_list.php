<?php

include 'db_config.php';
require_once '../../../app/Mage.php';
Mage::app();
	
$category = Mage::getModel('catalog/category');
$tree = $category->getTreeModel();
$tree->load();
$ids = $tree->getCollection()->getAllIds();


?>


<html>

	<head></head>
	<h1>Category Configuration</h1>
	<!--a href = "..\..\backend_home.php"><button class ="panel_button">Home</button></a-->
	<div id ="category_panels">
	
			<fieldset>
				
				<h3>Category/Sub Category List</h3>			
				<table id="report_table">
					
					<th>Name</th>
					<th></th>
					<th> Sort By</th>
					<?php
					if ($ids){
						foreach ($ids as $id){
							$cat = Mage::getModel('catalog/category');
							$cat->load($id);

							$entity_id = $cat->getId();
							$name = $cat->getName();
							$url_key = $cat->getUrlKey();
							$url_path = $cat->getUrlPath();
							?>
							<tr>
								<form action = "category_sort_update.php" method = "POST">
								<td><?php echo $name?></td>
								<td><input type = text name = 'category_id' value = <?php echo $entity_id; ?> readonly ='true'> </td>
								<td>
									<select name = 'sort_by' id = 'sort_by'> 
										<option value = 'Newest'>Newest</option>
										<option value = 'Mix it up'>Mix it up</option>
										<option value = 'High to Low'>High to Low</option>
								</td>
								<td><input type = "Submit" value = "Optimise"></td>
								</form>
							</tr>
							<?php
							
						}
					}
					?>
					</table>
<br>


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