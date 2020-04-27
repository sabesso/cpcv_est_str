<?php
/**
 * User: shahnuralam
 * Date: 11/9/15
 * Time: 8:01 PM
 */

namespace WPDM\admin\menus;


use WPDM\Session;

class Addons
{

    function __construct()
    {
        add_action('admin_menu', array($this, 'Menu'));
    }

    function Menu()
    {
        add_submenu_page('edit.php?post_type=wpdmpro', __( "Add-Ons &lsaquo; Download Manager" , "download-manager" ), __("Add-Ons" , "download-manager" ), WPDM_MENU_ACCESS_CAP, 'wpdm-addons', array($this, 'UI'));
    }

    function UI(){
        if (!Session::get('wpdm_addon_store_data') || !is_array(json_decode(Session::get('wpdm_addon_store_data')))) {
            $data = remote_get('https://www.wpdownloadmanager.com/?wpdm_api_req=getPackageList');
            $cats = remote_get('https://www.wpdownloadmanager.com/?wpdm_api_req=getCategoryList');
            Session::set('wpdm_addon_store_data', $data);
            Session::set('wpdm_addon_store_cats', $cats);
        } else {
            $data = Session::get('wpdm_addon_store_data');
            $cats = Session::get('wpdm_addon_store_cats');
        }
        $error = $data;
        $data = json_decode($data);
        $cats = json_decode($cats);
        if(!is_array($data)) echo $error;
        else
            include(WPDM_BASE_DIR . "/admin/tpls/addons-list.php");
    }

}