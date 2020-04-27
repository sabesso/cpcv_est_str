<?php
/*
Plugin Name: Download Manager
Plugin URI: https://www.wpdownloadmanager.com/pricing/
Description: Manage, Protect and Track file downloads, and sell digital products from your WordPress site. A complete digital asset management solution.
Author: W3 Eden
Version: 3.0.95
Author URI: https://www.wpdownloadmanager.com/
Text Domain: download-manager
Domain Path: /languages
*/


namespace WPDM;

global $WPDM;

//if(!isset($_SESSION) && (!isset($_REQUEST['action']) || $_REQUEST['action'] !== 'edit-theme-plugin-file') && !strstr($_SERVER['REQUEST_URI'], 'wpdm-media/') && !isset($_REQUEST['wpdmdl']) && !isset($_GET['health-check-troubleshoot-enable-plugin']))
//    @session_start();


$content_dir = str_replace('\\','/',WP_CONTENT_DIR);

if(!defined('WPDM_ADMIN_CAP'))
define('WPDM_ADMIN_CAP','manage_options');

if(!defined('WPDM_MENU_ACCESS_CAP'))
define('WPDM_MENU_ACCESS_CAP','manage_options');

define('WPDM_BASE_DIR',dirname(__FILE__).'/');

define('WPDM_BASE_URL',plugins_url('/download-manager/'));

if(!defined('WPDM_USE_GLOBAL'))
    define('WPDM_USE_GLOBAL', null);

if(!defined('UPLOAD_DIR'))
define('UPLOAD_DIR',$content_dir.'/uploads/download-manager-files/');

$upload_dir = wp_upload_dir();
$upload_dir = $upload_dir['basedir'];
if(!defined('WPDM_CACHE_DIR'))
define('WPDM_CACHE_DIR',$upload_dir.'/wpdm-cache/');

if(!defined('_DEL_DIR'))
define('_DEL_DIR',$content_dir.'/uploads/download-manager-files');

if(!defined('UPLOAD_BASE'))
define('UPLOAD_BASE',$content_dir.'/uploads/');

if(!defined('WPDM_TPL_FALLBACK'))
    define('WPDM_TPL_FALLBACK', dirname(__FILE__) . '/tpls/');

if(!defined('WPDM_TPL_DIR')) {
    if((int)get_option('__wpdm_bsversion', '') !== 3)
        define('WPDM_TPL_DIR', dirname(__FILE__) . '/tpls/');
    else
        define('WPDM_TPL_DIR', dirname(__FILE__) . '/tpls3/');
}

if(!defined('WPDM_FONTAWESOME_URL'))
    define('WPDM_FONTAWESOME_URL',WPDM_BASE_URL . 'assets/fontawesome/css/all.min.css');

if(!defined('NONCE_KEY') || !defined('NONCE_SALT')){
    //To avoid warning when not defined
    define('NONCE_KEY',       'Bm|_Ek@F|HdkA7)=alSJg5_<z-j-JmhK<l&*.d<J+/71?&7pL~XBXnF4jKz>{Apx');
    define('NONCE_SALT',       'XffybIqfklKjegGdRp7EU4kprZX00NESOE8olZ2BZ8+BQTw3bXXSbzeGssgZ');
    /**
     * Generate WordPress Security Keys and Salts from https://api.wordpress.org/secret-key/1.1/salt/ and place them in your wp-config.php
     */
}

ini_set('upload_tmp_dir',UPLOAD_DIR.'/cache/');


class WordPressDownloadManager{

    var $apply;
    public $shortCode;

    function __construct(){

        define('WPDM_Version','3.0.95');

        register_activation_hook(__FILE__, array($this, 'Install'));

        add_action( 'init', array($this, 'registerScripts'), 0 );
        add_action( 'init', array($this, 'registerPostTypeTaxonomy'), 1 );

        add_action( 'plugins_loaded', array($this, 'loadTextdomain') );
        add_action( 'wp_enqueue_scripts', array($this, 'enqueueScripts') );

        add_action( 'wp_head', array($this, 'wpHead') );
        add_action( 'wp_footer', array($this, 'wpFooter') );

        spl_autoload_register( array( $this, 'AutoLoad' ) );

        include_once(dirname(__FILE__) . "/wpdm-functions.php");

        include(dirname(__FILE__)."/wpdm-core.php");
        include(dirname(__FILE__)."/libs/class.AssetManager.php");

                            new \WPDM\UserDashboard();
        $this->apply      = new \WPDM\libs\Apply();
                            new \WPDM\admin\WordPressDownloadManagerAdmin();
        $this->shortCode  = new \WPDM\libs\ShortCodes();
                            new MediaAccessControl();

        //do_action("wpdm_addon_init", WPDM_Version);

    }

