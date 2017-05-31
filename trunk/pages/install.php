<?php
/*
File: install.php
Author: Shieldfy Security Team
Author URI: https://shieldfy.io/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

?>



<div class="wrap">
	<h2>Welcome to Shieldfy</h2>
	<p>&nbsp;</p>


	<h4>Follow this simple steps to get it started :- </h4>

    <ul class="list-group">
        <li class="list-group-item">1. Go to <a href=""><code>https://shieldfy.io/</code></a> and create an account if you don't have one</li>
        <li class="list-group-item">2. Create a new application with any name you want</li>
        <li class="list-group-item">3. choose <code>wordpress</code> from the installation page</li>
        <li class="list-group-item">4. copy Key &amp; Secret and paste them below and click Activate</li>
    </ul>


	<div class="panel panel-default">
		<div class="panel-heading">Activate Firewall</div>
			<div class="panel-body">
			<form class="form" action="" method="post">
				<div class="form-group">
					<label class="sr-only" for="key">Key</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-key"></i> Key &nbsp; &nbsp;</div>
						<input name="key" type="text" class="form-control" id="ShieldfyAppKey" placeholder="Key">
					</div>
				</div>
                <div class="form-group">
					<label class="sr-only" for="Secret">Secret</label>
					<div class="input-group">
						<div class="input-group-addon"><i class="fa fa-key"></i> Secret</div>
						<input name="Secret" type="text" class="form-control" id="ShieldfyAppSecret" placeholder="Secret">
					</div>
				</div>
				<button type="button" onclick="shieldfy_activate(this);" class="btn btn-primary"><i class="fa fa-check"></i> Activate</button>
			</form>
		</div>
	</div>
</div>

<script type="text/javascript">
    <?php
    $admin_ajax_url = admin_url( 'admin-ajax.php' );
	$complete_url = wp_nonce_url( $admin_ajax_url, 'shieldfy-install' );
    ?>
    var shieldfy_install_url = '<?php echo $complete_url; ?>';
    function shieldfy_activate(obj)
    {
        jQuery(obj).attr('disabled','disabled');
        jQuery(obj).parent().find('.label').remove();
        jQuery(obj).html('Loading Please Wait <i class="fa fa-spin fa-spinner"></i>');
        var data = {
            'action': 'shieldfy_install',
            'app_key':jQuery('#ShieldfyAppKey').val(),
            'app_secret':jQuery('#ShieldfyAppSecret').val(),
        };
        jQuery.post(shieldfy_install_url,data,function(data){
            jQuery(obj).removeAttr('disabled');
            if(data.status != 'success'){
                jQuery(obj).after(' &nbsp; &nbsp; <span class="label label-danger">'+data.message+'</span>');
                jQuery(obj).html('<i class="fa fa-check"></i> Activate');
            }else{
                jQuery(obj).attr('disabled','disabled');
                jQuery(obj).after(' &nbsp; &nbsp; <span class="label label-success">Shieldfy Activated successfully , You will redirect in a second ..</span>');
                jQuery(obj).html('<i class="fa fa-check"></i> Done');
                setTimeout(function(){
                    location.href = location.href;
                },1000);
            }
        });

    }
</script>
