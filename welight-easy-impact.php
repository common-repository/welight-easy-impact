<?php
/**
 * Bootstraping file.
 *
 * Plugin Name:       Welight - Easy Impact
 * Plugin URI:        https://welight.co/
 * Description:       Uma solução que pode ser integrada ao seu e-commerces, tornando o consumo dos seus produtos ou serviços um ato mais consciente.
 * Version:           0.5.0
 * Author:            Welight
 * Author URI:        https://welight.co/saiba-tudo
 * Text Domain:       welight
 * Domain Path:       /languages
 *
 *
 * WC requires at least: 3.1
 * WC tested up to: 3.5.2
 *
 * @package  Welight
 * @version  0.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Plugin functions!
if ( ! function_exists( 'is_plugin_active' ) ) {
	include_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Check Woocommerce is active!
if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
	return;
}

// WE CONSTANTS!
if ( ! defined( 'WELIGHTEI_PLUGIN_FILE' ) ) {
	define( 'WELIGHTEI_PLUGIN_FILE', __FILE__ );
}

// MAIN CLASS!
if ( ! class_exists( 'Welight' )
	&& file_exists( dirname( __FILE__ ) . '/includes/class-welight.php' ) ) {
	include_once dirname( __FILE__ ) . '/includes/class-welight.php';
}

/**
 * Main instance of Welight.
 *
 * @return Welight
 */
function welight() {
	return Welight::instance();
}

$GLOBALS['welight'] = welight();
