<?php
/**
 * Shieldfy Client for Wordpress
 * @version 5.0.0
 * @author Shieldfy Development Team
 * team@shieldfy.io
*/

define('SHIELDFY_DS',DIRECTORY_SEPARATOR);
define('SHIELDFY_VERSION','5.0.0');


if(!defined('SHIELDFY_ROOT_DIR')) define('SHIELDFY_ROOT_DIR',dirname(__FILE__) . SHIELDFY_DS);
if(!defined('SHIELDFY_DIR')) define('SHIELDFY_DIR',SHIELDFY_ROOT_DIR.'shieldfy'. SHIELDFY_DS);
if(!defined('SHIELDFY_CACHE_DIR')) define('SHIELDFY_CACHE_DIR',SHIELDFY_DIR.'tmpd'. SHIELDFY_DS);
if(!defined('SHIELDFY_DATA_DIR')) define('SHIELDFY_DATA_DIR',SHIELDFY_DIR.'data'. SHIELDFY_DS);

if(!defined('SHIELDFY_APP_KEY')) define('SHIELDFY_APP_KEY',"{{$APP_KEY}}");
if(!defined('SHIELDFY_APP_SECRET')) define('SHIELDFY_APP_SECRET',"{{$APP_SECRET}}");

if(!defined('SHIELDFY_API_ENDPOINT')) define('SHIELDFY_API_ENDPOINT',"{{$API_SERVER_ENDPOINT}}");
if(!defined('SHIELDFY_HOST_ROOT')) define('SHIELDFY_HOST_ROOT',"{{$HOST_ROOT}}");
if(!defined('SHIELDFY_HOST_ADMIN')) define('SHIELDFY_HOST_ADMIN',"{{$HOST_ADMIN}}");
if(!defined('SHIELDFY_BLOCKVIEW')) define('SHIELDFY_BLOCKVIEW','<!DOCTYPE html><html lang="en"><head><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><meta name="viewport" content="width=device-width, initial-scale=1"><title>Access Denied</title><link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css"><!--[if lt IE 9]><script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script><script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script><![endif]--></head><body><div class="container"><div class="row"><div class="col-sm-8 col-sm-offset-2"><div class="well" style="margin-top:80px;padding:40px;"><div class="row"><div class="col-sm-4"><img src="http://shieldfy.com/assets/img/block-sign.png" class="img-responsive"></div><div class="col-sm-8"><h1>Whooops!</h1><h4>Your request blocked for security reasons</h4><p>if you believe that your request shouldn\'t be blocked contact the administrator</p><hr/>Protected By <a href="http://shieldfy.com" target="_blank">Shieldfy</a> &trade; Web Shield </div></div></div></div></div></div></body></html>');

/* Helper Classes */
/* API Class */
class ShieldfyAPIConnector
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
        $url = SHIELDFY_API_ENDPOINT .'/'.$url;
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
        curl_setopt($this->ch,CURLOPT_CAINFO, SHIELDFY_DATA_DIR.'/cacert.pem');
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
/* Detection Class */
class Detect{
	public static function run($value){
		//number
		if(is_numeric($value)){
			return 'number';
		}
		//text
		if(!preg_match('/[^a-z0-9\.\s]/isU', $value)){
			return 'text';
		}
		//html & xml
		if($hox = self::isHTMLorXML($value)){
			return $hox;
		}
		//bbcode
		$res = preg_match('/\[([a-z0-9\="#:\/\.]+)\](.*)\[\/([a-z0-9\="#:\/\.]+)\]/isU', $value,$matches);	
		if($res){
			return 'bbcode';
		}
		//json
		if(self::isJson($value)){
			return 'json';
		}
		//serialize
		if(self::isSerialized($value)){
			return 'serialized';
		}
		//xml
		return 'unknown';
	}

	private static function isJson($value){
		$r = json_decode($value);
	 	$res =  (json_last_error() == JSON_ERROR_NONE);
	 	return $res && (is_object($r) || is_array($r));
	}

