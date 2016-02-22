<?php
class Thredshare_Order_Model_observer{
public function updateVendorPoints($observer){

	if ($observer->getEvent()->getStatus().""!="really_confirmed"){
			
			return;
		}
$order = $observer->getEvent()->getOrder();
   $items = $order->getAllItems();
   

foreach($items as $i){

    $status = $i->getStatus();
	if($status != 'Refunded')
    {
		$vendorId=Mage::getModel("catalog/product")->load($i->getProductId())->getVendor();
	
	
		if ($vendorId){	
			if($i->getPrice()<750){
			$_point = $i->getPrice()-200;
			Mage::getModel("vendor/info")->addPointsForVendorUserId($i->getPrice()-200,$vendorId);
			}
			else if ($i->getPrice()<5000){
			$_point = 0.7*$i->getPrice();
			Mage::getModel("vendor/info")->addPointsForVendorUserId(0.7*$i->getPrice(),$vendorId);
			}
			else if ($i->getPrice()<50000){
			$_point = 0.8*$i->getPrice();
			Mage::getModel("vendor/info")->addPointsForVendorUserId(0.8*$i->getPrice(),$vendorId);
			}
			else {
			$_point = 0.85*$i->getPrice();
			Mage::getModel("vendor/info")->addPointsForVendorUserId(0.85*$i->getPrice(),$vendorId);
			}
		
			$customer=Mage::getModel('admin/user')->load($vendorId);
				if ($customer && $customer->getEmail()){
					$customerEmail=Mage::getModel("customer/customer")->setWebsiteId(1)->loadByEmail($customer->getEmail());

					if ($customerEmail && $customerEmail->getId()){
	
						$vendorRewardPoint=Mage::getModel('rewardpoints/customer')->load($customerEmail->getId());
						if (!$vendorRewardPoint || !$vendorRewardPoint->getCustomerId()){
		
						$vendorRewardPoint=Mage::getModel('rewardpoints/customer');
						$vendorRewardPoint->setCustomerId($customerEmail->getId());
			}
			}
			}
			
			
			$store_id = Mage::getModel('customer/customer')->load($customer_id)->getStoreId();
			
			$oldPoints = $vendorRewardPoint->getMwRewardPoint();
			$newPoints = $oldPoints + $_point;
			
			$results = Mage::helper('rewardpoints/data')->getTransactionExpiredPoints($_point,$store_id);
    		$expired_day = $results[0];
			$expired_time = $results[1] ;
			$point_remaining = $results[2];

			
			$expired_day = (int)Mage::helper('rewardpoints/data')->getExpirationDaysPoint($store_id);
			$details = "Item Sold".$i->getSKU()."-".$i->getName();
			$historyData = array('type_of_transaction'=>MW_RewardPoints_Model_Type::ADMIN_ADDITION,
							           		 'amount'=>(int)$_point, 
							           		 'balance'=>$vendorRewardPoint->getMwRewardPoint(), 
							           		 'transaction_detail'=>$details, 
							           		 'transaction_time'=>now(),
							           		 'expired_day'=>$expired_day,
								    		 'expired_time'=>$expired_time,
								    		 'point_remaining'=>$point_remaining,
		           		                     'history_order_id'=>null,
							           		 'status'=>MW_RewardPoints_Model_Status::COMPLETE);
		    
		    
		    $vendorRewardPoint->saveTransactionHistory($historyData);
    }
}
}
}
}
?>