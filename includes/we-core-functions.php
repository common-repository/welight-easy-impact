<?php
/**
 * Core Functions.
 *
 * @package Welight
 * @subpackage Welight/Functions
 */

// API FUNCTIONS.
require_once WELIGHTEI_INCLUDES_PATH . 'we-api-functions.php';

// TEMPLATE FUNCTIONS.
require_once WELIGHTEI_INCLUDES_PATH . 'we-template-functions.php';

// ABANDONED CART.
require_once WELIGHTEI_INCLUDES_PATH . 'we-abandoned-cart-functions.php';

/**
 * Get template.
 *
 * @param string $name Template name.
 * @param array  $args Arguments.
 */
function welightei_get_template( $name, $args = array() ) {
	if ( ! empty( $args ) && is_array( $args ) ) {
		extract( $args ); // @codingStandardsIgnoreLine.
	}

	$template_path = WELIGHTEI_TEMPLATES_PATH;

	if ( ! file_exists( $template_path . $name ) ) {
		return;
	}

	include $template_path . $name;
}

/**
 * Get template HTML.
 *
 * @param string $name Template name.
 * @param array  $args Arguments.
 *
 * @return string
 */
function welightei_get_template_html( $name, $args = array() ) {
	ob_start();
	welightei_get_template( $name, $args );
	return ob_get_clean();
}

/**
 * Is development mode.
 *
 * @return boolean
 */
function welightei_is_dev_mode() {
	// developement mode.
	$dev = false;

	// valid flags for development.
	$dev_flags = apply_filters( 'welightei_developement_mode_flags', array( 'dev', 'development' ) );

	// Get environment.
	$server_env = isset( $_SERVER['ENVIRONMENT'] ) ? $_SERVER['ENVIRONMENT'] : null; // WPCS: input var ok, CSRF ok, sanitization ok.
	$local_env  = isset( $_ENV['ENVIRONMENT'] ) ? $_ENV['ENVIRONMENT'] : null; // WPCS: input var ok, CSRF ok, sanitization ok.

	$env = $server_env || $local_env;

	if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
		$dev = true;
	}

	if ( false === $dev && ! empty( $env ) && in_array( $env, $dev_flags, true ) ) {
		$dev = true;
	}

	return $dev;
}

/**
 * Get store name.
 *
 * Get store name, by default return WordPress site title if is not configured in welight settings.
 *
 * @return string The store name.
 */
function welightei_get_store_name() {
	// Default store name.
	$store_name    = get_bloginfo( 'name' );
	$we_store_name = get_option( 'welight_store_name' );

	if ( ! empty( $we_store_name ) ) {
		$store_name = $we_store_name;
	}

	return $store_name;
}

/**
 * Store Donation.
 *
 * Return the configured donation tax.
 *
 * @param bool $html_price Return the HTML Price.
 *
 * @return string The donation amount.
 */
function welightei_get_store_donation( $html_price = true ) {
	// amount.
	$donation_amount = get_option( 'welight_donation_tax', null );

	// is percentage.
	$is_percentage = strpos( $donation_amount, '%' ) !== false;

	if(!$is_percentage){
		$p = explode(".", $donation_amount);
		if($p[1] !== "00"){
			$donation_amount = $p[0].".".$p[1]."%";
			return $donation_amount;
		}

		$donation_amount = $p[0]."%";
		return $donation_amount;
	}

	if ( ! $is_percentage ) {
		$donation_amount = wc_format_localized_price( $donation_amount );


		if ( $html_price ) {
			$donation_amount = wc_price( $donation_amount );
		}
	}

	return $donation_amount;
}

/**
 * Check is percentage
 *
 * Check if donation configured is percentage value.
 *
 * @return bool
 */
function welightei_store_donation_is_percentage() {
	// donation amount.
	$donation_amount = get_option( 'welight_donation_tax', null );


	// is percentage.
	$is_percentage = strpos( $donation_amount, '%' ) !== false;

	if(!$is_percentage){
		$pr = explode(".", $donation_amount);
		$p = $pr[0]."%";
		$is_percentage = true;
		return $is_percentage;
	}




	return $is_percentage;
}

/**
 * Get percentage value.
 *
 * Return the percentagem value if is donation tax configured for percentage.
 *
 * @return float
 */
function welightei_get_store_donation_percentage() {
	if ( ! welightei_store_donation_is_percentage() ) {
		return null;
	}

	return wc_format_decimal( preg_replace( '/[^0-9.]+/si', '%', welightei_get_store_donation() ) );
}