	private static function isSerialized($value, &$result = null)
	{
		// Bit of a give away this one
		if (!is_string($value))
		{
			return false;
		}
		// Serialized false, return true. unserialize() returns false on an
		// invalid string or it could return false if the string is serialized
		// false, eliminate that possibility.
		if ($value === 'b:0;')
		{
			$result = false;
			return true;
		}
		$length	= strlen($value);
		$end	= '';
		switch ($value[0])
		{
			case 's':
				if ($value[$length - 2] !== '"')
				{
					return false;
				}
			case 'b':
			case 'i':
			case 'd':
				// This looks odd but it is quicker than isset()ing
				$end .= ';';
			case 'a':
                $end .= '}';
				if ($value[1] !== ':')
				{
					return false;
				}
				switch ($value[2])
				{
					case 0:
					case 1:
					case 2:
					case 3:
					case 4:
					case 5:
					case 6:
					case 7:
					case 8:
					case 9:
					break;
					default:
						return false;
				}
			case 'O':
                return false; //no serialization for object to prevent Object Injection Attack
			case 'N':
				$end .= ';';
				if ($value[$length - 1] !== $end[0])
				{
					return false;
				}
			break;
			default:
				return false;
		}
		if (($result = @unserialize($value)) === false)
		{
			$result = null;
			return false;
		}
		return true;
	}
	private static function isHTMLorXML($value){
		preg_match('/<[^?\/]+(>)?(.*)(\/>|<\/[a-z]+>)/isU', $value,$matches);
		if($matches){
			if(strstr($value,'<?xml')){
				return 'xml';
			}
			return 'html';
		}
	}
}
/* Normalization Class */
class Normalize{
    public static function run($value,$res){
        $value = self::normalizeReDosAttempts($value);
        if($res == 'xml'){
            $value = self::normalizeXML($value);
        }
        if($res == 'json'){
            $value = self::normalizeJSON($value);
        }
        $value = self::normalizeSafeLinks($value);
        $value = self::normalizeNormalOneCharsParameter($value);
        $value = self::normalizeURLRawEncoding($value);
        $value = self::normalizeCommented($value);
        $value = self::normalizeWhiteSpace($value);
        $value = self::normalizeEntities($value);
        $value = self::normalizeQuotes($value);
        $value = self::normalizeSQLHex($value);
        $value = self::normalizeSQLKeywords($value);
        $value = self::normalizeControlChars($value);
        $value = self::normalizeOutOfRangeChars($value);
        $value = self::normalizeJSUnicode($value);
        $value = self::normalizeJSCharcode($value);
        $value = self::normalizeJSRegexModifiers($value);
        $value = self::normalizeUTF7($value);
        $value = self::normalizeConcatenated($value);
        $value = self::normalizeProprietaryEncodings($value);
        $value = self::normalizeUrlencodeSqlComment($value);
        if($res == 'bbcode'){
            $value = self::normalizeBBCode($value);
        }
        $value = self::normalizeUTF8HexEncode($value);
        return $value;
    }

    private static function normalizeNormalOneCharsParameter($value){
        $allowedChars = [':'];
        $newvalue = str_replace($allowedChars, '', $value );
        if(preg_match('/(^[a-z\.]+$)/isU', $newvalue)){
          //its clean
          return $newvalue;
        }else{
          //its not
          return $value;
        }
    }

    private static function normalizeSafeLinks($value){
      $pattern = "/<a\\s*href=\"([a-z0-9:\\/\\._\\-]+)\"\\s*>([a-z0-9.\\-\\/\\s]+)<\\/a>/isU";
      $re = preg_replace($pattern, '$1 $2',$value);
      return $re;
    }

    private static function normalizeURLRawEncoding($value){
       if(!preg_match('/%([0-9a-fA-F]{2})/U', $value)) return $value;
       //keep nullbyte
       $value = str_replace('%00', '[sh_null]', $value);
       $value = rawurldecode($value);
       $value = str_replace('[sh_null]', '%00', $value);
       return $value;
    }

    private static function normalizeUTF8HexEncode($value){
        if(!preg_match('/%u([0-9a-fA-F]{4})/U', $value)) return $value;

        $value = preg_replace_callback('/%u([0-9a-fA-F]{4})/', function ($match) {
            $unicode = str_replace('%', '\\', $match[1]);
            $unicode = json_decode('["\\u'.$unicode.'"]');
            return $unicode[0];
        }, $value);
        return $value;
    }

    private static function normalizeReDosAttempts($value){
        $value = preg_replace('/([a-z])\1{10,}/i', 'a', $value);
        //$value = preg_replace('/([a]{20,}|[b]{20,})/i', 'a', $value);
        return $value;
    }

    private static function normalizeJSON($value){

        $json = json_decode($value,1);
        $json_value = '';
        array_walk_recursive($json, function($v,$k) use(&$json_value){
            $json_value .= $k.' '.$v;
        });
        $value = $json_value;

        return $value;
    }

    private static function normalizeXML($value){
        $value = preg_replace('/(<\\?xml)([a-z0-9\\.\\-"\'\\s=]+)(\\?>)/i', ' ', $value);
        $value = str_replace(array('<','>'), '', $value);
        return $value;
    }

    private static function normalizeBBCode($value){
        $pattern = '/\[([a-z]+)(\s*=\s*"?[a-z0-9#:\/\.\?]+"?)?\]/isU';
        preg_match_all($pattern, $value,$matches);

        if($matches[1]){
            $matches[2] = array_map(function($n){
                return str_replace(array('=','"',"'"), '', $n);
            }, $matches[2]);
            $matches[1] = array_map(function($n){
                return '[/'.$n.']';
            },$matches[1]);

            //array_merge($value)
            $value = str_replace($matches[0], $matches[2], $value);
            $value = str_replace($matches[1],'',$value);
            $value = str_replace('[/]', '', $value);
        }

        return $value;
    }

