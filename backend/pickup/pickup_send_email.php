<?php

include 'db_config.php';

require_once '../../app/Mage.php';
Mage::app();

$customer_email_id = $_POST['customer_email_id'];
$body = $_POST['body'];
$subject = $_POST['subject'];
$pickup_id = $_POST['pickup_id'];   //Required to update status of pick up
$email_type = $_POST['email_type'];
$name = $_POST['customer_name'];
$mobile = $_POST['mobile'];
$shop_url = $_POST['shop_url'];
$powerpacket = $_POST['powerpacket'];

var_dump($shop_url."thisishere");
var_dump("this is the mobile no:".$mobile);
require('../PHPMailer/class.phpmailer.php');
require_once('../PHPMailer/class.smtp.php');

	try
		{
			echo "Preparing email<br>";
			$mail = new PHPMailer(); //New instance, with exceptions enabled
			$mail->IsSMTP();                           // tell the class to use SMTP
			$mail->SMTPAuth   = true;                  // enable SMTP authentication
			//$mail->SMTPDebug  = 2;  			
			$mail->Port       = 465;                    // set the SMTP server port
			$mail->Host       = "smtp.gmail.com"; // SMTP server
			$mail->Username   = "hello@rekinza.com";     // SMTP server username
			$mail->Password   = "thredshare15";        // SMTP server password
			$mail->SMTPSecure = "ssl";
			//$mail->AddReplyTo("pratyooshm@floshowers.com","First Last");
			$mail->SetFrom("hello@rekinza.com","Rekinza");
			$to = $customer_email_id;
			//$tos=explode(',',fetchVendorEmailFromName($vendorName));
			//foreach($tos as $to)
			$mail->AddAddress($to);
			$mail->Subject  = $subject;
			//$mail->AltBody    = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
			$mail->WordWrap   = 50; // set word wrap
			$mail->Body = $body;
			$mail->IsHTML(true); // send as HTML	
			
			if($mail->Send() == true)
				{
					echo 'Message has been sent.';
					$status_update = getStatusFromEmailType($email_type, $mobile, $name, $shop_url,$customer_email_id,$powerpacket);
					$status_field = $status_update['status_field'];
					$status_field_value = $status_update['status_field_value'];
					$query = "UPDATE thredshare_pickup SET $status_field = '$status_field_value' WHERE id = '$pickup_id' ";
	
					$result = mysql_query($query);
					
					
					if ($result == 'TRUE')
					{
						echo 'Record Updated Successfully';
					}
					else
					{
						echo 'Record Update Failed';
					}
				
				}
				else
				{
					echo "Oh damn";
				}
		} 
		catch (phpmailerException $e)
		{
			echo $e->errorMessage();
			echo "Oh no";
		}


