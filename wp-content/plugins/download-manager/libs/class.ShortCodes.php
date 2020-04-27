<?php
namespace WPDM\libs;


class ShortCodes
{

    function __construct()
    {


        // Total Package Count
        add_shortcode('wpdm_package_count', array($this, 'TotalPackages'));

        // Total Package Count
        add_shortcode('wpdm_download_count', array($this, 'TotalDownloads'));

        // Login/Register Form
        add_shortcode('wpdm_login_form', array($this, 'LoginForm'));

         // Register form
        add_shortcode('wpdm_reg_form', array($this, 'registerForm'));

        // Edit Profile
        add_shortcode('wpdm_edit_profile', array($this, 'EditProfile'));

        // Show all packages
        add_shortcode('wpdm_packages', array($this, 'packages'));

        // Show a package by id
        add_shortcode('wpdm_package', array($this, 'Package'));

        // Generate direct download link
        add_shortcode('wpdm_direct_link', array($this, 'directLink'));

        // Show all packages in a responsive table
        add_shortcode('wpdm_all_packages', array($this, 'allPackages'));
        add_shortcode('wpdm-all-packages', array($this, 'allPackages'));

        //Search Result
        add_shortcode('wpdm_search_result', array($this, 'searchResult'));

        //Packages by tag
        add_shortcode("wpdm_tag", array($this, 'packagesByTag'));

    }

    /**
     * @usage Short-code function for total download count
     * @param array $params
     * @return mixed
     */
    function TotalDownloads($params = array()){
        global $wpdb;
        $download_count = $wpdb->get_var("select sum(meta_value) from {$wpdb->prefix}postmeta where meta_key='__wpdm_download_count'");
        return $download_count;
    }

    /**
     * @usage Short-code function for total package count
     * @param array $params
     * @return mixed
     */
    function TotalPackages($params = array()){
        $count_posts = wp_count_posts('wpdmpro');
        $status = isset($params['status'])?$params['status']:'publish';
        if($status=='draft') return $count_posts->draft;
        if($status=='pending') return $count_posts->pending;
        return $count_posts->publish;
    }

    /**
     * @usage Short-code callback function for login/register form
     * @return string
     */
    function loginForm($params = array()){

        global $current_user;

        if(!isset($params) || !is_array($params)) $params = array();

        if(isset($params) && is_array($params))
            extract($params);
        if(!isset($redirect)) $redirect = get_permalink(get_option('__wpdm_user_dashboard'));

        if(!is_array($params)) $params = array();

        if(!isset($regurl)) {
            $regurl = get_option('__wpdm_register_url');
            if ($regurl > 0)
                $regurl = get_permalink($regurl);
        }
        $log_redirect =  $_SERVER['REQUEST_URI'];
        if(isset($params['redirect'])) $log_redirect = esc_url($params['redirect']);
        if(isset($_GET['redirect_to'])) $log_redirect = esc_url($_GET['redirect_to']);

        $up = parse_url($log_redirect);
        if(isset($up['host']) && $up['host'] != $_SERVER['SERVER_NAME']) $log_redirect = $_SERVER['REQUEST_URI'];

        $log_redirect = strip_tags($log_redirect);

        if(!isset($params['logo']) || $params['logo'] == '') $params['logo'] = get_site_icon_url();

        $__wpdm_social_login = get_option('__wpdm_social_login');
        $__wpdm_social_login = is_array($__wpdm_social_login)?$__wpdm_social_login:array();

        ob_start();

        if(is_user_logged_in())
            $template = wpdm_tpl_path("already-logged-in.php", WPDM_TPL_DIR, WPDM_TPL_FALLBACK);
        else {
            if(wpdm_query_var('action') === 'lostpassword')
                $template = wpdm_tpl_path('lost-password-form.php');
            else if(wpdm_query_var('action') === 'rp')
                $template = wpdm_tpl_path('reset-password-form.php');
            else
                $template =  wpdm_tpl_path('wpdm-login-form.php');
        }

        include($template);

        $content =  ob_get_clean();
        $content = apply_filters("wpdm_login_form_html", $content);

        return $content;
    }



