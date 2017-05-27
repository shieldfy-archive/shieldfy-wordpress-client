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
        
        if(!$res){
            echo json_encode(array('status'=>'error','message'=>'Error contacting server , Try again later'));
            return;
        }

        if($res && $res->status == 'error'){
            echo json_encode(array('status'=>'error','message'=>'Wrong Key or Wrong Secret'));
            return;
        }
        $rulesData = $res->data->general;

        //start installation 

        //copy shieldfy.php
        $shield_code = file_get_contents(dirname(__FILE__).'/shieldfy.client.php');
        $shield_code = str_replace('{{$APP_KEY}}', $key, $shield_code);
        $shield_code = str_replace('{{$APP_SECRET}}', $secret, $shield_code);
        $shield_code = str_replace('{{$API_SERVER_ENDPOINT}}', SHIELDFY_API_ENDPOINT.'/', $shield_code);
        $host_root = '';
        if(defined('SHIELDFY_ROOT_DIR')){
            $host_root = SHIELDFY_ROOT_DIR;
        }
        if(function_exists('get_home_path')){
            $host_root = get_home_path();
        }
        $host_admin = '';
        if(function_exists('get_admin_url')){
            $host_admin = get_admin_url();
        }
        $shield_code = str_replace('{{$HOST_ROOT}}', $host_root, $shield_code);
        $shield_code = str_replace('{{$HOST_ADMIN}}',  $host_admin , $shield_code);

        file_put_contents($host_root.'shieldfy.php', $shield_code);

        //create directories //copy rules data
        
        @mkdir($host_root.'shieldfy');
        file_put_contents($host_root.'shieldfy'.DIRECTORY_SEPARATOR.".htaccess", "order deny,allow \n");
        @mkdir($host_root.'shieldfy'.DIRECTORY_SEPARATOR.'data');
        file_put_contents($host_root.'shieldfy'.DIRECTORY_SEPARATOR."data".DIRECTORY_SEPARATOR."general.json", $res->data->general);
        @mkdir($host_root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd');
        @mkdir($host_root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.'ban');
        @mkdir($host_root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.'firewall');
        @mkdir($host_root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.'logs');
        file_put_contents($host_root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.".htaccess", "order deny,allow \n deny from all");

        //add lines to htaccess or .user.ini

        /**
         *  $sapi_type = php_sapi_name();
         *  if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 3) == 'fpm') {
         *           $firewall = "auto_prepend_file = ".$host_root."shieldfy.php";
         *           insert_with_markers ( $host_root.'.user.ini', 'Shieldfy', $firewall );
         *   }else{
         *       $content .= "# ============= Firewall ============="."\n";
         *       $content .= '<IfModule mod_php5.c>'."\n";
         *       $content .= 'php_value auto_prepend_file "'.$host_root.'shieldfy.php"'."\n";
         *       $content .= '</IfModule>'."\n";
         *   }
         *   $content = explode("\n",$content);
         *   insert_with_markers ( $host_root.'.htaccess', 'Shieldfy', $content );
         */

        //update status with OK

        /** update_option('shieldfy_active_plugin','1'); */

        echo json_encode(array('status'=>'success'));
        return;

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