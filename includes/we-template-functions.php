<?php
/**
 * Template Functions.
 *
 * @package Welight
 * @subpackage Welight/Functions/Template
 */

// Add donation message hook.
add_action( 'welight_before_ong_carousel', 'welightei_donation_message_html' );

/**
 * Render Donation Message.
 *
 * @return mixed HTML of Donation message.
 */
function welightei_donation_message_html() {
	// Store name.
	$store_name = welightei_get_store_name();

	// Donation amount.
	$store_donation = welightei_get_store_donation();

	// for percentage value.
	$percentage_value = null;

	if ( welightei_store_donation_is_percentage() ) {
		$cart_totals = WC()->cart->get_subtotal();
		$percentage  = welightei_get_store_donation_percentage();

		$calculate_percentage = ( $percentage * $cart_totals ) / 100;
		$percentage_value     = $calculate_percentage;
	}

	$template_args = array(
		'store_name'     => $store_name,
		'store_donation' => $store_donation,
		'percentage'     => $percentage_value,
	);

	welightei_get_template( 'donation-message.php', $template_args );
}

/**
 * Render Donation Message on Cart Page.
 *
 * @return void HTML of Donation message.
 */
function welightei_donation_message_html_cart() {
	// buffer.
	ob_start();
	welightei_donation_message_html();
	$donation_message = ob_get_clean();

	// html.
	printf( '<tr class="welight-donation-message"><td colspan="2">%s</td></tr>', $donation_message ); // @codingStandardsIgnoreLine.
}

/**
 * Get display layout ong.
 *
 * Show display layout of ong in checkout.
 *
 * @param array $ongs The ong list.
 *
 * @return string The layout HTML.
 */
function welightei_get_display_ong_layout( $ongs = array() ) {
	// layout.
	$layout = get_option( 'welight_style_display_ong', 'welight' );

	// template args.
	$ongs = array( 'ongs' => $ongs );

	// default template.
	$html_default = welightei_get_template_html( 'carousel/welight-default.php', $ongs );

	switch ( $layout ) {
		case 'welight_simple':
			return welightei_get_template_html( 'carousel/welight-simple.php', $ongs );
		break; // @codingStandardsIgnoreLine.

		default:
			return $html_default;
	}
}

/**
 * Display text of context.
 *
 * Display configured text for diferents contexts.
 *
 * @param string $context The context to display text.
 *
 * @return string
 */
function welightei_get_display_text_context( $context = 'cart' ) {
	switch ( $context ) {
		case 'checkout':
			// text.
			$text = get_option(
				'welight_text_checkout',
				__( '<p>A <strong>{loja}</strong> vai doar <strong>{doacao}</strong> desta venda sem você gastar nada a mais.<br />Escolha as causas que quer apoiar abaixo:</p> <p>Total em doação: <strong>{doacao_total}</strong></p>', 'welight' )
			);

			// replace vars.
			$replace_variables = welightei_replace_welight_text_variables( $text );

			return $replace_variables;
		break; // @codingStandardsIgnoreLine.

		default:
			// text.
			$text = get_option(
				'welight_text_cart',
				__( '<p>A <strong>{loja}</strong> vai doar <strong>{doacao}</strong> desta venda sem você gastar nada a mais.<br />Você escolhe as causas que quer apoiar na próxima página.</p> <p>Total em doação: <strong>{doacao_total}</strong></p>', 'welight' )
			);

			// replace vars.
			$replace_variables = welightei_replace_welight_text_variables( $text );

			return $replace_variables;
		break; // @codingStandardsIgnoreLine.
	}
}

/**
 * Replace welight text variables
 *
 * @param string $text The text to replace vars.
 *
 * @return string
 */
function welightei_replace_welight_text_variables( $text ) {
	// get system vars.
	$store_name = welightei_get_store_name();
	$donation   = welightei_get_store_donation();

	// for percentage value.
	$donation_total = $donation;

	if ( welightei_store_donation_is_percentage() ) {
		$cart_totals = WC()->cart->get_subtotal();
		$percentage  = welightei_get_store_donation_percentage();

		$calculate_percentage = ( $percentage * $cart_totals ) / 100;
		$donation_total       = wc_price( $calculate_percentage );
	}

	// replace indexs.
	$replace_index = [
		'{loja}'         => $store_name,
		'{doacao}'       => $donation,
		'{doacao_total}' => $donation_total,
	];

	// replace.
	$text = str_replace( array_keys( $replace_index ), array_values( $replace_index ), $text );

	return $text;
}
