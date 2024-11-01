<?php
/**
 * Welight Setup
 *
 * @package Welight
 * @since   0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Class.
 *
 * @class Welight
 */
final class Welight {

	/**
	 * Welight version.
	 *
	 * @var string
	 */
	public $version = '0.5.0';

	/**
	 * Instance.
	 *
	 * @var Welight
	 */
	protected static $_instance = null;

	/**
	 * Ong class.
	 *
	 * @var WELIGHTEI_ONG
	 */
	public $ong;

	/**
	 * Welight Instance
	 *
	 * @static
	 * @return Welight - Main instance.
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Constructor
	 */
	public function __construct() {
		$this->init_constants();
		$this->init_includes();
		$this->init_hooks();
	}

	/**
	 * Define constants.
	 */
	private function init_constants() {
		$this->define( 'WELIGHTEI_ABSPATH', dirname( WELIGHTEI_PLUGIN_FILE ) . '/' );
		$this->define( 'WELIGHTEI_INCLUDES_PATH', WELIGHTEI_ABSPATH . 'includes/' );
		$this->define( 'WELIGHTEI_TEMPLATES_PATH', WELIGHTEI_ABSPATH . 'templates/' );
		$this->define( 'WELIGHTEI_PLUGIN_VERSION', $this->version );
		$this->define( 'WELIGHTEI_DEV_BASE_API_URL', 'https://apidev.welight.co/api/v2/' );
		$this->define( 'WELIGHTEI_DEV_BASE_URL', 'https://apidev.welight.co/' );
		$this->define( 'WELIGHTEI_DEV_SITE_URL', 'https://dev.welight.co/' );
		$this->define( 'WELIGHTEI_PROD_BASE_API_URL', 'https://api.welight.co/api/v2/' );
		$this->define( 'WELIGHTEI_PROD_BASE_URL', 'https://api.welight.co/' );
		$this->define( 'WELIGHTEI_PROD_SITE_URL', 'https://welight.co/' );

		// URL RELATIVE TO ENV.
		if ( $this->welightei_is_sandbox_mode() ) {
			$this->define( 'WELIGHTEI_BASE_API_URL', WELIGHTEI_DEV_BASE_API_URL );
			$this->define( 'WELIGHTEI_BASE_URL', WELIGHTEI_DEV_BASE_URL );
			$this->define( 'WELIGHTEI_SITE_URL', WELIGHTEI_DEV_SITE_URL );
		} else {
			$this->define( 'WELIGHTEI_BASE_API_URL', WELIGHTEI_PROD_BASE_API_URL );
			$this->define( 'WELIGHTEI_BASE_URL', WELIGHTEI_PROD_BASE_URL );
			$this->define( 'WELIGHTEI_SITE_URL', WELIGHTEI_PROD_SITE_URL );
		}
	}

	/**
	 * Include required files.
	 */
	private function init_includes() {
		include_once WELIGHTEI_INCLUDES_PATH . 'class-we-autoloader.php';

		// Core functions.
		include_once WELIGHTEI_INCLUDES_PATH . 'we-core-functions.php';

		// Admin Include.
		if ( is_admin() ) {
			include_once WELIGHTEI_INCLUDES_PATH . 'admin/class-welight-admin.php';
		}

		// Front-end Scripts.
		if ( ! is_admin() ) {
			include_once WELIGHTEI_INCLUDES_PATH . 'class-we-frontend-scripts.php';
		}

		// Checkout.
		include_once WELIGHTEI_INCLUDES_PATH . 'class-we-checkout.php';

		// ONG Carrousel.
		include_once WELIGHTEI_INCLUDES_PATH . 'class-we-ong-carousel.php';

		// Classes.
		$this->ong = include_once WELIGHTEI_INCLUDES_PATH . 'class-we-ong.php';

		// Abandoned Cart
		// include_once WELIGHTEI_INCLUDES_PATH . 'class-we-abondoned-cart.php';
	}

	/**
	 * Action and Filters.
	 */
	private function init_hooks() {
		do_action( 'welight_before_init', $this );
		add_action( 'init', array( $this, 'init' ), 0 );
		add_action( 'woocommerce_init', array( $this, 'woo_includes' ) );
	}

	/**
	 * After WooCommerce initialize.
	 *
	 * @return void
	 */
	public function woo_includes() {
		// include abandoned cart.
		include_once WELIGHTEI_INCLUDES_PATH . 'class-we-abondoned-cart.php';
	}

	/**
	 * Define constant.
	 *
	 * Define only constant if not exists.
	 *
	 * @param string $name Name of constant.
	 * @param string $value Value of constant.
	 */
	public function define( $name, $value = '' ) {
		if ( ! defined( $name ) ) {
			define( $name, $value );
		}
	}

	/**
	 * Init hook.
	 */
	public function init() {
		// For internationalize plugin.
		$this->load_plugin_textdomain();
	}

	/**
	 * Load localisation file.
	 */
	private function load_plugin_textdomain() {
		$language_path = plugin_basename( dirname( WELIGHTEI_PLUGIN_FILE ) ) . '/languages';
		load_plugin_textdomain( 'welight', false, $language_path );
	}

	/**
	 * Is Sandbox?
	 *
	 * Check if system checked to sandbox mode.
	 *
	 * @return bool
	 */
	private function welightei_is_sandbox_mode() {
		// sandbox.
		$sandbox = get_option( 'welight_sandbox', 'no' );

		// true if sandbox active, or false.
		return 'yes' === $sandbox;
	}

}
