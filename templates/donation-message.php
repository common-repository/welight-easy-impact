<?php
/**
 * Donation Messages.
 *
 * Show custom donation messages.
 *
 * @package Welight/Templates
 */

?>
<div class="checkout-donation-amount">

	<?php
	// donation texts.
	if ( is_cart() ) {
		echo welightei_get_display_text_context( 'cart' ); // @codingStandardsIgnoreLine.
	} elseif ( is_checkout() ) {
		echo welightei_get_display_text_context( 'checkout' ); // @codingStandardsIgnoreLine.
	}
	?>

</div>
