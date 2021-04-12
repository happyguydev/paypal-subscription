<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../../libs/PayPal-PHP-SDK/autoload.php');?>
<?php 

	require __DIR__ . '/../paypal_bootstrap.php';
	use PayPal\Api\Agreement;
    use PayPal\Api\AgreementStateDescriptor;
    use PayPal\Api\Payer;
    use PayPal\Api\Plan;
    use PayPal\Api\ShippingAddress;
	//------------------------------------------------------//
	//                      	INIT                        //
	//------------------------------------------------------//

    $q = 'SELECT * FROM payment_history WHERE user_id=' . get_app_info('main_userID') . ' AND app_id=' . get_app_info('app');
    $r = mysqli_query($mysqli, $q);

    if ($r && mysqli_num_rows($r) > 0)
	{
	    while($row = mysqli_fetch_array($r))
	    {
			$agreementItem = $row;
	    }
	} else {
        exit(1);
    }

    $agreementId = $agreementItem['agreement_id'];                  
    $agreement = new Agreement();            

    $agreement->setId($agreementId);
    $agreementStateDescriptor = new AgreementStateDescriptor();
    $agreementStateDescriptor->setNote("Cancel the agreement");

    try {
        $agreement->cancel($agreementStateDescriptor, $apiContext);
        $cancelAgreementDetails = Agreement::get($agreement->getId(), $apiContext);                
    } catch (Exception $ex) {  
        var_dump($ex);
        exit(1);
    }

    $date = new DateTime();
    $cancel_date = $date->format('Y-m-d H:i:s');
	$query = 'UPDATE payment_history SET state=cancelled, cancel_date="'.$cancel_date.'", is_active=0 WHERE user_id="' . get_app_info('main_userID') . '" AND app_id="' . get_app_info('app') .'"';
    $result = mysqli_query($mysqli, $query);
    
	echo $cancelAgreementDetails;
	//------------------------------------------------------//
	//                      FUNCTIONS                       //
	//------------------------------------------------------//
	
?>