<?php


namespace WPDM;

class Settings
{

    function get($name, $default = ''){
        $value = get_option($name);
        $value = htmlspecialchars_decode($value);
        $value = stripslashes_deep($value);
        $value = wpdm_escs($value);
        return $value;
    }


}