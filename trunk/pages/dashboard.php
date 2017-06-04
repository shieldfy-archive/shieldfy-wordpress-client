<?php
 $info = array(
            'host' => $_SERVER['HTTP_HOST'],
            'lang' => 'php',
            'script' => 'wordpress',
            'php_version'=>PHP_VERSION,
            'sapi_type'=>php_sapi_name(),
            'os_info'=>php_uname(),
            'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'None'),
            'loaded_extensions'=>implode(',', get_loaded_extensions()),
            'display_errors'=>ini_get('display_errors'),
            'register_globals'=>(ini_get('register_globals') ? ini_get('register_globals') : 'None'),
            'post_max_size'=>ini_get('post_max_size'),
            'curl'=>extension_loaded('curl') && is_callable('curl_init'),
            'fopen'=>@ini_get('allow_url_fopen'),
            'mcrypt'=>extension_loaded('mcrypt')
        );
?>
<div class="wrap">
	<h2>Welcome to Shieldfy</h2>
    <hr />
    <h3>
        <span class="label label-info"><i class="fa fa-key"></i> Key: <?php echo get_option('shieldfy_active_app_key'); ?></span> &nbsp;
        <span class="label label-info"><i class="fa fa-key"></i> Secret: <?php echo get_option('shieldfy_active_app_secret'); ?></span>
    </h3>
    <hr />
    <div class="row">
        <div class="col-sm-6">
            <ul class="list-group">
                <li class="list-group-item"><i class="fa fa-globe"></i> <?php echo $info['host'] ?> status: <i class="fa fa-circle text-success"></i> Secure</li>
                <li class="list-group-item"><i class="fa fa-laptop"></i> <?php echo $info['os_info'] ?></li>
                <li class="list-group-item"><i class="fa fa-code"></i> PHP <?php echo $info['php_version'] ?> <span class="label label-default"><?php echo $info['sapi_type'] ?></span></li>
            </ul>
        </div>
        <div class="col-sm-6">
            <a href="https://app.shieldfy.io/application/<?php echo get_option('shieldfy_active_app_key'); ?>/monitor" class="btn btn-block btn-lg btn-success" target="_blank"><i class="fa fa-dashboard"></i> Open the dashboard on https://shieldfy.io</a>
        </div>
    </div>
</div>