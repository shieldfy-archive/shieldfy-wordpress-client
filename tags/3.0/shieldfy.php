<?php
/*
Plugin Name: Shieldfy
Plugin URI: https://shieldfy.io/
Description: Web Shield and hacking prevension for your website
Author: Shieldfy Security Team
Version: 3.0.0
Author URI: https://shieldfy.io/
*/


if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/*****************************************************************************
 * constants and globals                                                     *
 *****************************************************************************/
if (!defined( 'SHIELDFY_PLUGIN_VERSION' )) {
    define( 'SHIELDFY_PLUGIN_VERSION', '3.0.0' );
}
if (!defined( 'SHIELDFY_SHIELD_VERSION' )) {
    define( 'SHIELDFY_SHIELD_VERSION', '5.0.0' );
}
if (!defined( 'SHIELDFY_PLUGIN_URL' )) {
    define( 'SHIELDFY_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if (!defined( 'SHIELDFY_PLUGIN_DIR' )) {
    define( 'SHIELDFY_PLUGIN_DIR', dirname( __FILE__ ) );
}
function get_blog_home_path()
{
    $home    = set_url_scheme( get_option( 'home' ), 'http' );
    $siteurl = set_url_scheme( get_option( 'siteurl' ), 'http' );
    if ( ! empty( $home ) && 0 !== strcasecmp( $home, $siteurl ) ) {
            $wp_path_rel_to_home = str_ireplace( $home, '', $siteurl ); /* $siteurl - $home */
            $pos = strripos( str_replace( '\\', '/', $_SERVER['SCRIPT_FILENAME'] ), trailingslashit( $wp_path_rel_to_home ) );
            $home_path = substr( $_SERVER['SCRIPT_FILENAME'], 0, $pos );
            $home_path = trailingslashit( $home_path );
    } else {
            $home_path = ABSPATH;
    }

    return str_replace( '\\', '/', $home_path );
}

if (!defined( 'SHIELDFY_ROOT_DIR' )) {
    define( 'SHIELDFY_ROOT_DIR', get_blog_home_path() );
}

if (!defined( 'SHIELDFY_ROOT_DATA_DIR' )) {
    define( 'SHIELDFY_ROOT_DATA_DIR', SHIELDFY_ROOT_DIR . 'shieldfy'  );
}

if(!defined('SHIELDFY_PLUGIN_API_ENDPOINT')){
    define( 'SHIELDFY_PLUGIN_API_ENDPOINT', 'http://api.shieldfy.io' );
}

if(!defined('SHIELDFY_ADMIN_URL')){
    define('SHIELDFY_ADMIN_URL',admin_url('admin.php?page=shieldfy'));
}

require_once( SHIELDFY_PLUGIN_DIR . '/bootstrap.php' );