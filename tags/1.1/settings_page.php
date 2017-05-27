<?php  if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<div class="wrap">
	<h2>Welcome to Shieldfy</h2>
	<p>&nbsp;</p>


	<h4>Follow this simple steps to get it started :- </h4>
	<div id="shieldfy_start_new" class="shieldfy_start" style="display:block">
		<div class="row">
			<div class="col-xs-12 col-md-3">
				<div class="thumbnail">
						<img src="<?php echo plugin_dir_url( __FILE__ ).'img/notregistered_1.jpg'; ?>" alt="Register new account at https://shieldfy.com">
						<a href="https://shieldfy.com/auth/register" target="_blank"><span>1 - Register new account at https://shieldfy.com/auth/register</span></a>
				</div>
			</div>
			<div class="col-xs-12 col-md-3">
				<div class="thumbnail">
						<img src="<?php echo plugin_dir_url( __FILE__ ).'img/notregistered_2.jpg'; ?>" alt="Add your website">
						<span>2 - Add your website</span>
				</div>
			</div>
			<div class="col-xs-12 col-md-3">
				<div class="thumbnail">
						<img src="<?php echo plugin_dir_url( __FILE__ ).'img/notregistered_3.jpg'; ?>" alt="Click on Shieldfy Client for Wordpress and copy the token">
						<span>3 - Choose Wordpress and copy the token</span>
				</div>
			</div>
			<div class="col-xs-12 col-md-3">
				<div class="thumbnail">
						<img src="<?php echo plugin_dir_url( __FILE__ ).'img/notregistered_4.jpg'; ?>" alt="Add the token below then click Activate">
						<span>4 - Paste it below and click activate</span>
				</div>
			</div>
		</div>
	</div>

	<div class="panel panel-default">
		<div class="panel-heading">Activate Website</div>
			<div class="panel-body">
			<form class="form" action="" method="post">
				<div class="form-group">
					<label class="sr-only" for="ShieldfyWebsiteToken">Token</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-key"></i></div>
						<input name="token" type="text" class="form-control" id="ShieldfyWebsiteToken" placeholder="Token">
					</div>
				</div>
				<button type="button" onclick="shieldfy_activate(this);" class="btn btn-primary"><i class="fa fa-check"></i> Activate</button>
			</form>
		</div>
	</div>

</div>
