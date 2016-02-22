<?php
class Thredshare_Pickup_Model_Pickup extends Mage_Core_Model_Abstract{
	
	
	public function _construct(){
	
		$this->_init("thredshare_pickup/pickup");
	}
	
	public function savePickUp($date,$mobile,$address1,$address2,$city,$state,$pincode,$name,$amount){
	
		if (Mage::getSingleton('customer/session')->isLoggedIn()){
		
			$customer=Mage::getSingleton('customer/session')->getCustomer();
			
			$this->setCustomerId($customer->getId());
			$this->setState($state);
			$this->setMobile($mobile);
			$this->setAddress1($address1);
			$this->setAmount($amount);
			$this->setAddress2($address2);
			$this->setCity($city);
		
			$date = Mage::getModel('core/date')->date("m/d/Y", $date);
			
			$this->setPickUpDate($date);
			
			
			$this->setPincode($pincode);
			$this->setName($name);
			$this->save();
		}
	
	
	}


}

?>