    /**
     * @usage Edit profile
     * @return string
     */
    public function EditProfile()
    {
        global $wpdb, $current_user, $wp_query;
        wp_reset_query();
        $currentAccess = maybe_unserialize(get_option('__wpdm_front_end_access', array()));

        if (!array_intersect($currentAccess, $current_user->roles) && is_user_logged_in())
            return WPDM_Messages::Error(wpautop(stripslashes(get_option('__wpdm_front_end_access_blocked'))), -1);


        $id = wpdm_query_var('ID');

        ob_start();

        if (is_user_logged_in()) {
            include(wpdm_tpl_path('wpdm-edit-user-profile.php'));
        } else {
            $this->loginForm();
        }

        $data = ob_get_clean();
        return $data;
    }

    function registerForm($params = array()){

        if(!get_option('users_can_register')) return \WPDM_Messages::warning(__( "User registration is disabled" , "download-manager" ), -1);

        if(!isset($params) || !is_array($params)) $params = array();

        ob_start();
        $regparams = \WPDM\libs\Crypt::Encrypt($params);
        $loginurl = wpdm_login_url();
        $reg_redirect =  $loginurl;
        if(isset($params['autologin']) && $params['autologin'] == 'true') $reg_redirect = wpdm_user_dashboard_url();
        if(isset($params['redirect'])) $reg_redirect = esc_url($params['redirect']);
        if(isset($_GET['redirect_to'])) $reg_redirect = esc_url($_GET['redirect_to']);
        $force = uniqid();

        $up = parse_url($reg_redirect);
        if(isset($up['host']) && $up['host'] != $_SERVER['SERVER_NAME']) $reg_redirect = home_url('/');

        $reg_redirect = esc_attr(esc_url($reg_redirect));

        if(!isset($params['logo'])) $params['logo'] = get_site_icon_url();

        \WPDM\Session::set('__wpdm_reg_params', $params);

        $tmp_reg_info = \WPDM\Session::get('tmp_reg_info');

        $__wpdm_social_login = get_option('__wpdm_social_login');
        $__wpdm_social_login = is_array($__wpdm_social_login)?$__wpdm_social_login:array();

        //Template
        include(wpdm_tpl_path('wpdm-reg-form.php'));

        $data = ob_get_clean();
        return $data;
    }

