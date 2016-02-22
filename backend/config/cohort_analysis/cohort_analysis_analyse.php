<?php

include '../db_config.php';
require_once '../../../app/Mage.php';
Mage::app();

$start_date = $_GET['start_date'];
$end_date = $_GET['end_date'];


/* Format our dates */
$start_date = date('Y-m-d H:i:s', strtotime($start_date));
$end_date = date('Y-m-d H:i:s', strtotime($end_date));

$analysis_end_date = date('Y-m-d', strtotime("+3 months", strtotime($end_date)));

/* Get the collection */
/*$orders = Mage::getModel('sales/order')->getCollection()
    ->addAttributeToFilter('created_at', array('from'=>$start_date, 'to'=>$end_date))
    ->addAttributeToFilter('status', array('eq' => Mage_Sales_Model_Order::STATE_COMPLETE));   // Decide the state
*/

//Get all orders between the requested date range

$orders = Mage::getModel('sales/order')->getCollection()
    ->addAttributeToFilter('created_at', array('from'=>$start_date, 'to'=>$end_date))
    ->addAttributeToFilter('status', array('neq' => 'customer_cancellation','payment_failure','canceled','closed_undelivered','undelivered'));   // Decide the state

if ($orders == NULL)
{
	echo "No results found";
}

else 
{
	$i = 0;
	foreach($orders as $order)
    {
    	// Get the email IDs of all orders
    	$email_id = $order->getShippingAddress()->getEmail();

    	$order_data[$i] = $email_id;
        $i++;

    }

    $unique_emails = array_unique($order_data);

    $start_of_rekinza_date = '2015-03-08 00:00:00';
   
    $one_day_before_analysis_start_date = date('Y-m-d', strtotime($start_date));
   
	$one_day_before_analysis_start_date = strtotime ( '-1 day' , strtotime ( $one_day_before_analysis_start_date ) ) ;
	$one_day_before_analysis_start_date = date ( 'Y-m-d H:i:s' , $one_day_before_analysis_start_date );

	echo "one day before ".$one_day_before_analysis_start_date."<br>";
	$index = 0;

    foreach($unique_emails as $email)
    {

    	$orders_by_email = Mage::getModel('sales/order')->getCollection()
    	->addAttributeToFilter('customer_email', $email)
    	->addAttributeToFilter('created_at', array('from'=>$start_of_rekinza_date, 'to'=>$one_day_before_analysis_start_date))
    	->addAttributeToFilter('status', array('neq' => 'customer_cancellation','payment_failure','canceled','closed_undelivered','undelivered'));   // Decide the state


	    if($orders_by_email->getSize() == 0)
	    {
	    	$new_customers_emails[$index] = $email; 
	    }
	    $index++;
    }

    //var_dump($new_customers_emails);


    //Get all the unique email IDs
    
    $within_one_month = 0;
    $within_two_months = 0;
    $within_three_months = 0;
    $repeat_customer_count = 0;
    $one_repeat = 0;
    $two_repeats = 0;
    $three_repeats = 0;
    $more_than_three_repeats = 0;
    $repeat_order_count =0;
    $total_new_order_count = 0;
    $total_new_order_count = count($new_customers_emails);

    echo $total_new_order_count;


 	// Process for each email ID
    foreach($new_customers_emails as $email)
    {

    	$orders_by_email = Mage::getModel('sales/order')->getCollection()
    	->addAttributeToFilter('customer_email', $email)
    	->addAttributeToFilter('created_at', array('from'=>$start_date, 'to'=>$analysis_end_date))
    	->addAttributeToFilter('status', array('neq' => 'customer_cancellation','payment_failure','canceled','closed_undelivered','undelivered'));   // Decide the state


    	// Variable to check whether first entry of a particular email ID is to be processed
    	$first_entry_check = TRUE;
    	$is_repeat_customer = FALSE;   // Set to true if multiple orders of a customer are found
    	$repeat_count_for_single_customer = 0;
    	//$total_new_order_count = $total_new_order_count + $orders_by_email->getSize();

		foreach($orders_by_email as $order)
	    {
	    	
	    	if($first_entry_check == TRUE)
	    	{
	    		$first_order_date = $order->getCreatedAt();
	    		$first_order_date = date('Y-m-d', strtotime($start_date));
	    		$first_entry_check = FALSE;
	    	}

	    	else
	    	{
	    		$repeat_count_for_single_customer = $repeat_count_for_single_customer + 1;

	    		$order_date = $order->getCreatedAt();
	    		$order_date = date('Y-m-d', strtotime($order_date));
	    		$repeat_order_date_diff = ceil(abs($order_date - $first_order_date) / 86400);

	    		$repeat_order_count++;
	    		
	    		

	    		if($repeat_order_date_diff <31 && $is_repeat_customer == FALSE)
	    		{
	    			$within_one_month = $within_one_month + 1;
	    			$is_repeat_customer = TRUE;
	    		}
	    		else if ($repeat_order_date_diff < 61 && $is_repeat_customer == FALSE)
	    		{
	    			$within_two_months = $within_two_months + 1;
	    		}
	    		else if ($is_repeat_customer == FALSE)
	    		{
	    			$within_three_months = $within_three_months + 1;
	    		}



	    	}

	    }

	    if($is_repeat_customer == TRUE)
	    	{
	    		$repeat_customer_count = $repeat_customer_count + 1;

	    		if ($repeat_count_for_single_customer ==1)
	    		{
	    			$one_repeat = $one_repeat + 1;
	    		}
	    		else if ($repeat_count_for_single_customer == 2)
	    		{
	    			$two_repeats = $two_repeats + 1;
	    		}
	    		else if ($repeat_count_for_single_customer == 3)
	    		{
	    			$three_repeats = $three_repeats + 1;
	    		}
	    		else
	    		{
	    			$more_than_three_repeats = $more_than_three_repeats + 1;
	    		}
	    	}

    }
   
    $repeat_order_percent = $repeat_order_count/$total_new_order_count * 100;
    $repeat_customer_percent = $repeat_customer_count/$total_new_order_count * 100;
    echo "start date is ".$start_date."<br>";
	echo "end date is ".$end_date."<br>";
	echo "total new orders ".$total_new_order_count."<br>";
	echo "total repeat orders ".$repeat_order_count."<br>";
    echo "repeat customer count ".$repeat_customer_count."<br>";
    echo "within 1 month ".$within_one_month."<br>";
    echo "within 2 months ".$within_two_months."<br>";
    echo "within 3 months ".$within_three_months."<br>";

    echo "one repeat ".$one_repeat."<br>";
    echo "two repeats ".$two_repeats."<br>";
    echo "three repeats ".$three_repeats."<br>";
    echo "more than 3 repeats ".$more_than_three_repeats."<br>";
    echo "percent of customers repeating orders ".$repeat_customer_percent."<br>";
    

}


?>