<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @link https://ninjatables.com/docs/ninja-tables-introduction/
 */
use NinjaTables\App\Models\NinjaTableItem;

/**
 * This class provides additional functionality for Ninja Tables integration with
 * the WPGetAPI plugin.
 */
class WpGetApi_Extras_NinjaTables {
	/**
	 * Slug for the Ninja Table custom post type.
	 *
	 * @var string
	 */
	private $cpt_name = 'ninja-table';

	/**
	 * Constructor.
	 *
	 * Sets up necessary hooks to manage AJAX requests, display the latest API data
	 * if the table shortcode is set on WPForms, Formidable Forms, or a page/post,
	 * and remove Ninja Table sync from the WPGetAPI endpoint if the table is removed
	 * from the admin.
	 */
	public function __construct() {

		// add new ninjatables field
		add_filter( 'wpgetapi_fields_endpoints', array( $this, 'ninjatables_field' ), 11, 1 );

		// create our table
		add_action( 'wp_ajax_wpgetapi_ninja_tables_save_table', array( $this, 'ninja_tables_save_table' ) );
		add_action( 'wp_ajax_wpgetapi_import_ninja_tables_data', array( $this, 'import_ninja_tables_data' ) );

		// reconnect a table
		add_action( 'wp_ajax_wpgetapi_reconnect_ninjatables', array( $this, 'update_existing_table' ) );

		// check if the shortcode exists on page
		add_action( 'template_redirect', array( $this, 'ninja_table_exists_on_page_post' ) );

		// check if Ninja Tables shortcode exists in wpforms
		add_action( 'wpforms_frontend_confirmation_message', array( $this, 'wpforms_exists' ), 10, 4 );

		add_filter( 'frm_success_filter', array( $this, 'formidable_forms_exists' ), 10, 2 );

		add_action( 'deleted_post', array( $this, 'deleted_ninjatable' ), 10, 2 );
	}

	/**
	 * Add Ninja Tables integration fields.
	 *
	 * @param array $endpoint_fields The existing endpoint fields.
	 * @return array The combined array of Ninja Table fields and endpoint fields.
	 */
	public function ninjatables_field( $endpoint_fields ) {

		if ( ! isset( $_GET['page'] ) || strpos( $_GET['page'], 'wpgetapi_' ) != false ) {
			return $endpoint_fields;
		}

		$fields = array(
			array(
				'name'    => '',
				'id'      => 'ninjatables-title',
				'type'    => 'title',
				'classes' => 'field-ninjatables-table',
				'desc'    => $this->get_buttons(),
			),
			array(
				'id'      => 'ninjatables',
				'classes' => 'field-ninjatables hidden',
				'type'    => 'text',
			),
			array(
				'name'       => 'Root' . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Specify the path to reach nested data.', 'wpgetapi-extras' ) . ' ' . esc_attr__( 'Format should be like <b>root->location</b> where location is the name of the first item in the JSON.', 'wpgetapi-extras' ) . ' ' . esc_attr__( ' If left blank, <b>root</b> is the default.', 'wpgetapi-extras' ) . '"></span>',
				'id'         => 'ninjatables_root',
				'classes'    => 'field-ninjatables-root',
				'type'       => 'text',
				'desc'       => __( 'It is likely there is no root, so you can leave this blank.', 'wpgetapi-extras' ),
				'attributes' => array(
					'placeholder' => 'root->location',
				),
			),
		);

		$endpoint_fields = array_merge( $endpoint_fields, $fields );

		return $endpoint_fields;
	}

