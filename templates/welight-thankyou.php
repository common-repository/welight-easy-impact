<?php
/**
 * Welight Thankyout.
 *
 * Show thanks message on order received.
 *
 * @package Welight/Templates
 */

?>

<div class="welight-thankyou-container">

	<h3 class="thankyou-title">
	<?php
	echo sprintf(
		// @codingStandardsIgnoreStart
		/* translators: %s: Donation value. */
		_n(
			'A sua compra foi consciente e você doou %s para a causa que escolheu.',
			'A sua compra foi consciente e você doou %s para as causas que escolheu.',
			$qtd_ongs,
			'welight'
		), $donation
		// @codingStandardsIgnoreEnd
	);
	?>
	</h3>

	<p class="thankyou-text"><?php echo esc_html( __( 'Cadastre-se na Welight e acompanhe os resultados da sua doação!', 'welight' ) ); ?></p>

	<p class="welight-logo">
		<a href="<?php echo sprintf( '%s/invite/company/%s', untrailingslashit( WELIGHTEI_SITE_URL ), 'italo-izaac' ); // @codingStandardsIgnoreLine. ?>" target="_blank" class="btn-welight">
			Acompanhar
		</a>

		<a href="<?php echo esc_attr( welightei_get_invite_link() ); ?>" target="_blank">
			<img src="<?php echo esc_attr( welightei_get_asset_url( 'images/logo.svg' ) ); ?>" alt="Welight" width="150">
		</a>
	</p>

</div>
