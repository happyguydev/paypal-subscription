<?php include('includes/header.php');?>
<?php include('includes/login/auth.php');?>
<?php include('includes/list/main.php');?>
<?php include('includes/helpers/short.php');?>
<?php
	if(get_app_info('is_sub_user')) 
	{
		if(get_app_info('app')!=get_app_info('restricted_to_app'))
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/list?i='.get_app_info('restricted_to_app').'"</script>';
			exit;
		}
		else if(get_app_info('campaigns_only')==1 && get_app_info('templates_only')==1 && get_app_info('lists_only')==1 && get_app_info('reports_only')==1)
		{
			echo '<script type="text/javascript">window.location="'.addslashes(get_app_info('path')).'/logout"</script>';
			exit;
		}
		else if(get_app_info('lists_only')==1)
		{
			go_to_next_allowed_section();
		}
	}
?>
<link href="<?php echo get_app_info('path');?>/js/tablesorter/theme.default.min.css" rel="stylesheet">
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/tablesorter/jquery.tablesorter.widgets.min.js"></script>
<script type="text/javascript" src="<?php echo get_app_info('path');?>/js/validate.js"></script>
<script type="text/javascript">
	$(document).ready(function() {
		$('table').tablesorter({
			widgets        : ['saveSort'],
			usNumberFormat : true,
			sortReset      : true,
			sortRestart    : true,
			headers: { 0: { sorter: false}, 5: {sorter: false}, 6: {sorter: false} }	
		});
		$("#new-list-btn, #add-one-btn").click(function(){
			$("#list-form").slideDown();
			$("#list_name").focus();
			$("#list-form").validate({
				rules: {
					list_name: {
						required: true	
					}
				},
				messages: {
					list_name: "<?php echo addslashes(_('List name is required'));?>"
				}
			});
		});
		$("#close-add-list-btn").click(function(){
			$("#list-form").slideUp();
		});
	});