    /**
     * Check for comments and erases them if available
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeCommented($value)
    {
        // check for existing comments
        if (preg_match('/(?:\<!-|-->|\/\*|\*\/|\/\/\W*\w+\s*$)|(?:--[^-]*-)/ms', $value)) {

            $pattern = array(
                '/(?:(?:<!)(?:(?:--(?:[^-]*(?:-[^-]+)*)--\s*)*)(?:>))/ms',
                '/(?:(?:\/\*\/*[^\/\*]*)+\*\/)/ms',
                '/(?:--[^-]*-)/ms'
            );

            $converted = preg_replace($pattern, ';', $value);
            $value    .= "\n" . $converted;
        }

        //make sure inline comments are detected and converted correctly
        $value = preg_replace('/(<\w+)\/+(\w+=?)/m', '$1/$2', $value);
        $value = preg_replace('/[^\\\:]\/\/(.*)$/m', '/**/$1', $value);
        $value = preg_replace('/([^\-&])#.*[\r\n\v\f]/m', '$1', $value);
        $value = preg_replace('/([^&\-])#.*\n/m', '$1 ', $value);
        $value = preg_replace('/^#.*\n/m', ' ', $value);

        return $value;
    }

    /**
     * Strip newlines
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeWhiteSpace($value)
    {
        //check for inline linebreaks
        $search = array('\r', '\n', '\f', '\t', '\v');
        $value  = str_replace($search, ';', $value);

        // replace replacement characters regular spaces
        $value = str_replace('�', ' ', $value);

        //convert real linebreaks
        return preg_replace('/(?:\n|\r|\v)/m', '  ', $value);
    }

    /**
     * Converts from hex/dec entities
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeEntities($value)
    {
        $converted = null;

        //deal with double encoded payload
        $value = preg_replace('/&amp;/', '&', $value);

        if (preg_match('/&#x?[\w]+/ms', $value)) {
            $converted = preg_replace('/(&#x?[\w]{2,6}\d?);?/ms', '$1;', $value);
            $converted = html_entity_decode($converted, ENT_QUOTES, 'UTF-8');
            $value    .= "\n" . str_replace(';;', ';', $converted);
        }

        // normalize obfuscated protocol handlers
        $value = preg_replace(
            '/(?:j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t\s*:)|(d\s*a\s*t\s*a\s*:)/ms',
            'javascript:',
            $value
        );

        return $value;
    }

    /**
     * Normalize quotes
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeQuotes($value)
    {
        // normalize different quotes to "
        $pattern = array('\'', '`', '´', '’', '‘');
        $value   = str_replace($pattern, '"', $value);

        //make sure harmless quoted strings don't generate false alerts
        $value = preg_replace('/^"([^"=\\!><~]+)"$/', '$1', $value);

        return $value;
    }

    /**
     * Converts SQLHEX to plain text
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeSQLHex($value)
    {
        $matches = array();
        if (preg_match_all('/(?:(?:\A|[^\d])0x[a-f\d]{3,}[a-f\d]*)+/im', $value, $matches)) {
            foreach ($matches[0] as $match) {
                $converted = '';
                foreach (str_split($match, 2) as $hex_index) {
                    if (preg_match('/[a-f\d]{2,3}/i', $hex_index)) {
                        $converted .= chr(hexdec($hex_index));
                    }
                }
                $value = str_replace($match, $converted, $value);
            }
        }
        // take care of hex encoded ctrl chars
        $value = preg_replace('/0x\d+/m', ' 1 ', $value);

        return $value;
    }

    /**
     * Converts basic SQL keywords and obfuscations
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeSQLKeywords($value)
    {
        $pattern = array(
            '/(?:is\s+null)|(like\s+null)|' .
            '(?:(?:^|\W)in[+\s]*\([\s\d"]+[^()]*\))/ims'
        );
        $value   = preg_replace($pattern, '"=0', $value);

        $value   = preg_replace('/[^\w\)]+\s*like\s*[^\w\s]+/ims', '1" OR "1"', $value);
        $value   = preg_replace('/null([,"\s])/ims', '0$1', $value);
        $value   = preg_replace('/\d+\./ims', ' 1', $value);
        $value   = preg_replace('/,null/ims', ',0', $value);
        $value   = preg_replace('/(?:between)/ims', 'or', $value);
        $value   = preg_replace('/(?:and\s+\d+\.?\d*)/ims', '', $value);
        $value   = preg_replace('/(?:\s+and\s+)/ims', ' or ', $value);

        $pattern = array(
            '/(?:not\s+between)|(?:is\s+not)|(?:not\s+in)|' .
            '(?:xor|<>|rlike(?:\s+binary)?)|' .
            '(?:regexp\s+binary)|' .
            '(?:sounds\s+like)/ims'
        );
        $value   = preg_replace($pattern, '!', $value);
        $value   = preg_replace('/"\s+\d/', '"', $value);
        $value   = preg_replace('/(\W)div(\W)/ims', '$1 OR $2', $value);
        $value   = preg_replace('/\/(?:\d+|null)/', null, $value);

        return $value;
    }

    /**
     * Detects nullbytes and controls chars via ord()
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeControlChars($value)
    {
        // critical ctrl values
        $search = array(
            chr(0), chr(1), chr(2), chr(3), chr(4), chr(5),
            chr(6), chr(7), chr(8), chr(11), chr(12), chr(14),
            chr(15), chr(16), chr(17), chr(18), chr(19), chr(24),
            chr(25), chr(192), chr(193), chr(238), chr(255), '\\0'
        );

        $value = str_replace($search, '%00', $value);

        //take care for malicious unicode characters
        $value = urldecode(
            preg_replace(
                '/(?:%E(?:2|3)%8(?:0|1)%(?:A|8|9)\w|%EF%BB%BF|%EF%BF%BD)|(?:&#(?:65|8)\d{3};?)/i',
                null,
                urlencode($value)
            )
        );
        $value = urlencode($value);
        $value = preg_replace('/(?:%F0%80%BE)/i', '>', $value);
        $value = preg_replace('/(?:%F0%80%BC)/i', '<', $value);
        $value = preg_replace('/(?:%F0%80%A2)/i', '"', $value);
        $value = preg_replace('/(?:%F0%80%A7)/i', '\'', $value);
        $value = urldecode($value);

        $value = preg_replace('/(?:%ff1c)/', '<', $value);
        $value = preg_replace('/(?:&[#x]*(200|820|200|820|zwn?j|lrm|rlm)\w?;?)/i', null, $value);
        $value = preg_replace(
            '/(?:&#(?:65|8)\d{3};?)|' .
            '(?:&#(?:56|7)3\d{2};?)|' .
            '(?:&#x(?:fe|20)\w{2};?)|' .
            '(?:&#x(?:d[c-f])\w{2};?)/i',
            null,
            $value
        );

        $value = str_replace(
            array(
                '«',
                '〈',
                '＜',
                '‹',
                '〈',
                '⟨'
            ),
            '<',
            $value
        );
        $value = str_replace(
            array(
                '»',
                '〉',
                '＞',
                '›',
                '〉',
                '⟩'
            ),
            '>',
            $value
        );

        return $value;
    }

    /**
     * Detects nullbytes and controls chars via ord()
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeOutOfRangeChars($value)
    {
        $values = str_split($value);
        foreach ($values as $item) {
            if (ord($item) >= 127) {
                $value = str_replace($item, ' ', $value);
            }
        }

        return $value;
    }

    /**
     * This method converts JS unicode code points to
     * regular characters
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeJSUnicode($value)
    {
        $matches = array();
        preg_match_all('/\\\u[0-9a-f]{4}/ims', $value, $matches);

        if (!empty($matches[0])) {
            foreach ($matches[0] as $match) {
                $chr = chr(hexdec(substr($match, 2, 4)));
                $value = str_replace($match, $chr, $value);
            }
            $value .= "\n\u0001";
        }

        return $value;
    }


    /**
     * Checks for common charcode pattern and decodes them
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeJSCharcode($value)
    {
        $matches = array();

        // check if value matches typical charCode pattern
        if (preg_match_all('/(?:[\d+-=\/\* ]+(?:\s?,\s?[\d+-=\/\* ]+)){4,}/ms', $value, $matches)) {
            $converted = '';
            $string    = implode(',', $matches[0]);
            $string    = preg_replace('/\s/', '', $string);
            $string    = preg_replace('/\w+=/', '', $string);
            $charcode  = explode(',', $string);

            foreach ($charcode as $char) {
                $char = preg_replace('/\W0/s', '', $char);

                if (preg_match_all('/\d*[+-\/\* ]\d+/', $char, $matches)) {
                    $match = preg_split('/(\W?\d+)/', implode('', $matches[0]), null, PREG_SPLIT_DELIM_CAPTURE);

                    if (array_sum($match) >= 20 && array_sum($match) <= 127) {
                        $converted .= chr(array_sum($match));
                    }

                } elseif (!empty($char) && $char >= 20 && $char <= 127) {
                    $converted .= chr($char);
                }
            }

            $value .= "\n" . $converted;
        }

        // check for octal charcode pattern
        if (preg_match_all('/(?:(?:[\\\]+\d+[ \t]*){8,})/ims', $value, $matches)) {
            $converted = '';
            $charcode  = explode('\\', preg_replace('/\s/', '', implode(',', $matches[0])));

            foreach (array_map('octdec', array_filter($charcode)) as $char) {
                if (20 <= $char && $char <= 127) {
                    $converted .= chr($char);
                }
            }
            $value .= "\n" . $converted;
        }

        // check for hexadecimal charcode pattern
        if (preg_match_all('/(?:(?:[\\\]+\w+\s*){8,})/ims', $value, $matches)) {
            $converted = '';
            $charcode  = explode('\\', preg_replace('/[ux]/', '', implode(',', $matches[0])));

            foreach (array_map('hexdec', array_filter($charcode)) as $char) {
                if (20 <= $char && $char <= 127) {
                    $converted .= chr($char);
                }
            }
            $value .= "\n" . $converted;
        }

        return $value;
    }

    /**
     * Eliminate JS regex modifiers
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeJSRegexModifiers($value)
    {
        return preg_replace('/\/[gim]+/', '/', $value);
    }

    /**
     * Converts relevant UTF-7 tags to UTF-8
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeUTF7($value)
    {
        if (preg_match('/\+A\w+-?/m', $value)) {
            if (function_exists('mb_convert_encoding')) {
                if (version_compare(PHP_VERSION, '5.2.8', '<')) {
                    $tmp_chars = str_split($value);
                    $value = '';
                    foreach ($tmp_chars as $char) {
                        if (ord($char) <= 127) {
                            $value .= $char;
                        }
                    }
                }
                $value .= "\n" . mb_convert_encoding($value, 'UTF-8', 'UTF-7');
            } else {
                //list of all critical UTF7 codepoints
                $schemes = array(
                    '+ACI-'      => '"',
                    '+ADw-'      => '<',
                    '+AD4-'      => '>',
                    '+AFs-'      => '[',
                    '+AF0-'      => ']',
                    '+AHs-'      => '{',
                    '+AH0-'      => '}',
                    '+AFw-'      => '\\',
                    '+ADs-'      => ';',
                    '+ACM-'      => '#',
                    '+ACY-'      => '&',
                    '+ACU-'      => '%',
                    '+ACQ-'      => '$',
                    '+AD0-'      => '=',
                    '+AGA-'      => '`',
                    '+ALQ-'      => '"',
                    '+IBg-'      => '"',
                    '+IBk-'      => '"',
                    '+AHw-'      => '|',
                    '+ACo-'      => '*',
                    '+AF4-'      => '^',
                    '+ACIAPg-'   => '">',
                    '+ACIAPgA8-' => '">'
                );

                $value = str_ireplace(
                    array_keys($schemes),
                    array_values($schemes),
                    $value
                );
            }
        }

        return $value;
    }

    /**
     * Converts basic concatenations
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeConcatenated($value)
    {
        //normalize remaining backslashes
        if ($value != preg_replace('/(\w)\\\/', "$1", $value)) {
            $value .= preg_replace('/(\w)\\\/', "$1", $value);
        }

        $compare = stripslashes($value);

        $pattern = array(
            '/(?:<\/\w+>\+<\w+>)/s',
            '/(?:":\d+[^"[]+")/s',
            '/(?:"?"\+\w+\+")/s',
            '/(?:"\s*;[^"]+")|(?:";[^"]+:\s*")/s',
            '/(?:"\s*(?:;|\+).{8,18}:\s*")/s',
            '/(?:";\w+=)|(?:!""&&")|(?:~)/s',
            '/(?:"?"\+""?\+?"?)|(?:;\w+=")|(?:"[|&]{2,})/s',
            '/(?:"\s*\W+")/s',
            '/(?:";\w\s*\+=\s*\w?\s*")/s',
            '/(?:"[|&;]+\s*[^|&\n]*[|&]+\s*"?)/s',
            '/(?:";\s*\w+\W+\w*\s*[|&]*")/s',
            '/(?:"\s*"\s*\.)/s',
            '/(?:\s*new\s+\w+\s*[+",])/',
            '/(?:(?:^|\s+)(?:do|else)\s+)/',
            '/(?:[{(]\s*new\s+\w+\s*[)}])/',
            '/(?:(this|self)\.)/',
            '/(?:undefined)/',
            '/(?:in\s+)/'
        );

        // strip out concatenations
        $converted = preg_replace($pattern, null, $compare);

        //strip object traversal
        $converted = preg_replace('/\w(\.\w\()/', "$1", $converted);

        // normalize obfuscated method calls
        $converted = preg_replace('/\)\s*\+/', ")", $converted);

        //convert JS special numbers
        $converted = preg_replace(
            '/(?:\(*[.\d]e[+-]*[^a-z\W]+\)*)|(?:NaN|Infinity)\W/ims',
            1,
            $converted
        );

        if ($converted && ($compare != $converted)) {
            $value .= "\n" . $converted;
        }

        return $value;
    }

    /**
     * This method collects and decodes proprietary encoding types
     *
     * @param string $value the value to convert
     *
     * @private static
     * @return string
     */
    private static function normalizeProprietaryEncodings($value)
    {
        //Xajax error reportings
        $value = preg_replace('/<!\[CDATA\[(\W+)\]\]>/im', '$1', $value);

        //strip false alert triggering apostrophes
        $value = preg_replace('/(\w)\"(s)/m', '$1$2', $value);

        //strip quotes within typical search patterns
        $value = preg_replace('/^"([^"=\\!><~]+)"$/', '$1', $value);

        //OpenID login tokens
        $value = preg_replace('/{[\w-]{8,9}\}(?:\{[\w=]{8}\}){2}/', null, $value);

        //convert Content and \sdo\s to null
        $value = preg_replace('/Content|\Wdo\s/', null, $value);

        //strip emoticons
        $value = preg_replace(
            '/(?:\s[:;]-[)\/PD]+)|(?:\s;[)PD]+)|(?:\s:[)PD]+)|-\.-|\^\^/m',
            null,
            $value
        );

        //normalize separation char repetion
        $value = preg_replace('/([.+~=*_\-;])\1{2,}/m', '$1', $value);

        //normalize multiple single quotes
        $value = preg_replace('/"{2,}/m', '"', $value);

        //normalize quoted numerical values and asterisks
        $value = preg_replace('/"(\d+)"/m', '$1', $value);

        //normalize pipe separated request parameters
        $value = preg_replace('/\|(\w+=\w+)/m', '&$1', $value);

        //normalize ampersand listings
        $value = preg_replace('/(\w\s)&\s(\w)/', '$1$2', $value);

        //normalize escaped RegExp modifiers
        $value = preg_replace('/\/\\\(\w)/', '/$1', $value);

        return $value;
    }

   /**
   * This method removes encoded sql # comments
   *
   * @param string $value the value to convert
   *
   * @private static
   * @return string
   */
    private static function normalizeUrlencodeSqlComment($value)
    {
        if (preg_match_all('/(?:\%23.*?\%0a)/im',$value,$matches)){
            $converted = $value;
            foreach($matches[0] as $match){
                $converted = str_replace($match,' ',$converted);
            }
            $value .= "\n" . $converted;
        }
        return $value;
    }
}