	/**
	 * Generates HTML buttons for integration actions with Ninja Tables.
	 *
	 * @return string HTML content of buttons.
	 */
	private function get_buttons() {

		$page = sanitize_text_field( $_GET['page'] );

		$table_url = add_query_arg(
			array(
				'page' => 'ninja_tables#/tables/',
			),
			admin_url( 'admin.php' )
		);

		ob_start();
		?>
			
			<div class="cmb-th">
				<label>
					<?php _e( 'Ninja Tables', 'wpgetapi-extras' ); ?>
					<span class="dashicons dashicons-editor-help" data-tip="
					<?php
					esc_attr_e( 'Press the button to create a new Ninja Tables from the endpoint data.', 'wpgetapi-extras' );
					echo ' ';
					esc_attr_e( 'Ensure that you have tested the endpoint and it is returning the data you want.', 'wpgetapi-extras' );
					?>
					">
					</span>
				</label>
			</div>
			<div class="cmb-td ninjatables-buttons" data-api-id="<?php echo esc_attr( $page ); ?>" data-endpoint-id="" data-ninjatables-id=""  data-ninjatables-root="">
				
				<?php echo wp_nonce_field( 'ninja_table_nonce', 'ninja_table_nonce_value' ); ?>

				<a target="_blank" class="ninjatables-connected hidden button button-secondary edit-ninjatables" href="<?php echo esc_url( $table_url ); ?>"><?php _e( 'Edit the Ninja Tables', 'wpgetapi-extras' ); ?></a>
				<button class="ninjatables-connected hidden button button-secondary reconnect-ninjatables"><?php _e( 'Reconnect Ninja Tables', 'wpgetapi-extras' ); ?></button>
				<button class="button button-secondary create-ninjatables" disabled="disabled"><?php _e( 'Create Ninja Tables from this endpoint', 'wpgetapi-extras' ); ?></button> 
				
				<div class="processing"></div>

				<p class="text ninjatables-connected hidden"><?php _e( 'Connected to Ninja Tables ID#', 'wpgetapi-extras' ); ?><span></span>.<br><?php _e( 'See', 'wpgetapi-extras' ); ?> <a target="_blank" href="https://wpgetapi.com/docs/using-with-ninja-tables/"><?php _e( 'docs', 'wpgetapi-extras' ); ?></a> <?php _e( 'for more info.', 'wpgetapi-extras' ); ?></p>

			</div>

		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}

	/**
	 * Check if the page or post contains Ninja Tables or Ninja Charts shortcodes.
	 *
	 * If the shortcode exists, call the API and update the latest API data on
	 * the table before it is displayed on the page or post.
	 *
	 * @global WP_Post $post The current post object.
	 * @global wpdb    $wpdb WordPress database abstraction object.
	 */
	public function ninja_table_exists_on_page_post() {

		global $post, $wpdb;

		if ( isset( $post->post_content ) && ( has_shortcode( $post->post_content, 'ninja_tables' ) || has_shortcode( $post->post_content, 'ninja_charts' ) ) ) {
			$table_ids     = array();
			$regex_pattern = get_shortcode_regex();

			preg_match_all( '/' . $regex_pattern . '/s', $post->post_content, $regex_matches );

			foreach ( $regex_matches[2] as $i => $match ) {
				$table_id = null;
				$chart_id = null;
				if ( 'ninja_charts' === $match ) {

					// Found a Ninja Tables, find out what ID
					// Turn the attributes into a URL parm string
					$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3][ $i ] ) );
					$attribure_str = str_replace( '"', '', $attribure_str );

					// Parse the attributes
					$defaults   = array(
						'preview' => '1',
					);
					$attributes = wp_parse_args( $attribure_str, $defaults );

					if ( isset( $attributes['id'] ) ) {
						$chart_id = $attributes['id'];
					}

					$dbtable  = $wpdb->prefix . 'ninja_charts';
					$table_id = $wpdb->get_var(
						$wpdb->prepare(
							"
                        SELECT table_id
                        FROM $dbtable WHERE id = %d
                    	",
							$chart_id
						)
					);

					// Check for duplicate and add array of ids
					if ( ! in_array( $table_id, $table_ids, true ) ) {
						$table_ids[] = $table_id;
					}
				}

