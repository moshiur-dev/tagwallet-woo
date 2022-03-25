<?php
/**
 * Plugin Name:       Woo Tagwallet Gateway
 * Plugin URI:        https://saifulananda.me/
 * Description:       WooCommerce Tagwallet Payment Gateway
 * Version:           1.0.0
 * Author:            Saiful Islam Ananda.
 * Author URI:        https://saifulananda.me/
 * License:           GNU General Public License v2 or later
 * Text Domain:       wpappsdev-tagwallet-gateway
 * Domain Path:       /languages.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

require_once __DIR__ . '/vendor/autoload.php';
/**
 * The main plugin class.
 */
final class WPAppsDev_Payinvite {
	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	private $version = '1.0.0';

	/**
	 * Class constructor.
	 */
	private function __construct() {
		$this->define_constants();

		add_action( 'plugins_loaded', [ $this, 'init_plugin' ] );

		register_activation_hook( __FILE__, [ $this, 'activate' ] );
		register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );
	}

	/**
	 * Initializes a singleton instance.
	 *
	 * @return \WPAppsDev_Payinvite
	 */
	public static function init() {
		static $instance = false;

		if ( ! $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	/**
	 * Define the required plugin constants.
	 *
	 * @return void
	 */
	public function define_constants() {
		define( 'WPADTWPG', __FILE__ );
		define( 'WPADTWPG_NAME', 'wpappsdev-tagwallet-gateway' );
		define( 'WPADTWPG_VERSION', $this->version );
		define( 'WPADTWPG_DIR', trailingslashit( plugin_dir_path( WPADTWPG ) ) );
		define( 'WPADTWPG_URL', trailingslashit( plugin_dir_url( WPADTWPG ) ) );
		define( 'WPADTWPG_ASSETS', trailingslashit( WPADTWPG_URL . 'assets' ) );
	}

	/**
	 * Initialize the plugin.
	 *
	 * @return void
	 */
	public function init_plugin() {
		$this->includes();
		$this->init_hooks();
		$this->init_classes();

		do_action( 'wpadtwpg_loaded' );
	}

	/**
	 * Include all the required files.
	 *
	 * @return void
	 */
	public function includes() {
		require_once WPADTWPG_DIR . 'includes/gateways/wc-gateway-tagwallet.php';
	}

	/**
	 * Initialize the action and filter hooks.
	 *
	 * @return void
	 */
	public function init_hooks() {
		// // Localize our plugin
		add_action( 'init', [ $this, 'localization_setup' ] );
		add_filter( 'woocommerce_payment_gateways', [ $this, 'add_custom_gateway' ], 5 );
	}

	/**
	 * Initialize plugin for localization.
	 *
	 * @uses load_plugin_textdomain()
	 */
	public function localization_setup() {
		load_plugin_textdomain( 'wpappsdev-tagwallet-gateway', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * Init all the classes.
	 *
	 * @return void
	 */
	public function init_classes() {
		new WPAppsDev\WCTWPG\Assets();

		if ( is_admin() ) {
			new WPAppsDev\WCTWPG\Admin();
		} else {
			new WPAppsDev\WCTWPG\Frontend();
		}

		if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
			new WPAppsDev\WCTWPG\Ajax();
		}
	}

	/**
	 * Do stuff upon plugin activation.
	 *
	 * @return void
	 */
	public function activate() {
		$installed = get_option( 'wpadtwpg_installed' );

		if ( ! $installed ) {
			update_option( 'wpadtwpg_installed', time() );
		}

		update_option( 'wpadtwpg_version', WPADTWPG_VERSION );
	}

	/**
	 * Do stuff upon plugin deactivation.
	 *
	 * @return void
	 */
	public function deactivate() {
	}

	/**
	 * Add custom gateway to WooCommerce.
	 *
	 * @param array $methods
	 *
	 * @return array
	 */
	public function add_custom_gateway($methods) {
		$methods[] = 'WC_Gateway_Payinvite';

		return $methods;
	}
}

/**
 * Initializes the main plugin.
 *
 * @return \WPAppsDev_Payinvite
 */
function wpadtwpg_process() {
	return WPAppsDev_Payinvite::init();
}

// kick-off the plugin
wpadtwpg_process();
