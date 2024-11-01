<?php
/**
 * Ong class.
 *
 * @package Welight
 * @subpackage Welight/Classes
 */

/**
 * Class WELIGHTEI_ONG
 */
class WELIGHTEI_ONG {

	/**
	 * Endpoint.
	 *
	 * Endpoint for Ong.
	 *
	 * @var string
	 */
	private $endpoint = '/doador-empresa/empresa-ong-public/?apikey=%s&username=%s';

	/**
	 * Limite results.
	 *
	 * @var int
	 */
	private $limit = 30;

	/**
	 * Meta.
	 *
	 * @var null
	 */
	public $meta = null;

	/**
	 * Objects.
	 *
	 * @var array
	 */
	public $objects = null;

	/**
	 * WELIGHTEI_ONG constructor.
	 */
	public function __construct() {
		// get auth.
		$we_auth = welightei_get_api_auth();

		// Modify endpoint.
		$this->endpoint = sprintf( $this->endpoint, $we_auth->apikey, $we_auth->username );
	}

	/**
	 * Get list of ongs.
	 *
	 * @param array $args Parameters for filter.
	 *
	 * @return array
	 */
	public function get_profiles( $args = array() ) {
		// default args.
		$args = wp_parse_args(
			$args, array(
				'limit' => $this->limit,
			)
		);

		// url.
		$endpoint = welightei_append_args_endpoint( $this->endpoint, $args );

		// response.
		$response = welightei_http_get( $endpoint );

		if ( ! $response ) {
			return array();
		}

		// set meta.
		$this->meta = $response->meta;

		// set objects.
		$this->objects = $response->objects;

		// array of ongs.
		return $this->objects;
	}


}

return new WELIGHTEI_ONG();
