<?php
/*
 * Plugin Name: SendToSMS Sharing Button for Jetpack
 * Plugin URI: http://wordpress.org/plugins/sendtosms-jetpack-button/
 * Description: Add SendToSMS button to Jetpack Sharing
 * Version: 1.0.0
 * Author: Scott Vandezande
 * Author URI: http://www.sendtosms.com
 * License: GPLv3 or later
 * Text Domain: jetpack-sendtosms
 * Domain Path: /languages/
*/

if( !function_exists('add_action') ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

if( version_compare( get_bloginfo('version'), '3.8', '<' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
	deactivate_plugins( __FILE__ );
}

define( 'jetsms__PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'jetsms__PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'jetsms__PLUGIN_FILE', __FILE__ );
define( 'jetsms__VERSION',     '1.0' );

add_action( 'init', array( 'Jetpack_SendToSMS_Pack', 'init' ) );

class Jetpack_SendToSMS_Pack {
	static $instance;

	private $data;

	static function init() {
		if( !self::$instance ) {
			if( did_action('plugins_loaded') ) {
				self::plugin_textdomain();
			} else {
				add_action( 'plugins_loaded', array( __CLASS__, 'plugin_textdomain' ) );
			}

			self::$instance = new Jetpack_SendToSMS_Pack;
		}
		return self::$instance;
	}

	private function __construct() {
		add_action( 'wp_enqueue_scripts',    array( &$this, 'register_assets' ) );
		add_action( 'admin_enqueue_scripts', array( &$this, 'admin_menu_assets' ) );

		if( did_action('plugins_loaded') ) {
			$this->require_services();
		} else {
			add_action( 'plugins_loaded', array( &$this, 'require_services' ) );
		}
		add_filter( 'plugin_row_meta', array( &$this, 'plugin_row_meta' ), 10, 2 );
	}

	function register_assets() {
		if( get_option('sharedaddy_disable_resources') ) {
			return;
		}

		if( !Jetpack::is_module_active('sharedaddy') ) {
			return;
		}
		wp_enqueue_style( 'jetpack-sendtosms', jetsms__PLUGIN_URL . 'assets/css/style.css', array(), jetsms__VERSION );
	}

	function admin_menu_assets( $hook ) {
		if( $hook == 'settings_page_sharing' ) {
			wp_enqueue_style( 'jetpack-sendtosms', jetsms__PLUGIN_URL . 'assets/css/style.css', array('sharing', 'sharing-admin'), jetsms__VERSION );
		}
	}

	function require_services() {
		if( class_exists('Jetpack') ) {
			require_once( jetsms__PLUGIN_DIR . 'includes/class.sendtosms-service.php' );
		}
	}

	static function plugin_textdomain() {
		$locale = get_locale();

		load_plugin_textdomain( 'jetpack-sendtosms', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	function plugin_row_meta( $links, $file ) {
		if( plugin_basename( jetsms__PLUGIN_FILE ) === $file ) {
			$links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url('http://www.sendtosms.com/'),
				__( 'Website', 'jetpack-sendtosms' )
			);
		}
		return $links;
	}
}
