<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
check_ajax_referer( 'shieldfy-install' );
header('Content-Type: application/json');
if($_POST && !empty($_POST['website_token'])){
	shieldfy_begin_install($_POST['website_token'],$_POST['root'],$_POST['admin']);
}else{
	echo json_encode(array('status'=>'error','message'=>'Empty Token'));
	exit();
}

function shieldfy_begin_install($token,$root,$admin){
	$info = array(
		'php_version'=>PHP_VERSION,
		'sapi_type'=>php_sapi_name(),
		'os_info'=>php_uname(),
        'disabled_functions'=>(@ini_get('disable_functions') ? @ini_get('disable_functions') : 'None'),
        'loaded_extensions'=>implode(',', get_loaded_extensions()),
        'display_errors'=>ini_get('display_errors'),
        'register_globals'=>(ini_get('register_globals') ? ini_get('register_globals') : 'None'),
        'post_max_size'=>ini_get('post_max_size'),
        'curl'=>extension_loaded('curl') && is_callable('curl_init'),
        'fopen'=>ini_get('allow_url_fopen'),
		'mcrypt'=>extension_loaded('mcrypt'),
		'root'=>$root,
		'admin'=>$admin
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

    $info['script'] = 'wordpress';
		$token = trim($token);
    $res = shieldfy_call_server('activate',array('info'=>$info),$token);
    //print_r($res);
    header('Content-Type: application/json');
    $res = json_decode($res);
    if($res){
    	if($res->status == 'success'){
    		$content = $res->ht."\n";
	    	//begin install

    		//copy shieldfy.php
    		$shield_code = file_get_contents(dirname(__FILE__).'/shieldfy.php.client');

				$shield_code = str_replace('#php','<?php',$shield_code);
    		$shield_code = str_replace('{{$WEBSITE_TOKEN}}', $token, $shield_code);

        $shield_code = str_replace('{{$API_SERVER}}', SHIELDFY_API_URL_CONST.'/', $shield_code);
        $shield_code = str_replace('{{$SHIELFY_SERVER}}', SHIELDFY_SERVER_URL_CONST.'/', $shield_code);

    		file_put_contents($root.'shieldfy.php', $shield_code);
    		/* htaccess Start */
				/* check sapi type for firewall install */
				$sapi_type = php_sapi_name();
				if (substr($sapi_type, 0, 3) == 'cgi' || substr($sapi_type, 0, 3) == 'fpm') {
						$firewall = "auto_prepend_file = ".$root."shieldfy.php";
					//	file_put_contents($root.'.user.ini', $firewall);
						insert_with_markers ( $root.'.user.ini', 'Shieldfy', $firewall );
				}else{
					$content .= "# ============= Firewall ============="."\n";
					$content .= '<IfModule mod_php5.c>'."\n";
					$content .= 'php_value auto_prepend_file "'.$root.'shieldfy.php"'."\n";
					$content .= '</IfModule>'."\n";
				}
				$content = explode("\n",$content);
				insert_with_markers ( $root.'.htaccess', 'Shieldfy', $content );
		    /* htaccess End */
		    /* required folders */
		    @mkdir($root.'shieldfy');
		    file_put_contents($root.'shieldfy'.DIRECTORY_SEPARATOR.".htaccess", "order deny,allow \n");
		    @mkdir($root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd');
		    @mkdir($root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.'ban');
		    @mkdir($root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.'firewall');
		    @mkdir($root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.'logs');
		   	file_put_contents($root.'shieldfy'.DIRECTORY_SEPARATOR.'tmpd'.DIRECTORY_SEPARATOR.".htaccess", "order deny,allow \n deny from all");


		   	//send done signal
		   	$res = shieldfy_call_server('activate_done',array('x'=>'1'),$token);

		   	$res = json_decode($res);
		   	if($res){

		   		if($res->status == 'success'){
						update_option('shieldfy_active_plugin','1');
		   			echo json_encode(array('status'=>'success','message'=>'done'));
		   		}else{
		   			echo json_encode(array('status'=>$res->status,'message'=>$res->message));
		   		}
		   	}else{
		   		echo json_encode(array('status'=>'error','message'=>'Error contacting server , Try again later'));
		   	}

    	}else{
    		echo json_encode(array('status'=>$res->status,'message'=>$res->message));
    	}

    }else{

    	echo json_encode(array('status'=>'error','message'=>'Error contacting server , Try again later'));
    }
}
