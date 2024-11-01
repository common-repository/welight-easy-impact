<?php
/**
 * Ong Carousel.
 *
 * Show Carousel of Ongs.
 *
 * @package Welight/Templates
 */

// Profiles.
$ong_profiles = welight()->ong->get_profiles();

if ( ! $ong_profiles ) return;
?>

<?php do_action( 'welight_before_container' ); ?>

<div class="welight-container hidden">

	<?php do_action( 'welight_before_ong_carousel' ); ?>

	<div class="ong-carousel owl-carousel owl-theme" id="carousel-ongs">
		<?php
		echo welightei_get_display_ong_layout( welight()->ong->get_profiles() ); // @codingStandardsIgnoreLine.
		?>
	</div>

	<?php do_action( 'welight_after_ong_carousel' ); ?>


</div>

<?php do_action( 'welight_after_container' ); ?>
