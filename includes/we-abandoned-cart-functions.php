<?php
/**
 * Abandoned Cart Functions.
 *
 * @package Welight
 * @subpackage Welight/Functions
 */

/**
 * Create abandoned cart.
 *
 * This function create one register for abadoned cart.
 *
 * @param array $args The meta args to save.
 *
 * @return WP_Post|WP_Error|null|object
 */
function welightei_create_abandoned_cart( $args = array() ) {
	// default args.
	$defaults = array(
		'is_guest' => 'no',
		'user_id'  => null,
	);

	// args.
	$args = (object) wp_parse_args( $args, $defaults );

	// no create register, if "user_id" empty.
	if ( ! $args->user_id ) {
		return new WP_Error( 'ERR_USER_ID', __( 'Um usuário deve ser informado.', 'welight' ) );
	}

	// check is exists by user id.
	if ( ( $cart = welightei_abandoned_cart_exists( $args->user_id ) ) ) { // @codingStandardsIgnoreLine.
		return welightei_get_abandoned_cart( $cart->ID );
	}

	// create register.
	$row_id = wp_insert_post(
		array(
			'post_status' => 'weei-abandoned',
			'post_type'   => 'weei_abandoned_cart',
			'meta_input'  => $args,
		)
	);

	// create error?
	if ( is_wp_error( $row_id ) ) {
		return $row_id;
	}

	// return the row created.
	return welightei_get_abandoned_cart( $row_id );
}

/**
 * Return the cart metas.
 *
 * @param int $id The cart ID.
 *
 * @return array
 */
function welightei_get_abandoned_cart_metas( $id ) {
	global $wpdb;

	// query.
	$query = $wpdb->prepare( "SELECT meta_key, meta_value FROM {$wpdb->postmeta} WHERE post_id = %d", absint( $id ) );

	// result.
	$result = $wpdb->get_results( $query ); // @codingStandardsIgnoreLine.

	// return result.
	$return_result = array();

	if ( $result ) {
		foreach ( $result as $r ) {
			$return_result[ $r->meta_key ] = maybe_unserialize( $r->meta_value ); // @codingStandardsIgnoreLine.
		}
	}

	return $return_result;
}

/**
 * Retrieve abandoned cart by ID.
 *
 * @param int $id The cart abandoned ID.
 *
 * @return object
 */
function welightei_get_abandoned_cart( $id ) {
	// cart.
	$cart = get_post( $id );

	// not found cart.
	if ( ! $cart ) {
		return;
	}

	// metas.
	$metas = welightei_get_abandoned_cart_metas( $id );

	// create object to return.
	$object = new stdClass();

	$object->cart = $cart;
	$object->meta = $metas;

	return $object;
}

/**
 * Check if abandoned cart exists.
 *
 * @param int $user_id The user id.
 *
 * @return bool|WP_Post
 */
function welightei_abandoned_cart_exists( $user_id ) {
	// search.
	$carts = new WP_Query(
		array(
			'post_type'   => 'weei_abandoned_cart',
			'post_status' => 'weei-abandoned',
			'orderby'     => 'post_date',
			'order'       => 'DESC',
			'meta_query'  => array( // @codingStandardsIgnoreLine.
				array(
					'key'     => 'user_id',
					'value'   => $user_id,
					'compare' => '=',
				),
				array(
					'key'     => 'expiring',
					'value'   => floatval( time() ),
					'compare' => '>',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	if ( $carts->have_posts() ) {
		// cart items.
		$carts_items = $carts->get_posts();

		return current( $carts_items );
	}

	// is contain rows?
	return false;
}

/**
 * Get abandoned cart by user_id.
 *
 * @param int $user_id The user id.
 *
 * @return null|WP_Post
 */
function welightei_get_abandoned_cart_by_user( $user_id ) {
	global $welightwei_abandoned_carts;

	// cart.
	$cart = welightei_abandoned_cart_exists( $user_id );

	// not exits.
	if ( ! $cart  ) {
		return;
	}

	return $cart;
}

/**
 * Updated abandoned cart contents.
 *
 * @param int   $user_id The abandoned cart ID.
 * @param mixed $args    The args to update cart.
 *
 * @return boolean
 */
function welightei_update_abandoned_cart_contents( $user_id, $args = array() ) {
	// get cart.
	$cart = welightei_get_abandoned_cart_by_user( $user_id );

	// exists?
	if ( ! $cart ) {
		return false;
	}

	// extract args.
	$args = (object) wp_parse_args(
		$args, array(
			'customer' => null,
			'contents' => null,
			'totals'   => 0,
		)
	);

	// update cart content.
	update_post_meta( absint( $cart->ID ), 'cart_content', $args->contents );

	// update cart totals.
	update_post_meta( absint( $cart->ID ), 'cart_totals', $args->totals );

	// customer.
	update_post_meta( absint( $cart->ID ), 'customer', $args->customer );

	return false;
}

/**
 * Remove abandoned cart by user_id.
 *
 * @param int      $user_id The user id.
 * @param WC_Order $order   The checkout order.
 *
 * @return bool
 */
function welightei_remove_abandoned_cart_from_user( $user_id, $order = null ) {
	global $wpdb;

	// not exists abandoned cart from user.
	if ( ! welightei_abandoned_cart_exists( $user_id ) ) {
		return true;
	}

	// search.
	$carts = new WP_Query(
		array(
			'post_type'   => 'weei_abandoned_cart',
			'post_status' => 'weei-abandoned',
			'meta_query'  => array( // @codingStandardsIgnoreLine.
				array(
					'key'     => 'user_id',
					'value'   => $user_id,
					'compare' => '=',
				),
			),
		)
	);

	// get ids.
	$ids = wp_list_pluck( $carts->get_posts(), 'ID' );

	// placeholder.
	$placeholders = array();

	foreach ( $ids as $id ) {
		$placeholders[] = esc_sql( $id );
	}

	$placeholders = implode( ',', $placeholders );

	// update post status.
	/*if ( $wpdb->query( $wpdb->prepare( "UPDATE {$wpdb->posts} AS p SET p.post_status = %s WHERE p.ID IN({$placeholders})", 'weei-order-completed' ) ) ) { // @codingStandardsIgnoreLine.
		if ( $order ) {
			foreach ( $ids as $id ) {
				update_post_meta( $id, 'order_id', $order->get_id() );
			}
		}

		return true;
	}*/

	// remove metas.
	$deleted_metas = $wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id IN($placeholders)" ); // @codingStandardsIgnoreLine.

	// remove posts.
	$deleted_posts = $wpdb->query( $wpdb->prepare( "DELETE FROM {$wpdb->posts} WHERE post_status = %s AND ID IN($placeholders)", 'weei-abandoned' ) ); // @codingStandardsIgnoreLine.

	// is delete?
	return $deleted_posts && $deleted_metas;
}

/**
 * Get expired carts.
 *
 * @param int $time The time to get. Optional.
 *
 * @return array
 */
function welightei_get_expired_abandoned_carts( $time = null ) {
	// current time.
	if ( ! $time ) {
		$time = current_time( 'timestamp' );
	}

	// search.
	$carts = new WP_Query(
		array(
			'post_type'   => 'weei_abandoned_cart',
			'post_status' => 'weei-abandoned',
			'meta_query'  => array( // @codingStandardsIgnoreLine.
				array(
					'key'     => 'expiring',
					'value'   => $time,
					'compare' => '<',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	// the carts.
	return $carts->have_posts() ? $carts->get_posts() : array();
}