    function packages($params = array('items_per_page' => 10, 'title' => false, 'desc' => false, 'order_field' => 'date', 'order' => 'desc', 'paging' => false, 'toolbar' => 1, 'template' => '','cols'=>3, 'colspad'=>2, 'colsphone' => 1))
    {

        $defaults = array('items_per_page' => 10, 's' =>'', 'title' => false, 'desc' => false, 'order_field' => 'date', 'order' => 'desc', 'paging' => false, 'toolbar' => 1, 'template' => 'link-template-panel','cols'=>3, 'colspad'=>2, 'colsphone' => 1);
        $params = shortcode_atts($defaults, $params, 'wpdm_packages' );

        if(is_array($params))
            extract($params);

        $cwd_class = "col-md-".(int)(12/$cols);
        $cwdsm_class = "col-sm-".(int)(12/$colspad);
        $cwdxs_class = "col-xs-".(int)(12/$colsphone);

        if(isset($id)) {
            $id = trim($id, ", ");
            $cids = explode(",", $id);
        }

        if(!isset($items_per_page) || $items_per_page < 1) $items_per_page = 10;

        global $wpdb, $current_user, $post, $wp_query;

        $order_field = isset($order_field) ? $order_field : 'date';
        $order_field = isset($_GET['orderby']) ? esc_attr($_GET['orderby']) : $order_field;
        $order = isset($order) ? $order : 'desc';
        $order = isset($_GET['order']) ? esc_attr($_GET['order']) : $order;
        $cp = wpdm_query_var('cp','num');
        if(!$cp) $cp = 1;

        $params = array(
            'post_type' => 'wpdmpro',
            'paged' => $cp,
            'posts_per_page' => $items_per_page,
            'include_children' => false,
        );

        if(isset($s) && $s != '')
            $params['s'] = $s;


        if (get_option('_wpdm_hide_all', 0) == 1) {
            $params['meta_query'] = array(
                array(
                    'key' => '__wpdm_access',
                    'value' => 'guest',
                    'compare' => 'LIKE'
                )
            );
            if(is_user_logged_in()){
                global $current_user;
                $params['meta_query'][] = array(
                    'key' => '__wpdm_access',
                    'value' => $current_user->roles[0],
                    'compare' => 'LIKE'
                );
                $params['meta_query']['relation'] = 'OR';
            }
        }

        $order_fields = array('__wpdm_download_count','__wpdm_view_count','__wpdm_package_size_b');
        if(!in_array( "__wpdm_".$order_field, $order_fields)) {
            $params['orderby'] = $order_field;
            $params['order'] = $order;
        } else {
            $params['orderby'] = 'meta_value_num';
            $params['meta_key'] = "__wpdm_".$order_field;
            $params['order'] = $order;
        }

        $params = apply_filters("wpdm_packages_query_params", $params);

        $packs = new \WP_Query($params);

        $total = $packs->found_posts;

        $pages = ceil($total / $items_per_page);
        $page = isset($_GET['cp']) ? (int)$_GET['cp'] : 1;
        $start = ($page - 1) * $items_per_page;

        if (!isset($paging) || intval($paging) == 1) {
            $pag = new \WPDM\libs\Pagination();
            $pag->items($total);
            $pag->nextLabel(' &#9658; ');
            $pag->prevLabel(' &#9668; ');
            $pag->limit($items_per_page);
            $pag->currentPage($page);
        }

        $burl = get_permalink();
        $url = $_SERVER['REQUEST_URI']; //get_permalink();
        $url = strpos($url, '?') ? $url . '&' : $url . '?';
        $url = preg_replace("/[\&]*cp=[0-9]+[\&]*/", "", $url);
        $url = strpos($url, '?') ? $url . '&' : $url . '?';
        if (!isset($paging) || intval($paging) == 1)
            $pag->urlTemplate($url . "cp=[%PAGENO%]");


        $html = '';
        $templates = maybe_unserialize(get_option("_fm_link_templates", true));

        if(isset($templates[$template])) $template = $templates[$template]['content'];

        //global $post;
        while($packs->have_posts()) { $packs->the_post();

            $pack = (array)$post;
            $repeater = "<div class='{$cwd_class} {$cwdsm_class} {$cwdxs_class}'>".\WPDM\Package::FetchTemplate($template, $pack)."</div>";
            $html .=  $repeater;

        }
        wp_reset_query();

        $html = "<div class='row'>{$html}</div>";


        if (!isset($paging) || intval($paging) == 1)
            $pgn = "<div style='clear:both'></div>" . $pag->show() . "<div style='clear:both'></div>";
        else
            $pgn = "";
        global $post;

        $sap = get_option('permalink_structure') ? '?' : '&';
        $burl = $burl . $sap;
        if (isset($_GET['p']) && $_GET['p'] != '') $burl .= 'p=' . esc_attr($_GET['p']) . '&';
        if (isset($_GET['src']) && $_GET['src'] != '') $burl .= 'src=' . esc_attr($_GET['src']) . '&';
        $orderby = isset($_GET['orderby']) ? esc_attr($_GET['orderby']) : 'create_date';
        $order = ucfirst($order);

        $title = isset($title) && $title !=''?"<h3>$title</h3>":"";

        return "<div class='w3eden'>" . $title . $desc  . $html  . $pgn . "<div style='clear:both'></div></div>";
    }

    /**
     * @param array $params
     * @return array|null|WP_Post
     * @usage Shortcode callback function for [wpdm_simple_search]
     */
    function searchResult( $params = array() ){
        global $wpdb;
        if(is_array($params))
            @extract($params);
        $template = isset($template) && $template != '' ? $template : 'link-template-calltoaction3';
        $items_per_page = isset($items_per_page) ? $items_per_page : 0;
        update_post_meta(get_the_ID(), "__wpdm_link_template", $template);
        update_post_meta(get_the_ID(), "__wpdm_items_per_page", $items_per_page);
        $cols = isset($cols) && in_array($cols, array(1,2,3,4,6))?$cols:1;
        $html = (!isset($init) || $init == 1 || wpdm_query_var('q') != '')? $this->Packages(array('items_per_page' => $items_per_page, 'template' => $template, 's' => wpdm_query_var('q'), 'paging' => false, 'toolbar' => 0,'cols'=> $cols, 'colspad'=>1, 'colsphone' => 1)) : '';
        $html = "<div class='w3eden'><form style='margin-bottom: 20px'><div class='input-group input-group-lg'><div class='input-group-addon no-radius'><i class='fa fa-search'></i></div><input type='text' name='q' value='".esc_attr(wpdm_query_var('q'))."' class='form-control input-lg no-radius' /></div></form><div class='container-fluid'><div class='row'>{$html}</div></div>";
        return str_replace(array("\r","\n"),"",$html);
    }