				if ( 'ninja_tables' === $match ) {

					// Found a Ninja Tables, find out what ID
					// Turn the attributes into a URL parm string
					$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3][ $i ] ) );
					$attribure_str = str_replace( '"', '', $attribure_str );

					// Parse the attributes
					$defaults   = array(
						'preview' => '1',
					);
					$attributes = wp_parse_args( $attribure_str, $defaults );
					// Check for duplicate and add array of ids
					if ( isset( $attributes['id'] ) && ! in_array( $attributes['id'], $table_ids, true ) ) {
						$table_ids[] = $attributes['id'];
					}
				}
			}

			// if we can't get the ids
			if ( empty( $table_ids ) ) {
				return;
			}

			foreach ( $table_ids as $table_id ) {
				$endpoint = null;
				$endpoint = $this->look_for_table( $table_id );

				if ( ! $endpoint ) {
					continue;
				}

				$api_data = $this->update_table_contents( $endpoint, $table_id );
			}
		}
	}

	/**
	 * Check if Ninja Tables shortcode exists in WPForms confirmation message.
	 *
	 * @link  https://wpforms.com/developers/wpforms_frontend_confirmation_message/
	 *
	 * @param string $confirmation The confirmation message.
	 * @param array  $form_data    The processed form settings/data.
	 * @param array  $fields       The sanitized field data.
	 * @param int    $entry_id     The entry ID.
	 * @return string The modified confirmation message.
	 */
	public function wpforms_exists( $confirmation, $form_data, $fields, $entry_id ) {

		if ( isset( $confirmation['message'] ) && has_shortcode( $confirmation['message'], 'ninja_tables' ) ) {

			$regex_pattern = get_shortcode_regex();

			preg_match( '/' . $regex_pattern . '/s', $confirmation['message'], $regex_matches );

			if ( 'ninja_tables' === $regex_matches[2] ) {
				// Found a Ninja Tables, find out what ID
				// Turn the attributes into a URL parm string
				$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3] ) );
				$attribure_str = str_replace( '"', '', $attribure_str );

				// Parse the attributes
				$defaults   = array(
					'preview' => '1',
				);
				$attributes = wp_parse_args( $attribure_str, $defaults );

				if ( isset( $attributes['id'] ) ) {
					$table_id = $attributes['id'];
				}
			}

			// if we can't get the id
			if ( ! $table_id ) {
				return $confirmation;
			}

			$endpoint = $this->look_for_table( $table_id );
			if ( ! $endpoint ) {
				return $confirmation;
			}

			$api_data = $this->update_table_contents( $endpoint, $table_id );

			$confirmation = do_shortcode( '[ninja_tables id=' . $table_id . ']' );

		}

		return $confirmation;
	}

	/**
	 * Check if Ninja Tables shortcode exists in Formidable Forms success message.
	 *
	 * @link  https://formidableforms.com/knowledgebase/frm_success_filter/
	 *
	 * @param array|string $type Possible return values are: message, redirect, or page.
	 * @param object       $form Form configuration data
	 * @return array|string The modified value $type.
	 */
	public function formidable_forms_exists( $type, $form ) {

		if ( isset( $_POST ) && isset( $_POST['frm_action'] ) && 'create' === $_POST['frm_action'] ) {

			if ( isset( $form->options['success_msg'] ) && has_shortcode( $form->options['success_msg'], 'ninja_tables' ) ) {

				$regex_pattern = get_shortcode_regex();

				preg_match( '/' . $regex_pattern . '/s', $form->options['success_msg'], $regex_matches );

				if ( 'ninja_tables' === $regex_matches[2] ) {

					// Found a Ninja Tables, find out what ID
					// Turn the attributes into a URL parm string
					$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3] ) );
					$attribure_str = str_replace( '"', '', $attribure_str );

					// Parse the attributes
					$defaults   = array(
						'preview' => '1',
					);
					$attributes = wp_parse_args( $attribure_str, $defaults );

					if ( isset( $attributes['id'] ) ) {
						$table_id = $attributes['id'];
					}
				}

				// if we can't get the id
				if ( ! $table_id ) {
					return $type;
				}

				$endpoint = $this->look_for_table( $table_id );
				if ( ! $endpoint ) {
					return $type;
				}

				$api_data = $this->update_table_contents( $endpoint, $table_id );

			}
		}

		return $type;
	}

	/**
	 * Check endpoints for a specific Ninja Table.
	 *
	 * @param int $table Ninja Table ID.
	 * @return array Associative array containing API ID, endpoint ID, and endpoint data
	 *               if a match is found; false if no match is found.
	 */
	private function look_for_table( $table = '' ) {

		$setup = get_option( 'wpgetapi_setup' );

		// if no apis, bail
		if ( empty( $setup['apis'] ) ) {
			return false;
		}

		$data  = array();
		$count = 0;

		// loop through APIs
		foreach ( $setup['apis'] as $api ) {

			$endpoints = get_option( 'wpgetapi_' . $api['id'] );

			// if no endpoints, bail
			if ( ! isset( $endpoints['endpoints'][0] ) ) {
				continue;
			}

			// loop through endpoints
			foreach ( $endpoints['endpoints'] as $endpoint ) {

				// if table not set, bail
				if ( ! isset( $endpoint['ninjatables'] ) || '' === $endpoint['ninjatables'] || $endpoint['ninjatables'] != $table ) {
					continue;
				}

				$endpoint['api_id']      = $api['id'];
				$endpoint['endpoint_id'] = $endpoint['id'];
				$data                    = $endpoint;
				++$count;

			}
		}

		if ( empty( $data ) ) {
			return false;
		}

		return $data;
	}

	/**
	 * create ninja table
	 */
	public function ninja_tables_save_table() {

		$nonce = empty( $_POST['nonce'] ) ? '' : $_POST['nonce'];
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ninja_table_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'wpgetapi-extras' ) );
		}

		if ( ! WP_Get_API::instance()->current_user_can_manage() ) {
			wp_die( __( 'Sorry, you don\'t have permission to perform this action.', 'wpgetapi-extras' ) );
		}

		$api_id      = sanitize_text_field( $_POST['api_id'] );
		$endpoint_id = sanitize_text_field( $_POST['endpoint_id'] );
		$page        = $api_id;
		$api_id      = str_replace( 'wpgetapi_', '', $page );
		$title       = $api_id . ' ' . $endpoint_id;
		$endpoints   = get_option( $page );

		if ( empty( $endpoints['endpoints'] ) ) {
			return false;
		}

		$table_id = wp_insert_post(
			array(
				'post_title'  => $title,
				'post_type'   => $this->cpt_name,
				'post_status' => 'publish',
			)
		);

		// loop through endpoints to get ours
		foreach ( $endpoints['endpoints'] as $i => $endpoint ) {

			// if id == id
			if ( $endpoint['id'] == $endpoint_id ) {

				// set these
				$endpoint['api_id']      = $api_id;
				$endpoint['endpoint_id'] = $endpoint['id'];

				// set the these in our endpoint
				$endpoints['endpoints'][ $i ]['api_id']      = $api_id;
				$endpoints['endpoints'][ $i ]['endpoint_id'] = $endpoint['id'];
				$endpoints['endpoints'][ $i ]['ninjatables'] = $table_id;

			}
		}

		// update endpoint
		update_option( $page, $endpoints );
	}

	/*
	 * Import Ninja Tables data from API.
	 */
	public function import_ninja_tables_data() {

		$nonce = empty( $_POST['nonce'] ) ? '' : $_POST['nonce'];
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ninja_table_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'wpgetapi-extras' ) );
		}

		if ( ! WP_Get_API::instance()->current_user_can_manage() ) {
			wp_die( __( 'Sorry, you don\'t have permission to perform this action.', 'wpgetapi-extras' ) );
		}

		$api_id      = sanitize_text_field( $_POST['api_id'] );
		$endpoint_id = sanitize_text_field( $_POST['endpoint_id'] );
		$page        = $api_id;
		$api_id      = str_replace( 'wpgetapi_', '', $page );
		$endpoints   = get_option( $page );

		// loop through endpoints to get ours
		foreach ( $endpoints['endpoints'] as $i => $endpoint ) {

			// if id == id
			if ( $endpoint['id'] == $endpoint_id ) {

				// set the table id in our endpoint
				$table_id = $endpoints['endpoints'][ $i ]['ninjatables'];
				$this->update_table_contents( $endpoint, $table_id );
				break;
			}
		}
	}

	/**
	 * Update an existing Ninja Table's data.
	 */
	public function update_existing_table() {

		$nonce = empty( $_POST['nonce'] ) ? '' : $_POST['nonce'];
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'ninja_table_nonce' ) ) {
			wp_die( __( 'Security check failed.', 'wpgetapi-extras' ) );
		}

		if ( ! WP_Get_API::instance()->current_user_can_manage() ) {
			wp_die( __( 'Sorry, you don\'t have permission to perform this action.', 'wpgetapi-extras' ) );
		}

		$api_id      = sanitize_text_field( $_POST['api_id'] );
		$page        = $api_id;
		$api_id      = str_replace( 'wpgetapi_', '', $page );
		$endpoint_id = sanitize_text_field( $_POST['endpoint_id'] );
		$table_id    = sanitize_text_field( $_POST['table_id'] );
		$endpoints   = get_option( $page );

		// loop through endpoints to get ours
		foreach ( $endpoints['endpoints'] as $endpoint ) {

			// if id == id
			if ( $endpoint['id'] == $endpoint_id ) {

				$endpoint['api_id']      = $api_id;
				$endpoint['endpoint_id'] = $endpoint_id;

				$this->update_table_contents( $endpoint, $table_id );
				break;
			}
		}

		$output = array(
			'page'        => $page,
			'endpoint_id' => $endpoint_id,
			'api_id'      => $api_id,
			'table_id'    => $table_id,
		);
		echo json_encode( $output );

		wp_die();
	}

	/**
	 * Update Ninja Tables data using API endpoint.
	 *
	 * @param array $endpoint Endpoint settings data.
	 * @param int   $table_id Ninja Table ID to update.
	 * @return array Array with updated Ninja Table ID.
	 */
	private function update_table_contents( $endpoint, $table_id ) {

		// call our endpoint
		$data = wpgetapi_endpoint(
			$endpoint['api_id'],
			$endpoint['endpoint_id'],
			array(
				'results_format' => 'json_decoded',
				'debug'          => false,
			)
		);

		$root = ( isset( $endpoint['ninjatables_root'] ) && ! empty( $endpoint['ninjatables_root'] ) ) ? $endpoint['ninjatables_root'] : 'root';

		$content = $this->get_data_from_root( $data, $root );

		if ( empty( $content ) ) {
			return array(
				'table_id' => $table_id,
			);
		}

		$ninja_table_columns = get_post_meta( $table_id, '_ninja_table_columns', true );
		if ( ! $ninja_table_columns ) {
			$ninja_table_columns = array();
		}

		// Reference code from the ninja-tables\app\Http\Controllers\ImportController.php file uploadTableJson() function
		$reverse_content = array_reverse( $content );
		$header          = array_keys( array_pop( $reverse_content ) );

		$formatted_header = array();
		foreach ( $header as $head ) {
			$column_name = $this->get_ninja_table_column_name( $ninja_table_columns, $head );
			if ( $column_name ) {
				$formatted_header[ $head ] = $column_name;
			} else {
				$formatted_header[ $head ] = $head;
			}
		}

		// Reference code from the ninja-tables\app\Http\Controllers\ImportController.php file storeTableConfigWhenImporting() function
		if ( empty( $ninja_table_columns ) ) {
			foreach ( $formatted_header as $key => $name ) {
				$ninja_table_columns[] = array(
					'key'         => $key,
					'name'        => $name,
					'breakpoints' => '',
				);
			}
		}

		update_post_meta( $table_id, '_ninja_table_columns', $ninja_table_columns );
		$ninja_table_settings = ninja_table_get_table_settings( $table_id, 'admin' );
		update_post_meta( $table_id, '_ninja_table_settings', $ninja_table_settings );
		NinjaTableItem::where( 'table_id', $table_id )->delete(); // Removed all existing data from table
		ninjaTablesClearTableDataCache( $table_id );

		ninjaTableInsertDataToTable( $table_id, $content, $formatted_header );

		return array(
			'table_id' => $table_id,
		);
	}

	/**
	 * Retrieves the 'name' value from an array based on a given 'key'.
	 *
	 * This function returns the ninja tables column 'name' value from the specified
	 * 'key'. If the key is not found, the function returns null.
	 *
	 * @param  array       $ninja_table_columns Existing table column settings.
	 * @param  string      $find_key            Column array data match key.
	 * @return string|false
	 */
	private function get_ninja_table_column_name( $ninja_table_columns, $find_key ) {
		if ( empty( $ninja_table_columns ) ) {
			return false;
		}

		foreach ( $ninja_table_columns as $item ) {
			if ( $item['key'] == $find_key ) {
				return $item['name'];
			}
		}
		return false;
	}

	/**
	 * Retrieve data from a specific root path in a JSON-decoded array.
	 *
	 * @param object $data JSON-decoded array or object containing the data.
	 * @param string $root Root path to navigate within the data structure.
	 * @return array|null The array data located at the specified root path, or null if path does not exist.
	 */
	private function get_data_from_root( $data, $root ) {

		if ( $root == 'root' ) {
			return $data; // Return the original array
		}

		$keys = explode( '->', $root ); // Split the path into keys

		foreach ( $keys as $key ) {
			// Check if key exists in current level of data
			if ( $key == 'root' ) {
				continue;
			}

			if ( isset( $data[ $key ] ) ) {
				$data = $data[ $key ];
			} else {
				// Key does not exist, return null
				return null;
			}
		}

		return $data;
	}

	/**
	 * Remove synchronization of Ninja Table from endpoint settings when the table is deleted.
	 *
	 * @param int     $post_id Post ID of the deleted Ninja Table.
	 * @param WP_Post $post    Post object data.
	 */
	public function deleted_ninjatable( $post_id, $post ) {
		if ( $this->cpt_name != $post->post_type ) {
			return;
		}

		$setup = get_option( 'wpgetapi_setup' ); // get all the api setup
		if ( empty( $setup ) ) {
			return;
		}

		foreach ( $setup['apis'] as $api_item ) {
			$api_id    = 'wpgetapi_' . $api_item['id'];
			$endpoints = get_option( $api_id );
			if ( ! empty( $endpoints['endpoints'] ) ) {
				foreach ( $endpoints['endpoints'] as $i => $endpoint ) {
					if ( isset( $endpoint['ninjatables'] ) && $post_id == $endpoint['ninjatables'] ) {
						unset( $endpoints['endpoints'][ $i ]['ninjatables'] ); // remove ninjatable id from the endpoint settings
						update_option( $api_id, $endpoints );
						return;
					}
				}
			}
		}
	}
}

return new WpGetApi_Extras_NinjaTables();