</script>
<div class="row-fluid">
    <div class="span2">
        <?php include('includes/sidebar.php');?>
    </div> 
    <div class="span10">
    	<div>
	    	<p class="lead">
		    	<?php if(get_app_info('is_sub_user')):?>
			    	<?php echo get_app_data('app_name');?>
		    	<?php else:?>
			    	<a href="<?php echo get_app_info('path'); ?>/edit-brand?i=<?php echo get_app_info('app');?>" data-placement="right" title="<?php echo _('Edit brand settings');?>"><?php echo get_app_data('app_name');?></a>
		    	<?php endif;?>
		    </p>
    	</div>
    	<h2><?php echo _('Subscriber lists');?></h2><br/>
    	
    	<div style="clear:both;">
	    	<button class="btn" id="new-list-btn"><i class="icon-plus-sign"></i> <?php echo _('Add a new list');?></button>
	    	
	    	<form class="form-search" action="<?php echo get_app_info('path');?>/search-all-lists" method="GET" style="float:right;">
	    		<input type="hidden" name="i" value="<?php echo get_app_info('app');?>">
				<input type="text" class="input-medium search-query" name="s" style="width: 200px;">
				<button type="submit" class="btn"><i class="icon-search"></i> <?php echo _('Search all lists');?></button>
			</form>
		</div>
		
		<form action="<?php echo get_app_info('path')?>/includes/subscribers/import-add.php" method="POST" accept-charset="utf-8" class="form-vertical well" enctype="multipart/form-data" id="list-form">
	    	
	    	<label class="control-label" for="list_name"><?php echo _('List name');?></label>
	    	<div class="control-group">
		    	<div class="controls">
	              <input type="text" class="input-xlarge" id="list_name" name="list_name" placeholder="<?php echo _('The name of your new list');?>">
	            </div>
	        </div>
	        
	        <input type="hidden" name="app" value="<?php echo get_app_info('app');?>">
	        
	        <button type="submit" class="btn btn-inverse"><i class="icon icon-plus"></i> <?php echo _('Add');?></button> 
	        <a href="javascript:void(0)" id="close-add-list-btn"><span class="icon icon-remove-sign"></span> <?php echo _('Cancel');?></a>
	    </form>
		
		<br/>
		
		<?php $has_gdpr_subscribers = has_gdpr_subscribers();?>
    	
	    <table class="table table-striped responsive">
		  <thead>
		    <tr>
		      <th><?php echo _('ID');?></th>
		      <th><?php echo _('List');?></th>
		      <th><?php echo _('Active');?></th>
		      <?php if($has_gdpr_subscribers):?>
		      <th><?php echo _('GDPR');?></th>
		      <?php endif;?>
		      <th title="Segments"><?php echo _('Segs');?></th>
		      <th title="Autoresponders"><?php echo _('ARs');?></th>
		      <th><?php echo _('Unsubscribed');?></th>
		      <th><?php echo _('Bounced');?></th>
		      <th><?php echo _('Edit');?></th>
		      <th><?php echo _('Delete');?></th>
		    </tr>
		  </thead>
		  <tbody>
		  	
		  	<!-- Auto select encrypted listID -->
		  	<script type="text/javascript">
		  		$(document).ready(function() {
					$(".encrypted-list-id").mouseover(function(){
						$(this).selectText();
					});
				});
			</script>
		  	
		  	<?php 
			  	$limit = get_app_data('campaign_report_rows');
				$total_lists = totals($_GET['i']);
				$total_pages = ceil($total_lists/$limit);
				$p = isset($_GET['p']) ? $_GET['p'] : null;
				$offset = $p!=null ? ($p-1) * $limit : 0;
			  	
			  	$q = 'SELECT id, name FROM lists WHERE app = '.get_app_info('app').' AND userID = '.get_app_info('main_userID').' ORDER BY id DESC LIMIT '.$offset.','.$limit;
			  	$r = mysqli_query($mysqli, $q);
			  	if ($r && mysqli_num_rows($r) > 0)
			  	{
			  	    while($row = mysqli_fetch_array($r))
			  	    {
			  			$id = $row['id'];
			  			$name = stripslashes($row['name']);
			  			$subscribers_count = get_subscribers_count($id);
			  			$unsubscribers_count = get_unsubscribers_count($id);
			  			$bounces_count = get_bounced_count($id);
			  			if(strlen(short($id))>5) $listid = substr(short($id), 0, 5).'..';
			  			else $listid = short($id);
			  			
			  			$us_percentage = get_unsubscribers_percentage($subscribers_count, $unsubscribers_count);
			  			
			  			$b_percentage = get_bounced_percentage($bounces_count, $subscribers_count);
			  			
			  			$no_of_segs = get_segment_count($id);
			  			$seg_label = $no_of_segs>0 ? 'label-info' : 'label';
			  			$seg_count = $no_of_segs>0 ? '<a href="'.get_app_info('path').'/segments-list?i='.get_app_info('app').'&l='.$id.'" style="color:white;">'.$no_of_segs.'</a>' : '<a href="'.get_app_info('path').'/segments-list?i='.get_app_info('app').'&l='.$id.'">'.$no_of_segs.'</a>';
			  			
			  			$no_of_ars = get_ar_count($id);
			  			$ar_label = $no_of_ars>0 ? 'label-info' : 'label';
			  			$ar_count = $no_of_ars>0 ? '<a href="'.get_app_info('path').'/autoresponders-list?i='.get_app_info('app').'&l='.$id.'" style="color:white;">'.$no_of_ars.'</a>' : '<a href="'.get_app_info('path').'/autoresponders-list?i='.get_app_info('app').'&l='.$id.'"">'.$no_of_ars.'</a>';
			  				
			  			echo '
			  			
			  			<tr id="'.$id.'">
			  			  <td><span class="label" id="list'.$id.'">'.$listid.'</span><span class="label encrypted-list-id" id="list'.$id.'-encrypted" style="display:none;">'.short($id).'</span></td>
					      <td><a href="'.get_app_info('path').'/subscribers?i='.get_app_info('app').'&l='.$id.'" title="">'.$name.'</a></td>
					      <td id="progress'.$id.'"><span class="badge badge-success">'.$subscribers_count.'</span></td>';
					      
					    if($has_gdpr_subscribers)
					    {
						    $gdpr_count = get_gdpr_count($id);
						    $gdpr_percentage = get_gdpr_percentage($id, $gdpr_count);
						    echo '<td><span class="label label-warning">'.$gdpr_percentage.'%</span> '.$gdpr_count.'</td>';
						}
					      
					    echo '
					      <td><span class="label '.$seg_label.'">'.$seg_count.'</span></td>
					      <td><span class="label '.$ar_label.'">'.$ar_count.'</span></td>
					      <td><span class="label">'.$us_percentage.'%</span> '.$unsubscribers_count.'</td>
					      <td><span class="label">'.$b_percentage.'%</span> '.$bounces_count.'</td>
					      <td><a href="edit-list?i='.get_app_info('app').'&l='.$id.'" title=""><i class="icon icon-pencil"></i></a></td>
					      <td><a href="javascript:void(0)" title="'._('Delete').' '.$name.'?" id="delete-btn-'.$id.'" class="delete-list"><i class="icon icon-trash"></i></a></td>
					      <script type="text/javascript">
					    	$("#delete-btn-'.$id.'").click(function(e){
							e.preventDefault(); 
							c = confirm("'._('All subscribers, custom fields and autoresponders in this list will also be permanently deleted. Confirm delete').' '.$name.'?");
							if(c)
							{
								$.post("includes/list/delete.php", { list_id: '.$id.' },
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
							$("#list'.$id.'").mouseover(function(){
								$("#list'.$id.'-encrypted").show();
								$(this).hide();
							});
							$("#list'.$id.'-encrypted").mouseout(function(){
								$(this).hide();
								$("#list'.$id.'").show();
							});
							</script>
					    </tr>
						
			  			';
			  	    }  
			  	}
			  	else
			  	{
				  	echo '
				  		<tr>
				  			<td>'._('No list yet.').' <a href="javascript:void(0)" title="" id="add-one-btn" style="text-decoration: underline;">'._('Add one').'</a>!</td>
				  			<td></td>
				  			<td></td>
				  			<td></td>
				  			<td></td>
				  			<td></td>
				  			<td></td>
				  		</tr>
				  	';
			  	}
		  	?>
		    
		  </tbody>
		</table>	
		
		<?php pagination($limit); ?>	
    </div>   
</div>
<?php include('includes/footer.php');?>
