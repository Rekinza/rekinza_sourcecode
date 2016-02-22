<?php

include '../app/Mage.php';
Mage::app();


$orderId = 100000259;
$order = Mage::getModel("sales/order")->loadByIncrementId($orderId); 
$ordered_items = $order->getAllVisibleItems();
foreach($ordered_items as $item){  
      $status = $item->getStatus();
	  echo $status."<br>";
}

?>