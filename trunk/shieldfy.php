<?php
/*
Plugin Name: Shieldfy
Plugin URI: https://shieldfy.io/
Description: Web Shield and hacking prevension for your website
Author: Shieldfy Security Team
Version: 2.0
Author URI: https://shieldfy.io/
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*****************************************************************************
 * constants and globals                                                     *
 *****************************************************************************/
if (!defined( 'SHIELDFY_PLUGIN_VERSION' )) {
    define( 'SHIELDFY_PLUGIN_VERSION', '2.1' );
}
if (!defined( 'SHIELDFY_PLUGIN_URL' )) {
    define( 'SHIELDFY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if (!defined( 'SHIELDFY_PLUGIN_DIR' )) {
    define( 'SHIELDFY_PLUGIN_DIR', dirname( __FILE__ ) );
}

function get_blog_home_path()
{
    if(function_exists('get_home_path')){
        return get_home_path();
    }
    
    if(isset($_SERVER['DOCUMENT_ROOT'])){
        return $_SERVER['DOCUMENT_ROOT'].'/';
    }

    return realpath(__DIR__ .'/../../../').'/';
}

if (!defined( 'SHIELDFY_ROOT_DIR' )) {
    define( 'SHIELDFY_ROOT_DIR', get_blog_home_path() );
}

if (!defined( 'SHIELDFY_ROOT_DATA_DIR' )) {
    define( 'SHIELDFY_ROOT_DATA_DIR', SHIELDFY_ROOT_DIR . 'shieldfy'  );
}

if(!defined('SHIELDFY_API_ENDPOINT')){
    define( 'SHIELDFY_API_ENDPOINT', 'http://api.flash.app' );
}

if(!defined('SHIELDFY_ADMIN_URL')){
    define('SHIELDFY_ADMIN_URL',admin_url('admin.php?page=shieldfy'));
}

require_once( SHIELDFY_PLUGIN_DIR . '/bootstrap.php' );
