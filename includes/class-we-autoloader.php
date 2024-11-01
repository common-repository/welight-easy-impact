<?php
/**
 * Welight Autoloader.
 *
 * @package Welight/Classes
 */

/**
 * Autoloader class.
 */
class WELIGHTEI_Autoloader {

	/**
	 * WELIGHTEI_Autoloader constructor.
	 */
	public function __construct() {
		if ( function_exists( '__autoload' ) ) {
			spl_autoload_register( '__autoload' );
		}

		spl_autoload_register( array( $this, 'autoload' ) );
	}

	/**
	 * Autoload.
	 *
	 * @param string $class Class name.
	 */
	public function autoload( $class ) {
		$class = strtolower( $class );

		if ( 0 !== strpos( $class, 'we_' ) ) {
			return;
		}

		$filename = $this->get_filename( $class );
		$path     = '';

		if ( 0 === strpos( $class, 'welightei_admin_' ) ) {
			$path = WELIGHTEI_INCLUDES_PATH . 'admin/';
		}

		if ( empty( $path ) || ! $this->load_file( $path . $filename ) ) {
			$this->load_file( WELIGHTEI_INCLUDES_PATH . $filename );
		}
	}

	/**
	 * Get filename.
	 *
	 * Get file name from class name.
	 *
	 * @param string $class Class name.
	 *
	 * @return string
	 */
	private function get_filename( $class ) {
		return sprintf( 'class-%s.php', str_replace( '_', '-', $class ) );
	}

	/**
	 * Include a class file.
	 *
	 * @param  string $path File path.
	 * @return bool Successful or not.
	 */
	private function load_file( $path ) {
		if ( $path && is_readable( $path ) ) {
			include_once $path;
			return true;
		}
		return false;
	}
}

new WELIGHTEI_Autoloader();