function getStatusFromEmailType($email_type, $mobile, $name, $shop_url,$customer_email_id,$powerpacket)
{
	if ($email_type =='pickedup')
	{
		$status['status_field'] = 'status';
		$status['status_field_value'] = 'picked-up';
		return $status;
	}
	else if ($email_type =='received')
	{
		//starting NIK SMS TYPE THING
			$authKey = "99008A9xcctkyRXr565fed78";

			//Multiple mobiles numbers separated by comma
			$mobileNumber = "91{$mobile}";
			echo $mobileNumber;

			//Sender ID,While using route4 sender id should be 6 characters long.
			$senderId = "RKINZA";

			//Your message to send, Add URL encoding here.
			$message = urlencode("Hi {$name}. We have received your items! They will now be processed for quality and authenticity. Accepted items will be priced, photographed and uploaded for sale within 10 days. Thank you for trusting us.(hello@rekinza.com /+91-9810961177)");

			//Define route 
			$route = "4";
			//Prepare you post parameters
			$postData = array(
			    'authkey' => $authKey,
			    'mobiles' => $mobileNumber,
			    'message' => $message,
			    'sender' => $senderId,
			    'route' => $route
			);

			//API URL
			$url="http://api.msg91.com/sendhttp.php";

			// init the resource
			$ch = curl_init();
			curl_setopt_array($ch, array(
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POST => true,
			    CURLOPT_POSTFIELDS => $postData
			    //,CURLOPT_FOLLOWLOCATION => true
			));


			//Ignore SSL certificate verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


			//get response
			$output = curl_exec($ch);

			//Print error if any
			if(curl_errno($ch))
			{
			    echo 'error:' . curl_error($ch);
			}

			curl_close($ch);

			echo $output;

			//ENDING SMS TYPE THING.
			
			$status['status_field'] = 'status';
			$status['status_field_value'] = 'received';
			return $status;
	}
	else if ($email_type =='processed')
	{
			$status['status_field'] = 'status';
			$status['status_field_value'] = 'processed';
			return $status;
	}
		else if ($email_type =='live')
	{
		
					//starting NIK SMS TYPE THING
			$authKey = "99008A9xcctkyRXr565fed78";

			//Multiple mobiles numbers separated by comma
			$mobileNumber = "91{$mobile}";
			echo $mobileNumber;

			//Sender ID,While using route4 sender id should be 6 characters long.
			$senderId = "RKINZA";

			//Your message to send, Add URL encoding here.
			$message = urlencode("Hi {$name}. Your closet is now online! Check it out at {$shop_url}. Share the link with your family and friends now! (hello@rekinza.com /+91-9810961177)");

			//Define route 
			$route = "4";
			//Prepare you post parameters
			$postData = array(
			    'authkey' => $authKey,
			    'mobiles' => $mobileNumber,
			    'message' => $message,
			    'sender' => $senderId,
			    'route' => $route
			);

			//API URL
			$url="http://api.msg91.com/sendhttp.php";

			// init the resource
			$ch = curl_init();
			curl_setopt_array($ch, array(
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POST => true,
			    CURLOPT_POSTFIELDS => $postData
			    //,CURLOPT_FOLLOWLOCATION => true
			));


			//Ignore SSL certificate verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


			//get response
			$output = curl_exec($ch);

			//Print error if any
			if(curl_errno($ch))
			{
			    echo 'error:' . curl_error($ch);
			}

			curl_close($ch);

			echo $output;

			//ENDING SMS TYPE THING.

			//Add power packet reward to customer Kinza Cash - Stuti
			if($powerpacket == 1)
			{
				$powerpoints = 100;
				
				$customer=Mage::getModel("customer/customer")->setWebsiteId(1)->loadByEmail($customer_email_id);

				if ($customer && $customer->getId()){

					$vendorRewardPoint=Mage::getModel('rewardpoints/customer')->load($customer->getId());

				}
				
				$vendorRewardPoint->addRewardPoint($powerpoints);

				$store_id = Mage::getModel('customer/customer')->load($customer_id)->getStoreId();
				
				$oldPoints = $vendorRewardPoint->getMwRewardPoint();
				$newPoints = $oldPoints + $powerpoints;
				
				$results = Mage::helper('rewardpoints/data')->getTransactionExpiredPoints($powerpoints,$store_id);
	    		$expired_day = $results[0];
				$expired_time = $results[1] ;
				$point_remaining = $results[2];

				
				$expired_day = (int)Mage::helper('rewardpoints/data')->getExpirationDaysPoint($store_id);
				$details = "Power Packet Reward";
				$historyData = array('type_of_transaction'=>MW_RewardPoints_Model_Type::ADMIN_ADDITION,
								           		 'amount'=>(int)$powerpoints, 
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

			$status['status_field'] = 'status';
			$status['status_field_value'] = 'live';
			return $status;
	}

	else if ($email_type == 'cancelled')
	{
			//starting NIK SMS TYPE THING
			$authKey = "99008A9xcctkyRXr565fed78";

			//Multiple mobiles numbers separated by comma
			$mobileNumber = "91{$mobile}";
			echo $mobileNumber;

			//Sender ID,While using route4 sender id should be 6 characters long.
			$senderId = "RKINZA";

			//Your message to send, Add URL encoding here.
			$message = urlencode("Hi {$name}, this is Vidisha from Rekinza. You had shown an interest in selling with us by scheduling a pick up. However I noticed that your pickup did not happen. Please do let me know if I can reschedule your pickup for you. Feel free to call me in case you have any queries.(+91-9810961177)");

			//Define route 
			$route = "4";
			//Prepare you post parameters
			$postData = array(
			    'authkey' => $authKey,
			    'mobiles' => $mobileNumber,
			    'message' => $message,
			    'sender' => $senderId,
			    'route' => $route
			);

			//API URL
			$url="http://api.msg91.com/sendhttp.php";

			// init the resource
			$ch = curl_init();
			curl_setopt_array($ch, array(
			    CURLOPT_URL => $url,
			    CURLOPT_RETURNTRANSFER => true,
			    CURLOPT_POST => true,
			    CURLOPT_POSTFIELDS => $postData
			    //,CURLOPT_FOLLOWLOCATION => true
			));


			//Ignore SSL certificate verification
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);


			//get response
			$output = curl_exec($ch);

			//Print error if any
			if(curl_errno($ch))
			{
			    echo 'error:' . curl_error($ch);
			}

			curl_close($ch);

			echo $output;

			//ENDING SMS TYPE THING.

			$status['status_field'] = 'status';
			$status['status_field_value'] = 'cancelled';
			return $status;
	}	
	else if($email_type == 'return_initiated')
	{
		$status['status_field'] = 'unaccepted_item_status';
		$status['status_field_value'] = 'return initiated';
		return $status;
	}
	else if($email_type == 'return_dispatched')
	{
		$status['status_field'] = 'unaccepted_item_status';
		$status['status_field_value'] = 'return dispatched';
		return $status;
	}
	
}


?>

