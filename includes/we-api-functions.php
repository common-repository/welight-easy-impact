<?php
/**
 * API Functions.
 *
 * @package Welight
 * @subpackage Welight/Functions/API
 */

/**
 * Remote GET Method.
 *
 * Call GET to API.
 *
 * @param string $endpoint Endpoint of API.
 *
 * @return mixed
 */
function welightei_http_get( $endpoint = '' ) {

	if ( ! $endpoint ) {
		return false;
	}

	// URL.
	$_url = welightei_get_base_api_url( $endpoint );

	// GET.
	$_response = wp_remote_get( $_url ); // @codingStandardsIgnoreLine.

	// error.
	if ( is_wp_error( $_response ) ) {
		return false;
	}

	// GET BODY.
	$_body = wp_remote_retrieve_body( $_response );

	if ( ! is_object( $_body ) || ! is_array( $_body ) ) {
		$_body = json_decode( $_body );
	}

	return $_body;
}

/**
 * Remote POST Method.
 *
 * Call POST to API.
 *
 * @param string $endpoint  Endpoint for http request.
 * @param array  $data      Data for send in request.
 *
 * @return mixed
 */
function welightei_http_post( $endpoint = '', $data = array() ) {

	if ( ! $endpoint ) {
		return false;
	}

	// URL.
	$_url = welightei_get_base_api_url( $endpoint );

	// convert data.
	$_body = ! is_string( $data ) ? wp_json_encode( $data ) : $data;

	// POST.
	$_response = wp_remote_post(
		$_url, array(
			'method'  => 'POST',
			'timeout' => 30,
			'headers' => array(
				'Content-Type' => 'application/json',
			),
			'body'    => $_body,
		)
	);

	// error.
	if ( is_wp_error( $_response ) ) {
		return false;
	}

	// GET BODY.
	$_body = wp_remote_retrieve_body( $_response );

	if ( ! is_object( $_body ) || ! is_array( $_body ) ) {
		$_body = json_decode( $_body );
	}

	return $_body;
}

/**
 * Remote DELETE Method.
 *
 * Call DELETE to API.
 *
 * @param string $endpoint Endpoint for DELETE.
 *
 * @return bool|null
 */
function welightei_http_delete( $endpoint = '' ) {

	if ( ! $endpoint ) {
		return false;
	}

	// URL.
	$_url = welightei_get_base_api_url( $endpoint );

	// DELETE.
	$_response = wp_remote_request(
		$_url, array(
			'method'  => 'DELETE',
			'timeout' => 30,
		)
	);

	// error.
	if ( is_wp_error( $_response ) ) {
		return false;
	}

	return true;
}

/**
 * Get base API URL.
 *
 * @param string $endpoint Endpoint to concanate to base API url.
 *
 * @return string
 */
function welightei_get_base_api_url( $endpoint = '' ) {
	// replace first slash if exists.
	$endpoint = preg_replace( '/^\/(.*)?/si', '$1', $endpoint );
	return sprintf( '%1$s/%2$s', untrailingslashit( WELIGHTEI_BASE_API_URL ), $endpoint );
}

/**
 * Get base URL.
 *
 * @param string $endpoint Endpoint to concanate to base API url.
 *
 * @return string
 */
function welightei_get_base_url( $endpoint = '' ) {
	// replace first slash if exists.
	$endpoint = preg_replace( '/^\/(.*)?/si', '$1', $endpoint );
	return sprintf( '%1$s/%2$s', untrailingslashit( WELIGHTEI_BASE_URL ), $endpoint );
}

/**
 * Append args to endpoint.
 *
 * @param string       $endpoint Endpoint.
 * @param string|array $args Query string.
 *
 * @return string
 */
function welightei_append_args_endpoint( $endpoint, $args = null ) {
	$query_string = '';

	// args.
	if ( is_array( $args ) && $args ) {
		$query_string = sprintf( '%s', http_build_query( $args ) );
	}

	if ( strpos( $endpoint, '?' ) === false ) {
		// trailingslashit.
		$endpoint = trailingslashit( $endpoint );

		// append "?".
		$endpoint .= '?';
	} else {
		$endpoint .= '&';
	}

	return sprintf( '%1$s%2$s', $endpoint, $query_string );

}

/**
 * Get api auth options.
 *
 * Return the username and apikey configured on panel options.
 *
 * @param string $key The key of get.
 *
 * @return string|object
 */
function welightei_get_api_auth( $key = '' ) {
	// api key and username.
	$apikey   = get_option( 'welight_api_key', null );
	$username = get_option( 'welight_api_username', null );

	if ( ! $apikey || ! $username ) {
		return null;
	}

	switch ( $key ) {
		case 'username':
		case 'user':
			return $username;
			break; // @codingStandardsIgnoreLine.

		case 'apikey':
		case 'key':
			return $apikey;
		break; // @codingStandardsIgnoreLine.

		default:
			return (object) array(
				'apikey'   => $apikey,
				'username' => $username,
			);
	}
}