/* Filter Class */
class ShieldfyFilter
{
    private $block_score = 11;
    private $filterSet;
    public function __construct()
    {
        
        $filters = json_decode(file_get_contents(SHIELDFY_DATA_DIR.'general.json'));
        $this->filterSet = (array)$filters;
    }

    public function check($params = array())
    {
        $info = array();
        
        $res = array(
            'response' => 'pass',
            'score' => 0,
            'infection' => array()
        );
        //print_r($param['get']);exit;
		$this->analyze($params['get'],$res,'get');
		$this->analyze($params['post'],$res,'post');

        //return array('res'=>$res,'params'=>$params);
        return  $res;
    }

    public function analyze(&$params,&$res,$method){
		foreach($params as $key=>$value):
            if(is_array($value)){
                $this->analyze($params[$key],$res,$method);
            }else{
                $result  = $this->detect($key,$value,$method);
                if($result && $result['score'] >= $this->block_score):
                    //found a thing
                    //$res['status'] = 1;
                    $res['score'] += $result['score']; //change score to score
                    if($result['type'] == 'DDos'){
                        $res['ddos'] = 1;
                        $params[$key] = '[ddos]';
                    }
                    $res['infection'][$method.'.'.$key] = $result['ids'];

                    if($method == 'post' && $result['type'] == 'html' && $res['response'] != 'block'){ //if block there is no need to replace
                        // double the score for post html contents
                        if($result['score'] <= ($this->block_score * 2)){
                            //cool we just give a warning
                           // $res['danger'] = 'mid';
                            $res['response'] = 'pass';
                        }else{
                            //dangerous
                           // $res['danger'] = 'high';
                            $res['response'] = 'block';
                        }
                    }else{
                      //  $res['danger'] = 'high';
                        $res['response'] = 'block';
                    }
                endif;
            }
	    endforeach;
	}


