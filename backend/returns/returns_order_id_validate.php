<?php

include '../../app/Mage.php';

Mage::app();

$order_number =$_POST['order_number'];

$collection = Mage::getModel('sales/order')->getCollection()->addFieldToFilter('increment_id', $order_number);

if ($collection->count())   //Check if order ID entered is valid
{
    //Do nothing;    
}
else
{
	echo "Please enter valid Order ID";
}

?>