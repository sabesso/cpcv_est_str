<?php

namespace WPDM\libs;

use WPDM\Session;
use WPDM\TempStorage;

class Apply {

    function __construct(){

        add_filter('wpdm_custom_data', array( $this, 'SR_CheckPackageAccess' ), 10, 2);

        add_action('publish_wpdmpro', array( $this, 'customPings' ));

        $this->AdminActions();
        $this->FrontendActions();

    }

    function FrontendActions(){
        if(is_admin()) return;
        //add_action("init", array($this, 'playMedia'), 0);
        add_action("init", array($this, 'triggerDownload'), 1);
        add_filter('widget_text', 'do_shortcode');
        add_action('query_vars', array( $this, 'dashboardPageVars' ));
        add_action('init', array( $this, 'addWriteRules' ), 1, 0 );
        add_action('init', array($this, 'login'));
        add_action('init', array($this, 'register'));
        add_action('wp', array($this, 'wpdmIframe'));
        add_action('wp', array($this, 'updateProfile'));
        add_action('init', array($this, 'Logout'));
        add_action('request', array($this, 'rssFeed'));
        add_filter( 'ajax_query_attachments_args', array($this, 'usersMediaQuery') );
        add_action( 'init', array($this, 'sfbAccess'));
        add_action( 'wp_head', array($this, 'addGenerator'), 9999);
        add_action( 'wp_head', array($this, 'googleFont'), 999999);
        add_filter('pre_get_posts', array($this, 'queryTag'));
        add_filter('the_excerpt_embed', array($this, 'oEmbed'));



    }

    function AdminActions(){
        if(!is_admin()) return;

        add_action("wp_ajax_nopriv_updatePassword", array($this, 'updatePassword'));
        add_action("wp_ajax_nopriv_resetPassword", array($this, 'resetPassword'));

        add_action( 'admin_init', array($this, 'sfbAccess'));
        add_action('save_post', array( $this, 'dashboardPages' ));
        add_action( 'wp_ajax_wpdm_clear_stats', array($this, 'clearStats'));
        add_action( 'wp_ajax_clear_cache', array($this, 'clearCache'));
        add_action( 'admin_head', array($this, 'uiColors'), 999999);

    }

    function SR_CheckPackageAccess($data, $id){
        global $current_user;
        $skiplocks = maybe_unserialize(get_option('__wpdm_skip_locks', array()));
        if( is_user_logged_in() ){
            foreach($skiplocks as $lock){
                unset($data[$lock."_lock"]); // = 0;
            }
        }

        return $data;
    }

    function AddWriteRules(){
        global $wp_rewrite;
        $udb_page_id = get_option('__wpdm_user_dashboard', 0);
        if($udb_page_id) {
            $page_name = get_post_field("post_name", $udb_page_id);
            add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $udb_page_id . '&udb_page=$matches[1]', 'top');
        }
        $adb_page_id = get_option('__wpdm_author_dashboard', 0);

