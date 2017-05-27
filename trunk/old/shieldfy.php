<?php
/*
Plugin Name: Shieldfy
Plugin URI: https://shieldfy.com/
Description: Web Shield and Anti Malware for your website
Author: Shieldfy Security Team
Version: 2.0
Author URI: https://shieldfy.com/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

shieldfy_firewall_init_check();


register_activation_hook(__FILE__, 'shieldfy_activation');
register_deactivation_hook(__FILE__, 'shieldfy_deactivation');

add_action('admin_menu', 'shieldfy_plugin_settings');
add_action('admin_notices', 'shieldfy_admin_notice' );

add_action( 'admin_enqueue_scripts', 'shieldfy_custom_include_function' );
add_action( 'admin_print_footer_scripts', 'shieldfy_custom_inline_script' );

add_action( 'wp_ajax_shieldfy_install', 'shieldfy_install_callback' );

add_action( 'muplugins_loaded', 'shieldfy_firewall_init_check' );
add_action( 'plugins_loaded', 'shieldfy_firewall_init_check' );

require_once('lib.php');

function shieldfy_activation() {

}

function shieldfy_firewall_init_check(){
	$shieldfy_active = get_option('shieldfy_active_plugin');
	if($shieldfy_active){
		//plugin activated check for firewall signature
		if(!defined('SHIELDFY_VERSION')){

			//include the firewall if exists
			if(function_exists('get_home_path')){
				$root = get_home_path();
			}else{
				$root = @$_SERVER['DOCUMENT_ROOT'].'/';
			}
			if(file_exists($root.'shieldfy.php')){
				@require_once($root.'shieldfy.php');
			}
		}
	}
	return true;
}

function shieldfy_install_callback(){
	include dirname(__FILE__).'/ajax.php';
	exit;
}


function shieldfy_deactivation() {

	delete_option('shieldfy_active_plugin');
	$root = get_home_path();
	//remove entry from htaccess
	insert_with_markers ( $root.'.htaccess', 'Shieldfy', array() );
	//temporary solution for php_value cache in apache
	$php_ini = $root.'.user.ini';
	if(file_exists($php_ini)){
		$php_ini_content = @file_get_contents($php_ini);
		$new_ini_content = str_replace('php_value auto_prepend_file "'.$root.'shieldfy.php"', '', $php_ini_content);
		@file_put_contents($php_ini,$new_ini_content);
		insert_with_markers ( $php_ini, 'Shieldfy', array() );
	}

	//@unlink($root.'/shieldfy.php');
	$dir = $root.'/shieldfy/';
	@unlink($dir.'.htaccess');
	@unlink($dir.'tmpd/.htaccess');
	$res = scandir($dir.'tmpd/ban');
	foreach($res as $re){
		if(is_file($dir.'tmpd/ban/'.$re)){
			@unlink($dir.'tmpd/ban/'.$re);
		}
	}
	$res = scandir($dir.'tmpd/firewall');
	// print_r($res);
	// exit;
	foreach($res as $re){
		if(is_file($dir.'tmpd/firewall/'.$re)){
			@unlink($dir.'tmpd/firewall/'.$re);
		}
	}
	$res = scandir($dir.'tmpd/logs');
	foreach($res as $re){
		if(is_file($dir.'tmpd/logs/'.$re)){
			@unlink($dir.'tmpd/logs/'.$re);
		}
	}

	@rmdir($dir.'tmpd/ban');
	@rmdir($dir.'tmpd/firewall');
	@rmdir($dir.'tmpd/logs');
	@rmdir($dir.'tmpd');
	@rmdir($dir);

	$res = shieldfy_call_server('plugin_deactivated',array('x'=>'1'),$token);
}

function shieldfy_plugin_settings() {

	shieldfy_check_if_installed();

  add_menu_page('Shieldfy Security', 'Shieldfy Security', 'administrator', 'shieldfy', 'shieldfy_display_settings',plugin_dir_url( __FILE__ ).'/shieldfy.png');

}

function shieldfy_check_if_installed(){
	if($res = get_option('shieldfy_active_plugin')){
		return;
	}
	if(defined('SHIELDFY_VERSION')){
		update_option('shieldfy_active_plugin','1');
	}
}



function shieldfy_custom_include_function() {
	$screen = get_current_screen();
	if($screen->id == 'toplevel_page_shieldfy' || $_GET['page'] == 'shieldfy'){

		wp_enqueue_script( 'jquery' );
		wp_register_style( 'bootstrap_min', plugin_dir_url( __FILE__ ) . 'css/bootstrap.min.css', false, '1.0.0' );
		wp_register_style( 'fontawesome_min', plugin_dir_url( __FILE__ ) . 'css/font-awesome.min.css', false, '1.0.0' );
    wp_enqueue_style( 'bootstrap_min' );
		wp_enqueue_style( 'fontawesome_min' );
		wp_enqueue_script( 'sparkline', plugin_dir_url( __FILE__ ) . 'js/jquery.sparkline.min.js' );

	}
}

function shieldfy_custom_inline_script(){
	wp_enqueue_script( 'jquery' );
	$admin_ajax_url = admin_url( 'admin-ajax.php' );
	$complete_url = wp_nonce_url( $admin_ajax_url, 'shieldfy-install' );
	$txt = '<script type="text/javascript">
		function shieldfy_activate(obj){
			jQuery(obj).attr(\'disabled\',\'disabled\');
			jQuery(obj).parent().find(\'.label\').remove();
			jQuery(obj).html(\'Loading Please Wait <i class="fa fa-spin fa-spinner"></i>\');
			var data = {
				\'action\': \'shieldfy_install\',
				\'website_token\':jQuery(\'#ShieldfyWebsiteToken\').val(),
				\'root\':\''.get_home_path().'\',
				\'admin\':\''.get_admin_url().'\'
			};
			jQuery.post("'.$complete_url.'",data,function(data){
				jQuery(obj).removeAttr(\'disabled\');
				if(data.status != \'success\'){
					jQuery(obj).after(\' &nbsp; &nbsp; <span class="label label-danger">\'+data.message+\'</span>\');
					jQuery(obj).html(\'<i class="fa fa-check"></i> Activate\');
				}else{
					jQuery(obj).attr(\'disabled\',\'disabled\');
					jQuery(obj).after(\' &nbsp; &nbsp; <span class="label label-success">Shieldfy Activated successfully , You will redirect in a second ..</span>\');
					jQuery(obj).html(\'<i class="fa fa-check"></i> Done\');
					setTimeout(function(){
						location.href = location.href;
					},1000);
				}
			});
		}

		jQuery(function(){
				//INITIALIZE SPARKLINE CHARTS
			    jQuery(".sparkline").each(function () {
			      var $this = jQuery(this);
			      $this.sparkline(\'html\', $this.data());
			    });
			});
	</script>';
	echo $txt;
}


function shieldfy_admin_notice() {
	$shieldfy_active = get_option('shieldfy_active_plugin');
	if($shieldfy_active == false){
		echo '<div style="padding: 10px; margin: 5px; background: #f8c317; color: rgb(64, 62, 61);"> <img src="'.plugin_dir_url( __FILE__ ).'/shieldfy.png'.'" style="vertical-align:-5px;">
		Thank You for activating Shieldfy , Almost done -
		go to <a href="'.get_admin_url().'admin.php?page=shieldfy"> plugin Page </a> and follow instruction to activate the firewall shield and relax . </div>';
	}
}

function shieldfy_display_settings(){
	//update_option('shieldfy_active_plugin','1');
	$shieldfy_active = get_option('shieldfy_active_plugin');
	if($shieldfy_active == false){
		include dirname(__FILE__).'/settings_page.php';
	}else{
		include dirname(__FILE__).'/dashboard.php';
	}
}

?>
