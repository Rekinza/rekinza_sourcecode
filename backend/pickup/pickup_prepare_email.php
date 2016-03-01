<?php

include 'db_config.php';

$customer_email_id = $_GET['email_id'];
$customer_name = $_GET['name'];
$waybill_number = $_GET['waybill_number'];
$pickup_id = $_GET['pickup_id'];
$email_type = $_GET['email_type'];
$mobile = $_GET['mobile'];
$powerpacket = $_GET['powerpacket'];
$return_tracking_id = $_GET['return_tracking_id'];

if ($email_type == "pickedup")
{
	$subject = $customer_name.", Your Items Have Been Picked Up";
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/items-picked-up.html');
	
	$html = str_replace("{customer_name}", $customer_name, $html);
	$html = str_replace("{tracking_number}", $waybill_number, $html);
	
	$body = $html;
	
	echo $body;
}

else if($email_type == "received") 
{
	$subject = $customer_name.", Your Items Are Here";
	
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/items-received.html');
	
	$html = str_replace("{customer_name}", $customer_name, $html);
	
	$body = $html;
	
	echo $body;
}

else if($email_type == "processed") 
{
	$subject = $customer_name.", Your Items Have Been Processed";
	
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/items-processed.html');
	
	$query = "SELECT product_name, brand, qc_status, rejection_reason FROM inventory WHERE pickup_id = '$pickup_id' ";
	$result = mysql_query($query);
	
	if ($result)
	{
		$numresult = mysql_numrows($result);
		
		echo $numresult;
		
		if ($numresult > 0)
		{
			$i = 0;
			while ( $i < $numresult )
			{
				$product_name = mysql_result($result,$i,'product_name');
				$brand = mysql_result($result,$i,'brand');
				$qc_status = mysql_result($result,$i,'qc_status');
				$rejection_reason = mysql_result($result,$i,'rejection_reason');
				
				if(!$rejection_reason)
				{
					$rejection_reason = "-";
				}
				$index = $i+1;
				$html = str_replace("<td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\">", 
				"<td class = \"items\">".$index."</td><td class = \"items\">".$product_name."</td><td class = \"items\">".$brand."</td><td class = \"items\">".$qc_status."</td><td class = \"items\">".$rejection_reason."</td>"."<tr><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td></tr>",$html);
				
				$i++;
			}
			
			$html = str_replace("<td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td><td class = \"items\"></td>","",$html);
		}	
	}
	$html = str_replace("{customer_name}", $customer_name, $html);

	if($powerpacket == 1)
	{
		$power_pack_text = "<div>&nbsp;</div><div style=\"width: 10%;display: inline-block;text-align: center;\"><img src=\"http://www.rekinza.com/images/power-packet-badge.png\" style=\"width: 40%;\"></div><div style=\"width: 90%;display: inline-block;margin-bottom: 28px;vertical-align: top;margin-top: 4px;\">Congratulations! You have earned a <a href=\"http://rekinza.freshdesk.com/support/solutions/folders/8000076132\">Power Packet</a> badge. As a reward, we will add <b>Rs.100 KinzaCash </b> to your Rekinza account as soon as your items are live on the website.</div><div style=\"background-color:#eaeaea;padding:5px;text-align: center;\">A Power Packet is a packet with more than 75% items accepted by our quality check team.</div><br/>";
		
	}
	else
	{
		$power_pack_text = "";
	}

	$html = str_replace("{power_pack_reward}", $power_pack_text, $html);
	$body = $html;
	
	echo $body;
	
}

else if ($email_type == "live")
{
	$subject = $customer_name.", Your Items Are Live";
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/items-live.html');
	
	/* Get customer user ID */
	
	$query = "SELECT user_id FROM admin_user WHERE email = '$customer_email_id' ";
	$result = mysql_query($query);

	$numresult_userid = mysql_numrows($result);
	
	if($numresult_userid >0)
		{	
			
			$user_id = mysql_result($result,0,'user_id');
			
			echo $user_id."<br>";
			
			$q = "SELECT entity_id FROM openwriter_cartmart_profile WHERE user_id = '$user_id' ";
			$result = mysql_query($q);

			$numresult_shopid = mysql_numrows($result);
			
			if($numresult_shopid >0)
			{	
				$html = str_replace("{customer_name}", $customer_name, $html);
				
				$shop_id = mysql_result($result,0,'entity_id');
				echo $shop_id;
				$shop_url = "http://www.rekinza.com/cartmart/vendor/profile/id/".$shop_id;
				$html = str_replace("http://www.rekinza.com/cartmart/vendor/profile/id/", $shop_url, $html);
				
				$body = $html;
				
				echo $body;
			}
			else
			{
				echo "Shop ID not found";
			}
		}
		else
		{
			echo "customer id not found ";
		}
		
}

else if($email_type=="cancelled")
{

	$subject = $customer_name.", Give Us A Chance";
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/pickup-cancellation.html');
	$html = str_replace("{customer_name}", $customer_name, $html);
	$body = $html;
	echo $body;
}	

else if($email_type=="return_initiated")
{
	if($customer_name == NULL || $waybill_number == NULL)
	{
		echo "customer name or waybill number missing";
	}
	$subject = $customer_name.", Update on your item(s) to be returned";
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/unaccepted-returns-initiated.html');
	$html = str_replace("{customer_name}", $customer_name, $html);
	$body = $html;
	echo $body;
}	
else if($email_type=="return_dispatched")
{
	$subject = $customer_name.", your item(s) to be returned are on your way";
	$tracking_link = "http://www.pyck.in/customer_tracking/?tracking_id=".$return_tracking_id;
	$html = file_get_contents('http://www.rekinza.com/emails/pickup/unaccepted-returns-dispatched.html');
	$html = str_replace("{customer_name}", $customer_name, $html);
	$html = str_replace("{tracking_link}", $tracking_link, $html);
	$html = str_replace("{tracking_id}", $return_tracking_id, $html);
	$body = $html;
	echo $body;
}


	
else{
	
	echo "blank email";
}


?>
<html>
<form action = "pickup_send_email.php" method = "POST" target="_blank">
<td><input type = "text" value = '<?php echo $customer_email_id ?>' name = "customer_email_id"></td><br>
<td><input type = "text" value = '<?php echo $subject ?>' name = "subject"></td><br>
<td><textarea name = "body"><?php echo $body ?></textarea></td><br>
<td><input type = "text" value = '<?php echo $pickup_id ?>' name = "pickup_id" hidden = true></td><br>
<td><input type = "text" value = '<?php echo $email_type ?>' name = "email_type" hidden = true></td><br>
<td><input type = "text" value = '<?php echo $customer_name ?>' name = "customer_name" hidden = true></td><br>
<td><input type = "text" value = '<?php echo $mobile ?>' name = "mobile" hidden = true></td><br>
<td><input type = "text" value = '<?php echo $shop_url ?>' name = "shop_url" hidden = true></td><br>
<td><input type = "text" value = '<?php echo $powerpacket ?>' name = "powerpacket" hidden = true></td><br>
<td><input type = "Submit" value = "Send Email!"></td>
</form>
</html>