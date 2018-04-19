<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! extension_loaded( 'mysql' ) && ! extension_loaded( 'mysqli' ) && ! extension_loaded( 'mysqlnd' ) ) {
	wp_load_translations_early();

	$protocol = wp_get_server_protocol();
	header( sprintf( '%s 500 Internal Server Error', $protocol ), true, 500 );
	header( 'Content-Type: text/html; charset=utf-8' );
	die( __( 'Your PHP installation appears to be missing the MySQL extension which is required by WordPress.' ) );
}

if(!class_exists(\Composer\Autoload\ClassLoader::class)) require_once(realpath(WP_CONTENT_DIR.'/plugins/shieldfy/vendor/').'/autoload.php');

$shieldfy = \Shieldfy\Guard::init([
    'app_key'       => '{APIKEY}',
    'app_secret'    => '{APISECRET}'
]);

define('SHIELDFY_IS_LOADED',TRUE);

$originalWPDB = new \wpdb( DB_USER, DB_PASSWORD, DB_NAME, DB_HOST );
$wpdb  = new  \Shieldfy\Extensions\DBProxy($originalWPDB, $shieldfy);
