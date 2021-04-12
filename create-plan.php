<?php include('includes/header.php');?>
<?php //include('includes/login/auth.php');?>

<div class="row-fluid">
    <div>
        <form action="<?php echo get_app_info('path')?>/includes/plans/save-plan.php<?php if(get_app_info('is_sub_user')) echo '?i='.get_app_info('app');?>" method="POST" accept-charset="utf-8" class="form-vertical" id="edit-form">
            <div class="span3">
                <label class="control-label" for="plan_name"><?php echo _('Plan name');?></label>
                <div class="control-group">
                    <div class="controls">
                        <input type="text" class="input-xlarge" id="plan_name" name="plan_name" placeholder="<?php echo _('Name of this plan');?>" required>
                    </div>
                </div>
                <label class="control-label" for="plan_description"><?php echo _('Plan description');?></label>
                <div class="control-group">
                    <div class="controls">
                        <input type="text" class="input-xlarge" id="plan_description" name="plan_description" placeholder="<?php echo _('Description of this plan');?>">
                    </div>
                </div>
                <label class="control-label" for="plan_price"><?php echo _('Plan price');?></label>
                <div class="control-group">
                    <div class="controls">
                        <input type="text" class="input-xlarge" id="plan_price" name="plan_price" placeholder="<?php echo _('Price of this plan');?>" required>
                    </div>
                </div>
                
                <button type="submit" class="btn btn-inverse" id="save-button"><i class="icon-ok icon-white"></i> <?php echo _('Save plan');?></button>
            </div>
        </form>
    </div>
    <br/>
    <div>
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th>Plan Name</th>
		      <th>Plan Description</th>
              <th>Price</th>
		      <th>Plan ID</th>
		      <th>Active</th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<?php 
				
			  	$q = 'SELECT * FROM payment_plans';
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
                        $id = $row['id'];
                        $name = $row['name'];
                        $description = $row['description'];
                        $amount = $row['amount'];
                        $paypalID = $row['paypalID'];
                        $is_active = $row['is_active'];
			  			
			  			echo '
					  		<tr id="'.$id.'">
						      <td><a href="edit-plan" title="">'.$name.'</a></td>
						      <td>'.$description.'</td>
						      <td>'. $amount .'</td> 
                              <td>'. $paypalID .'</td>
                              <td>'. ($is_active ? 'active' : 'inactive') .'</td>
						      <td><a href="javascript:void(0)" title="'._('Delete').' '.$name.'?" id="delete-btn-'.$id.'" class="delete-template"><i class="icon icon-trash"></i></a></td>
						      
						      <script type="text/javascript">
						    	$("#delete-btn-'.$id.'").click(function(e){
								e.preventDefault(); 
								c = confirm("'._('This plan will be permanently deleted. Confirm delete').' '.$name.'?");
								if(c)
								{
									$.post("includes/plans/delete.php", { template_id: '.$id.' },
									  function(data) {
									      if(data)
									      {
									      	$("#'.$id.'").fadeOut();
									      }
									      else
									      {
									      	alert("'._('Sorry, unable to delete. Please try again later!').'");
									      }
									  }
									);
								}
								});
							  </script>
							  
						    </tr>
					  	';
			  	    }  
			  	}
		  	?>
		    
		  </tbody>
		</table>
    </div>
</div>
<?php include('includes/footer.php');?>