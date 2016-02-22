<?php
date_default_timezone_set('Asia/Kolkata');
class MW_RewardPoints_Model_Cashout extends Mage_Core_Model_Abstract{
	
	protected function _construct(){
        $this->_init('rewardpoints/cashout');
    }
	
	public function cashOutRequest($type,$message,$amount){
		
		
		if (Mage::getSingleton('customer/session')->isLoggedIn()){
		$this->setType($type);
		$this->setMessage($message);
		$this->setPoints($amount);
		$this->setTimestamp(time());
		$this->setCustomerId(Mage::getSingleton('customer/session')->getCustomer()->getId());
		$this->save();
		}
	
	}
	
}

?>