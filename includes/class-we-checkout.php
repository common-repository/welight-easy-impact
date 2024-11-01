<?php
/**
 * Checkout class.
 *
 * @package Welight
 * @subpackage Welight/Classes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class WELIGHTEI_Checkout
 */
class WELIGHTEI_Checkout {

	/**
	 * AB Test.
	 *
	 * @var object
	 */
	private $ab_test;

	/**
	 * WELIGHTEI_Checkout constructor.
	 */
	public function __construct() {
		// set value.
		$this->ab_test = welightei_get_current_value_ab_test();

		add_action( 'woocommerce_checkout_process', array( $this, 'validate_ong_fields' ), 10 );
		add_action( 'woocommerce_checkout_create_order', array( $this, 'save_welight_order_meta' ), 10, 2 );
		add_filter( 'woocommerce_checkout_posted_data', array( $this, 'add_welight_ong_posted_data' ) );
		add_action( 'woocommerce_order_status_changed', array( $this, 'welight_order_status_changed' ), 20, 3 );
		add_action( 'woocommerce_thankyou', array( $this, 'welight_thankyou' ), 5 );

		if ( 'b' !== $this->ab_test->current ) {
			add_action( 'woocommerce_cart_totals_after_order_total', 'welightei_donation_message_html_cart' );
			add_action( 'woocommerce_review_order_before_payment', array( $this, 'output_ong_carousel' ) );
		}
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

	/**
	 * Validate ongs selecteds on checkout.
	 *
	 * @return void
	 */
	public function validate_ong_fields() {
		if ( apply_filters( 'welight_disabled_checkout_validation', false ) ) {
			return;
		}

		// welight plugin settings.
		$activated = get_option( 'welight_activated', 'yes' ) === 'yes';

		// disable validation if not activate settings.
		if ( ! $activated || ! isset( $_POST['welight_ong'] ) ) { // WPCS: input var ok, CSRF ok.
			return;
		}

		// selecte ongs.
		$_ongs = $_POST['welight_ong']; // WPCS: input var ok, CSRF ok, sanitization ok.

	}

	/**
	 * Save welight meta on checkout.
	 *
	 * @param WC_Order $order The Order.
	 * @param mixed    $data  The posted data.
	 *
	 * @return void
	 */
	public function save_welight_order_meta( $order, $data ) {

		// disabled is deactivate by settings.
		if ( get_option( 'welight_activated', 'yes' ) !== 'yes' ) {
			return;
		}

		// metas.
		$welight_meta = array(
			'_welight_ongs'            => isset( $data['welight_ong'] ) ? $data['welight_ong'] : array(),
			'_welight_donation_amount' => welightei_get_store_donation( false ),
			'_welight_teste_ab'        => $this->ab_test->current,
		);

		foreach ( $welight_meta as $meta_key => $meta_value ) :
			// serialize if array.
			if ( is_array( $meta_value ) || is_object( $meta_value ) ) {
				$meta_value = maybe_serialize( $meta_value );
			} else {
				$meta_value = esc_attr( $meta_value );
			}

			// Add order meta.
			$order->update_meta_data( $meta_key, $meta_value );
		endforeach;
	}

	/**
	 * Add welight_ong to posted data.
	 *
	 * @param array $data Posted data.
	 *
	 * @return array
	 */
	public function add_welight_ong_posted_data( $data ) {
		// welight ong.
		$data['welight_ong'] = isset( $_POST['welight_ong'] ) ? $_POST['welight_ong'] : array(); // WPCS: input var ok, CSRF ok, sanitization ok.

		return $data;
	}

	/**
	 * Order changed status.
	 *
	 * @param int    $order_id     Order ID.
	 * @param string $status_from  Old Status.
	 * @param string $status_to    New Status.
	 */
	public function welight_order_status_changed( $order_id, $status_from, $status_to ) {
		update_option( 'welight_changed_order_status:' . $order_id, sprintf( 'yes:%s:%s', $status_from, $status_to ) );

		// order.
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// welight donation status.
		$donation_status = $order->get_meta( '_welight_donation_status' );
		$donation_ongs   = maybe_unserialize( $order->get_meta( '_welight_ongs' ) );
		$donation        = array();

		// verify donation.
		$donation_value = $order->get_meta( '_welight_donation_amount' );

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
					'doacao_porcent' => floatval( wc_format_decimal( $donation_value  ) ),
				);
			}
		}

		// api settings.
		$api_key      = welightei_get_api_auth( 'apikey' );
		$api_username = welightei_get_api_auth( 'username' );

		// payload.
		$payload = array(
			'plataforma' => 'woocommerce',
			'auth'       => array(
				'username' => $api_username,
				'apikey'   => $api_key,
			),
			'cliente'    => array(
				'nome'  => $order->get_formatted_billing_full_name(),
				'email' => $order->get_billing_email(),
			),
			'venda'      => array(
				'uid'   => $order->get_id(),
				'moeda' => $order->get_currency(),
				'valor' => $order->get_subtotal(),
			),
			'doacao'     => $donation,
			'ongs'       => $donation_ongs,
			'teste_ab'   => $this->ab_test->current,
		);

		switch ( $status_to ) {
			// process donation.
			case 'processing':
			case 'completed':
				if ( 'completed' === $donation_status ) {
					return;
				}

				// response.
				$_response = welightei_http_post( 'doador-empresa/ecommerce-venda/', $payload );


				if ( ! $_response ) {
					return;
				}

				// donation amount.
				$donation_amount = wc_price( $_response->doacao->doacao_total );

				// update meta data.
				$order->update_meta_data( '_welight_donation_response', $_response );
				$order->update_meta_data( '_welight_donation_status', 'completed' );

				// add note.
				if ( 'b' !== $this->ab_test->current ) {
					/* translators: %s: Donation value */
					$order->add_order_note( sprintf( __( 'A Welight recebeu uma doação desta venda no valor de <u>%s</u>. A Welight agradece sua doação.', 'welight' ), $donation_amount ) );
				}

				$order->save(); // save order.

				break;

			// cancel donation.
			case 'cancelled':
			case 'refunded':
				if ( 'completed' !== $donation_status ) {
					return;
				}

				// base url.
				$base_url = sprintf( 'doador-empresa/ecommerce-venda/%1$d/?apikey=%2$s&username=%3$s', $order->get_id(), $api_key, $api_username );

				// get donation.
				$donation = welightei_http_get( $base_url );

				// donation amount.
				$donation_amount = wc_price( $donation->doacao->doacao_total );

				// check status faturamento.
				if ( 'doacao_nao_faturada' !== $donation->doacao->status ) {
					return;
				}

				// delelete.
				if ( ! welightei_http_delete( $base_url ) ) {
					return;
				}

				// order meta.
				$order->delete_meta_data( '_welight_donation_response' );
				$order->update_meta_data( '_welight_donation_status', 'cancelled' );

				// add note.
				if ( 'b' !== $this->ab_test->current ) {
					/* translators: %s: Donation value */
					$order->add_order_note( sprintf( __( 'A Welight cancelou a doação de <u>%s</u> para este pedido. Ele não será mais faturado.', 'welight' ), $donation_amount ) );
				}

				$order->save();

				break;

			default:
				break;
		}
	}

	/**
	 * Render Thankyou section
	 *
	 * @param int $order_id The order ID.
	 *
	 * @return void The HTML of Section.
	 */
	public function welight_thankyou( $order_id ) {
		// get order.
		$order = wc_get_order( $order_id );

		// teste ab.
		$teste_ab = $order->get_meta( '_welight_teste_ab' );

		if ( ! $teste_ab || 'b' === $teste_ab ) {
			return;
		}

		// get donation amount.
		$donation_amount       = $order->get_meta( '_welight_donation_amount' );
		$donation_amount_total = $donation_amount;

		// calcule donation total percentage.
		if ( strpos( $donation_amount, '%' ) !== false ) {
			$order_total = $order->get_subtotal();
			$percent     = welightei_format_percent_string( $donation_amount );

			$calculate_percentage  = ( $percent * $order_total ) / 100;
			$donation_amount_total = wc_price( $calculate_percentage );
		}

		// get ongs.
		$ongs = maybe_unserialize( $order->get_meta( '_welight_ongs' ) );

		// template args.
		$template_args = array(
			'order'            => $order,
			'donation'         => $donation_amount_total,
			'donation_percent' => $donation_amount,
			'ongs'             => $ongs,
			'qtd_ongs'         => count( $ongs ),
		);

		// template.
		welightei_get_template( 'welight-thankyou.php', $template_args );
	}

}

new WELIGHTEI_Checkout();