    function detect($key,$value,$method){
        //check for ddos first
        if(strlen($value) > 51200){ //50kb
            //check for beginning
            $long = mb_substr($value, 0, 45); //longest word in major dectionary is 45 char long https://en.wikipedia.org/wiki/Longest_word_in_English
            if(!strstr($long, " ")){
                //ddos
                $res =  array('type'=>'DDos','score'=>80,'ids'=>'2','tags'=>'DDos');
                return $res;
            }
            //check the last
            $long = mb_substr($value, -45);
            if(!strstr($long, " ")){
                //ddos
                $res =  array('type'=>'DDos','score'=>80,'ids'=>'2','tags'=>'DDos');
                return $res;
            }
        }

        // check for magic quotes and remove them if necessary
        if (function_exists('get_magic_quotes_gpc') && !get_magic_quotes_gpc()) {
            $value = preg_replace('(\\\(["\'/]))im', '$1', $value);
        }

        $score = 0;

        $non_value = str_replace(' ', '', $value);
        if($non_value == ''){
            return false;
        }

        $type = Detect::run($value);
        $value = Normalize::run($value,$type);


        $ids = [];
        $length = 0;
        $tags = [];
        foreach($this->filterSet as $id => $filter){
            $res = preg_match("/".$filter->rule."/i", strtolower($value),$matches);
            if($res){
                $score += $filter->score;
                $ids[] = $id;
                $length += strlen($matches[0]);
                
            }
        }
        if( ($length >= (strlen($value) / 3)) && $score != 0 ){ //if matched attack > 1/3 of the input add 5 to score
            $score += 5;
        }

        if( ($method == 'get') && $score != 0){ 
            $score += 5;
        }

        $res =  array('type'=>$type,'val'=>$value,'score'=>$score,'ids'=>$ids,'tags'=>$tags);

        if($score){
            return $res;
        }else{
            return false;
        }
	}

}

