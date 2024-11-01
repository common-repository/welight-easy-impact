<?php
/**
 * Ong Carousel class.
 *
 * @package Welight
 * @subpackage Welight/Classes
 */

/**
 * Class WELIGHTEI_ONG_Carousel
 */
class WELIGHTEI_ONG_Carousel {

	/**
	 * WELIGHTEI_ONG_Carousel constructor.
	 */
	public function __construct() {
		// add_action( 'woocommerce_review_order_before_payment', array( $this, 'output_ong_carousel' ) ); @codingStandardsIgnoreLine.
	}

	/**
	 * Render HTML Carousel.
	 *
	 * Render carousel for ongs in checkout page.
	 */
	public function output_ong_carousel() {
		// Template.
		welightei_get_template( 'ong-carousel.php' );
	}

}

new WELIGHTEI_ONG_Carousel();
