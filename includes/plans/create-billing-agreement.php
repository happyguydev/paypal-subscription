<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../../libs/PayPal-PHP-SDK/autoload.php');?>
<?php 

	require __DIR__ . '/../paypal_bootstrap.php';
	use PayPal\Api\Agreement;
    use PayPal\Api\Payer;
    use PayPal\Api\Plan;
    use PayPal\Api\ShippingAddress;
	//------------------------------------------------------//
	//                      	INIT                        //
	//------------------------------------------------------//

	$plan_id = $_POST['plan_id'];
    $q1 = 'SELECT * FROM payment_plans WHERE id = ' . $plan_id;
    $r = mysqli_query($mysqli, $q1);

    if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$payment_plan = $row;
	    }  
	} else {
        exit(1);
    }

    $agreement = new Agreement();

    $date = new DateTime();
    $start_date = $date->add(new DateInterval('PT6H'))->format('Y-m-d H:i:s');
    $formatted_date = $date->add(new DateInterval('PT6H'))->format(DateTime::ATOM);

    // echo $formatted_date;

    $agreement->setName($payment_plan['name'])
        ->setDescription($payment_plan['description'])
        ->setStartDate($formatted_date);

    $plan = new Plan();
    $plan->setId($payment_plan['paypalID']);
    $agreement->setPlan($plan);

    $payer = new Payer();
    $payer->setPaymentMethod('paypal');
    $agreement->setPayer($payer);

    // $shippingAddress = new ShippingAddress();
    // $shippingAddress->setLine1('111 First Street')
    //     ->setCity('Saratoga')
    //     ->setState('CA')
    //     ->setPostalCode('95070')
    //     ->setCountryCode('US');
    // $agreement->setShippingAddress($shippingAddress);

    $request = clone $agreement;

	try {
		$agreement = $agreement->create($apiContext);
		$approvalUrl = $agreement->getApprovalLink();
	} catch (Exception $ex) {
		var_dump($ex);
		exit(1);
	}
    // var_dump($query);
    $parts = parse_url($approvalUrl);
    parse_str($parts['query'], $query);
    $token = $query['token'];
    
	$query = "SELECT id FROM payment_history";
	$result = mysqli_query($mysqli, $query);

	if(empty($result)) {
		$query = "CREATE TABLE payment_history (
				id int(11) AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				description varchar(255),
                user_id varchar(255),
                app_id varchar(255),
				amount double,
				plan_id varchar(255) NOT NULL,
                agreement_id varchar(255),
                agreement_token varchar(255),
				is_active int default 0,
                state varchar(255),
                start_date datetime default CURRENT_TIMESTAMP,
				created_at datetime default CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
				)";
		$result = mysqli_query($mysqli, $query);
    }
    
    $q = 'SELECT * FROM payment_history WHERE user_id="' . get_app_info('main_userID') . '" AND app_id="' . get_app_info('app').'"';
    $r = mysqli_query($mysqli, $q);
    
    if ($r && mysqli_num_rows($r) > 0)
    {
        $query = 'UPDATE payment_history SET name="'. $payment_plan['name'] .'", description="'. $payment_plan['description'] .'", amount="'. $payment_plan['amount'] .'", plan_id="'. $payment_plan['paypalID'] .'", agreement_token="'. $token .'", start_date="'. $start_date .'", state=null, cancel_date=null, is_active=0 WHERE user_id="' . get_app_info('main_userID') . '" AND app_id="' . get_app_info('app') .'"';
    } else {
        $query = 'INSERT INTO payment_history (name, description, amount, plan_id, agreement_token, start_date, user_id, app_id) VALUES ("' . $payment_plan['name']. '", "'. $payment_plan['description'] .'", '. $payment_plan['amount'] .', "' . $payment_plan['paypalID'] .'", "'. $token .'", "'. $start_date .'", "'. get_app_info('main_userID') .'", "'. get_app_info('app') .'")';
    }

    $result = mysqli_query($mysqli, $query);
    
	echo $approvalUrl;
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
?>