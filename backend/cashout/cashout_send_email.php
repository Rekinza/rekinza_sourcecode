<?php
include 'db_config.php';
$customer_email_id = $_POST['customer_email_id'];
$body = $_POST['body'];
$subject = $_POST['subject'];
$cashout_id = $_POST['cashout_id'];   //Required to update status of pick up
$email_type = $_POST['email_type'];
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
					$status = getStatusFromEmailType($email_type);
					//when email sent from any state, state in table is automatically updated to "approved"
					$query = "UPDATE mw_rewardpoints_cashout SET state = '$status' WHERE id = '$cashout_id' ";
					echo $status;
					echo $cashout_id;
	
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
//not required but left the function as it is so incase other cases come up later
function getStatusFromEmailType($email_type)
{
	if ($email_type =='approved')
	{
		return 'approved';
	}
	
}
?>