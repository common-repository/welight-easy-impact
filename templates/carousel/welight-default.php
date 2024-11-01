<?php
/**
 * Welight Default carousel.
 *
 * @package Welight/Templates/Carousel
 */

/**
 * Get list of ongs.
 */
foreach ( $ongs as $ong ) :
	$logo_image       = welightei_get_base_url( $ong->ong->profile_detail->img_avatar );
	$background_image = welightei_get_base_url( $ong->ong->profile_detail->img_fundo );
	?>
	<div class="ong" style="background-image: url(<?php echo esc_attr( $background_image ); ?>);">
		<div class="overlayer"></div>
		<div class="checkbox">
			<div class="welight-checkbox">
				<input type="checkbox" id="<?php printf( 'welight-ong-checkbox-%d', esc_attr( $ong->ong->id ) ); ?>" name="welight_ong[]" value="<?php echo esc_attr( $ong->ong->id ); ?>" class="welight">
				<label for="<?php printf( 'welight-ong-checkbox-%d', esc_attr( $ong->ong->id ) ); ?>" class="checkbox"></label>
			</div>
		</div>
		<div class="ong-logo" style="background-image: url(<?php echo esc_attr( $logo_image ); ?>);"></div>
		<div class="more-link">
			<a href="javascript:void(0);">+ info</a>
		</div>
		<div class="ong-info">
			<span class="close">&times;</span>
			<p><?php echo esc_html( $ong->ong->profile_detail->missao_resumo ); ?></p>
		</div>
	</div>
<?php endforeach; ?>
