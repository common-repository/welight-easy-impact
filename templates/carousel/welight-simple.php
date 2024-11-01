<?php
/**
 * Welight Simple Carousel.
 *
 * @package Welight/Templates/Carousel
 */

/**
 * Get list of ongs
 */
foreach ( $ongs as $ong ) :
	$logo_image       = welightei_get_base_url( $ong->ong->profile_detail->img_avatar );
	$background_image = welightei_get_base_url( $ong->ong->profile_detail->img_fundo );

	// tooltip.
	$ong_name     = esc_attr( sprintf( '<h4 class="tippy-ong-title">%s</h4>', $ong->ong->nome ) );
	$ong_desc     = esc_attr( $ong->ong->profile_detail->missao_resumo );
	$tooltip_html = esc_attr( sprintf( '<div class="tippy-ong">%1$s %2$s</div>', $ong_name, $ong_desc ) );
	?>
	<div class="ong style-simple-ong tippify" data-tippy-arrow="true" data-tippy-placement="bottom" title="<?php echo esc_attr( $tooltip_html ); ?>">
		<div class="ong-logo" style="background-image: url(<?php echo esc_attr( $logo_image ); ?>);"></div>

		<div class="checkbox">
			<div class="welight-checkbox">
				<input type="checkbox" id="<?php printf( 'welight-ong-checkbox-%d', esc_attr( $ong->ong->id ) ); ?>" name="welight_ong[]" value="<?php echo esc_attr( $ong->ong->id ); ?>" class="welight">
				<label for="<?php printf( 'welight-ong-checkbox-%d', esc_attr( $ong->ong->id ) ); ?>" class="checkbox"></label>
			</div>
		</div>
	</div>
<?php endforeach; ?>
