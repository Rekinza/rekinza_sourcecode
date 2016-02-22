<?php

include 'db_config.php';
require_once '../../app/Mage.php';
Mage::app();

$customer = Mage::getModel("customer/customer");
  $customer->setWebsiteId(Mage::app()->getWebsite()->getId()); 
  
$customer_email_id = $_GET['email_id'];
$waybill_number = $_GET['waybill_number'];
//$customer_name = $_GET['name'];
$returns_id = $_GET['returns_id'];
$email_type = $_GET['email_type'];

$customer->loadByEmail($customer_email_id);
$firstname = $customer->getfirstname(); 
if (strlen($firstname) < 2)
  {

  	$lastname = $customer->getlastname();
  	$customer_name = $firstname." ".$lastname;
  }
  else
  {
  	$customer_name = $firstname;
  }


//email type will always be approved though
if ($email_type == "picked-up")
{
	//Please fill the subject and html url accordingly
	$subject = "Rekinza Returns - Item(s) Picked up";
	$html = file_get_contents('http://rekinza.com/emails/returns/returns-picked.html');
	
	$html = str_replace("{customer_name}", $customer_name, $html);
	$html = str_replace("{tracking_number}", $waybill_number, $html);
	
	$body = $html;
	
	echo $body;
}

else if($email_type == "received") 
{
	$subject = "Rekinza Returns - Item(s) Received";
	
	$html = file_get_contents('http://rekinza.com/emails/returns/returns-received.html');
	
	$html = str_replace("{customer_name}", $customer_name, $html);
	
	$body = $html;
	
	echo $body;
}

else{
	
	echo "blank email";
}


?>
<html>
<form action = "returns_send_email.php" method = "POST" target="_blank">
<td><input type = "text" value = '<?php echo $customer_email_id ?>' name = "customer_email_id"></td><br>
<td><input type = "text" value = '<?php echo $subject ?>' name = "subject"></td><br>
<td><textarea name = "body"><?php echo $body ?></textarea></td><br>
<td><input type = "text" value = '<?php echo $returns_id ?>' name = "returns_id" hidden = true></td><br>
<td><input type = "text" value = '<?php echo $email_type ?>' name = "email_type" hidden = true></td><br>
<td><input type = "Submit" value = "Send Email!"></td>
</form>
</html>