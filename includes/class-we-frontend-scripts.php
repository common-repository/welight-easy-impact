<?php
/**
 * Frontend Scripts.
 *
 * @package Welight
 * @subpackage Welight/Classes
 */

/**
 * Class WELIGHTEI_Frontend_Scripts
 */
class WELIGHTEI_Frontend_Scripts {

	/**
	 * Scripts.
	 *
	 * @var array
	 */
	private $scripts = array();

	/**
	 * Styles.
	 *
	 * @var array
	 */
	private $styles = array();

	/**
	 * WELIGHTEI_Frontend_Scripts constructor.
	 */
	public function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
	}

	/**
	 * Get frontend styles.
	 *
	 * @return array
	 */
	private function get_styles() {
		return apply_filters(
			'welight_enqueue_frontend_styles', array(
				'welight-general' => array(
					'src'     => $this->get_asset_url( 'css/welight.css' ),
					'deps'    => array(),
					'version' => WELIGHTEI_PLUGIN_VERSION,
					'media'   => 'all',
					'rtl'     => false,
				),
			)
		);
	}

	/**
	 * Get frontend scripts.
	 *
	 * @return array
	 */
	private function get_scripts() {
		return apply_filters(
			'welight_enqueue_frontend_scripts', array(
				'we-owl-carousel' => array(
					'src'     => $this->get_asset_url( 'js/lib/owl.carousel.min.js' ),
					'deps'    => array( 'jquery' ),
					'version' => null,
				),
				'we-tooltip'      => array(
					'src'     => $this->get_asset_url( 'js/lib/tippy.all.min.js' ),
					'deps'    => array(),
					'version' => null,
				),
				'welight-general' => array(
					'src'     => $this->get_asset_url( 'js/welight.js' ),
					'deps'    => array( 'jquery', 'we-owl-carousel', 'we-tooltip' ),
					'version' => WELIGHTEI_PLUGIN_VERSION,
				),
			)
		);
	}

	/**
	 * Get asset URL.
	 *
	 * @param string $file Filename.
	 *
	 * @return string
	 */
	public function get_asset_url( $file ) {
		return welightei_get_asset_url( $file );
	}

	/**
	 * Register script.
	 *
	 * @param string   $handle Handle name.
	 * @param string   $path Path to file.
	 * @param string[] $deps Dependecies.
	 * @param string   $version Version.
	 * @param bool     $in_footer Show in footer.
	 */
	private function register_script( $handle, $path = '', $deps = array( 'jquery' ), $version = WELIGHTEI_PLUGIN_VERSION, $in_footer = true ) {
		$this->scripts = $handle;
		wp_register_script( $handle, $path, $deps, $version, $in_footer );
	}

	/**
	 * Enqueue script.
	 *
	 * @param string   $handle Handle name.
	 * @param string   $path Path to file.
	 * @param string[] $deps Dependencies.
	 * @param string   $version Version.
	 * @param bool     $in_footer Show in footer.
	 */
	private function enqueue_script( $handle, $path = '', $deps = array( 'jquery' ), $version = WELIGHTEI_PLUGIN_VERSION, $in_footer = true ) {
		if ( ! in_array( $handle, $this->scripts, true ) && $path ) {
			$this->register_script( $handle, $path, $deps, $version, $in_footer );
		}
		wp_enqueue_script( $handle );
	}

	/**
	 * Register style on the class and WordPress.
	 *
	 * @param string   $handle Handle name.
	 * @param string   $path Path to file.
	 * @param string[] $deps Dependencies.
	 * @param string   $version Version.
	 * @param string   $media CSS media.
	 * @param bool     $rtl Right-to-Left support.
	 */
	private function register_style( $handle, $path = '', $deps = array(), $version = WELIGHTEI_PLUGIN_VERSION, $media = 'all', $rtl = false ) {
		$this->styles[] = $handle;
		wp_register_style( $handle, $path, $deps, $version, $media );

		if ( $rtl ) {
			wp_style_add_data( $handle, 'rtl', 'replace' );
		}
	}

	/**
	 * Enqueue style.
	 *
	 * @param string   $handle Handle name.
	 * @param string   $path Path to file.
	 * @param string[] $deps Dependencies.
	 * @param string   $version Version.
	 * @param string   $media CSS media.
	 * @param bool     $rtl Right-to-Left support.
	 */
	private function enqueue_style( $handle, $path = '', $deps = array(), $version = WELIGHTEI_PLUGIN_VERSION, $media = 'all', $rtl = false ) {
		if ( ! in_array( $handle, $this->styles, true ) && $path ) {
			$this->register_style( $handle, $path, $deps, $version, $media, $rtl );
		}
		wp_enqueue_style( $handle );
	}

	/**
	 * Register styles.
	 */
	private function register_styles() {
		foreach ( $this->get_styles() as $handle => $props ) {
			$this->register_style( $handle, $props['src'], $props['deps'], $props['version'], $props['media'], $props['rtl'] );
		}
	}

	/**
	 * Register scripts.
	 */
	private function register_scripts() {
		foreach ( $this->get_scripts() as $handle => $props ) {
			// in footer.
			$props['in_footer'] = isset( $props['in_footer'] ) ? $props['in_footer'] : true;

			$this->register_script( $handle, $props['src'], $props['deps'], $props['version'], $props['in_footer'] );
		}
	}

	/**
	 * Enqueue Scripts.
	 */
	public function load_scripts() {

		$this->register_scripts();
		$this->register_styles();

		// on checkout page, on cart page.
		if ( is_checkout() || is_cart() || is_order_received_page() ) {
			wp_enqueue_style( 'welight-general' );
			wp_enqueue_script( 'welight-general' );
		}

	}

}

new WELIGHTEI_Frontend_Scripts();