        if($adb_page_id) {
            $page_name = get_post_field("post_name", $adb_page_id);
            add_rewrite_rule('^' . $page_name . '/(.+)/?', 'index.php?page_id=' . $adb_page_id . '&adb_page=$matches[1]', 'top');
        }
        //$wp_rewrite->flush_rules();
        //dd($wp_rewrite);


    }

    function dashboardPages($post_id){
        if ( wp_is_post_revision( $post_id ) )  return;
        $page_id = get_option('__wpdm_user_dashboard', 0);
        $post = get_post($post_id);
        $flush = 0;
        if((int)$page_id > 0 && has_shortcode($post->post_content, "wpdm_user_dashboard")) {
            update_option('__wpdm_user_dashboard', $post_id);
            $flush = 1;
        }

        $page_id = get_option('__wpdm_author_dashboard', 0);
        $post = get_post($post_id);

        if((int)$page_id > 0 && has_shortcode($post->post_content, "wpdm_frontend")) {
            update_option('__wpdm_author_dashboard', $post_id);
            $flush = 1;
        }

        if($flush == 1) {
            $this->AddWriteRules();
            global $wp_rewrite;
            $wp_rewrite->flush_rules();
        }

    }

    function dashboardPageVars( $vars ){
        array_push($vars, 'udb_page', 'adb_page');
        return $vars;
    }

    /**
     * Login function
     */

    function login()
    {

        global $wp_query, $post, $wpdb;
        if (!isset($_POST['wpdm_login'])) return;

        $login_try = (int)Session::get('login_try');
        $login_try++;
        Session::set('login_try', $login_try);

        if($login_try > 30) wp_die("Slow Down!");

        $csk = get_option('_wpdm_recaptcha_secret_key');
        if((int)get_option('__wpdm_recaptcha_loginform', 0) === 1 && $csk != ''){
            $ret = remote_post('https://www.google.com/recaptcha/api/siteverify', array('secret' => $csk, 'response' => wpdm_query_var('__recap')));
            $ret = json_decode($ret);
            if(!$ret->success) {
                Session::set('login_error', __("Invalid CAPTCHA!", "download-manager"));
                if (wpdm_is_ajax()) {
                    wp_send_json(array('success' => false, 'message' => 'Error: ' . Session::get('login_error')));
                    die();
                }
                header("location: " . $_POST['permalink']);
                die();
            }
        }

        Session::clear('login_error');
        $creds = array();
        $creds['user_login'] = $_POST['wpdm_login']['log'];
        $creds['user_password'] = $_POST['wpdm_login']['pwd'];
        $creds['remember'] = isset($_POST['rememberme']) ? $_POST['rememberme'] : false;
        $user = wp_signon($creds, false);
        if (is_wp_error($user)) {
            Session::set('login_error', $user->get_error_message());

            if(wpdm_is_ajax()) wp_send_json(array('success' => false, 'message' => $user->get_error_message()));

            header("location: " . $_SERVER['HTTP_REFERER']);
            die();
        } else {
            wp_set_auth_cookie($user->ID);
            Session::set('login_try', 0);
            do_action('wp_login', $creds['user_login'], $user);
            if(wpdm_is_ajax()) wp_send_json(array('success' => true, 'message' => __( "Success! Redirecting...", "download-manager" )));

            header("location: " . $_POST['redirect_to']);
            die();
        }
    }

    /**
     * @usage Logout an user
     */

    function logout()
    {

        if (isset($_REQUEST['logout']) && wp_verify_nonce(wpdm_query_var('logout'), NONCE_KEY)) {
            wp_logout();
            header("location: " . wpdm_login_url());
            die();
        }
    }

    /**
     * @usage Register an user
     */
    function register()
    {
        global $wp_query, $wpdb;
        if (!isset($_POST['wpdm_reg'])) return;

        /*
        $shortcode_params = Session::get('__wpdm_reg_params');

        if(!is_array($shortcode_params)){
            $reg_error = apply_filters("wpdm_reg_error", __( "Something is wrong! Required parameters are missing!" , "download-manager" ), $error_type = 'missing_params');
            Session::set('reg_error', $reg_error);
            wp_send_json(array('success' => false, 'message' => 'Error: ' . $reg_error));
            die();
        }
        */

        $shortcode_params = Crypt::decrypt(wpdm_query_var('__phash'));

        if(!is_array($shortcode_params)) $shortcode_params = array();

        $_verify_email = isset($shortcode_params['verifyemail']) && ( $shortcode_params['verifyemail'] === 'true' || (int)$shortcode_params['verifyemail'] === 1 ) ? true : false;
        $_show_captcha = !isset($shortcode_params['captcha']) || ( $shortcode_params['captcha'] === 'true' || (int)$shortcode_params['captcha'] === 1 ) ? true : false;
        $_auto_login = isset($shortcode_params['autologin']) && ( $shortcode_params['autologin'] === 'true' || (int)$shortcode_params['autologin'] === 1 ) ? true : false;

        if(!isset($_REQUEST['__reg_nonce']) || !wp_verify_nonce($_REQUEST['__reg_nonce'], NONCE_KEY)){
            $reg_error = apply_filters("wpdm_reg_error", __( "Something is Wrong! Please refresh the page and try again" , "download-manager" ), $error_type = 'nonce');
            Session::set('reg_error',  $reg_error);
            if (wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . $reg_error)); die(); }
            header("location: " . $_POST['permalink']);
            die();
        }

        $csk = get_option('_wpdm_recaptcha_secret_key');
        if((int)get_option('__wpdm_recaptcha_regform', 0) === 1 && $csk != '' && $_show_captcha !== false){
            $ret = remote_post('https://www.google.com/recaptcha/api/siteverify', array('secret' => $csk, 'response' => wpdm_query_var('__recap')));
            $ret = json_decode($ret);
            if(!$ret->success) {
                $reg_error = apply_filters("wpdm_reg_error", __( "Invalid CAPTCHA!" , "download-manager" ), $error_type = 'captcha');
                Session::set('reg_error', $reg_error);
                if (wpdm_is_ajax()) {
                    wp_send_json(array('success' => false, 'message' => 'Error: ' . $reg_error));
                    die();
                }
                header("location: " . $_POST['permalink']);
                die();
            }
        }

        if(!get_option('users_can_register') && isset($_POST['wpdm_reg'])){
            $reg_error = apply_filters("wpdm_reg_error", __( "Error: User registration is disabled!" , "download-manager" ), $error_type = 'reg_disabled');
            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => $reg_error)); die(); }
            else Session::set('reg_error', $reg_error);
            header("location: " . $_POST['permalink']);
            die();
        }


        $_POST['wpdm_reg']['full_name'] = $_POST['wpdm_reg']['first_name']." ".$_POST['wpdm_reg']['last_name'];
        extract($_POST['wpdm_reg']);
        $display_name = $first_name." ".$last_name;

        Session::set('tmp_reg_info', $_POST['wpdm_reg']);
        $user_id = username_exists($user_login);
        $loginurl = $_POST['permalink'];
        if ($user_login == '') {

            $reg_error = apply_filters("wpdm_reg_error", __( "Username is Empty!" , "download-manager" ), $error_type = 'empty_username');
            Session::set('reg_error',  $reg_error);

            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => $reg_error)); die(); }

            header("location: " . $_POST['permalink']);
            die();
        }
        if (!isset($user_email) || !is_email($user_email)) {

            $reg_error = apply_filters("wpdm_reg_error", __( "Invalid email address!" , "download-manager" ), $error_type = 'invalid_email');
            Session::set('reg_error',  $reg_error);

            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => $reg_error)); die(); }

            header("location: " . $_POST['permalink']);
            die();
        }

        if (!$user_id) {
            $user_id = email_exists($user_email);
            if (!$user_id) {

                $auto_login = $_auto_login;

                $user_pass = $_verify_email || !isset($user_pass) || $user_pass == ''?wp_generate_password(12, false):$user_pass;
                $emlpass = $_verify_email ? __( "Password: " , "download-manager" ).$user_pass."<br/>":"";

                $errors = new \WP_Error();

                do_action( 'register_post', $user_login, $user_email, $errors );

                $errors = apply_filters( 'registration_errors', $errors, $user_login, $user_email );

                if ( $errors->get_error_code() ) {
                    if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => 'Error: ' . $errors->get_error_message())); die(); }
                    else Session::set('reg_error',  'Error: ' . $errors->get_error_message());
                    header("location: " . $_POST['permalink']);
                    die();
                }

                $user_id = wp_create_user($user_login, $user_pass, $user_email);

                $user_meta = array('ID' => $user_id, 'display_name' => $display_name, 'first_name' => $first_name, 'last_name' => $last_name);

                $valid_roles = get_option('__wpdm_signup_roles', array());

                if(isset($shortcode_params['role']) && trim($shortcode_params['role']) !== '' && is_array($valid_roles) && count($valid_roles) > 0 && in_array($shortcode_params['role'], $valid_roles)){

                    $user_meta['role'] = $shortcode_params['role'];

                }
                wp_update_user($user_meta);

                //To User
                $usparams = array('to_email' => $user_email, 'name' => $display_name, 'first_name' => $first_name, 'last_name' => $last_name, 'user_email' => $user_email, 'username' => $user_login, 'password' => $user_pass);

                foreach ($_POST['wpdm_reg'] as $key => $value){
                    $usparams["user_".$key] = $value;
                }

                //Filter user email params
                $usparams = apply_filters("wpdm_signup_user_mail_params", $usparams, $user_id);

                \WPDM\Email::send("user-signup", $usparams);

                //To Admin
                $ip = wpdm_get_client_ip();
                $data = array(
                    array('Name', $display_name),
                    array('Username', $user_login),
                    array('Email', $user_email),
                    array('IP', $ip)
                );
                $css = array('col' => array('background: #edf0f2 !important'), 'td' => 'border-bottom:1px solid #e6e7e8');
                $table = MailUI::table(null, $data, $css);

                $params['name'] = $display_name;
                $params['username'] = $user_login;
                $params['email'] = $user_email;
                $params['user_ip'] = $ip;
                $params['edit_user_btn'] = "<a class='button' style='display:block;margin:10px 0 0;text-decoration: none;text-align:center;' href='".admin_url('user-edit.php?user_id='.$user_id)."'> ".__( "Edit User" , "download-manager" )." </a>";
                //Include all data from signup form
                foreach ($_POST['wpdm_reg'] as $key => $value){
                    $params["user_".$key] = $value;
                }

                //Filter admin email params
                $params = apply_filters("wpdm_signup_admin_mail_params", $params, $user_id);
                \WPDM\Email::send("user-signup-admin", $params);

                Session::clear('guest_order');
                Session::clear('login_error');
                Session::clear('tmp_reg_info');

                $creds['user_login'] = $user_login;
                $creds['user_password'] = $user_pass;
                $creds['remember'] = true;

                $reg_success = apply_filters("wpdm_reg_success", __( "Your account has been created successfully and login info sent to your mail address." , "download-manager" ));
                Session::set('sccs_msg', $reg_success);

                if($auto_login==1) {
                    $reg_success = apply_filters("wpdm_reg_success", __( "Your account has been created successfully." , "download-manager" ));
                    Session::set('sccs_msg', $reg_success);
                    wp_signon($creds);
                    wp_set_current_user($user_id);
                    wp_set_auth_cookie($user_id);
                    $loginurl = wpdm_user_dashboard_url();

                }

                if(wpdm_is_ajax()) {  wp_send_json(array('success' => true)); die(); }

                header("location: " . $loginurl);
                die();
            } else {
                $reg_error = apply_filters("wpdm_reg_error", __( "Invalid Email Address!" , "download-manager" ), $error_type = 'invalid_email');
                Session::set('reg_error', $reg_error);
                $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];

                if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => $reg_error)); die(); }

                header("location: " . $loginurl);
                die();
            }
        } else {
            $reg_error = apply_filters("wpdm_reg_error", __( "Username already exists." , "download-manager" ), $error_type = 'username_exists');
            Session::set('reg_error', $reg_error);
            $plink = $_POST['permalink'] ? $_POST['permalink'] : $_SERVER['HTTP_REFERER'];

            if(wpdm_is_ajax()) { wp_send_json(array('success' => false, 'message' => $reg_error)); die(); }

            header("location: " . $loginurl);
            die();
        }

    }

    function resetPassword(){
        if(wpdm_query_var('__reset_pass')){

            if ( empty( $_POST['user_login'] ) ) {
                die('error');
            } elseif ( strpos( $_POST['user_login'], '@' ) ) {
                $user_data = get_user_by( 'email', trim( wp_unslash( $_POST['user_login'] ) ) );
                if ( empty( $user_data ) )
                    die('error');
            } else {
                $login = trim($_POST['user_login']);
                $user_data = get_user_by('login', $login);
            }
            if(Session::get( '__reset_time' ) && time() - Session::get( '__reset_time' ) < 60){
                echo "toosoon";
                exit;
            }
            if(!is_object($user_data) || !isset($user_data->user_login)) die('error');
            $user_login = $user_data->user_login;
            $user_email = $user_data->user_email;
            $key = get_password_reset_key( $user_data );



            $reseturl = add_query_arg(array('action' => 'rp', 'key' => $key, 'login' => rawurlencode($user_login)), wpdm_login_url());

            $params = array('reset_password' => $reseturl, 'to_email' => $user_email);

            \WPDM\Email::send('password-reset', $params);
            Session::set( '__reset_time' , time() );
            echo 'ok';
            exit;

        }
    }

    function updatePassword(){
        if(wpdm_query_var('__update_pass')){

            if(wp_verify_nonce(wpdm_query_var('__update_pass'), NONCE_KEY)){
                $pass = wpdm_query_var('password');
                if($pass == '') die('error');
                $user = Session::get('__up_user');
                $user = maybe_unserialize($user);
                if(is_object($user) && isset($user->ID)) {
                    wp_set_current_user($user->ID, $user->user_login);
                    wp_set_auth_cookie($user->ID);
                    //do_action('wp_login', $user->user_login);
                    wp_set_password($pass, $user->ID);
                    //print_r($user);
                    wp_send_json(array('success' => true, 'message' => ''));
                } else wp_send_json(array('success' => false, 'message' => __('Session Expired! Please try again.', 'download-manager')));
            }
            else
                wp_send_json(array('success' => false, 'message' => __('Session Expired! Please try again.', 'download-manager')));

        }
    }

    function updateProfile()
    {
        global $current_user;

        if (isset($_POST['wpdm_profile']) && is_user_logged_in() && wp_verify_nonce(wpdm_query_var('__wpdm_epnonce'), NONCE_KEY)) {

            $error = 0;

            $pfile_data['display_name'] = $_POST['wpdm_profile']['display_name'];
            $pfile_data['description'] = $_POST['wpdm_profile']['description'];
            $pfile_data['user_email'] = $_POST['wpdm_profile']['user_email'];


            if ($_POST['password'] != $_POST['cpassword']) {
                Session::set('member_error', 'Password not matched');
                $error = 1;
            }
            if (!$error) {
                $pfile_data['ID'] = $current_user->ID;
                if ($_POST['password'] != '')
                    $pfile_data['user_pass'] = $_POST['password'];

                wp_update_user($pfile_data);

                update_user_meta($current_user->ID, 'payment_account', $_POST['payment_account']);
                Session::set( 'member_success' , 'Profile data updated successfully.' );
            }

            do_action("wpdm_update_profile");

            if(wpdm_is_ajax()){
                ob_clean();
                if($error == 1){
                    $msg['type'] = 'danger';
                    $msg['title'] = 'ERROR!';
                    $msg['msg'] = Session::get( 'member_error' );
                    Session::clear('member_error');
                    wp_send_json($msg);
                    die();
                } else {
                    $msg['type'] = 'success';
                    $msg['title'] = 'DONE!';
                    $msg['msg'] = Session::get( 'member_success' );
                    Session::clear('member_success');
                    wp_send_json($msg);
                    die();
                }
            }
            header("location: " . $_SERVER['HTTP_REFERER']);
            die();
        }
    }

    function playMedia(){

        if(strstr("!{$_SERVER['REQUEST_URI']}", "/wpdm-media/")){
            $media = explode("wpdm-media/", $_SERVER['REQUEST_URI']);
            $media = explode("/", $media[1]);
            list($ID, $file, $name) = $media;
            $key = wpdm_query_var('_wpdmkey');

            if ( isset($_SERVER['HTTP_RANGE']) ) {
                $partialContent = true;
                preg_match('/bytes=(\d+)-(\d+)?/', $_SERVER['HTTP_RANGE'], $matches);
                $offset = intval($matches[1]);
                $length = intval($matches[2]) - $offset;
            } else {
                $partialContent = false;
            }

            $keyValid = is_wpdmkey_valid($ID, $key, true);
            if(!$partialContent) {
                if ($key == '' || !$keyValid)
                    \WPDM_Messages::Error(stripslashes(get_option('wpdm_permission_msg')), 1);
            }
            $files = \WPDM\Package::getFiles($ID);
            $file = $files[$file];
            $file = \WPDM\libs\FileSystem::fullPath($file, $ID);
            $stream = new \WPDM\libs\StreamMedia($file);
            $stream->start();
            die();
        }
    }


    /**
     * @usage Process Download Request
     */
    function triggerDownload()
    {

        global $wpdb, $current_user, $wp_query;
        //get_currentuserinfo();
        if (!isset($wp_query->query_vars['wpdmdl']) && !isset($_GET['wpdmdl'])) return;
        $id = isset($_GET['wpdmdl']) ? (int)$_GET['wpdmdl'] : (int)$wp_query->query_vars['wpdmdl'];
        if ($id <= 0) return;
        $key = esc_attr(wpdm_query_var('_wpdmkey'));
        $key = $key == '' && array_key_exists('_wpdmkey', $wp_query->query_vars) ? $wp_query->query_vars['_wpdmkey'] : $key;
        $key = preg_replace("/[^_a-z|A-Z|0-9]/i", "", $key);
        $key = "__wpdmkey_".$key;
        $package = get_post($id, ARRAY_A);
        $package = array_merge($package, wpdm_custom_data($package['ID']));
        if (isset($package['files']))
            $package['files'] = maybe_unserialize($package['files']);
        else
            $package['files'] = array();
        //$package = wpdm_setup_package_data($package);

        $package['access'] = wpdm_allowed_roles($id);

        if (is_array($package)) {
            $role = @array_shift(@array_keys($current_user->caps));
            $cpackage = apply_filters('before_download', $package);
            $lock = '';
            $package = $cpackage ? $cpackage : $package;

            if (isset($package['password_lock']) && $package['password_lock'] == 1) $lock = 'locked';
            if (isset($package['captcha_lock']) && $package['captcha_lock'] == 1) $lock = 'locked';

            if ($lock !== 'locked')
                $lock = apply_filters('wpdm_check_lock', $lock, $id);

            if (isset($_GET['masterkey']) && esc_attr($_GET['masterkey']) == $package['masterkey']) {
                $lock = 0;
            }


            //$limit = $key ? (int)trim(get_post_meta($package['ID'], $key, true)) : 0;

            $xlimit = $key != '' ? get_post_meta($package['ID'], $key, true) : '';
            $xlimit = maybe_unserialize($xlimit);
            $limit = !is_array($xlimit)?(int)$xlimit:$xlimit['use'];


            if ($limit <= 0 && $key != '') delete_post_meta($package['ID'], $key);
            else if ($key != '')
                update_post_meta($package['ID'], $key, $limit - 1);

            $matched = (is_array(@maybe_unserialize($package['access'])) && is_user_logged_in())?array_intersect($current_user->roles, @maybe_unserialize($package['access'])):array();

            if (($id != '' && is_user_logged_in() && count($matched) < 1 && !@in_array('guest', $package['access'])) || (!is_user_logged_in() && !@in_array('guest', $package['access']) && $id != '')) {
                do_action("wpdm_download_permission_denied", $id);
                wpdm_download_data("permission-denied.txt", __("You don't have permission to download this file",'download-manager'));
                die();
            } else {

                if ($lock === 'locked' && $limit <= 0) {
                    do_action("wpdm_invalid_download_link", $id, $key);
                    if ($key != '')
                        wpdm_download_data("link-expired.txt", __("Download link is expired. Please get new download link.",'download-manager'));
                    else
                        wpdm_download_data("invalid-link.txt", __("Download link is expired or not valid. Please get new download link.",'download-manager'));
                    die();
                } else
                    if ($package['ID'] > 0) {
                        if((int)$package['quota'] == 0 || $package['quota'] > $package['download_count'])
                            include(WPDM_BASE_DIR . "wpdm-start-download.php");
                        else
                            wpdm_download_data("stock-limit-reached.txt", __("Stock Limit Reached", 'wpdmpro'));

                    }

            }
        } else
            wpdm_notice(__("Invalid download link.",'download-manager'));
    }


    /**
     * @usage Add with main RSS feed
     * @param $query
     * @return mixed
     */
    function rssFeed($query) {
        if ( isset($query['feed'])  && !isset($query['post_type']) &&  get_option('__wpdm_rss_feed_main', 0) == 1 ){
            $query['post_type'] = array('post','wpdmpro');
        }
        return $query;
    }

    /**
     * @usage Schedule custom ping
     * @param $post_id
     */
    function customPings($post_id){
        wp_schedule_single_event(time(), 'do_pings', array($post_id));
    }

    /**
     * @usage Allow access to server file browser for selected user roles
     */
    function sfbAccess(){

        global $wp_roles;

        $roleids = is_array($wp_roles->roles)?array_keys($wp_roles->roles):array();
        $roles = get_option('_wpdm_file_browser_access',array('administrator'));
        $naroles = array_diff($roleids, $roles);

        foreach($roles as $role) {
            $role = get_role($role);
            if(is_object($role))
                $role->add_cap('access_server_browser');
        }

        if(is_array($naroles)) {
            foreach ($naroles as $role) {
                $role = get_role($role);
                $role->remove_cap('access_server_browser');

            }
        }

    }

    /**
     * @usage Allow front-end users to access their own files only
     * @param $query_params
     * @return string
     */
    function usersMediaQuery( $query_params ){
        global $current_user;

        if(current_user_can('edit_posts')) return $query_params;

        if( is_user_logged_in() ){
            $query_params['author'] = $current_user->ID;
        }
        return $query_params;
    }

    /**
     * @usage Add packages wth tag query
     * @param $query
     * @return mixed
     */
    function queryTag($query)
    {
        if ($query->is_tag() && $query->is_main_query()) {
            $post_type = get_query_var('post_type');
            if (!is_array($post_type))
                $post_type = array('post', 'wpdmpro');
            else
                $post_type = array_merge($post_type, array('post', 'wpdmpro'));
            $query->set('post_type', $post_type);
        }
        return $query;
    }

    /**
     * @usage Add generator tag
     */
    function addGenerator(){
        echo '<meta name="generator" content="WordPress Download Manager '.WPDM_Version.'" />'."\r\n";
    }

    function oEmbed($content){
        if(get_post_type(get_the_ID()) !== 'wpdmpro') return $content;
        if(function_exists('wpdmpp_effective_price') && wpdmpp_effective_price(get_the_ID()) > 0)
            $template = '<table class="table table-bordered"><tbody><tr><td colspan="2">[excerpt_200]</td></tr><tr><td>[txt=Price]</td><td>[currency][effective_price]</td></tr><tr><td>[txt=Version]</td><td>[version]</td></tr><tr><td>[txt=Total Files]</td><td>[file_count]</td></tr><tr><td>[txt=File Size]</td><td>[file_size]</td></tr><tr><td>[txt=Create Date]</td><td>[create_date]</td></tr><tr><td>[txt=Last Updated]</td><td>[update_date]</td><tr><td colspan="2" style="text-align: right;border-bottom: 0"><a class="wpdmdlbtn" href="[page_url]" target="_parent">[txt=Buy Now]</a></td></tr></tbody></table><br/><style> .wpdmdlbtn {-moz-box-shadow:inset 0px 1px 0px 0px #9acc85;-webkit-box-shadow:inset 0px 1px 0px 0px #9acc85;box-shadow:inset 0px 1px 0px 0px #9acc85;background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));background:-moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#74ad5a\', endColorstr=\'#68a54b\',GradientType=0);background-color:#74ad5a;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:1px solid #3b6e22;display:inline-block;cursor:pointer;color:#ffffff !important; font-size:12px;font-weight:bold;padding:10px 20px;text-transform: uppercase;text-decoration:none !important;}.wpdmdlbtn:hover {background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));background:-moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#68a54b\', endColorstr=\'#74ad5a\',GradientType=0);background-color:#68a54b;}.wpdmdlbtn:active {position:relative;top:1px;} .table{width:100%;border: 1px solid #eeeeee;} .table td{ padding:10px;border-bottom:1px solid #eee;}</style>';
        else
            $template = '<table class="table table-bordered"><tbody><tr><td colspan="2">[excerpt_200]</td></tr><tr><td>[txt=Version]</td><td>[version]</td></tr><tr><td>[txt=Total Files]</td><td>[file_count]</td></tr><tr><td>[txt=File Size]</td><td>[file_size]</td></tr><tr><td>[txt=Create Date]</td><td>[create_date]</td></tr><tr><td>[txt=Last Updated]</td><td>[update_date]</td><tr><td colspan="2" style="text-align: right;border-bottom: 0"><a class="wpdmdlbtn" href="[page_url]" target="_parent">[txt=Download]</a></td></tr></tbody></table><br/><style> .wpdmdlbtn {-moz-box-shadow:inset 0px 1px 0px 0px #9acc85;-webkit-box-shadow:inset 0px 1px 0px 0px #9acc85;box-shadow:inset 0px 1px 0px 0px #9acc85;background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #74ad5a), color-stop(1, #68a54b));background:-moz-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-webkit-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-o-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:-ms-linear-gradient(top, #74ad5a 5%, #68a54b 100%);background:linear-gradient(to bottom, #74ad5a 5%, #68a54b 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#74ad5a\', endColorstr=\'#68a54b\',GradientType=0);background-color:#74ad5a;-moz-border-radius:3px;-webkit-border-radius:3px;border-radius:3px;border:1px solid #3b6e22;display:inline-block;cursor:pointer;color:#ffffff !important; font-size:12px;font-weight:bold;padding:10px 20px;text-transform: uppercase;text-decoration:none !important;}.wpdmdlbtn:hover {background:-webkit-gradient(linear, left top, left bottom, color-stop(0.05, #68a54b), color-stop(1, #74ad5a));background:-moz-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-webkit-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-o-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:-ms-linear-gradient(top, #68a54b 5%, #74ad5a 100%);background:linear-gradient(to bottom, #68a54b 5%, #74ad5a 100%);filter:progid:DXImageTransform.Microsoft.gradient(startColorstr=\'#68a54b\', endColorstr=\'#74ad5a\',GradientType=0);background-color:#68a54b;}.wpdmdlbtn:active {position:relative;top:1px;} .table{width:100%; border: 1px solid #eeeeee; } .table td{ padding:10px;border-bottom:1px solid #eee;}</style>';
        return \WPDM\Package::fetchTemplate($template, get_the_ID());
    }

    function clearStats(){
        if(!current_user_can('manage_options')) die('error');
        global $wpdb;
        $wpdb->query('truncate table '.$wpdb->prefix.'ahm_download_stats');
        $wpdb->query("delete from {$wpdb->prefix}postmeta where meta_key='__wpdmx_user_download_count'");
        die('ok');
    }

    function wpdmIframe(){
        if(isset($_REQUEST['__wpdmlo'])){
            include wpdm_tpl_path("lock-options-iframe.php");
            die();
        }
    }

    /**
     * Empty cache dir
     */
    function clearCache(){
        if(!current_user_can('manage_options')) return;
        \WPDM\libs\FileSystem::deleteFiles(WPDM_CACHE_DIR, false);
        die('ok');
    }


    static function googleFont(){
        $wpdmss = maybe_unserialize(get_option('__wpdm_disable_scripts', array()));
        $uicolors = maybe_unserialize(get_option('__wpdm_ui_colors', array()));
            ?>
            <style>
                <?php if(!in_array('google-font', $wpdmss)) { ?>
                @import url('https://fonts.googleapis.com/css?family=Rubik:400,500');
                <?php } ?>



                .w3eden .fetfont,
                .w3eden .btn,
                .w3eden .btn.wpdm-front h3.title,
                .w3eden .wpdm-social-lock-box .IN-widget a span:last-child,
                .w3eden #xfilelist .panel-heading,
                .w3eden .wpdm-frontend-tabs a,
                .w3eden .alert:before,
                .w3eden .panel .panel-heading,
                .w3eden .discount-msg,
                .w3eden .panel.dashboard-panel h3,
                .w3eden #wpdm-dashboard-sidebar .list-group-item,
                .w3eden #package-description .wp-switch-editor,
                .w3eden .w3eden.author-dashbboard .nav.nav-tabs li a,
                .w3eden .wpdm_cart thead th,
                .w3eden #csp .list-group-item,
                .w3eden .modal-title {
                    font-family: Rubik, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
                    text-transform: uppercase;
                    font-weight: 500;
                }
                .w3eden #csp .list-group-item{
                    text-transform: unset;
                }
            </style>
            <?php
            self::uiColors();

    }

    function modalLoginForm($params = array()){

        global $current_user;

        if(!isset($params) || !is_array($params)) $params = array();

        if(isset($params) && is_array($params))
            extract($params);
        if(!isset($redirect)) $redirect = get_permalink(get_option('__wpdm_user_dashboard'));

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
        //get_option('__wpdm_modal_login', 0)

        include( wpdm_tpl_path('wpdm-login-modal-form.php') );

        $content =  ob_get_clean();
        $content = apply_filters("wpdm_login_modal_form_html", $content);

        return $content;
    }

    static function uiColors(){

        $wpdmss = maybe_unserialize(get_option('__wpdm_disable_scripts', array()));
        if (is_array($wpdmss) && in_array('wpdm-front', $wpdmss)) return;

        $uicolors = maybe_unserialize(get_option('__wpdm_ui_colors', array()));
        $primary = isset($uicolors['primary'])?$uicolors['primary']:'#4a8eff';
        $secondary = isset($uicolors['secondary'])?$uicolors['secondary']:'#4a8eff';
        $success = isset($uicolors['success'])?$uicolors['success']:'#18ce0f';
        $info = isset($uicolors['info'])?$uicolors['info']:'#2CA8FF';
        $warning = isset($uicolors['warning'])?$uicolors['warning']:'#f29e0f';
        $danger = isset($uicolors['danger'])?$uicolors['danger']:'#ff5062';
        $font = get_option('__wpdm_google_font', 'Rubik');
        $font = $font?$font.',':'';
        if(is_singular('wpdmpro'))
            $ui_button = get_option('__wpdm_ui_download_button');
        else
            $ui_button = get_option('__wpdm_ui_download_button_sc');
        $class =  ".btn.".(isset($ui_button['color'])?$ui_button['color']:'btn-primary').(isset($ui_button['size']) && $ui_button['size'] != ''?".".$ui_button['size']:'');
        ?>
        <style>

            :root{
                --color-primary: <?php echo $primary; ?>;
                --color-primary-rgb: <?php echo wpdm_hex2rgb($primary); ?>;
                --color-primary-hover: <?php echo isset($uicolors['primary'])?$uicolors['primary_hover']:'#4a8eff'; ?>;
                --color-primary-active: <?php echo isset($uicolors['primary'])?$uicolors['primary_active']:'#4a8eff'; ?>;
                --color-secondary: <?php echo $secondary; ?>;
                --color-secondary-rgb: <?php echo wpdm_hex2rgb($secondary); ?>;
                --color-secondary-hover: <?php echo isset($uicolors['secondary'])?$uicolors['secondary_hover']:'#4a8eff'; ?>;
                --color-secondary-active: <?php echo isset($uicolors['secondary'])?$uicolors['secondary_active']:'#4a8eff'; ?>;
                --color-success: <?php echo $success; ?>;
                --color-success-rgb: <?php echo wpdm_hex2rgb($success); ?>;
                --color-success-hover: <?php echo isset($uicolors['success_hover'])?$uicolors['success_hover']:'#4a8eff'; ?>;
                --color-success-active: <?php echo isset($uicolors['success_active'])?$uicolors['success_active']:'#4a8eff'; ?>;
                --color-info: <?php echo $info; ?>;
                --color-info-rgb: <?php echo wpdm_hex2rgb($info); ?>;
                --color-info-hover: <?php echo isset($uicolors['info_hover'])?$uicolors['info_hover']:'#2CA8FF'; ?>;
                --color-info-active: <?php echo isset($uicolors['info_active'])?$uicolors['info_active']:'#2CA8FF'; ?>;
                --color-warning: <?php echo $warning; ?>;
                --color-warning-rgb: <?php echo wpdm_hex2rgb($warning); ?>;
                --color-warning-hover: <?php echo isset($uicolors['warning_hover'])?$uicolors['warning_hover']:'orange'; ?>;
                --color-warning-active: <?php echo isset($uicolors['warning_active'])?$uicolors['warning_active']:'orange'; ?>;
                --color-danger: <?php echo $danger; ?>;
                --color-danger-rgb: <?php echo wpdm_hex2rgb($danger); ?>;
                --color-danger-hover: <?php echo isset($uicolors['danger_hover'])?$uicolors['danger_hover']:'#ff5062'; ?>;
                --color-danger-active: <?php echo isset($uicolors['danger_active'])?$uicolors['danger_active']:'#ff5062'; ?>;
                --color-green: <?php echo isset($uicolors['green'])?$uicolors['green']:'#30b570'; ?>;
                --color-blue: <?php echo isset($uicolors['blue'])?$uicolors['blue']:'#0073ff'; ?>;
                --color-purple: <?php echo isset($uicolors['purple'])?$uicolors['purple']:'#8557D3'; ?>;
                --color-red: <?php echo isset($uicolors['red'])?$uicolors['red']:'#ff5062'; ?>;
                --color-muted: rgba(69, 89, 122, 0.6);
                --wpdm-font: <?php echo $font; ?> -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
            }
            .wpdm-download-link<?php echo $class; ?>{
                border-radius: <?php echo (isset($ui_button['borderradius'])?$ui_button['borderradius']:4); ?>px;
            }


        </style>
        <?php

    }



}
