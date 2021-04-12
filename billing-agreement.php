<?php include('includes/header.php');?>
<?php //include('includes/login/auth.php');?>

<div class="row-fluid">
    <div>
        <select id="selected_plan">
        <?php 
            $q = 'SELECT * FROM payment_plans WHERE is_active = 1';
            $r = mysqli_query($mysqli, $q);
            if ($r && mysqli_num_rows($r) > 0)
            {
                while($row = mysqli_fetch_array($r))
                {
                    $id = $row['id'];
                    $name = $row['name'];
                    $amount = $row['amount'];
                    
                    echo '
                        <option value="' . $id . '">
                            ' . $name . '(' . $amount . ' USD)
                        </option>
                    ';
                }  
            }
        ?>
        </select>
        <br/>
        <button class="btn btn-info" id="pay_button">Pay by Paypal</button>
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
echo '<script type="text/javascript">
    $("#pay_button").click(function(e){
        $("#loading-overlay").addClass("show");
        e.preventDefault(); 
        plan_id = $("#selected_plan").val();
        c = confirm("Please click \"OK\" to confirm your subscription.");
        if(c)
        {
            $.post("includes/plans/create-billing-agreement.php?i='. get_app_info('app') .'", { plan_id: plan_id },
                function(data) {
                    $("#loading-overlay").removeClass("show");
                    if(data)
                    {
                        window.location = data;
                    }
                    else
                    {
                        alert("Sorry, unable to delete. Please try again later!");
                    }
                }
            );
        }
    });
</script>';
?>
<?php include('includes/footer.php');?>