/**
 * Convert percent string.
 *
 * @param string $percent The percent string with symbol.
 *
 * @return float
 */
function welightei_format_percent_string( $percent = '' ) {
	return wc_format_decimal( preg_replace( '/[^0-9.]+/si', '', $percent ) );
}

/**
 * Get asset file url.
 *
 * @param string $file The file name with complete destination.
 *
 * @return mixed
 */
function welightei_get_asset_url( $file ) {
	// Filename.
	$filename = basename( $file );

	// extension.
	$extension = explode( '.', $filename );
	$extension = end( $extension );

	// add suffix minified.
	if ( ! welightei_is_dev_mode() ) {
		// suffix.
		$suffix = sprintf( '.min.%s', $extension );

		// assets path.
		$assets_path = sprintf( '%s/assets/', untrailingslashit( plugin_dir_path( WELIGHTEI_PLUGIN_FILE ) ) );

		// replace.
		if ( ! preg_match( '/\.min\.(css|js)/si', $filename ) ) {
			// file minified.
			$file_minified = preg_replace( '/\.(css|js)/si', $suffix, $file );

			// only exists file.
			if ( file_exists( $assets_path . $file_minified ) ) {
				$file = $file_minified;
			}
		}
	}

	return apply_filters( 'welight_get_asset_url', plugins_url( sprintf( 'assets/%s', $file ), WELIGHTEI_PLUGIN_FILE ), $file );
}

/**
 * Is Sandbox?
 *
 * Check if system checked to sandbox mode.
 *
 * @return bool
 */
function welightei_is_sandbox_mode() {
	// sandbox.
	$sandbox = get_option( 'welight_sandbox', 'no' );

	// true if sandbox active, or false.
	return 'yes' === $sandbox;
}

/**
 * Add Dynamic Constants.
 *
 * @param Welight $class The Welight class.
 *
 * @return void.
 */
function welightei_add_constants_dynamic( $class = null ) {
	global $welight;

	if ( welightei_is_sandbox_mode() ) {
		$class->define( 'WELIGHTEI_BASE_API_URL', WELIGHTEI_DEV_BASE_API_URL );
		$class->define( 'WELIGHTEI_BASE_URL', WELIGHTEI_DEV_BASE_URL );
		$class->define( 'WELIGHTEI_SITE_URL', WELIGHTEI_DEV_SITE_URL );
	} else {
		$class->define( 'WELIGHTEI_BASE_API_URL', WELIGHTEI_PROD_BASE_API_URL );
		$class->define( 'WELIGHTEI_BASE_URL', WELIGHTEI_PROD_BASE_URL );
		$class->define( 'WELIGHTEI_SITE_URL', WELIGHTEI_PROD_SITE_URL );
	}
}

/**
 * Current AB Test Value.
 *
 * Get current AB Test value from token account.
 *
 * @param string $default The default value for test.
 *
 * @return object
 */
function welightei_get_current_value_ab_test( $default = 'a' ) {
	// auth.
	$auth = welightei_get_api_auth();

	// default value.
	if ( ! $auth ) {
		return (object) array( 'current' => $default );
	}

	// args.
	$args = array(
		'apikey'   => $auth->apikey,
		'username' => $auth->username,
	);

	// request for endpoint.
	$request = welightei_http_get( welightei_append_args_endpoint( 'doador-empresa/venda/current-teste-ab/', $args ) );

	if ( ! $request ) {
		return (object) array( 'current' => $default );
	}

	return (object) array(
		'current' => $request->current_teste_ab,
	);


}

/**
 * Invite of Empresa.
 *
 * Get invite link of empresa.
 *
 * @param string $default The default value for link.
 *
 * @return string
 */
function welightei_get_invite_link( $default = 'javascript:void(0)' ) {
	// auth.
	$auth = welightei_get_api_auth();

	// default value.
	if ( ! $auth ) {
		return $default;
	}

	// args.
	$args = array(
		'apikey'   => $auth->apikey,
		'username' => $auth->username,
	);

	// request for endpoint.
	$request = welightei_http_get( welightei_append_args_endpoint( 'doador-empresa/venda/current-teste-ab/', $args ) );

	if ( ! $request ) {
		return $default;
	}

	return $request->empresa_invite;
}

/**
 * Debug function.
 *
 * Print debug "print_r" on screen.
 *
 * @param mixed $var The content to print.
 *
 * @return string|void.
 */
function welightei_print_r( $var = null ) {
	echo '<pre>';
	print_r($var);
	echo '</pre>';
}
