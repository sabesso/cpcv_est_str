<?php
/**
 * User: shahnuralam
 * Date: 01/11/18
 * Time: 7:08 PM
 * From v4.7.9
 * Last Updated: 10/11/2018
 */

namespace WPDM;

use WPDM\libs\Crypt;

class Session
{
    static $data;
    static $deviceID;

    function __construct()
    {

        $deviceID = md5(wpdm_get_client_ip().$_SERVER['HTTP_USER_AGENT']);

        if(file_exists(WPDM_CACHE_DIR."session-{$deviceID}.txt")) {
            $data = file_get_contents(WPDM_CACHE_DIR . "session-{$deviceID}.txt");
            $data = Crypt::decrypt($data);
            if(!is_array($data)) $data = array();
        } else {
            $data = array();
        }

        self::$deviceID = $deviceID;
        self::$data = $data;

        register_shutdown_function(array($this, 'saveSession'));
    }

    static function deviceID($deviceID){
        self::$deviceID = $deviceID;
    }

    static function set($name, $value, $expire = 1800){
        self::$data[self::$deviceID][$name] = array('value' => $value, 'expire' => time() + $expire);
    }

    static function get($name){
        if(!isset(self::$data[self::$deviceID], self::$data[self::$deviceID][$name])) return null;
        $_value = self::$data[self::$deviceID][$name];
        if(count($_value) == 0) return null;
        extract($_value);
        if(isset($expire) && $expire < time()) {
            unset(self::$data[$name]);
            $value = null;
        }
        return $value;

    }

    static function clear($name = ''){
        if($name == '')
            self::$data = array();
        else {
            if(isset(self::$data[self::$deviceID], self::$data[self::$deviceID][$name])) unset(self::$data[self::$deviceID][$name]);
        }
        //file_put_contents(WPDM_CACHE_DIR . '/session.txt', '');
    }

    static function show(){
        echo "<pre>".print_r(self::$data, 1)."</pre>";
    }

    static function saveSession()
    {
        if(is_array(self::$data)) {
            $data = Crypt::encrypt(self::$data);
            if(!file_exists(WPDM_CACHE_DIR)) {
                @mkdir(WPDM_CACHE_DIR, 0755);
                @chmod(WPDM_CACHE_DIR, 0755);
            }
            file_put_contents(WPDM_CACHE_DIR . 'session-'.self::$deviceID.'.txt', $data);
        }

    }

}

new Session();

