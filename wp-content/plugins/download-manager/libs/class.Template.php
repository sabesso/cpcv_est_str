<?php

namespace WPDM;


class Template
{
    public $Vars;

    function __construct(){
        return $this;
    }

    public static function locate($file, $tpldir = ''){

        $template_dirs = array(
            get_stylesheet_directory().'/download-manager/',
            get_template_directory().'/download-manager/',
            ''
        );
        if($tpldir !== '') {
            $template_dirs[] = rtrim($tpldir, '/').'/';
            $template_dirs[] = get_template_directory().'/download-manager/'.$tpldir.'/';
        }
        $template_dirs[] = WPDM_TPL_DIR;

        $template_dirs = apply_filters("wpdm_tpl_dir", $template_dirs);

        foreach ($template_dirs as $template_dir){
            if(file_exists($template_dir.$file))
                return $template_dir.$file;
        }
        return "";
    }

    function assign($var, $val){
        $this->Vars[$var] = $val;
        return $this;
    }

    function fetch($template, $tpldir = '' , $fallback = ''){
        $template = self::locate($template, $tpldir);
        if(is_array($this->Vars))
            extract($this->Vars);
        ob_start();
        include $template;
        return ob_get_clean();
    }

    function display($template, $tpldir = '' , $fallback = ''){
        echo $this->fetch($template, $tpldir, $fallback);
    }

    function execute($code){
        ob_start();
        if(is_array($this->Vars))
            extract($this->Vars);
        echo $code;
        return ob_get_clean();
    }

    static function output($data, $vars)
    {
        if(strstr($data, '.php')) {
            $filename = self::locate($data);
            $data = file_get_contents($filename);
        }
        $data = str_replace(array_keys($vars), array_values($vars), $data);
        return $data;
    }

}
