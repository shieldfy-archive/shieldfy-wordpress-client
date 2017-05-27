<?php
 if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$res = shieldfy_call_server('dashboard',array('x'=>1),SHIELDFY_TOKEN);
$res = json_decode($res,1);
?>

<div class="wrap">
	<h2>Shieldfy Mini Dashboard</h2>
	<p>&nbsp;</p>
	<div class="alert alert-info">
		<strong>Note</strong>
		 For full statistics and control go to your panel at <a class="btn btn-default" href="https://shieldfy.com/app" target="_blank">https://shieldfy.com/app</a>
	</div>


	<div class="row">
		<div class="col-sm-4">
			<div class="panel panel-default">
			  <div class="panel-body text-center">
			  	<i class="fa fa-bomb fa-3x"></i><br /><br />
			  	(<b><?php echo $res['data']['attacks_count']; ?></b>)
			    Attacks Blocked
			  </div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="panel panel-default">
			  <div class="panel-body text-center">
			  	<i class="fa fa-bug fa-3x"></i><br /><br />
			  	(<b><?php echo $res['data']['malwares_count']; ?></b>)
			    Malware Detected
			  </div>
			</div>
		</div>
		<div class="col-sm-4">
			<div class="panel panel-default">
			  <div class="panel-body text-center">
			  	<i class="fa fa-ban fa-3x"></i><br /><br />
			  	(<b><?php echo $res['data']['bans_count']; ?></b>)
			    IP Banned
			  </div>
			</div>
		</div>
	</div>
  <?php
  /*
	<div class="row">
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">Hits</div>
				<div class="panel-body text-center">
					<div id="hits" class="sparkline" data-type="line"  data-spot-Radius="3" data-highlight-Spot-Color="#f39c12" data-highlight-Line-Color="#222" data-min-Spot-Color="#f56954" data-max-Spot-Color="#00a65a" data-spot-Color="#39CCCC" data-offset="90" data-width="100%" data-height="100px" data-line-Width="2" data-line-Color="#39CCCC" data-fill-Color="rgba(57, 204, 204, 0.08)" data-tooltip-Suffix=" Hits">
				      	<?php $x = ''; ?>
				        <?php
				        	foreach($res['data']['hits'] as $hit):
								echo $x;
								echo $hit['total'];
								$x = ',';
				        	endforeach;
				        ?>

				    </div>

				    <div id="hits_values" style="display:none">
				      	<?php foreach($res['data']['hits'] as $hit): ?>
							<div class="val-<?php echo $hit['total']; ?>"><?php echo $hit['day']; ?></div>
				        <?php endforeach; ?>
				    </div>
				</div>
			</div>
		</div>
		<div class="col-sm-6">
			<div class="panel panel-default">
				<div class="panel-heading">Attacks</div>
				<div class="panel-body text-center">

					<div id="attacks" class="sparkline" data-type="line"  data-spot-Radius="3" data-highlight-Spot-Color="#f39c12" data-highlight-Line-Color="#222" data-min-Spot-Color="#f56954" data-max-Spot-Color="#00a65a" data-spot-Color="#39CCCC" data-offset="90" data-width="100%" data-height="100px" data-line-Width="2" data-line-Color="#39CCCC" data-fill-Color="rgba(57, 204, 204, 0.08)" data-tooltip-Suffix=" Attack">
				        <?php
				        $x = '';
				        foreach($res['data']['attack_dates'] as $attack):
							echo $x;
							echo $attack;
							$x = ',';
				        endforeach;
				        ?>
				    </div>
				    <div id="attacks_values" style="display:none">
				      	<?php foreach($res['data']['attack_dates'] as $day=>$count): ?>
							<div class="val-<?php echo $count; ?>"><?php echo $day; ?></div>
				        <?php endforeach; ?>
				    </div>
				</div>
			</div>
		</div>
	</div>
  */
  ?>



</div>

<script type="text/javascript">
	jQuery(function(){
		//INITIALIZE SPARKLINE CHARTS
	    // jQuery(".sparkline").each(function () {
	    //   var $this = jQuery(this);
	    //   $this.sparkline('html', $this.data());
	    // });
	});


</script>