/* ShieldfyShield */
class ShieldfyCoreShield{
    public $sessionID = '';
    public $userIP = null;
	/* Views */
	public function block(){
		$this->end(403,"Unauthorize Action :: Shieldfy Web Shield",SHIELDFY_BLOCKVIEW);
	}
    
    public function report($info , $judgment, $block = false)
    {
        $info['created'] = time();
        unset($judgment['response']);
        $data = [
            'incidentId'    => ip2long($this->userIP).time(), //maybe add appkey for ensure not duplicate
            'host'          => $_SERVER['HTTP_HOST'],
            'sessionId'     => $this->sessionID,
            'ip'            => $this->userIP,
            'monitor'       => 'general',
            'judgment'      => $judgment,
            'info'          => $info,
            'code'          => array(),
            'history'       => array()
        ];
        $res = $this->callApi('activity',$data);
        if($block){
            $this->block();
        }
    }
	public function end($status = 403,$message = "",$html = ""){
		@header($_SERVER["SERVER_PROTOCOL"].' '.$status.' '.$message);@die($html);
	}

	public function show($arr = array()){
		header('Content-Type: application/json');
		echo json_encode($arr);
		exit;
	}
	public function response($status,$message = ''){
		$arr = array('handshake'=>1,'status'=>$status,'message'=>$message);
		$this->show($arr);
	}