    /**
     * @usage Callback function for shortcode [wpdm_package id=PID]
     * @param mixed $params
     * @return mixed
     */
    function Package($params)
    {
        extract($params);
        if(!isset($id)) return '';
        $id = (int)$id;
        $postlink = site_url('/');
        if (isset($pagetemplate) && $pagetemplate == 1) {
            $template = get_post_meta($id,'__wpdm_page_template', true);
            $wpdm_package['page_template'] = stripcslashes($template);
            $data = FetchTemplate($template, $id, 'page');
            $siteurl = site_url('/');
            return  "<div class='w3eden'>{$data}</div>";
        }

        $template = isset($params['template'])?$params['template']:get_post_meta($id,'__wpdm_template', true);
        if($template == '') $template = 'link-template-default.php';
        return "<div class='w3eden'>" . \WPDM\Package::fetchTemplate($template, $id, 'link') . "</div>";
    }

    /**
     * @usage Generate direct link to download
     * @param $params
     * @param string $content
     * @return string
     */
    function directLink($params, $content = "")
    {
        extract($params);

        global $wpdb;
        $ID = (int)$params['id'];

        if(\WPDM\Package::isLocked($ID))
            $linkURL = get_permalink($ID);
        else
            $linkURL = home_url("/?wpdmdl=".$ID);

        $extras = isset($params['extras'])?wpdm_sanitize_var($params['extras'], 'txt'):"";
        $target = isset($params['target'])?"target='".wpdm_sanitize_var($params['target'], 'txt')."'":"";
        $class = isset($params['class'])?"class='".wpdm_sanitize_var($params['class'], 'txt')."'":"";
        $style = isset($params['style'])?"style='".wpdm_sanitize_var($params['style'], 'txt')."'":"";
        $rel = isset($params['rel'])?"rel='".wpdm_sanitize_var($params['rel'], 'txt')."'":"";
        $eid = isset($params['eid'])?"id='".wpdm_sanitize_var($params['eid'], 'txt')."'":"";
        $linkLabel = isset($params['label']) && !empty($params['label'])?$params['label']:get_post_meta($ID, '__wpdm_link_label', true);
        $linkLabel = empty($linkLabel)?get_the_title($ID):$linkLabel;
        $linkLabel = wpdm_sanitize_var($linkLabel, 'kses');
        return  "<a {$target} {$class} {$eid} {$style} {$rel} {$extras} href='$linkURL'>$linkLabel</a>";

    }


    /**
     * @usage Short-code [wpdm_all_packages] to list all packages in tabular format
     * @param array $params
     * @return string
     */
    function AllPackages($params = array())
    {
        global $wpdb, $current_user, $wp_query;
        $items = isset($params['items_per_page']) && $params['items_per_page'] > 0 ? $params['items_per_page'] : 20;
        if(isset($params['jstable']) && $params['jstable']==1) $items = 2000;
        $cp = isset($wp_query->query_vars['paged']) && $wp_query->query_vars['paged'] > 0 ? $wp_query->query_vars['paged'] : 1;
        $terms = isset($params['categories']) ? explode(",", $params['categories']) : array();
        if (isset($_GET['wpdmc'])) $terms = array(esc_attr($_GET['wpdmc']));
        $offset = ($cp - 1) * $items;
        $total_files = wp_count_posts('wpdmpro')->publish;
        if (count($terms) > 0) {
            $tax_query = array(array(
                'taxonomy' => 'wpdmcategory',
                'field' => 'slug',
                'terms' => $terms,
                'operator' => 'IN',
                'include_children' => false
            ));
        }

        ob_start();
        include(wpdm_tpl_path("wpdm-all-downloads.php"));
        $data = ob_get_clean();
        return $data;
    }

    /**
     * @usage Show packages by tag
     * @param $params
     * @return string
     */
    function packagesByTag($params)
    {
        $params['order_field'] = isset($params['order_by'])?$params['order_by']:'publish_date';
        $params['tag'] = 1;
        unset($params['order_by']);
        if (isset($params['item_per_page']) && !isset($params['items_per_page'])) $params['items_per_page'] = $params['item_per_page'];
        unset($params['item_per_page']);
        return wpdm_embed_category($params);

    }





}
