<?php
/*
File: ajax.php
Author: Shieldfy Security Team
Author URI: https://shieldfy.io/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'wp_ajax_shieldfy_install', 'shieldfy_install_callback' );

function shieldfy_install_callback()
{
    check_ajax_referer( 'shieldfy-install' );
    header('Content-Type: application/json');

    if(!isset($_POST['app_key']) || empty($_POST['app_key'])) echo json_encode(array('status'=>'error','message'=>'Key can\'t be empty'));
    if(!isset($_POST['app_secret']) || empty($_POST['app_secret'])) echo json_encode(array('status'=>'error','message'=>'Secret can\'t be empty')); 
    ShieldfyBase::install(trim($_POST['app_key']),trim($_POST['app_secret']));
    exit;
}