    /**
     * @usage Install Plugin
     */
    function Install(){
        global $wpdb;

        delete_option('wpdm_latest');

        $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ahm_download_stats` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `pid` int(11) NOT NULL,
              `uid` int(11) NOT NULL,
              `oid` varchar(100) NOT NULL,
              `year` int(4) NOT NULL,
              `month` int(2) NOT NULL,
              `day` int(2) NOT NULL,
              `timestamp` int(11) NOT NULL,
              `ip` varchar(20) NOT NULL,
              PRIMARY KEY (`id`)
            )";

        $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ahm_emails` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `email` varchar(255) NOT NULL,
              `pid` int(11) NOT NULL,
              `date` int(11) NOT NULL,
              `custom_data` text NOT NULL,
              `request_status` INT( 1 ) NOT NULL,
              PRIMARY KEY (`id`)
            )";

        $sqls[] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}ahm_assets` (
              `ID` bigint(20) NOT NULL AUTO_INCREMENT,
              `path` text NOT NULL,
              `owner` int(11) NOT NULL,
              `activities` text NOT NULL,
              `comments` text NOT NULL,
              `access` text NOT NULL,
              `metadata` text NOT NULL,
              PRIMARY KEY (`ID`)
            )";


        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        foreach($sqls as $sql){
            $wpdb->query($sql);
            ///dbDelta($sql);
        }


        $this->RegisterPostTypeTaxonomy();
        flush_rewrite_rules();
        self::createDir();

    }

    /**
     * @usage Load Plugin Text Domain
     */
    function loadTextdomain(){
        load_plugin_textdomain('download-manager', false, 'download-manager/languages/');
    }

    /**
     * @usage Register WPDM Post Type and Taxonomy
     */
    public function registerPostTypeTaxonomy()
    {
        $labels = array(
            'name' => __('Downloads','download-manager'),
            'singular_name' => __('File','download-manager'),
            'add_new' => __('Add New','download-manager'),
            'add_new_item' => __('Add New File','download-manager'),
            'edit_item' => __('Edit File','download-manager'),
            'new_item' => __('New File','download-manager'),
            'all_items' => __('All Files','download-manager'),
            'view_item' => __('View File','download-manager'),
            'search_items' => __('Search Files','download-manager'),
            'not_found' => __('No File Found','download-manager'),
            'not_found_in_trash' => __('No Files found in Trash','download-manager'),
            'parent_item_colon' => '',
            'menu_name' => __('Downloads','download-manager')

        );


        $args = array(
            'labels' => $labels,
            'public' => true,
            'publicly_queryable' => get_option('__wpdm_publicly_queryable', 1),
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_nav_menus' => true,
            'query_var' => true,
            'rewrite' => array('slug' => 'download', 'with_front' => (bool)get_option('__wpdm_purl_with_front', false)), //get_option('__wpdm_purl_base','download')
            'capability_type' => 'post',
            'has_archive' => (get_option('__wpdm_has_archive', false)==false?false:sanitize_title(get_option('__wpdm_archive_page_slug', 'all-downloads'))),
            'hierarchical' => false,
            'taxonomies' => array('post_tag'),
            'menu_icon' => 'dashicons-download',
            'exclude_from_search' => (bool)get_option('__wpdm_exclude_from_search', false),
            'supports' => array('title', 'editor', 'publicize', 'excerpt', 'custom-fields', 'thumbnail', 'tags', 'comments','author')

        );


        register_post_type('wpdmpro', $args);


        $labels = array(
            'name' => __('Categories','download-manager'),
            'singular_name' => __('Category','download-manager'),
            'search_items' => __('Search Categories','download-manager'),
            'all_items' => __('All Categories','download-manager'),
            'parent_item' => __('Parent Category','download-manager'),
            'parent_item_colon' => __('Parent Category:','download-manager'),
            'edit_item' => __('Edit Category','download-manager'),
            'update_item' => __('Update Category','download-manager'),
            'add_new_item' => __('Add New Category','download-manager'),
            'new_item_name' => __('New Category Name','download-manager'),
            'menu_name' => __('Categories','download-manager'),
        );

        $args = array(
            'hierarchical' => true,
            'labels' => $labels,
            'show_ui' => true,
            'show_admin_column' => true,
            'query_var' => true,
            'rewrite' => array('slug' =>  'download-category'),
        );

        register_taxonomy('wpdmcategory', array('wpdmpro'), $args);


    }

    /**
     * @usage Create upload dir
     */
    public static function createDir()
    {
        if (!file_exists(UPLOAD_BASE)) {
            @mkdir(UPLOAD_BASE, 0755);
        }
        @chmod(UPLOAD_BASE, 0755);
        @mkdir(UPLOAD_DIR, 0755);
        @chmod(UPLOAD_DIR, 0755);

        if(!file_exists(WPDM_CACHE_DIR)) {
            @mkdir(WPDM_CACHE_DIR, 0755);
            @chmod(WPDM_CACHE_DIR, 0755);
        }

        self::setHtaccess();
        if (isset($_GET['re']) && $_GET['re'] == 1) {
            if (file_exists(UPLOAD_DIR)) $s = 1;
            else $s = 0;
            echo "<script>
        location.href='{$_SERVER['HTTP_REFERER']}&success={$s}';
        </script>";
            die();
        }
    }


    /**
     * @usage Protect Download Dir using .htaccess rules
     */
    public static function setHtaccess()
    {
        \WPDM\libs\FileSystem::blockHTTPAccess(UPLOAD_DIR);
    }

    function registerScripts(){

        wp_register_style('wpdm-font-awesome', WPDM_FONTAWESOME_URL);

        wp_register_style('wpdm-front-bootstrap3', plugins_url('/download-manager/assets/bootstrap3/css/bootstrap.min.css'));
        wp_register_style('wpdm-front-bootstrap', plugins_url('/download-manager/assets/bootstrap/css/bootstrap.min.css'));
        wp_register_style('wpdm-front3', plugins_url() . '/download-manager/assets/css/front3.css');
        wp_register_style('wpdm-front', plugins_url() . '/download-manager/assets/css/front.css');

        wp_register_script('wpdm-front-bootstrap3', plugins_url('/download-manager/assets/bootstrap3/js/bootstrap.min.js'), array('jquery'));
        wp_register_script('wpdm-front-bootstrap', plugins_url('/download-manager/assets/bootstrap/js/bootstrap.bundle.min.js'), array('jquery'));
        wp_register_script('jquery-validate', plugins_url('/download-manager/assets/js/jquery.validate.min.js'), array('jquery'));
    }

    /**
     * @usage Enqueue all styles and scripts
     */
    function enqueueScripts()
    {
        global $post;

        wp_enqueue_script('jquery');
        wp_enqueue_script('jquery-form');

        $wpdmss = maybe_unserialize(get_option('__wpdm_disable_scripts', array()));

        //if((is_object($post) && has_shortcode($post->post_content,'wpdm_frontend')) || get_post_type()=='wpdmpro' )

        if (!in_array('wpdm-font-awesome', $wpdmss))
            wp_enqueue_style('wpdm-font-awesome' );


        if(is_object($post) && ( has_shortcode($post->post_content,'wpdm_frontend') || has_shortcode($post->post_content,'wpdm-package-form') || has_shortcode($post->post_content,'wpdm_user_dashboard') || has_shortcode($post->post_content,'wpdm-file-browser') ) ){
            wp_enqueue_script('jquery-ui');
            wp_enqueue_script('jquery-ui-datepicker');
            wp_enqueue_script('thickbox');
            wp_enqueue_style('thickbox');
            wp_enqueue_script('media-upload');
            wp_enqueue_media();
        }

        if(get_post_type()=='wpdmpro' && is_single()){
            wp_enqueue_script('wpdm-datatable', WPDM_BASE_URL . 'assets/js/jquery.dataTables.min.js', array('jquery'));
        }


        $bsversion = get_option('__wpdm_bsversion', '');

        if (is_array($wpdmss) && !in_array('wpdm-bootstrap-css', $wpdmss)) {
            wp_enqueue_style('wpdm-front-bootstrap' . $bsversion);
        }

        if (is_array($wpdmss) && !in_array('wpdm-front', $wpdmss)) {
            wp_enqueue_style('wpdm-front' . $bsversion, 9999999999);
        }


        if (is_array($wpdmss) && !in_array('wpdm-bootstrap-js', $wpdmss)) {
            wp_enqueue_script('wpdm-front-bootstrap' . $bsversion);
        }

        wp_register_script('frontjs', plugins_url('/download-manager/assets/js/front.js'), array('jquery'));

        $wpdm_asset = array(
            'spinner' => '<i class="fas fa-sun fa-spin"></i>'
        );
        $this->asset = $wpdm_asset;
        $wpdm_asset = apply_filters("wpdm_js_vars", $wpdm_asset);


        wp_localize_script('frontjs', 'wpdm_url', array(
            'home' => esc_url_raw(home_url('/')),
            'site' => esc_url_raw(site_url('/')),
            'ajax' => esc_url_raw(admin_url('/admin-ajax.php'))
        ));

        wp_localize_script('frontjs', 'wpdm_asset', $wpdm_asset);

        wp_enqueue_script('frontjs');

        wp_enqueue_script('jquery-choosen', WPDM_BASE_URL . 'assets/js/chosen.jquery.min.js', array('jquery'));
        //wp_enqueue_style('choosen-css', plugins_url('/download-manager/assets/css/chosen.css'), 999999);


    }

    /**
     * @usage insert code in wp head
     */
    function wpHead(){
        if(is_singular('wpdmpro'))
            $ui_button = get_option('__wpdm_ui_download_button');
        else
            $ui_button = get_option('__wpdm_ui_download_button_sc');
        $class =  ".btn.".(isset($ui_button['color'])?$ui_button['color']:'btn-primary').".".(isset($ui_button['size'])?$ui_button['size']:'');
        ?>

        <script>
            var wpdm_site_url = '<?php echo site_url('/'); ?>';
            var wpdm_home_url = '<?php echo home_url('/'); ?>';
            var ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
            var wpdm_ajax_url = '<?php echo admin_url('admin-ajax.php'); ?>';
            var wpdm_ajax_popup = '<?php echo get_option('__wpdm_ajax_popup', 0); ?>';
        </script>
        <style>
            .wpdm-download-link<?php echo $class; ?>{
                border-radius: <?php echo (isset($ui_button['borderradius'])?$ui_button['borderradius']:4); ?>px;
            }
        </style>


        <?php
    }

    /**
     * @usage insert code in wp footer
     */
    function wpFooter(){
        if(get_option('__wpdm_modal_login', 0))
            echo $this->apply->modalLoginForm();
        if(is_single()&&get_post_type()=='wpdmpro'){
            ?>
            <script>
                jQuery(function($){
                    $.get('<?php echo 'index.php?_nonce='.wp_create_nonce('__wpdm_view_count').'&id='.get_the_ID(); ?>');
                });
            </script>
            <?php
        }
    }



    /**
     * @param $name
     * @usage Class autoloader
     */
    function autoLoad($name) {

        $originClass = $name;
        $name = str_replace("WPDM_","", $name);
        $name = str_replace("WPDM\\","", $name);
        //$relative_path = str_replace("\\", "/", $name);
        $parts = explode("\\", $name);
        $class_file = end($parts);
        $class_file = 'class.'.$class_file.'.php';
        $parts[count($parts)-1] = $class_file;
        $relative_path = implode("/", $parts);


        $classPath = WPDM_BASE_DIR.$relative_path;
        $x_classPath = WPDM_BASE_DIR.str_replace("class.", "libs/class.", $relative_path);

        if(strlen($class_file) < 40 && file_exists($classPath)){
            require_once $classPath;
        } else if(strlen($class_file) < 40 && file_exists($x_classPath)){
            require_once $x_classPath;
        }
    }

}

$WPDM = new \WPDM\WordPressDownloadManager();

