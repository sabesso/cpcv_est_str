<?php
/**
 * User: shahnuralam
 * Date: 4/11/18
 * Time: 1:10 PM
 * From v4.7.9
 * Last Updated: 10/11/2018
 */


namespace WPDM;

use WPDM\libs\Crypt;

class TempStorage
{
    static $data;

    function __construct()
    {
        if(file_exists(WPDM_CACHE_DIR.'/temp-storage.txt')) {
            $data = file_get_contents(WPDM_CACHE_DIR . '/temp-storage.txt');
            $data = Crypt::decrypt($data);
            if(!is_array($data)) $data = array();
        } else {
            $data = array();
        }
        self::$data = $data;

        register_shutdown_function(array($this, 'saveData'));
    }

    static function set($name, $value, $expire = 86400){
        self::$data[$name] = array('value' => $value, 'expire' => time() + $expire);
    }

    static function get($name){
        if(!isset(self::$data[$name])) return null;
        $_value = self::$data[$name];
        if(count($_value) == 0) return null;
        extract($_value);
        if(isset($expire) && $expire < time()) {
            unset(self::$data[$name]);
            $value = null;
        }
        return $value;
    }

    static function kill($name){
        if(isset(self::$data[$name]))
            unset(self::$data[$name]);
    }

    static function clear(){
        file_put_contents(WPDM_CACHE_DIR . '/temp-storage.txt', '');
    }

    function __destruct()
    {
        if(is_array(self::$data)) {
            $data = Crypt::encrypt(self::$data);
            file_put_contents(WPDM_CACHE_DIR . '/temp-storage.txt', $data);
        }
    }

    static function saveData()
    {
        if(is_array(self::$data)) {
            $data = Crypt::encrypt(self::$data);
            file_put_contents(WPDM_CACHE_DIR . '/temp-storage.txt', $data);
        }

    }

}

new TempStorage();