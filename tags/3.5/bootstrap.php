<?php
/*
File: bootstrap.php
Author: Shieldfy Security Team
Author URI: https://shieldfy.io/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( SHIELDFY_PLUGIN_DIR . '/libs/base.php');
require_once( SHIELDFY_PLUGIN_DIR . '/libs/api.php');
require_once( SHIELDFY_PLUGIN_DIR . '/libs/ajax.php');

shieldfy_firewall_init_check();

register_activation_hook(__DIR__.'/shieldfy.php', 'shieldfy_activation');
register_deactivation_hook(__DIR__.'/shieldfy.php', 'shieldfy_deactivation');
register_uninstall_hook(__DIR__.'/shieldfy.php', 'shieldfy_uninstall' );

add_action( 'muplugins_loaded', 'shieldfy_firewall_init_check' );
add_action( 'plugins_loaded', 'shieldfy_firewall_init_check' );

add_action( 'admin_menu' , 'shieldfy_plugin_menu');
add_action('admin_enqueue_scripts', 'shieldfy_include_assets' );
add_action( 'admin_notices' , 'shieldfy_admin_notice' );

function shieldfy_activation() { }
function shieldfy_deactivation() {
    return ShieldfyBase::uninstall();
}
function shieldfy_uninstall() {
   // echo 'uninstall';exit;
    return ShieldfyBase::uninstall();
}
function shieldfy_firewall_init_check(){
   // return ShieldfyBase::check();
}


function shieldfy_plugin_menu()
{
    add_menu_page('Shieldfy Security', 'Shieldfy Security', 'administrator', 'shieldfy', 'shieldfy_start',plugin_dir_url( __FILE__ ).'/shieldfy.png');
}


function shieldfy_include_assets() {
	$screen = get_current_screen();
    $page = (isset($_GET['page']))?$_GET['page']:'index';
	if($screen->id == 'toplevel_page_shieldfy' || $page == 'shieldfy'){
		wp_register_script( 'main', SHIELDFY_PLUGIN_URL . '/assets/js/main.js', false, '1.0.0');
		wp_register_style( 'style', SHIELDFY_PLUGIN_URL . '/assets/css/style.css', false, '1.0.0' );
		wp_enqueue_style( 'style' );
        wp_enqueue_script( 'jquery' );
        wp_enqueue_script( 'main' );
	}
}

function shieldfy_start()
{
    $shieldfy_active = get_option('shieldfy_active_plugin');
	if($shieldfy_active == false){
		include_once(dirname(__FILE__).'/pages/install.php');
	}else{
		include_once(dirname(__FILE__).'/pages/dashboard.php');
	}
}

function shieldfy_admin_notice()
{
    $shieldfy_active = get_option('shieldfy_active_plugin');
	if($shieldfy_active == false){
        $user =  get_userdata(get_current_user_id());
        $avatar = get_avatar(get_current_user_id(),48,"monsterid","",array(
            'extra_attr'=>'style="margin-right:10px; float: left;border-radius: 50%;"'
        ));
        echo '<div role="alert" style="border-radius: 4px;  margin-left: -20px;  margin-bottom: 20px;    padding: 15px;background-color: #dff0d8;    border-color: #d6e9c6;    color: #3c763d;">
            '.$avatar.'<strong>Well done '.$user->user_nicename.'!</strong> <br>
                Thanks, You successfully installed <b>Shieldfy Security Firewall</b>. Almost done -
		        go to <a href="'.SHIELDFY_ADMIN_URL.'"> plugin Page </a> and follow instruction to activate the firewall. </a>.
             <div style="clear:both"></div>
         </div>';
	}

}
