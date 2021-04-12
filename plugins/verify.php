<?php

include('../includes/functions.php');
include('../helpers/PHPMailerAutoload.php');
require_once('../helpers/ses.php');
require_once('../helpers/EmailAddressValidator.php');
require_once('../helpers/short.php');
$app_path = "http://app.ading.us";
function validateUser($email, $usercompay, $token) {
     $validationLink = $app_path.'/includes/login/reset.php?d='.$token;

     //Get 'main user' login email address
$r = mysqli_query($mysqli, 'SELECT id, username, s3_key, s3_secret, ses_endpoint, api_key FROM login ORDER BY id ASC LIMIT 1');
if ($r) 
{
	while($row = mysqli_fetch_array($r)) 
	{
		$main_user_id = $row['id'];
		$main_user_email_address = $row['username'];
		$aws_key = stripslashes($row['s3_key']);
		$aws_secret = stripslashes($row['s3_secret']);
		$ses_endpoint = stripslashes($row['ses_endpoint']);
		$api_key = stripslashes($row['api_key']);
	}
}


         //send a password reset confirmation email 
	$plain_text = 'Thank you for signing up for Ading Smart Email Program. \r\n

To verify your account, please click the following link:

Accout validation link: '.$validationLink;

    $message = "<div style=\"margin: -10px -10px; padding:50px 30px 50px 30px; height:100%;\">
	<div style=\"margin:0 auto; max-width:660px;\">
		<div style=\"float: left; background-color: #FFFFFF; padding:10px 30px 10px 30px; border: 1px solid #f6f6f6;\">
			<div style=\"float: left; max-width: 106px; margin: 10px 20px 15px 0;\">
				<img src=\"$app_path/img/key.gif\" style=\"width: 50px;\"/>
			</div>
			<div style=\"float: left; max-width:470px;\">
				<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
					<strong style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 18px;\">"._('Verify your account')."</strong>
				</p>	
				<div style=\"line-height: 21px; min-height: 100px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">"._('Thank you for signing up for Ading Smart Email Program.')."</p>
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">"._('To verify your account, please click the following link:')."</p>
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px; margin-bottom: 25px; background-color:#f7f9fc; padding: 15px;\">
						<strong>"._('Account verification link').": </strong><a style=\"color:#4371AB; text-decoration:none;\" href=\"$validationLink\">Verify My Ading Account</a>
					</p>
					<p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
					</p>
				</div>
			</div>
		</div>
	</div>
</div>";

 //send email to me
	$mail = new PHPMailer();
	if($aws_key!='' && $aws_secret!='')
	{
		//Initialize ses class
		$ses = new SimpleEmailService($aws_key, $aws_secret, $ses_endpoint);
		
		//Check if user's AWS keys are valid
		$testAWSCreds = $ses->getSendQuota();
		if($testAWSCreds)
		{			
			//Check if login email is verified in Amazon SES console
			$v_addresses = $ses->ListIdentities();
			$verifiedEmailsArray = array();
			$verifiedDomainsArray = array();
			foreach($v_addresses['Addresses'] as $val){
				$validator = new EmailAddressValidator;
				if ($validator->check_email_address($val)) array_push($verifiedEmailsArray, $val);
				else array_push($verifiedDomainsArray, $val);
			}
			$veriStatus = true;
			$getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($email);
			foreach($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) 
				if($getIdentityVerificationAttribute=='Pending') $veriStatus = false;
			
			//If login email address is in Amazon SES console,
			if(in_array($email, $verifiedEmailsArray) || in_array($email_domain, $verifiedDomainsArray))
			{
				//and the email address is 'Verified'
				if($veriStatus)
				{
					//Send password reset email via Amazon SES
					$mail->IsAmazonSES();
					$mail->AddAmazonSESKey($aws_key, $aws_secret);
				}
			}
		}
	}
	$mail->CharSet	  =	"UTF-8";
	$mail->From       = $main_user_email_address;
	$mail->FromName   = $usercompay;
	$mail->Subject = '['.$company.'] '._('Verify Your Ading Account');
	$mail->AltBody = $plain_text;
	$mail->Body = $message;
	$mail->IsHTML(true);
	$mail->AddAddress($email, $usercompay);
	$mail->Send();
    
    echo $email == $main_user_email_address ? 'main_user' : true;
    exit;
}

?>
