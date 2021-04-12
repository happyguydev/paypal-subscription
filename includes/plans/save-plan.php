<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../../libs/PayPal-PHP-SDK/autoload.php');?>
<?php 

	require __DIR__ . '/../paypal_bootstrap.php';
	use PayPal\Api\ChargeModel;
	use PayPal\Api\Currency;
	use PayPal\Api\MerchantPreferences;
	use PayPal\Api\PaymentDefinition;
	use PayPal\Api\Plan;
	use PayPal\Api\Patch;
	use PayPal\Api\PatchRequest;
	use PayPal\Common\PayPalModel;
	//------------------------------------------------------//
	//                      	INIT                        //
	//------------------------------------------------------//

	$plan_name = $_POST['plan_name'];
	$plan_description = $_POST['plan_description'];
	$plan_price = $_POST['plan_price'];

	$plan = new Plan();

	$plan->setName($plan_name)
		->setDescription($plan_description)
		->setType('fixed');

	$paymentDefinition = new PaymentDefinition();

	$paymentDefinition->setName('Regular Payments')
		->setType('REGULAR')
		->setFrequency('Month')
		->setFrequencyInterval("1")
		->setCycles("12")
		->setAmount(new Currency(array('value' => $plan_price, 'currency' => 'USD')));

	$chargeModel = new ChargeModel();
	$chargeModel->setType('SHIPPING')
		->setAmount(new Currency(array('value' => 0, 'currency' => 'USD')));
	
	$paymentDefinition->setChargeModels(array($chargeModel));
	
	$merchantPreferences = new MerchantPreferences();
	$baseUrl = getBaseUrl();

	$merchantPreferences->setReturnUrl("$baseUrl/ExecuteAgreement.php?i=" . get_app_info('app') . "&success=true")
		->setCancelUrl("$baseUrl/ExecuteAgreement.php?i=" . get_app_info('app') . "&success=false")
		->setAutoBillAmount("yes")
		->setInitialFailAmountAction("CONTINUE")
		->setMaxFailAttempts("0")
		->setSetupFee(new Currency(array('value' => 0, 'currency' => 'USD')));


	$plan->setPaymentDefinitions(array($paymentDefinition));
	$plan->setMerchantPreferences($merchantPreferences);

	$request = clone $plan;

	try {
		$output = $plan->create($apiContext);
	} catch (Exception $ex) {
		// ResultPrinter::printError("Created Plan", "Plan", null, $request, $ex);
		var_dump($ex);
		exit(1);
	}

	try {
		$patch = new Patch();
		$value = new PayPalModel('{
			"state": "ACTIVE"
		}');
		$patch->setOp('replace')
			->setPath('/')
			->setValue($value);
		$patchRequest = new PatchRequest();
		$patchRequest->addPatch($patch);
		$plan->update($patchRequest, $apiContext);
		$paypalId = $plan->getId();
	} catch (Exception $ex) {
		var_dump($ex);
		exit(1);
	}

	$query = "SELECT id FROM payment_plans";
	$result = mysqli_query($mysqli, $query);

	if(empty($result)) {
		$query = "CREATE TABLE payment_plans (
				id int(11) AUTO_INCREMENT,
				name varchar(255) NOT NULL,
				description varchar(255),
				amount double,
				paypalID varchar(255) NOT NULL,
				is_active int default 0,
				created_at datetime default CURRENT_TIMESTAMP,
				PRIMARY KEY  (id)
				)";
		$result = mysqli_query($mysqli, $query);
	}

	$query = 'INSERT INTO payment_plans (name, description, amount, paypalID, is_active) VALUES ("' . $plan_name. '", "'. $plan_description .'", '. $plan_price .', "' . $paypalId .'", "1")';
	$result = mysqli_query($mysqli, $query);
	// var_dump($query);
	echo '<script type="text/javascript">window.location = "'.addslashes(get_app_info('path')).'/create-plan";</script>';
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
?>