<?php
class Thredshare_Order_Model_observer{
public function updateVendorPoints($observer){

	if ($observer->getEvent()->getStatus().""!="really_confirmed"){
			
			return;
		}
$order = $observer->getEvent()->getOrder();
   $items = $order->getAllVisibleItems();
   

foreach($items as $i){

	$vendorId=Mage::getModel("catalog/product")->load($i->getProductId())->getVendor();

	if ($vendorId){	
		Mage::getModel("vendor/info")->addPointsForVendorUserId(0.7*$i->getPrice(),$vendorId);
	}
}
}
}
?>