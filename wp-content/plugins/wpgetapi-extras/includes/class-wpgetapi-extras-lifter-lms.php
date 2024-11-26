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
class WpGetApi_Extras_Lifter_Lms {


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
		if ( isset( $api->args['on'] ) && $api->args['on'] === 'lifter_lms_order' ) {

			// get our order_id
			if ( isset( $_GET['order-complete'] ) ) {

				$current_order_id = sanitize_text_field( $_GET['order-complete'] );

				if ( ! $current_order_id ) {
					return;
				}

				$args  = array(
					'post_type'   => 'llms_order',
					'post_status' => 'llms-completed',
					'meta_key'    => '_llms_order_key',
					'meta_value'  => $current_order_id,
				);
				$posts = get_posts( $args );

				$post_id = null;
				if ( $posts ) {
					$post_id = $posts[0]->ID;
				}

				if ( ! $post_id ) {
					return;
				}

				// if we have already called the API for this order
				// as we only want to send it once per order
				$api_called = get_post_meta( $post_id, '_wpgetapi_lifter_api_called', true );

				// we now want to call the API
				if ( ! $api_called ) {
					update_post_meta( $post_id, '_wpgetapi_lifter_api_called', 'yes' );
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

return new WpGetApi_Extras_Lifter_Lms();
