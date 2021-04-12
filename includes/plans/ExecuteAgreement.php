<?php include('../functions.php');?>
<?php include('../login/auth.php');?>
<?php include('../../libs/PayPal-PHP-SDK/autoload.php');?>

<?php
require __DIR__ . '/../paypal_bootstrap.php';

if (isset($_GET['success']) && $_GET['success'] == 'true') {
    $token = $_GET['token'];
    $agreement = new \PayPal\Api\Agreement();
    try {
        $agreement->execute($token, $apiContext);
    } catch (Exception $ex) {
        var_dump($ex);
        exit(1);
    }
    try {
        $agreement = \PayPal\Api\Agreement::get($agreement->getId(), $apiContext);
    } catch (Exception $ex) {
        var_dump($ex);
        exit(1);
    }

    $q = 'UPDATE payment_history SET agreement_id="'. $agreement->getId() .'", state="'. $agreement->getState() .'", is_active=1 WHERE agreement_token = "' . $token .'"';
    $r = mysqli_query($mysqli, $q);
	// echo $q;
    // var_dump($agreement);
    echo '<script type="text/javascript">window.location = "'.addslashes(get_app_info('path')).'/manage-subscription' . (get_app_info('is_sub_user') ? '?i='. get_app_info('app') : '') .'";</script>';
    // return $agreement;
} else {
    echo "User Cancelled the Approval";
}