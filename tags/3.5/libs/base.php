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

            //check if shieldfy is here
            if(!defined('SHIELDFY_IS_LOADED')){
                $key = get_option('shieldfy_active_app_key');
                $secret = get_option('shieldfy_active_app_secret');
                self::install($key, $secret , true);
            }

        }
        return true;
    }

    public static function install($key, $secret, $silent = false)
    {

        $dbFile = WP_CONTENT_DIR.'/db.php';
        $newContent  = file_get_contents(__DIR__.'/_alternative_db.php');

        if(file_exists($dbFile)){
            $oldContent = file_get_contents($dbFile);
            //check for shieldfy code
            if(strstr($oldContent, '\Shieldfy\Guard')){
                echo json_encode(array('status'=>'success'));
                return;
            }
            //insert our content as the beginning of the code
            $newContent .=  "\n ?>" . $oldContent;
        }

        $newContent = str_replace('{APIKEY}', $key, $newContent);
        $newContent = str_replace('{APISECRET}', $secret, $newContent);

        file_put_contents($dbFile, $newContent);

        update_option('shieldfy_active_plugin','1');
        update_option('shieldfy_active_app_key',$key);
        update_option('shieldfy_active_app_secret',$secret);

        if($silent == false){
            echo json_encode(array('status'=>'success'));
        }
        return;

        //update status with OK
        echo json_encode(array('status'=>'success'));return;

        if($silent == false){
            echo json_encode(array('status'=>'success'));
        }
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
        delete_option('shieldfy_active_plugin');
        delete_option('shieldfy_active_app_key');
        delete_option('shieldfy_active_app_secret');



    }
}
