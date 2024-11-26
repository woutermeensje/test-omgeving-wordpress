<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class
 *
 * @since 2.7.0
 */
class WpGetApi_Extras_Enqueues {


	/**
	 * Main constructor
	 *
	 *
	 */
	public function __construct() {

		add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts_styles' ) );
	}

	/**
	 * Enqueue scripts and styles.
	 */
	public function admin_scripts_styles( $hook_suffix ) {

		if ( isset( $hook_suffix ) && strpos( $hook_suffix, 'wpgetapi_' ) !== false ) {

			$v = WPGETAPIEXTRASVERSION;
			//$v = time();
			wp_enqueue_script( 'wpgetapi-extras', WPGETAPIEXTRASURL . 'assets/js/wpgetapi-extras.js', array( 'jquery' ), $v );

			$upload     = wp_upload_dir();
			$upload_url = $upload['baseurl'] . '/wpgetapi';

			global $wpdb;

			wp_localize_script(
				'wpgetapi-extras',
				'wpgetapi_extras',
				array(
					'upload_url'                     => $upload_url,
					'pre'                            => $wpdb->prefix,
					'call_api_text'                  => __( '...calling API', 'wpgetapi-extras' ),
					'ninja_table_created_text'       => __( '...Ninja Tables created', 'wpgetapi-extras' ),
					'ninja_table_create_button_endpoint_text' => __( 'Create Ninja Tables from this endpoint', 'wpgetapi-extras' ),
					'ninja_table_create_button_text' => __( 'Create new Ninja Tables', 'wpgetapi-extras' ),
					'finalizing_table_text'          => __( '...finalizing', 'wpgetapi-extras' ),
				)
			);

			wp_enqueue_style( 'wpgetapi-extras-style', WPGETAPIEXTRASURL . 'assets/css/wpgetapi-extras.css', false, $v );
		}
	}
}

return new WpGetApi_Extras_Enqueues();
