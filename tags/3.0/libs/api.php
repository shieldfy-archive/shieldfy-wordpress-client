<?php
/*
File: api.php
Author: Shieldfy Security Team
Author URI: https://shieldfy.io/
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ShieldfyAPI
{
    private $key = '';
    private $secret = '';
    private $data;
    private $ch;

    public function __construct($key, $secret)
    {
        $this->key = $key;
        $this->secret = $secret;
    }
    public function callUrl($url , $data = array())
    {
        $url = SHIELDFY_PLUGIN_API_ENDPOINT .'/'.$url;
        return $this->init($url)
                    ->setCertificate()
                    ->setData($data)
                    ->setHash()
                    ->execute();
    }

    private function init($url)
    {
        $this->ch = curl_init();
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($this->ch,CURLOPT_USERAGENT, 'shieldfy-php/2.1 (php '.phpversion().' )');
        curl_setopt($this->ch,CURLOPT_TIMEOUT, 30);
        return $this;
    }

    private function setCertificate()
    {
        curl_setopt($this->ch,CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($this->ch,CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($this->ch,CURLOPT_CAINFO, SHIELDFY_PLUGIN_DIR.'/certificate/cacert.pem');
        return $this;
    }

    private function setData($data = array())
    {
        $this->data = json_encode($data);        
        curl_setopt($this->ch,CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->ch,CURLOPT_POSTFIELDS, $this->data);
        return $this;
    }

    private function setHash()
    {
        $body = str_Replace('\\','',$this->data); //fix backslash double encoding in json
        $hash = hash_hmac('sha256', $body, $this->secret);
        curl_setopt($this->ch,CURLOPT_HTTPHEADER,
            [
                'X-Shieldfy-Api-Key: '.$this->key,
                'X-Shieldfy-Api-Hash: '.$hash,
                'Content-Type: application/json',
                'Content-Length: ' . strlen($this->data)
            ]
        );

        return $this;
    }

    
    
    private function execute()
    {

        $result = curl_exec($this->ch);

        if (is_resource($this->ch)) {
            curl_close($this->ch);
        }
        return $result;
    }
}