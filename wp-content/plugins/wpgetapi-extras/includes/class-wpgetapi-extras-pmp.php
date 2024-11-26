<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class
 *
 * @since 1.0.0
 */
class WpGetApi_Extras_Pmp {


	// public $user_id;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {

		add_action( 'pmpro_after_checkout', array( $this, 'checkout' ), 10, 2 );
	}

	/**
	 * checkout
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function checkout( $user_id, $order ) {

		$setup = get_option( 'wpgetapi_setup' );

		if ( ! isset( $setup['apis'] ) ) {
			return;
		}

		$api_id      = null;
		$endpoint_id = null;

		// loop through APIs
		foreach ( $setup['apis'] as $i1 => $api ) {

			$endpoints = get_option( 'wpgetapi_' . $api['id'] );

			if ( ! isset( $endpoints['endpoints'][0] ) ) {
				continue;
			}

			// loop through endpoints
			foreach ( $endpoints['endpoints'] as $i2 => $endpoint ) {

				if ( ! isset( $endpoint['header_parameters'][0] ) ) {
					continue;
				}

				$wpgetapi   = new WpGetApi_Api();
				$parameters = $wpgetapi->decrypt( $endpoint['header_parameters'] );

				// loop through headers
				foreach ( $parameters as $i3 => $headers ) {

					if ( isset( $headers['name'] ) && $headers['name'] == 'wpgetapi_on' && $headers['value'] == 'pmp_checkout' ) {
						$api_id      = $api['id'];
						$endpoint_id = $endpoint['id'];
					}
				}
			}
		}

		if ( ! $api_id || ! $endpoint_id ) {
			return;
		}

		// if we have already called the API for this order
		// as we only want to send it once per order
		$api_called = get_user_meta( $user_id, '_wpgetapi_pmp_checkout', true );

		// we now want to call the API
		if ( ! $api_called ) {

			$data = wpgetapi_endpoint( $api_id, $endpoint_id, array( 'debug' => false ) );

			// allow to filter what we are saving
			$data = apply_filters( 'wpgetapi_pmp_saved_data', $data );

			update_user_meta( $user_id, '_wpgetapi_pmp_checkout', $data );

		}

		return;
	}
}

return new WpGetApi_Extras_Pmp();