	/* Api */
	public function callApi($route,$postdata = array()){
		$api = new ShieldfyAPIConnector(SHIELDFY_APP_KEY, SHIELDFY_APP_SECRET);
        return $api->callUrl($route,$postdata);
	}
	public function getUserIP(){
		$ipaddress = '';
			if (@$_SERVER['HTTP_CLIENT_IP']){
				$ipaddress = $_SERVER['HTTP_CLIENT_IP'];
			}else if(@$_SERVER['HTTP_X_FORWARDED_FOR']){
				$ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
			}else if(@$_SERVER['HTTP_X_FORWARDED']){
				$ipaddress = $_SERVER['HTTP_X_FORWARDED'];
			}else if(@$_SERVER['HTTP_FORWARDED_FOR']){
				$ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
			}else if(@$_SERVER['HTTP_FORWARDED']){
				$ipaddress = $_SERVER['HTTP_FORWARDED'];
			}else if(@$_SERVER['REMOTE_ADDR']){
				$ipaddress = $_SERVER['REMOTE_ADDR'];
			}else{
				$ipaddress = 'UNKNOWN';
			}
		return $ipaddress;
	}
}
/* Main Shield Class */
class ShieldfyShield extends ShieldfyCoreShield{
	public $config = array();
	private static $_instance = null;
	private function __construct () { }
	public static function init ()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
	public function shield(){
		$this->userIP = $this->getUserIP();
        //open session if not cached
        $userID = ip2long($this->userIP);
        $session_cache_file = SHIELDFY_CACHE_DIR.'firewall'.SHIELDFY_DS.$userID;
        if(!file_exists($session_cache_file)){
            $result = $this->callApi("session",array(
                'user' => array(
                    'id'        => $userID,
                    'ip'        => $this->userIP,
                    'userAgent' => (isset($_SERVER['HTTP_USER_AGENT']))?$_SERVER['HTTP_USER_AGENT']:''
                )
            ));
            $response = json_decode($result);
            if ($response && $response->status == 'success') {
                $this->sessionID = $response->sessionId;
                file_put_contents($session_cache_file,$this->sessionID);
            }else{
                $this->sessionID = md5(time() * mt_rand());
            }
        }else{
            $this->sessionID = file_get_contents($session_cache_file);
        }
               
		$this->run();
	}
	private function run(){
		
		/* expose useful headers */
		header('X-XSS-Protection: 1; mode=block');
		header('X-Content-Type-Options: nosniff');

		/* remove deprecated */
		if(isset($HTTP_COOKIE_VARS)) $HTTP_COOKIE_VARS = array();
		if(isset($HTTP_ENV_VARS)) $HTTP_ENV_VARS = array();
		if(isset($HTTP_GET_VARS)) $HTTP_GET_VARS = array();
		if(isset($HTTP_POST_VARS)) $HTTP_POST_VARS = array();
		if(isset($HTTP_POST_FILES)) $HTTP_POST_FILES = array();
		if(isset($HTTP_RAW_POST_DATA)) $HTTP_RAW_POST_DATA = array();
		if(isset($HTTP_SERVER_VARS)) $HTTP_SERVER_VARS = array();
		if(isset($HTTP_SESSION_VARS)) $HTTP_SESSION_VARS = array();

        

        $info = array(
            'get'     => $_GET,
            'post'    => $_POST,
            'method'  => (isset($_SERVER['REQUEST_METHOD']))?$_SERVER['REQUEST_METHOD']:'GET',
            'uri'     => (isset($_SERVER['REQUEST_URI']))?$_SERVER['REQUEST_URI']:''
        );

       
        

		/* check for illegal types of file uploads */
		foreach($_FILES as $name=>$file){
			//check content if its illegal
			$res = file_get_contents($file['tmp_name']);
			if(strstr($res, '<?php')){
				//its php file , exit now                
                $judgment = array(
                    "score"=>200,
                    "infection"=>array(
                        "files.".$name => array()
                    )                    
                );
				$this->report($info, $judgment, true);
			}
		}

        if(SHIELDFY_HOST_ADMIN != '' && strpos($info['uri'],SHIELDFY_HOST_ADMIN) === 0){
           return;
        }
        
		/* check cached firewall */
		$cache_file = SHIELDFY_CACHE_DIR.'firewall'.SHIELDFY_DS.md5(json_encode($info)).'.shcache';
		if(file_exists($cache_file) && (filemtime($cache_file) + 3600 ) > time() && $result = file_get_contents($cache_file)) {
			if($result == 'NO'){
				$this->block();
			}
		}else{
			@unlink($cache_file);


            $filter = new ShieldfyFilter;
            $result = $filter->check($info);


			if($result['response'] == 'pass'){
				/* pass lets cache it for a while */
				@file_put_contents($cache_file, "YES");
			}
			if($result['response'] == 'replace'){
				$get = (array)$result->get;
				$post = (array)$result->post;
				if($get){
					$get = json_decode(json_encode($get), true);
					$_GET = $get;
				}
				if($post){
					$post = json_decode(json_encode($post), true);
					$_POST = $post;
				}
			}
			if($result['response'] == 'block'){
				/* block cache it then block */                
				@file_put_contents($cache_file, "NO");
				$this->report($info,$result, true);
			}
		}
	}
}


