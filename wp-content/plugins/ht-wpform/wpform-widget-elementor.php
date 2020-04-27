<?php
/**
 * Plugin Name: WPForms Widget For Elementor Page Builder
 * Description: The Wpform Widget Elementor is a elementor addons for WordPress.
 * Plugin URI:  https://hasthemes.com/
 * Author:      HasTheme
 * Author URI:  https://profiles.wordpress.org/htplugins/#content-plugins
 * Version:     1.0.4
 * License:     GPL2
 * License URI:  https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: ht-wpform
 * Domain Path: /languages
*/

if( ! defined( 'ABSPATH' ) ) exit(); // Exit if accessed directly

define( 'HTWPFORM_VERSION', '1.0.4' );
define( 'HTWPFORM_ROOT', __FILE__ );
define( 'HTWPFORM_PL_URL', plugins_url( '/', HTWPFORM_ROOT ) );
define( 'HTWPFORM_PL_PATH', plugin_dir_path( HTWPFORM_ROOT ) );
define( 'HTWPFORM_PL_BASE', plugin_basename( HTWPFORM_ROOT ) );
define( 'HTWPFORM_PL_NAME', 'WPForms Widget For Elementor Page Builder' );

require ( HTWPFORM_PL_PATH . 'include/base.php' );
\HTWPForm\Elementor\Base::instance();