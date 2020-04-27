<?php

namespace WPDM\admin\menus;


class Templates
{

    function __construct()
    {
        add_action('admin_init', array($this, 'Save'));
        add_action('wp_ajax_template_preview', array($this, 'Preview'));
        add_action('wp_ajax_wpdm_delete_template', array($this, 'deleteTemplate'));
        add_action('wp_ajax_update_template_status', array($this, 'updateTemplateStatus'));
        add_action('wp_ajax_wpdm_save_email_setting', array($this, 'saveEmailSetting'));
        add_action('admin_menu', array($this, 'Menu'));
    }

    function Menu()
    {
        add_submenu_page('edit.php?post_type=wpdmpro', __( "Templates &lsaquo; Download Manager" , "download-manager" ), __( "Templates" , "download-manager" ), WPDM_MENU_ACCESS_CAP, 'templates', array($this, 'UI'));
    }

    function UI(){
        $ttype = isset($_GET['_type']) ? esc_attr($_GET['_type']) : 'link';

       if (wpdm_query_var('task') === 'EditEmailTemplate')
            \WPDM\admin\menus\Templates::EmailEditor();
        else
            \WPDM\admin\menus\Templates::Show();
    }


    public static function Editor(){
        include(WPDM_BASE_DIR . "admin/tpls/template-editor.php");
    }


    public static function EmailEditor(){
        include(WPDM_BASE_DIR . "admin/tpls/email-template-editor.php");
    }


    public static function Show(){
        include(WPDM_BASE_DIR . "admin/tpls/templates.php");
    }

    /**
     * @usage Delete link/page template
     * @since 4.7.0
     */

    function deleteTemplate(){
        if (current_user_can(WPDM_ADMIN_CAP)) {
            $ttype = wpdm_query_var('ttype');
            $tplid = wpdm_query_var('tplid');
            $tpldata = maybe_unserialize(get_option("_fm_{$ttype}_templates"));
            if (!is_array($tpldata)) $tpldata = array();
            unset($tpldata[$tplid]);
            update_option("_fm_{$ttype}_templates", @serialize($tpldata));
            die('ok');
        }

    }


    /**
     * @usage Save Link/Page Templates
     */
    function Save()
    {
        if (!isset($_GET['page']) || $_GET['page'] != 'templates') return;
        $ttype = isset($_GET['_type']) ? esc_attr($_GET['_type']) : 'link';
        if (isset($_GET['task']) && $_GET['task'] == 'DeleteTemplate') {
            $tpldata = maybe_unserialize(get_option("_fm_{$ttype}_templates"));
            if (!is_array($tpldata)) $tpldata = array();
            unset($tpldata[wpdm_query_var('tplid')]);
            update_option("_fm_{$ttype}_templates", @serialize($tpldata));

            header("location: edit.php?post_type=wpdmpro&page=templates&_type=$ttype");
            die();
        }

        if (isset($_POST['tpl'])) {
            if (is_array(get_option("_fm_{$ttype}_templates")))
                $tpldata = (get_option("_fm_{$ttype}_templates"));
            else
                $tpldata = maybe_unserialize(get_option("_fm_{$ttype}_templates"));
            if (!is_array($tpldata)) $tpldata = array();
            $tplid = wpdm_query_var('tplid');
            $tpldata[$tplid] = $_POST['tpl'];
            update_option("_fm_{$ttype}_templates", @serialize($tpldata));

            header("location: edit.php?post_type=wpdmpro&&page=templates&_type=$ttype");
            die();
        }

        if (isset($_POST['email_template'])) {
            if(current_user_can(WPDM_ADMIN_CAP) && wp_verify_nonce(wpdm_query_var('__wpdm_nonce'), NONCE_KEY)) {
                check_ajax_referer(NONCE_KEY, '__wpdm_nonce');
                $email_template = wpdm_query_var('email_template', array('validate' => array('subject' => '', 'message' => 'escs', 'from_name' => '', 'from_email' => '')));
                update_option("__wpdm_etpl_" . wpdm_query_var('id'), $email_template);
                if (wpdm_is_ajax()) {
                    die('ok');
                }
                header("location: edit.php?post_type=wpdmpro&&page=templates&_type=$ttype");
                die();
            }
        }

    }

    /**
     * @usage Preview link/page template
     */
    function Preview()
    {
        error_reporting(0);

        $wposts = array();

        $template = sanitize_file_name(wpdm_query_var('template'));
        $type = wpdm_query_var("_type");

        $args=array(
            'post_type'=>'wpdmpro',
            'posts_per_page'=>1
        );

        $wposts = get_posts( $args  );

        $html = "";

        foreach( $wposts as $p ) {

            $package = (array)$p;

            $html .= wpdm_fetch_template($template, $package, $type);

        }

        if(count($wposts)==0) $html = "<div class='col-md-12'><div class='alert alert-info'>".__( "No package found! Please create at least 1 package to see template preview" , "download-manager" )."</div> </div>";
        $html = "<div class='w3eden'>".$html."</div><div style='clear:both'></div>";

        echo $html;
        die();

    }