/* run the firewall */
$uri = trim($_SERVER['SCRIPT_NAME'],'/');
$uri = explode('/',$uri);
$uri = @$uri[count($uri)-1];

if($uri != 'shieldfy.php'):
	/* main shield */
	$signature = hash_hmac('sha256', SHIELDFY_APP_KEY, SHIELDFY_APP_SECRET);
    header('X-Web-Shield: ShieldfyWebShield');
    header('X-Shieldfy-Signature: '.$signature);
    if (function_exists('header_remove')) {
        header_remove('x-powered-by');
    }else{
        header('X-Powered-By: NONE');
    }
	ShieldfyShield::init()->shield();
	return; //end shield
endif;

/* internal shield server */
@set_time_limit(0);
@ini_set('max_execution_time', 600);
error_reporting(0);
/* error handling */
function ShieldfyErrorHandler($errno, $errstr, $errfile, $errline){
	$fp = fopen(SHIELDFY_CACHE_DIR.'logs'.SHIELDFY_DS.'err_log.shieldfy', "a");
	fwrite($fp, date("H:i:s")." :: $errno :: $errstr :: => $errfile:$errline"."\n");
	fclose($fp);
}
$seh = set_error_handler("ShieldfyErrorHandler");

/* Router  */
class ShieldfyServer extends ShieldfyCoreShield{
	private static $_instance = null;
	private static $map = null;
	private static $source = null;
	private function __construct () { }
	public static function getInstance ()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }
    function auth(){
    	$id = @$_SERVER['HTTP_X_SHIELDFY_CALLBACK_TOKEN'];
    	if($id == ''){
    		$id = @$_GET['token'];
    	}
    	if($id != hash_hmac('sha256',SHIELDFY_APP_SECRET,SHIELDFY_APP_KEY)){
    		$this->block();
    	}
    	return $this;
    }
	function load($source){
		self::$source = $source;
		return $this;
	}
	function to($map){
		self::$map = $map;
		return $this;
	}
	function run(){
		$source = trim(self::$source,'/');
		if(empty($source)){
			$this->end(404,"Not Found");
			return false;
		}
		$map = self::$map;
		if(is_array($map)){
			foreach($map as $route => $action){
				if($source == $route){
					$action_arr = explode('@', $action);
					$result = self::loadAndRun($action_arr[0],$action_arr[1]);
					if($result){
						return true;
					}else{
						$this->end(404,"Not Found");
						return false;
					}
				}
			}
			$this->end(404,"Not Found");
			return false;
		}
	}
	function loadAndRun($controller,$action){
		$controller = 'Shieldfy'.$controller;
		$controllerClass = new $controller;
		if(is_callable(array($controllerClass,$action))){
			$return = $controllerClass->$action();
			return true;
		}else{
			return false;
		}
	}
}

/* controllers */
/* == Hello == */
class ShieldfyHello extends ShieldfyCoreShield{
    function hi()
    {
        $this->response(200,SHIELDFY_VERSION);
    }
}
/* == Logs == */
class ShieldfyLogs extends ShieldfyCoreShield{
	function errors(){
		$err = @file_get_contents(SHIElDFY_CACHE_DIR.'logs'.SHIELDFY_DS.'err_log.shieldfy');
		if($err){
			@file_put_contents(SHIElDFY_CACHE_DIR.'logs'.SHIELDFY_DS.'err_log.shieldfy', "");
			$this->response(200,$err);
		}else{
			$this->response(404,"Nothing Found");
		}
	}
	function clear(){
		$cache_dir = SHIElDFY_CACHE_DIR.'firewall'.SHIELDFY_DS;
		$res = scandir($cache_dir);
		foreach($res as $file){
			if(!is_dir($file)){
				$ext = pathinfo($file, PATHINFO_EXTENSION);
				if($ext == 'shcache'){
					@unlink(SHIElDFY_CACHE_DIR.'firewall'.SHIELDFY_DS.$file);
				}
			}
		}
		$this->response(200,"Ok");
	}
}

/* Route Map */
$map = array(
	/* init */
	'hello'=>'Hello@hi',
	/* logs */
	'logs/errors'=>'Logs@errors',
	'cache/clear' => 'Logs@clear'
);
/* execute */
ShieldfyServer::getInstance()->auth()->load($_GET['a'])->to($map)->run();
exit;