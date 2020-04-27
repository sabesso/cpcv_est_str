<?php
namespace HTWPForm\Elementor;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
* Base Class
*/
class Base{

    const MINIMUM_ELEMENTOR_VERSION = '2.0.0';
    const MINIMUM_PHP_VERSION = '6.0';

    private static $_instance = null;
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    
    public function __construct(){
        add_action( 'init', [ $this, 'i18n' ] );
        add_action( 'plugins_loaded', [ $this, 'init' ] );
    }

    // Load Textdomain
    public function i18n() {
        load_plugin_textdomain( 'ht-wpform' );
    }

    public function init() {

        if ( ! function_exists('is_plugin_active')) { include_once( ABSPATH . 'wp-admin/includes/plugin.php' ); }

        // Check if Elementor installed and activated
        if ( ! did_action( 'elementor/loaded' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_base_elementor_status' ] );
            return;
        }

        // Check for required Elementor version
        if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
            return;
        }

        // Check for required PHP version
        if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
            add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
            return;
        }

        // Check for WPForms
        if( !is_plugin_active('wpforms-lite/wpforms.php') ){
            if( !is_plugin_active('wpforms/wpforms.php') ){
                add_action( 'admin_notices', [ $this, 'admin_notice_base_wpform_status' ] );
                return;
            }
        }

        // Widgets register
        add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );

    }

    // Check Plugins is Installed or not
    public function is_plugins_active( $pl_file_path = NULL ){
        $installed_plugins_list = get_plugins();
        return isset( $installed_plugins_list[$pl_file_path] );
    }

    // Enementor Plugins status checking
    public function admin_notice_base_elementor_status() {

        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

        $elementor = 'elementor/elementor.php';
        if( $this->is_plugins_active( $elementor ) ) {
            if( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $elementor . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $elementor );

            $message = '<p>' . __( 'HT WPForms Addons not working because you need to activate the Elementor plugin.', 'ht-wpform' ) . '</p>';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate Elementor Now', 'ht-wpform' ) ) . '</p>';
        } else {
            if ( ! current_user_can( 'install_plugins' ) ) {
                return;
            }
            $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=elementor' ), 'install-plugin_elementor' );
            $message = '<p>' . __( 'HT WPForms Addons not working because you need to install the Elementor plugin', 'ht-wpform' ) . '</p>';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install Elementor Now', 'ht-wpform' ) ) . '</p>';
        }
        echo '<div class="error"><p>' . $message . '</p></div>';

    }

    // Check WPForm install or not.
    function admin_notice_base_wpform_status(){
        $wpforms = 'wpforms-lite/wpforms.php';
        if( $this->is_plugins_active( $wpforms ) ) {
            if( ! current_user_can( 'activate_plugins' ) ) {
                return;
            }
            $activation_url = wp_nonce_url( 'plugins.php?action=activate&amp;plugin=' . $wpforms . '&amp;plugin_status=all&amp;paged=1&amp;s', 'activate-plugin_' . $wpforms );

            $message = '<p>' . __( 'HT WPForms Addons not working because you need to activate the WPForms plugin.', 'ht-wpform' ) . '</p>';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $activation_url, __( 'Activate WPForms Now', 'ht-wpform' ) ) . '</p>';
        } else {
            if ( ! current_user_can( 'install_plugins' ) ) {
                return;
            }
            $install_url = wp_nonce_url( self_admin_url( 'update.php?action=install-plugin&plugin=wpforms-lite' ), 'install-plugin_wpforms-lite' );
            $message = '<p>' . __( 'HT WPForms Addons not working because you need to install the Wpforms plugin', 'ht-wpform' ) . '</p>';
            $message .= '<p>' . sprintf( '<a href="%s" class="button-primary">%s</a>', $install_url, __( 'Install WPForms Now', 'ht-wpform' ) ) . '</p>';
        }
        echo '<div class="error"><p>' . $message . '</p></div>';
    }

    // Elementor Version compare
    public function admin_notice_minimum_elementor_version() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
        $message = sprintf(
            /* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ht-wpform' ),
            '<strong>' . esc_html__( 'HT WPForms Addons', 'ht-wpform' ) . '</strong>',
            '<strong>' . esc_html__( 'Elementor', 'ht-wpform' ) . '</strong>',
             self::MINIMUM_ELEMENTOR_VERSION
        );
        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }

    // Php version Compare
    public function admin_notice_minimum_php_version() {
        if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );
        $message = sprintf(
            /* translators: 1: Plugin name 2: PHP 3: Required PHP version */
            esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'ht-wpform' ),
            '<strong>' . esc_html__( 'HT WPForms Addons', 'ht-wpform' ) . '</strong>',
            '<strong>' . esc_html__( 'PHP', 'ht-wpform' ) . '</strong>',
             self::MINIMUM_PHP_VERSION
        );
        printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );
    }
    
    // Register widget
    public function init_widgets() {
        if( is_plugin_active('wpforms-lite/wpforms.php') || is_plugin_active('wpforms/wpforms.php') ){
            require( HTWPFORM_PL_PATH.'include/elementor_widgets.php' );
        }
    }



}
