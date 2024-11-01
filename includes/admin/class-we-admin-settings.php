<?php
/**
 * Admin Settings
 *
 * @package    Welight
 * @subpackage Welight/Admin
 * @author     Welight <dev@welight.co>
 */

if ( ! class_exists( 'WC_Settings_Page' ) ) {
	if ( file_exists( WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php' ) ) {
		require_once WP_PLUGIN_DIR . '/woocommerce/includes/admin/settings/class-wc-settings-page.php';
	}
}

/**
 * Admin settings class.
 */
class WELIGHTEI_Admin_Settings extends WC_Settings_Page {

	/**
	 * WELIGHTEI_Admin_Settings constructor.
	 */
	public function __construct() {
		$this->id    = 'welight';
		$this->label = __( 'Welight', 'welight' );

		// parent.
		parent::__construct();

		// filters.
		add_filter( 'woocommerce_admin_settings_sanitize_option_welight_donation_tax', array( $this, 'onsave_donation_tax' ) );

		// actions.
		add_action( 'woocommerce_admin_field_welightei_tiny_editor', array( $this, 'welightei_tiny_editor_field' ) );

		add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'save_html_field' ), 20, 3 );
	}

	/**
	 * Save HTML field.
	 *
	 * @param midex $value The value.
	 * @param mixed $option Field options.
	 * @param mixed $raw_value Original value.
	 *
	 * @return string
	 */
	public function save_html_field( $value, $option, $raw_value ) {
		if ( $option && isset( $option['type'] ) && 'welightei_tiny_editor' === $option['type'] ) {
			$value = wpautop( $raw_value );
		}

		return $value;
	}

	/**
	 * Tiny Editor Field.
	 *
	 * @param mixed $value The field args.
	 */
	public function welightei_tiny_editor_field( $value ) {
		?>
		<style type="text/css">
		<!--
		td.forminp.forminp-we_tiny_editor .wp-editor-container {
			max-width: 650px !important;
			marign-top: 10px;
		}
		-->
		</style>
		<tr valign="top">
			<th scope="row" class="titledesc">
				<label for="<?php echo esc_attr( $value['id'] ); ?>"><?php echo esc_html( $value['title'] ); ?></label>
			</th>

			<td class="forminp forminp-<?php echo esc_attr( sanitize_title( $value['type'] ) ); ?>">
				<?php
				// value.
				$content_value = WC_Admin_Settings::get_option( $value['id'], $value['default'] );

				// description.
				echo ( ! empty( $value['desc'] ) ) ? sprintf( '<p class="description">%s</p>', $value['desc'] ) : ''; // @codingStandardsIgnoreLine.

				// editor.
				wp_editor(
					$content_value,
					$value['id'], array(
						'teeny'         => true,
						'quicktags'     => false,
						'media_buttons' => false,
						'textarea_rows' => 5,
						'editor_class'  => 'we-editor-container',
					)
				);
				?>
			</td>
		</tr>
		<?php
	}

	/**
	 * Get Settings.
	 *
	 * @return array
	 */
	public function get_settings() {

		// Description.
		$we_tiny_editor_description = __( "Texto sobre doação exibido no carrinho. Utilize as variaveis <code>{loja}</code> (nome da sua loja), <code>{doacao}</code> (valor de doação configurado) ou <code>{doacao_total}</code> (para exibir o valor que vai ser doado na compra, baseado nas configurações).", 'welight' ); // @codingStandardsIgnoreLine.

		$settings = apply_filters(
			'welight_admin_settings', array(
				array(
					'title' => __( 'Configurações gerais', 'welight' ),
					'type'  => 'title',
					'desc'  => __( 'Obtenha sua chave de autorização, configure a taxa de doação de sua preferência.', 'welight' ),
					'id'    => 'welight_general_settings',
				),
				array(
					'id'      => 'welight_activated',
					'type'    => 'checkbox',
					'title'   => __( 'Ativar / Desativar', 'welight' ),
					'desc'    => __( 'Ativa. Caso deseje desativar temporariamente, desmarque a caixa ao lado.', 'welight' ),
					'default' => 'yes',
				),
				array(
					'id'      => 'welight_sandbox',
					'type'    => 'checkbox',
					'title'   => __( 'Sandbox', 'welight' ),
					'desc'    => __( 'Se selecionado, será utilizado em modo de desenvolvimento / testes.', 'welight' ),
					'default' => 'no',
				),
				array(
					'id'          => 'welight_api_username',
					'type'        => 'text',
					'title'       => __( 'Nome de Usuário', 'welight' ),
					'placeholder' => __( 'E-mail do seu usuário Welight ...', 'welight' ),
					'desc'        => __( 'E-mail usado no cadastro como doador na Welight.', 'welight' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				array(
					'id'          => 'welight_api_key',
					'type'        => 'text',
					'title'       => __( 'Chave de autorização', 'welight' ),
					'placeholder' => __( 'Cole a chave de autorização aqui ...', 'welight' ),
					'desc'        => __( 'Obtenha sua chave da API acessando o seu dashboard em http://welight.co/. Entre em contato para mais informações.', 'welight' ),
					'default'     => '',
					'desc_tip'    => true,
				),
				array(
					'id'       => 'welight_donation_tax',
					'type'     => 'text',
					'title'    => __( 'Taxa de doação', 'welight' ),
					'desc'     => __( 'Defina quanto será a taxa de doação para quando uma venda for efetivada. Exemplo: 5% ou 99.90 para taxa fixa.', 'welight' ),
					'default'  => '',
					'desc_tip' => true,
					'css'      => 'width: 70px;',
				),
				array(
					'id'          => 'welight_store_name',
					'type'        => 'text',
					'title'       => __( 'Nome da Loja', 'welight' ),
					'placeholder' => __( 'Deixe em branco para padrão ...', 'welight' ),
					/* translators: %s: The default value. */
					'desc'        => sprintf( __( 'Caso não seja informado, o padrão será: <u>%s</u>', 'welight' ), get_bloginfo( 'name' ) ),
					'default'     => '',
					'desc_tip'    => true,
				),
				array(
					'type' => 'sectionend',
					'id'   => 'welight_general_settings',
				),
				array(
					'title' => __( 'Configurações de Layout', 'welight' ),
					'type'  => 'title',
					'desc'  => __( 'Modifique as opções de layout para aquele que mais gostar ou se adequar a sua loja.', 'welight' ),
					'id'    => 'welight_layout_settings',
				),
				array(
					'id'       => 'welight_style_display_ong',
					'type'     => 'select',
					'title'    => __( 'Tipo de exibição', 'welight' ),
					'desc'     => __( 'Escolhar o modelo de exibição das ongs no checkout', 'welight' ),
					'default'  => 'welight',
					'desc_tip' => true,
					'options'  => array(
						'welight'        => __( 'Padrão', 'welight' ),
						'welight_simple' => __( 'Simples', 'welight' ),
					),
				),
				array(
					'id'      => 'welight_net_impact',
					'type'    => 'checkbox',
					'title'   => __( 'Ativar NetImpact', 'welight' ),
					'desc'    => __( 'Ativa seção de publicidade da Welight na página depois do pagamento.', 'welight' ),
					'default' => 'yes',
				),
				array(
					'type' => 'sectionend',
					'id'   => 'welight_layout_settings',
				),
				array(
					'title' => __( 'Configurações de Textos', 'welight' ),
					'type'  => 'title',
					'desc'  => __( 'Modifique os texto que é exibido nas páginas.', 'welight' ),
					'id'    => 'welight_texts_settings',
				),
				array(
					'id'          => 'welight_text_cart',
					'type'        => 'welightei_tiny_editor',
					'title'       => __( 'Carrinho', 'welight' ),
					'placeholder' => __( 'Deixe em branco para padrão ...', 'welight' ),
					'desc'        => $we_tiny_editor_description,
					'default'     => __( '<p>A <strong>{loja}</strong> vai doar <strong>{doacao}</strong> desta venda sem você gastar nada a mais.<br />Você escolhe as causas que quer apoiar na próxima página.</p> <p>Total em doação: <strong>{doacao_total}</strong></p>', 'welight' ),
				),
				array(
					'id'          => 'welight_text_checkout',
					'type'        => 'welightei_tiny_editor',
					'title'       => __( 'Checkout', 'welight' ),
					'placeholder' => __( 'Deixe em branco para padrão ...', 'welight' ),
					'desc'        => $we_tiny_editor_description,
					'default'     => __( '<p>A <strong>{loja}</strong> vai doar <strong>{doacao}</strong> desta venda sem você gastar nada a mais.<br />Escolha as causas que quer apoiar abaixo:</p> <p>Total em doação: <strong>{doacao_total}</strong></p>', 'welight' ),
				),
				array(
					'type' => 'sectionend',
					'id'   => 'welight_texts_settings',
				),
			)
		);

		return $settings;
	}

	/**
	 * Output the settings.
	 */
	public function output() {
		$settings = $this->get_settings();

		WC_Admin_Settings::output_fields( $settings );
	}

	/**
	 * Sanitize donation tax.
	 *
	 * @param string $value The tax value.
	 *
	 * @return mixed
	 */
	public function onsave_donation_tax( $value ) {
		// Is percentage value.
		$is_percentage = strpos( $value, '%' ) !== false;

		// sanitize value to remove symbols.
		$value = preg_replace( '/[^0-9.,]/si', '', $value );

		// sanitize price.
		$value = wc_format_decimal( $value );

		return $value . ( $is_percentage ? '%' : '' );
	}

}

return new WELIGHTEI_Admin_Settings();
