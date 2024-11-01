<?php
/**
 * The Admin specific file.
 *
 * @package    Welight
 * @subpackage Welight/Admin
 * @author     Welight <dev@welight.co>
 */

/**
 * Welight Admin.
 */
class Welight_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {
		// Admin templates path.
		welight()->define( 'WELIGHTEI_ADMIN_TEMPLATE_PATH', WELIGHTEI_TEMPLATES_PATH . 'admin/' );

		$this->admin_includes();
		$this->admin_hooks();
	}

	/**
	 * Include required files.
	 */
	private function admin_includes() {
		// Admin functions.
		include_once WELIGHTEI_INCLUDES_PATH . 'admin/we-admin-functions.php';
	}

	/**
	 * Action and Filters.
	 */
	private function admin_hooks() {
		add_filter( 'woocommerce_get_settings_pages', array( $this, 'welight_admin_settings_page' ), 20 );
	}

	/**
	 * Welight Admin Settings.
	 *
	 * @param array $settings Setting file.
	 *
	 * @return array
	 */
	public function welight_admin_settings_page( $settings ) {
		// Add new tab.
		$settings[] = include_once WELIGHTEI_INCLUDES_PATH . 'admin/class-we-admin-settings.php';

		return $settings;
	}

}

new Welight_Admin();
