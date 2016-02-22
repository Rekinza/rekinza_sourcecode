<?php
date_default_timezone_set('Asia/Kolkata');
class Thredshare_Pickup_PickupController extends Mage_Core_Controller_Front_Action{

	
	
	public function requestpickupAction(){
	
		$params=$this->getRequest()->getParams();
		$date=$params['date'];
		$mobile_no=$params['mobile_no'];
		$address1=$params['address1'];
		$address2=$params['address2'];
		$city=$params['city'];
		$state=$params['state'];
		$pincode=$params['pincode'];
		$name=$params['name'];
		$amount=$params['amount'];
		$customer=Mage::getSingleton("customer/session")->getCustomer();
		if (!$customer || !$customer->getEmail()){
		$this->_redirect("customer/account/login");
		return;
		}
		Mage::getModel("thredshare_pickup/pickup")->savePickUp($date,$mobile,$address1,$address2,$city,$state,$pincode,$name,$amount);
		Mage::getSingleton('core/session')->addSuccess("Your pick up request is submitted");
		 $storeId = Mage::app()->getStore()->getStoreId();
            $emailId = "hello@rekinza.com";
            $mailTemplate = Mage::getModel('core/email_template');
			$mailTemplate->addBcc('hello@rekinza.com');
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                ->setReplyTo($emailId);
			$mailTemplate->sendTransactional( 3,
            array('name'=>"REKINZA","email"=>$emailId),
            $customer->getEmail(),
            strtoupper($customer->getName()),
            array(
            'customer'  =>strtoupper($customer->getName()),
            'date' => $date,
			'day'=>date('l',strtotime($date))
            ));
		
			if (!$mailTemplate->getSentSuccess()) {
               	Mage::logException(new Exception('Cannot send pick up mail'));
				var_dump("Cannot send mail");
            }

		$this->loadLayout();
		$this->renderLayout();
	
	}
	
	
	public function getpickupAction(){
	
		
		$this->loadLayout();
		$this->renderLayout();
	
	}
	
	public function sendpickupreminderAction(){
	
		$time=time()+86400;
		$date=date("Y-m-d",$time);
		$requests=Mage::getModel("thredshare_pickup/pickup")->getCollection()->addFieldToFilter("pick_up_date",array("eq"=>$date));
		$storeId = Mage::app()->getStore()->getStoreId();
		 $emailId = "hello@rekinza.com";
		foreach ($requests as $req){
		
           $customer=Mage::getModel("customer/customer")->load($req->getCustomerId());
            $mailTemplate = Mage::getModel('core/email_template');              
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$storeId))
                ->setReplyTo($emailId);
			$mailTemplate->sendTransactional( 18,
            array('name'=>"REKINZA","email"=>$emailId),
            $customer->getEmail(),
            strtoupper($customer->getName()),
            array(
            'customer'  => strtoupper($customer->getName()),
            'date' => $date,
			'day'=>date('l',strtotime($date))
            ));
			
			if (!$mailTemplate->getSentSuccess()) {
               	Mage::logException(new Exception('Cannot send pick up mail'));
				var_dump("Cannot send mail");
            }else{
			echo "mail sent to ".$customer->getName()." at time ".$date;
			}
			
		}
	
	
	}

}

?>