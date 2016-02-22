<?php

include 'db_config.php';
require_once '../../../app/Mage.php';
Mage::app();

$sort_by = $_POST['sort_by'];
$category_id = $_POST['category_id'];

echo $sort_by."<br>";
echo $category_id."<br>";

$category = Mage::getModel('catalog/category')->setStoreId(Mage_Core_Model_App::ADMIN_STORE_ID)->load($category_id);   //Load category details from cat ID
$products = $category->getProductsPosition();


$count = count($products);


if ($sort_by == 'Newest')
{
	foreach ($products as $id=>$value){
		$products[$id] = $count - 1;         //Start from last position ..the oldest item should have last position and so on
		$count = $count - 1;
		}
}


	
else if ($sort_by =='Mix it up')
{
	echo "Mixing"."<br>";
	$products = array_reverse($products, true);
	$counter = 0;            //To keep track of how many products have been processed
	$first_fold_size = 8;      //Number of newest units that have to be displayed on top
	$loop_interval = $first_fold_size + ($count - $first_fold_size)/2 ; 
	$index = 1;         //Store the index position value of the current product being processed
	$skip = 0;          // Variable to control when to shuffle between new items and old items
	
	
	foreach ($products as $id=>$value)
	{
		if ($counter < $first_fold_size)
		{
			$products[$id] = $index;
			$index = $index + 1;
			$counter = $counter + 1;
		}

		else if ($counter <= $loop_interval )
		{
			if ($skip ==0 || ($skip & 1))	
			{
				// Do nothing
			}
			else
			{
				$index = $index + 2;
			}
				$products[$id] = $index;
				$index = $index + 1;
				$counter = $counter + 1;
				$skip = $skip + 1;
		}
		else 
		{
			if ($counter == $loop_interval + 1)
			{
				$index = $count;
				$skip = 0;
				
			}
			if ($skip ==0 || ($skip & 1))	
			{
				// Do nothing
			}
			else
			{
				$index = $index - 2;
			}
				$products[$id] = $index;
				$index = $index - 1;
				$counter = $counter + 1;
				$skip = $skip + 1;

		}
	}
	
var_dump($products);	
$category->setPostedProducts($products);
$category->save();

	$prod = $category->getProductsPosition();
echo "gettingPostedProducts:";
var_dump($prod);
echo "=================================================================================================";
asort($prod);
echo "now showing the same array sorted by value :";
var_dump($prod);
echo "pppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppppp";
$ctr = 0;
$pricearray = array();
$indexarray = array();
$newindex = 1;
$finalproductarray = array();
 
foreach ($prod as $key => $value) {
	

			
			$ctr++;
			$newindex++;
			// echo ("this is the id: $key");
			// var_dump($value);
			// var_dump($prod[$key]);
			$pproduct = Mage::getModel('catalog/product')->load($key);
			$pspecialPrice = $pproduct->getSpecialPrice();
			echo "key: $key";
			echo "special price";
			var_dump($pspecialPrice);
			$pricearray[$key] = $pspecialPrice;
			$indexarray[$key] = $value;
			//echo "this is the valkue of ctr in foreach: $ctr ===|| ";
			
			if ($ctr == 12 || $ctr == $count)
			 {
			 	//echo "inside if";
				arsort($pricearray);
				echo "pricearray for 12";
				var_dump($pricearray);
				// echo "new index inside 12 wala if is: $newindex";
				$newindex = $newindex - 12;
				if ($newindex < 1)    // This can happen if the total number of products in the category is <=12
				{
					$newindex = 1;
				}
				//echo "after subtracting 12 it is: $newindex";
				foreach ($pricearray as $id => $price) {
					foreach ($indexarray as $keyindex => $keyvalue) {
							if($id == $keyindex)
							{
								$indexarray[$keyindex] = $newindex;
								$finalproductarray[$keyindex] = $newindex;
								//var_dump($indexarray[$keyindex]);
								$newindex++;
							}

					}

				
				}
				echo "if for 12 items is complete. Now showing indexarray:";
				var_dump($indexarray);
				echo "this is the newindex after initializing 12 items : $newindex";
				$pricearray = array();
				$indexarray = array();
				$ctr = 0;
			}

		}
		echo "finalproductarray:";
		var_dump($finalproductarray);
		$category->setPostedProducts($finalproductarray);
		$category->save();
		echo "saved";
	
}
	
	





echo "Sorting complete";
?>