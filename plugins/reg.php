
<?php include '../includes/functions.php';?>
<?php include 'verify.php';?>
require_once('../helpers/short.php');

<?php
//------------------------------------------------------//
//                      VARIABLES                       //
//------------------------------------------------------//
echo "Registration started";
$app_name = mysqli_real_escape_string($mysqli, $_POST['cname']);
$from_name = mysqli_real_escape_string($mysqli, $_POST['cname']);
$from_email = mysqli_real_escape_string($mysqli, $_POST['email']);
$login_email = mysqli_real_escape_string($mysqli, $_POST['email']);
$reply_to = mysqli_real_escape_string($mysqli, $_POST['email']);
$allowed_attachments = "jpeg,jpg,gif,png,pdf,zip";
$plan = mysqli_real_escape_string($mysqli, $_POST['plan_name']);
$currency = "USD";
$delivery_fee = "2";
$cost_per_recipient = null;
$password = "AdingUser";
$pass_encrypted = hash('sha512', $password . 'PectGtma');
$smtp_host = null; //mysqli_real_escape_string($mysqli, $_POST['smtp_host']);
$smtp_port = null; //mysqli_real_escape_string($mysqli, $_POST['smtp_port']);
$smtp_ssl = "ssl"; //mysqli_real_escape_string($mysqli, $_POST['smtp_ssl']);
$smtp_username = null; //mysqli_real_escape_string($mysqli, $_POST['smtp_username']);
$smtp_password = null; //mysqli_real_escape_string($mysqli, $_POST['smtp_password']);
$language = "en_US"; //mysqli_real_escape_string($mysqli, $_POST['language']);
$choose_limit = "custom"; //mysqli_real_escape_string($mysqli, $_POST['choose-limit']);
$campaigns = 0; //isset($_POST['campaigns']) ? 0 : 1;
$templates = 0; //isset($_POST['templates']) ? 0 : 1;
$lists = 0; //isset($_POST['lists-subscribers']) ? 0 : 1;
$reports = 0; //isset($_POST['reports']) ? 0 : 1;
$notify_campaign_sent = 1; //isset($_POST['notify_campaign_sent']) ? 1 : 0;
$campaign_report_rows = 10; //is_numeric($_POST['campaign_report_rows']) ? mysqli_real_escape_string($mysqli, (int)$_POST['campaign_report_rows']) : 10;
$query_string = null; //mysqli_real_escape_string($mysqli, $_POST['query_string']);
$gdpr_only = isset($_POST['gdpr_only']) ? 1 : 0;
$gdpr_only_ar = isset($_POST['gdpr_only_ar']) ? 1 : 0;
$gdpr_options = 1; //isset($_POST['gdpr_options']) ? 1 : 0;
$recaptcha_sitekey = null; //mysqli_real_escape_string($mysqli, $_POST['recaptcha_sitekey']);
$recaptcha_secretkey = null; //mysqli_real_escape_string($mysqli, $_POST['recaptcha_secretkey']);
$test_email_prefix = null; //mysqli_real_escape_string($mysqli, $_POST['test_email_prefix']);
$custom_domain_protocol = null; //mysqli_real_escape_string($mysqli, $_POST['protocol']);
$custom_domain = null; //mysqli_real_escape_string($mysqli, $_POST['custom_domain']);
$custom_domain_enabled = is_numeric($_POST['custom_domain_status']) ? mysqli_real_escape_string($mysqli, (int) $_POST['custom_domain_status']) : 0;

echo "Plan name: " . $plan;
echo "<br> today date is " . date('d');

if ($choose_limit == 'custom' || $choose_limit == 'no_expiry') {
    if ($plan == '1') {
        $monthly_limit = 2000;
    } else if ($plan == '2') {
        $monthly_limit = 20000;
    } else if ($plan == '3') {
        $monthly_limit = 100000;
    }

    if ($choose_limit == 'custom') {
        $reset_on_day = date('d');

        //Calculate month of next reset
        $today_unix_timestamp = time();
        $day_today = strftime("%e", $today_unix_timestamp);
        $month_today = strftime("%b", $today_unix_timestamp);
        $month_next = strtotime('1 ' . $month_today . ' +1 month');
        $month_next = strftime("%b", $month_next);
        if ($day_today < $reset_on_day) {
            $month_to_reset = $month_today;
        } else {
            $month_to_reset = $month_next;
        }

        $no_expiry = 0;
    } else if ($choose_limit == 'no_expiry') {
        $reset_on_day = 1;
        $month_to_reset = '';
        $no_expiry = 1;
    }
} else if ($choose_limit == 'unlimited') {
    $monthly_limit = -1;
    $reset_on_day = 1;
    $month_to_reset = '';
    $no_expiry = 0;
}

//------------------------------------------------------//
//                      FUNCTIONS                       //
//------------------------------------------------------//

$q = 'INSERT INTO apps (userID, app_name, from_name, from_email, reply_to, allowed_attachments, currency, delivery_fee, cost_per_recipient, smtp_host, smtp_port, smtp_ssl, smtp_username, smtp_password, app_key, allocated_quota, day_of_reset, month_of_next_reset, no_expiry, reports_only, campaigns_only, templates_only, lists_only, notify_campaign_sent, campaign_report_rows, query_string, gdpr_only, gdpr_only_ar, gdpr_options, recaptcha_sitekey, recaptcha_secretkey, test_email_prefix, custom_domain_protocol, custom_domain, custom_domain_enabled) VALUES (' . 1 . ', "' . $app_name . '", "' . $from_name . '", "' . $from_email . '", "' . $reply_to . '", "' . $allowed_attachments . '", "' . $currency . '", "' . $delivery_fee . '", "' . $cost_per_recipient . '", "' . $smtp_host . '", "' . $smtp_port . '", "' . $smtp_ssl . '", "' . $smtp_username . '", "' . $smtp_password . '", "' . ran_string(30, 30, true, false, true) . '", ' . $monthly_limit . ', ' . $reset_on_day . ', "' . $month_to_reset . '", ' . $no_expiry . ', ' . $reports . ', ' . $campaigns . ', ' . $templates . ', ' . $lists . ', ' . $notify_campaign_sent . ', ' . $campaign_report_rows . ', "' . $query_string . '", ' . $gdpr_only . ', ' . $gdpr_only_ar . ', ' . $gdpr_options . ', "' . $recaptcha_sitekey . '", "' . $recaptcha_secretkey . '", "' . $test_email_prefix . '", "' . $custom_domain_protocol . '", "' . $custom_domain . '", ' . $custom_domain_enabled . ')';
$r = mysqli_query($mysqli, $q);
if ($r) {
    //app id
        $id = mysqli_insert_id($mysqli);

    // for email verification
    $rpk = $rpk = ran_string(20, 20, true, false, true);  
    $token = short('{"rpk":"'.$rpk.'", "id":"'.$id.'"}');
        //insert new record
		$q = 'INSERT INTO login (name, company, username, password, tied_to, app, timezone, language, reset_password_key) VALUES ("'.$from_name.'", "'.$app_name.'", "'.$login_email.'", "'.$pass_encrypted.'", '. 1 .', '.$id.', "'.get_app_info('timezone').'", "'.$language.'", "'.$reset_password_key.'")';
        $r = mysqli_query($mysqli, $q);
        validateUser($from_email, $app_name,$token);
}

?>
