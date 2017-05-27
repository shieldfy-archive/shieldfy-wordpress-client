<?php
/*
File: base.php
Author: Shieldfy Security Team
Author URI: https://shieldfy.io/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ShieldfyBase
{
    public static function check()
    {
        $shieldfy_active = get_option('shieldfy_active_plugin');
        if($shieldfy_active){
            //plugin activated check for firewall signature
            if(!defined('SHIELDFY_VERSION')){
                //include the firewall if exists
                if(file_exists(SHIELDFY_ROOT_DIR.'shieldfy.php')){
                    @require_once(SHIELDFY_ROOT_DIR.'shieldfy.php');
                }
            }
        }
        return true;
    }

    public static function install($key, $secret)
    {
        $info = array(
            'host' => $_SERVER['HTTP_HOST'],
            'https' => self::isUsingSSL(),
            'lang' => 'php',
            'sdk_version' => 'wordpress',
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

        if(@touch('shieldfy_tmpfile.tmp')){
            $info['create_file'] = 1;
            $delete = @unlink('shieldfy_tmpfile.tmp');
            if($delete){
                $info['delete_file'] = 1;
            }else{
                $info['delete_file'] = 0;
            }
        }else{
            $info['create_file'] = 0;
            $info['delete_file'] = 0;
        }
        if(file_exists($root.'.htaccess')){
            $info['htaccess_exists'] = 1;
            if(is_writable($root.'.htaccess')){
                $info['htaccess_writable'] = 1;
            }else{
                $info['htaccess_writable'] = 0;
            }
        }else{
            $info['htaccess_exists'] = 0;
        }
        
        $api = new ShieldfyAPI($key, $secret);
        $result = $api->callUrl('install',$info);

        $res = json_decode($result);
        print_r($result);
        if($res && $res->status == 'error'){
            echo json_encode(array('status'=>'error','message'=>'Wrong Key or Wrong Secret'));
            return;
        }

        //print_r($result);
    }

    public static function isUsingSSL()
    {
        return 
        (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || $_SERVER['SERVER_PORT'] == 443;
    }

    public static function uninstall()
    {

    }
}