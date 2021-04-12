<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>

<?php 
    $q = 'SELECT id, start_date, name, description, amount, state, cancel_date FROM payment_history WHERE user_id="' . get_app_info('main_userID') . '" AND app_id="' . get_app_info('app') . '"';
    $r = mysqli_query($mysqli, $q);

    $status = false;
    if ($r && mysqli_num_rows($r) > 0)
    {
        while($row = mysqli_fetch_array($r))
        {
            if($row['state'] == 'Active') {
                $status = true;
            }
        }  
    }
?>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/settings/main.js?8"></script>
<div class="row-fluid">
    <div>
        <?php if ($status): ?>
        <button class="btn btn-inverse" disabled>Subscribe</button>
        <button class="btn" id="cancel-subscription">Cancel Subscription</button>
        <?php else: ?>
        <a href="billing-agreement<?php if(get_app_info('is_sub_user')) echo '?i='.get_app_info('app');?>"><button class="btn btn-inverse">Subscribe</button></a>
        <button class="btn" disabled>Cancel Subscription</button>
        <?php endif; ?>
        <a href="https://sandbox.paypal.com" target="_blank">Login to your paypal account to see transaction history</a>
    </div>
    <br/>
    <div>
	    <table class="table table-striped responsive">
            <thead>
                <tr>
                    <th><?php echo _('Subscription Start Date');?></th>
                    <th><?php echo _('Plan Name');?></th>
                    <th><?php echo _('Amount');?></th>
                    <th><?php echo _('Status');?></th>
                </tr>
            </thead>
            <tbody>
                
                <?php 
                    $q = 'SELECT id, start_date, name, description, amount, state, cancel_date FROM payment_history WHERE user_id="' . get_app_info('main_userID') . '" AND app_id="' . get_app_info('app') . '"';
                    $r = mysqli_query($mysqli, $q);
                
                    if ($r && mysqli_num_rows($r) > 0)
                    {
                        while($row = mysqli_fetch_array($r))
                        {
                            $id = $row['id'];
                            $payment_date = $row['start_date'];
                            $plan_name = $row['name'];
                            $amount = $row['amount'];
                            
                            if($row['state'] == 'Active') {
                                echo '
                                <tr id="'.$id.'">
                                    <td>'. $payment_date . '</td>
                                    <td>'. $plan_name .'</td>
                                    <td>'. $amount .'</td>
                                    <td>Active</td>
                                </tr>
                                ';
                            } else {
                                echo '
                                <tr id="'.$id.'">
                                    <td>'. $payment_date . '</td>
                                    <td>'. $plan_name .'</td>
                                    <td>'. $amount .'</td>
                                    <td>Cancelled at ' . $row['cancel_date'] .'</td>
                                </tr>
                                ';
                            }
                            
                        }  
                    }
                ?>
                
            </tbody>
		</table>
    </div>
</div>
<div id="loading-overlay" class="load-wrapper">
    <div class="loader-wrapper">
        <div class="loader"></div>
    </div>
</div>
<style>

    .load-wrapper {
        display: none;
        position: fixed;
        top: 0px;
        left: 0px;
        z-index: 10000;
        width: 100vw;
        height: 100vh;
        background-color: #0000007a;
    }

    .loader-wrapper {
        position: absolute;
        left: 50%;
        top: 50%;
        transform: translate(-50%, -50%);
    }

    .loader {
        display: block;
        border: 10px solid #f3f3f3;
        border-top: 10px solid #3498db;
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 2s linear infinite;
    }

    .show {
        display: block;
    }

    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
</style>
<?php 
    echo '
    <script>
        $("#cancel-subscription").click(function(e) {
            $("#loading-overlay").addClass("show");
            e.preventDefault(); 
            plan_id = $("#selected_plan").val();
            c = confirm("Are you sure?.");
            if(c)
            {
                $.post("includes/plans/cancel-billing-agreement.php?i='. get_app_info('app') .'", {},
                    function(data) {
                        $("#loading-overlay").removeClass("show");
                        if(data)
                        {
                            // window.location = data;
                            // $("tr").fadeOut();
                            window.location.reload(false); 
                        }
                        else
                        {
                            alert("Sorry, unable to delete. Please try again later!");
                        }
                    }
                );
            }
        })
    </script>
    ';
?>

<?php include('includes/footer.php');?>
