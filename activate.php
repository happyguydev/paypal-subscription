<?php

// If brand user attempts to reset password, the 'From email' that's saved in the brand settings will be used to send the password reset email via Amazon SES using the main user's IAM credentials.
// Email will be sent to the login email address.

//------------------------------------------------------//
//                          INIT                        //
//------------------------------------------------------//

include 'includes/functions.php';
include 'includes/helpers/PHPMailerAutoload.php';
require_once 'includes/helpers/ses.php';
require_once 'includes/helpers/EmailAddressValidator.php';
require_once 'includes/helpers/short.php';

//Get api key and id from GET string
$data = mysqli_real_escape_string($mysqli, $_GET['d']);
$data = short($data, true);
$data = json_decode($data);
$rpk = $data->{'rpk'};
$uid = $data->{'id'};

$app_path = get_app_info('path');

//------------------------------------------------------//
//                         EVENTS                       //
//------------------------------------------------------//

//Get 'main user' login email address
$r = mysqli_query($mysqli, 'SELECT id, username, s3_key, s3_secret, ses_endpoint, api_key, reset_password_key FROM login ORDER BY id ASC LIMIT 1');
if ($r) {
    while ($row = mysqli_fetch_array($r)) {
        $main_user_id = $row['id'];
        $main_user_email_address = $row['username'];
        $aws_key = $row['s3_key'];
        $aws_secret = $row['s3_secret'];
        $ses_endpoint = $row['ses_endpoint'];
        //$reset_password_key = $row['reset_password_key'];
    }
}

$q = 'SELECT id, name, username, company, app,  reset_password_key FROM login WHERE id = ' . $uid;
$r = mysqli_query($mysqli, $q);
if ($r && mysqli_num_rows($r) > 0) {
    while ($row = mysqli_fetch_array($r)) {
        $uid = $row['id'];
        $company = stripslashes($row['company']);
        $email = $row['username'];
        $app = $row['app'];
        $reset_password_key = $row['reset_password_key'];
    }

    if ($reset_password_key == $rpk) {

        // rpkmatches.. Now lets:
        // 1. Create a new encrypted password
        // 2. Update the login table with this password
        // 3. Send the temp password via email

        //create password
        $new_pass = ran_string(12, 12, true, false, true);
        $pass_encrypted = hash('sha512', $new_pass . 'PectGtma');

        //Change user's password to the new one
        $q = 'UPDATE login SET password = "' . $pass_encrypted . '" WHERE id = ' . $uid;
        $r = mysqli_query($mysqli, $q);

        if ($r) {
            //send a message to let them know
            $plain_text = _('Congratulation! Here is your temporary password to access you Ading account.') . ':
    
    ' . _('Temporary Password') . ': ' . $new_pass . '
    
    ' . _('For better security, we recommend changing your password immediately once you log back in.');
    
            $message = "<div style=\"margin: -10px -10px; padding:50px 30px 50px 30px; height:100%;\">
        <div style=\"margin:0 auto; max-width:660px;\">
            <div style=\"float: left; background-color: #FFFFFF; padding:10px 30px 10px 30px; border: 1px solid #f6f6f6;\">
                <div style=\"float: left; max-width: 106px; margin: 10px 20px 15px 0;\">
                    <img src=\"$app_path/img/key.gif\" style=\"width: 50px;\"/>
                </div>
                <div style=\"float: left; max-width:470px;\">
                    <p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
                        <strong style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 18px;\">" . _('Your temporary password') . "</strong>
                    </p>
                    <div style=\"line-height: 21px; min-height: 100px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
                        <p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">" . _('Congratulation! Here is your temporary password to access you Ading account.') . ":</p>
                        <p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px; margin-bottom: 25px; background-color:#f7f9fc; padding: 15px;\">
                            <strong>" . _('Temporary Password') . ": </strong>$new_pass
                        </p>
                        <p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">" . _('For better security, it\'s recommended to change your password immediately once you log back in.') . "</p>
                        <p style=\"line-height: 21px; font-family: Helvetica, Verdana, Arial, sans-serif; font-size: 12px;\">
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>";
    
            //send email to user
            $mail = new PHPMailer();
            if ($aws_key != '' && $aws_secret != '') {
                //Initialize ses class
                $ses = new SimpleEmailService($aws_key, $aws_secret, $ses_endpoint);
    
                //Check if user's AWS keys are valid
                $testAWSCreds = $ses->getSendQuota();
                if ($testAWSCreds) {
                    //Check if login email is verified in Amazon SES console
                    $v_addresses = $ses->ListIdentities();
                    $verifiedEmailsArray = array();
                    $verifiedDomainsArray = array();
                    foreach ($v_addresses['Addresses'] as $val) {
                        $validator = new EmailAddressValidator;
                        if ($validator->check_email_address($val)) {
                            array_push($verifiedEmailsArray, $val);
                        } else {
                            array_push($verifiedDomainsArray, $val);
                        }
    
                    }
                    $veriStatus = true;
                    $getIdentityVerificationAttributes = $ses->getIdentityVerificationAttributes($email);
                    foreach ($getIdentityVerificationAttributes['VerificationStatus'] as $getIdentityVerificationAttribute) {
                        if ($getIdentityVerificationAttribute == 'Pending') {
                            $veriStatus = false;
                        }
                    }
    
                    //If login email address is in Amazon SES console,
                    if (in_array($email, $verifiedEmailsArray) ) {
                        //and the email address is 'Verified'
                        if ($veriStatus) {
                            //Send password reset email via Amazon SES
                            $mail->IsAmazonSES();
                            $mail->AddAmazonSESKey($aws_key, $aws_secret);
                        }
                    }
                }
            }
            $mail->CharSet = "UTF-8";
            $mail->From = $main_user_email_address;
            $mail->FromName = $company;
            $mail->Subject = '[' . $company . '] ' . _('Your Temporary Password to Access Ading');
            $mail->AltBody = $plain_text;
            $mail->Body = $message;
            $mail->IsHTML(true);
            $mail->AddAddress($email, $company);
            $mail->Send();
    
            $q2 = 'UPDATE login SET reset_password_key = "" WHERE id = ' . $uid;
            mysqli_query($mysqli, $q2);
        }

    } else {
	header("Location:https://ading.us/invalid");
        exit;
    }


    header("Location: $app_path/login?i=1");
    exit;
} else {
	header("Location:https://ading.us/invalid");
    exit;
}