    public static function Dropdown($params, $activeOnly = false)
    {
        extract($params);
        $type = isset($type) && in_array($type, array('link', 'page', 'email')) ? esc_attr($type) : 'link';
        $tplstatus = maybe_unserialize(get_option("_fm_{$type}_template_status"));

        $activetpls = array();
        if(is_array($tplstatus)) {
            foreach ($tplstatus as $tpl => $active) {
                if ($active)
                    $activetpls[] = $tpl;
            }
        }

        $ttpldir = get_stylesheet_directory() . '/download-manager/' . $type . '-templates/';
        $ttpls = array();
        if(file_exists($ttpldir)) {
            $ttpls = scandir($ttpldir);
            array_shift($ttpls);
            array_shift($ttpls);
        }

        $ltpldir = WPDM_TPL_DIR . $type . '-templates/';
        $ctpls = scandir($ltpldir);
        array_shift($ctpls);
        array_shift($ctpls);

        foreach($ctpls as $ind => $tpl){
            $ctpls[$ind] = $ltpldir.$tpl;
        }

        foreach($ttpls as $tpl){
            if(!in_array($ltpldir.$tpl, $ctpls)) {
                $ctpls[] = $ttpldir . $tpl;
            }
        }

        $custom_templates = maybe_unserialize(get_option("_fm_{$type}_templates",true));

        $name = isset($name)?$name:$type.'_template';
        $css = isset($css)?"style='$css'":'';
        $id = isset($id)?$id:uniqid();
        $default = $type == 'link'?'link-template-calltoaction3.php':'page-template-1col-flat.php';
        $xdf = str_replace(".php", "", $default);
        if(is_array($activetpls) && count($activetpls) > 0)
            $default = in_array($xdf, $activetpls) || !isset($activetpls[0]) ? $default : $activetpls[0];
        $html = "<select name='$name' id='$id' class='form-control template {$type}_template' {$css}><option value='$default'>Select ".ucfirst($type)." Template</option>";
        $data = array();
        foreach ($ctpls as $ctpl) {
            $ind = str_replace(".php", "", basename($ctpl));
            if(!$activeOnly || ($activeOnly && (!isset($tplstatus[$ind]) || $tplstatus[$ind] == 1))) {
                $tmpdata = file_get_contents($ctpl);
                $regx = "/WPDM.*Template[\s]*:([^\-\->]+)/";
                if (preg_match($regx, $tmpdata, $matches)) {
                    $data[basename($ctpl)] = $matches[1];
                    $eselected = isset($selected) && $selected == basename($ctpl) ? 'selected=selected' : '';

                    $html .= "<option value='" . basename($ctpl) . "' {$eselected}>{$matches[1]}</option>";
                }
            }
        }

        if(is_array($custom_templates)) {
            foreach ($custom_templates as $id => $template) {
                if(!$activeOnly || ($activeOnly && (!isset($tplstatus[$id]) || $tplstatus[$id] == 1))) {
                    $data[$id] = $template['title'];
                    $eselected = isset($selected) && $selected == $id ? 'selected=selected' : '';
                    $html .= "<option value='{$id}' {$eselected}>{$template['title']}</option>";
                }
            }
        }
        $html .= "</select>";

        return isset($data_type) && $data_type == 'ARRAY'? $data : $html;
    }

    function saveEmailSetting(){
        if(current_user_can(WPDM_ADMIN_CAP) && wp_verify_nonce(wpdm_query_var('__wpdm_nonce'), NONCE_KEY)) {
            check_ajax_referer(NONCE_KEY, '__wpdm_nonce');
            update_option('__wpdm_email_template', wpdm_query_var('__wpdm_email_template'));
            $email_settings = wpdm_query_var('__wpdm_email_setting', array('validate' => array('logo' => 'url', 'banner' => 'url', 'youtube' => 'url', 'twitter' => 'url', 'facebook' => 'url', 'footer_text' => 'txts')));
            update_option('__wpdm_email_setting', $email_settings);
            die("Done!");
        }
    }

    function updateTemplateStatus(){
        if(!current_user_can(WPDM_ADMIN_CAP)) die('error');
        $type = wpdm_query_var('type');
        $tpldata = maybe_unserialize(get_option("_fm_{$type}_template_status"));
        $tpldata[wpdm_query_var('template')] = wpdm_query_var('status');
        update_option("_fm_{$type}_template_status", @serialize($tpldata));
        echo "OK";
        die();
    }
}
