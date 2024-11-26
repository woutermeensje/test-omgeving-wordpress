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
class WpGetApi_Extras_Woocommerce {


	// public $user_id;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {

		add_filter( 'wpgetapi_should_we_stop', array( $this, 'order' ), 10, 2 );
	}

	/**
	 * woocommerce order
	 *
	 * Set the return to true, to stop the api call.
	 * Set the return to false, to allow the api call.
	 *
	 * @since 1.0.0
	 *
	 */
	public function order( $on, $api ) {

		// if we have on set to woocommerce order
		// we don't want to do the API until we have the order id
		if ( isset( $api->args['on'] ) && 'woocommerce_order' === $api->args['on'] ) {

			// get our order_id
			if ( is_wc_endpoint_url( 'order-received' ) ||
				( isset( $_GET['order_id'] ) && ( isset( $_GET['key'] ) && strpos( 'wc_order_', $_GET['key'] ) !== false ) )
			) {

				global $wp;

				$current_order_id = intval( str_replace( 'checkout/order-received/', '', $wp->request ) );

				if ( ! $current_order_id ) {
					$current_order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : false;
				}

				// if we have already called the API for this order
				// as we only want to send it once per order
				$api_called = get_post_meta( $current_order_id, '_wpgetapi_api_called', true );

				// we now want to call the API
				if ( ! $api_called && $current_order_id ) {
					update_post_meta( $current_order_id, '_wpgetapi_api_called', 'yes' );
					return false;
				} else {

					// stop the api if it has been called on this order
					// or we don't have order id
					return true;

				}
			} else {

				// stop the api if we aren't on the order-received screen
				return true;
			}
		}

		return $on;
	}
}

return new WpGetApi_Extras_Woocommerce();
