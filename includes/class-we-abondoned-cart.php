<?php
/**
 * Welight Abondoned Cart
 *
 * To get and process abondoned carts.
 *
 * @package Welight
 * @subpackage Welight/Classes
 */

/**
 * Class WELIGHTEI_Abondoned_Cart
 */
class WELIGHTEI_Abondoned_Cart {

	/**
	 * Expiration Time.
	 *
	 * Time limit to remove carts.
	 *
	 * @var int
	 */
	private $expiration_time = ( 60 * 60 * 24 * 2 );

	/**
	 * Session.
	 *
	 * @var WC_Session_Handler|WC_Session
	 */
	private $session = null;

	/**
	 * Session.
	 *
	 * @var WC_Cart|WC_Cart_Session
	 */
	private $cart = null;

	/**
	 * WELIGHTEI_Abondoned_Cart constructor.
	 */
	public function __construct() {
		// set session.
		$this->set_session();

		// set cart.
		$this->set_cart();

		add_action( 'init', array( $this, 'register_post_type' ), 1 );
		add_action( 'woocommerce_cart_loaded_from_session', array( $this, 'we_register_cart_session' ), 99 );
		add_action( 'woocommerce_cart_updated', array( $this, 'we_update_abandoned_cart' ) );
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'we_remove_abandoned_cart' ), 99, 3 );
		add_action( 'wp_login', array( $this, 'we_login_session' ), 99, 2 );

		// schedule.
		add_action( 'welightei_cleanup_expired_abandoned_carts', array( $this, 'we_cleanup_expired_carts' ) );

		if ( ! wp_next_scheduled( 'welightei_cleanup_expired_abandoned_carts' ) ) {
			wp_schedule_event( time(), 'twicedaily', 'welightei_cleanup_expired_abandoned_carts' );
		}
	}

	/**
	 * Set session in class.
	 *
	 * @global $woocommerce.
	 *
	 * @return void
	 */
	private function set_session() {
		global $woocommerce;

		if ( function_exists( 'WC' ) ) {
			$this->session = WC()->session;
		} elseif ( $woocommerce ) {
			$this->session = $woocommerce->session;
		}
	}

	/**
	 * Set cart in class.
	 *
	 * @global $woocommerce
	 *
	 * @return void
	 */
	private function set_cart() {
		global $woocommerce;

		if ( function_exists( 'WC' ) ) {
			$this->cart = WC()->cart;
		} elseif ( $woocommerce ) {
			$this->cart = $woocommerce->cart;
		}
	}

	/**
	 * Register post type.
	 *
	 * @return void
	 */
	public function register_post_type() {
		// abandoned cart post type.
		register_post_type(
			'weei_abandoned_cart',
			array(
				'public'     => false,
				'show_in_ui' => false,
			)
		);
	}

	/**
	 * Register cart session.
	 *
	 * @param WC_Cart_Session $cart_session The cart session class.
	 *
	 * @return void
	 */
	public function we_register_cart_session( $cart_session ) {
		// custumer id.
		$custumer_id = $this->session->get_customer_id();

		// not continue if cart is empty.
		if ( $this->cart->is_empty() && ! $this->session->has_session() ) {
			return;
		}

		// args to create.
		$args = array(
			'is_guest' => is_numeric( $custumer_id ) && is_user_logged_in() ? 'no' : 'yes',
			'user_id'  => $custumer_id,
			'expiring' => current_time( 'timestamp' ) + $this->expiration_time,
		);

		// register.
		$cart = welightei_create_abandoned_cart( $args );

		// check status.
		if ( $cart && 'weei-order-completed' === $cart->cart->post_status ) {
			// delete.
			wp_delete_post( $cart->cart->ID, true );

			// recreate.
			$cart = welightei_create_abandoned_cart( $args );
		}
	}

	/**
	 * Update Abandoned Cart.
	 *
	 * @return void
	 */
	public function we_update_abandoned_cart() {
		// customer id.
		$customer_id = $this->session->get_customer_id();

		// has cart.
		if ( $this->cart->is_empty() ) {
			// emptie cart.
			if ( welightei_abandoned_cart_exists( $customer_id ) ) {
				welightei_remove_abandoned_cart_from_user( $customer_id );
			}

			// stop here.
			return;
		}

		// args.
		$args = array(
			'customer' => $this->session->get( 'customer' ),
			'contents' => $this->session->get( 'cart' ),
			'totals'   => $this->session->get( 'cart_totals' ),
		);

		// update cart content.
		welightei_update_abandoned_cart_contents( $customer_id, $args );
	}

	/**
	 * Remove abandoned cart.
	 *
	 * Remove abandoned cart if checkout process is finish.
	 *
	 * @param int      $order_id    Order ID.
	 * @param mixed    $posted_data The posted data.
	 * @param WC_Order $order       Order Object.
	 */
	public function we_remove_abandoned_cart( $order_id, $posted_data, $order ) {
		// customer id.
		$customer_id = $this->session->get_customer_id();

		// remove.
		welightei_remove_abandoned_cart_from_user( $customer_id, $order );
	}

	/**
	 * Update user_id.
	 *
	 * Update user id from session.
	 *
	 * @param string  $user_login The user login.
	 * @param WP_User $user The user class.
	 *
	 * @return void
	 */
	public function we_login_session( $user_login, $user ) {
		global $wpdb;

		if ( ( $cart = welightei_abandoned_cart_exists( $this->session->get_customer_id() ) ) ) { // @codingStandardsIgnoreLine.
			// customer id.
			$customer_id = $this->session->get_customer_id();

			// valid user.
			if ( $customer_id && $user->exists() ) {
				// update user id.
				update_post_meta( $cart->ID, 'user_id', $user->ID );
			}
		}
	}

	/**
	 * Cleanup and send to backend Welight.
	 *
	 * @return void
	 */
	public function we_cleanup_expired_carts() {
		// expired cart.
		$carts = welightei_get_expired_abandoned_carts();

		// currtime.
		$currtime = current_time( 'timestamp' );

		// loop.
		if ( $carts ) {
			foreach ( $carts as $cart ) {
				// cart id.
				$cart = welightei_get_abandoned_cart( $cart->ID );

				// expiring.
				$expiring = floatval( $cart->meta['expiring'] );

				// next item in loop.
				if ( $expiring > $currtime ) {
					continue;
				}

				// cart totals.
				$cart_totals = $cart->meta['cart_totals'];

				// order total.
				$order_total = ! empty( $cart_totals['total'] ) ? $cart_totals['total'] : 0;

				// order id.
				$order_id = $this->we_generate_random_order_id();

				// customer.
				$customer = $cart->meta['customer'];

				$customer_first_name = ! empty( $customer['first_name'] ) ? $customer['first_name'] : '';
				$customer_last_name  = ! empty( $customer['last_name'] ) ? $customer['last_name'] : '';
				$customer_email      = ! empty( $customer['email'] ) ? $customer['email'] : '';

				// full name.
				if ( ! $customer_first_name ) {
					$name = __( 'Visitante', 'welight' );
				} else {
					$name = trim( $customer_first_name );

					// append last name.
					if ( $customer_last_name ) {
						$name .= ' ' . trim( $customer_last_name );
					}
				}

				// email.
				$email = $customer_email;

				if ( ! $email ) {
					$email = apply_filters( 'welight_easy_impact_guest_email', 'guest@welight.co' );
				}

				// send to API.
				$response = $this->we_create_abandoned_order(
					array(
						'customer_name'  => $name,
						'customer_email' => $email,
						'order_uid'      => $order_id,
						'order_amount'   => floatval( wc_format_decimal( $order_total ) ),
					)
				);

				// delete this abandoned cart.
				if ( ! empty( $response->id ) ) {
					// delete.
					wp_delete_post( $cart->cart->ID, true );
				}
			}
		}
	}

	/**
	 * Generate Randon ID.
	 *
	 * @return string
	 */
	private function we_generate_random_order_id() {
		require_once ABSPATH . 'wp-includes/class-phpass.php';

		// hasher.
		$hasher = new PasswordHash( 8, false );

		// value.
		return md5( $hasher->get_random_bytes( 32 ) );
	}

	/**
	 * Create and Send to API abandoned order.
	 *
	 * @param array $args The args in order.
	 *
	 * @return object|null|mixed
	 */
	private function we_create_abandoned_order( $args = array() ) {
		// args.
		$args = (object) wp_parse_args(
			$args,
			array(
				// Customer.
				'customer_name'  => '',
				'customer_email' => '',
				// Order.
				'order_uid'      => null,
				'order_currency' => get_woocommerce_currency(),
				'order_amount'   => 0,
				// Donation.
				'donation'       => welightei_get_store_donation( false ),
			)
		);

		// api settings.
		$api_key      = welightei_get_api_auth( 'apikey' );
		$api_username = welightei_get_api_auth( 'username' );

		// donation value.
		$donation_value = $args->donation;
		$donation       = array();

		// donation payload.
		if ( $donation_value ) {
			// is percentagem value.
			$donation_percentagem = strpos( $donation_value, '%' ) !== false;

			if ( $donation_percentagem ) {
				$donation_value = preg_replace( '/[^0-9.]+/si', '', $donation_value );

				// percetn donation.
				$donation = array(
					'porcentagem'    => true,
					'doacao_porcent' => floatval( wc_format_decimal( $donation_value ) ),
				);
			} else {
				// fixed donation.
				$donation = array(
					'porcentagem'  => false,
					'doacao_total' => floatval( wc_format_decimal( $donation_value ) ),
				);
			}
		}

		// payload.
		$payload = array(
			'plataforma' => 'woocommerce',
			'auth'       => array(
				'username' => $api_username,
				'apikey'   => $api_key,
			),
			'cliente'    => array(
				'nome'  => $args->customer_name,
				'email' => $args->customer_email,
			),
			'venda'      => array(
				'uid'    => $args->order_uid,
				'moeda'  => $args->order_currency,
				'valor'  => $args->order_amount,
				'status' => 'abandono',
			),
			'doacao'     => $donation,
		);

		// post request.
		return welightei_http_post( 'doador-empresa/ecommerce-venda/', $payload );
	}

	/**
	 * Check is current request agent.
	 *
	 * @param string $user_agent The current user agent to check.
	 *
	 * @return bool
	 */
	private function is_search_engine( $user_agent = null ) {
		// valid search engine.
		$searchengines = array(
			'Googlebot',
			'Slurp',
			'search.msn.com',
			'nutch',
			'simpy',
			'bot',
			'ASPSeek',
			'crawler',
			'msnbot',
			'Libwww-perl',
			'FAST',
			'Baidu',
		);

		// default is false.
		$return_value = false;

		// empty user agent.
		if ( ! $user_agent ) {
			return $return_value;
		}

		// check.
		foreach ( $searchengines as $searchengine ) {
			if ( ! empty( $_SERVER['HTTP_USER_AGENT'] ) && false !== strpos( strtolower( $user_agent ), strtolower( $searchengine ) ) ) { // @codingStandardsIgnoreLine.
				$return_value = true;
				break;
			}
		}

		return $return_value;
	}

}

// Instance.
return new WELIGHTEI_Abondoned_Cart();
