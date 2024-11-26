<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * The main class
 *
 * https://wpdatatables.com/documentation/creating-wpdatatables/creating-wordpress-tables-from-nested-json-data-with-json-authentication/
 *
 * @since 1.0.0
 */
class WpGetApi_Extras_Wpdatatables {


	public $confirmation;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 */
	public function __construct() {

		// add new wpdatatables field
		add_filter( 'wpgetapi_fields_endpoints', array( $this, 'wpdatatables_field' ), 11, 1 );

		// create our table
		add_action( 'wp_ajax_wpgetapi_create_dummy_file', array( $this, 'create_dummy_file' ) );
		add_action( 'wp_ajax_wpgetapi_create_wpdatatable', array( $this, 'create_initial_file' ) );

		// reconnect a table
		add_action( 'wp_ajax_wpgetapi_reconnect_wpdatatable', array( $this, 'update_existing_file' ) );

		// check if the shortcode exists on page
		add_action( 'template_redirect', array( $this, 'exists' ) );

		// check if wpdatatables shortcode exists in wpforms
		add_action( 'wpforms_frontend_confirmation_message_before', array( $this, 'wpforms_exists' ), 10, 4 );

		add_filter( 'frm_success_filter', array( $this, 'formidable_forms_exists' ), 10, 2 );
	}


	/**
	 * get upload dir
	 */
	public function upload_dir() {
		$upload = wp_upload_dir();
		return $upload['basedir'] . '/wpgetapi';
	}

	/**
	 * get upload url
	 */
	public function upload_url() {
		$upload = wp_upload_dir();
		return $upload['baseurl'] . '/wpgetapi';
	}


	/**
	 * add our field
	 *
	 * @since 1.0.0
	 */
	public function wpdatatables_field( $endpoint_fields ) {

		if ( is_plugin_active( 'wpdatatables/wpdatatables.php' ) ) {

			if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'wpgetapi_' ) !== false ) {

				$fields = array(
					array(
						'name'    => '',
						'id'      => 'wpdatatables-title',
						'type'    => 'title',
						'classes' => 'field-wpdatatables-table',
						'desc'    => $this->get_buttons(),
					),
					array(
						'id'      => 'wpdatatables',
						'classes' => 'field-wpdatatables hidden',
						'type'    => 'text',
					),
					array(
						'name'       => 'Root' . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'If there are issues creating the table, try setting the default root.', 'wpgetapi-extras' ) . ' ' . esc_attr__( 'Format should be like <b>root->location</b> where location is the name of the first item in the JSON.', 'wpgetapi-extras' ) . ' ' . esc_attr__( 'If left blank, <b>root</b> is the default.', 'wpgetapi-extras' ) . '"></span>',
						'id'         => 'wpdatatables_root',
						'classes'    => 'field-wpdatatables-root',
						'type'       => 'text',
						'desc'       => 'Leave blank or set to root if unsure.',
						'attributes' => array(
							'placeholder' => 'root->location',
						),
					),
				);

				$endpoint_fields = array_merge( $endpoint_fields, $fields );

			}
		}

		return $endpoint_fields;
	}

	/**
	 * get field options
	 *
	 * @since 1.0.0
	 */
	public function get_buttons() {

		$page = sanitize_text_field( $_GET['page'] );

		$table_url = add_query_arg(
			array(
				'page'   => 'wpdatatables-constructor',
				'source' => '',
			),
			admin_url( 'admin.php' )
		);

		ob_start();
		?>
			
			<div class="cmb-th">
				<label>
					<?php _e( 'wpDataTables', 'wpgetapi-extras' ); ?>
					<span class="dashicons dashicons-editor-help" data-tip="
					<?php
					esc_attr_e( 'Press the button to create a new wpDataTable from the endpoint data.', 'wpgetapi-extras' );
					echo ' ';
					esc_attr_e( 'Ensure that you have tested the endpoint and it is returning the data you want.', 'wpgetapi-extras' );
					?>
					">
					</span>
				</label>
			</div>
			<div class="cmb-td wpdatatables-buttons" data-api-id="<?php echo $page; ?>" data-endpoint-id="" data-wpdatatable-id=""  data-wpdatatable-root="">
				
				<?php echo wp_nonce_field( 'wdtEditNonce', 'wdtNonce' ); ?>

				<a target="_blank" class="connected hidden button button-secondary edit-wpdatatable" href="<?php echo $table_url; ?>">Edit the wpDataTable</a>
				<button class="connected hidden button button-secondary reconnect-wpdatatable">Reconnect wpDataTable</button>
				<button class="button button-secondary create-wpdatatable">Create wpDataTable from this endpoint</button> 
				
				<div class="processing"></div>

				<p class="text connected hidden">Connected to wpDataTable ID#<span></span>.<br>Edit the table and click on 'Get JSON roots' to finalise table setup. See <a target="_blank" href="https://wpgetapi.com/docs/using-with-wpdatatables/">docs</a> for more info.</p>

			</div>

		<?php
		$content = ob_get_contents();
		ob_end_clean();
		return $content;
	}


	/**
	 * get field options
	 *
	 * @since 1.0.0
	 */
	public function get_field_options() {

		$options = apply_filters(
			'wpgetapi_pro_wpdatatables_field_options',
			array(
				'' => __( '-- No Table --', 'wpgetapi-extras' ),
			)
		);

		$all_tables = WPDataTable::getAllTables();
		$tables     = array();

		if ( $all_tables ) {
			foreach ( $all_tables as $table ) {
				$tables[ $table['id'] ] = esc_html__( 'wpDataTables', 'wpgetapi-extras' ) . ' - ' . esc_html( $table['title'] ) . ' (ID ' . intval( $table['id'] ) . ')';
			}
		} else {
			$tables[] = esc_html__( 'wpDataTables - No Tables found', 'wpgetapi-extras' );
		}

		$options = array_merge( $options, $tables );

		return $options;
	}

	/**
	 * check if we have the table on the page
	 *
	 * @since 1.0.0
	 */
	public function exists() {

		global $post, $wpdb;

		if ( isset( $post->post_content ) && ( has_shortcode( $post->post_content, 'wpdatatable' ) || has_shortcode( $post->post_content, 'wpdatachart' ) ) ) {
			$table_ids     = array();
			$regex_pattern = get_shortcode_regex();

			preg_match_all( '/' . $regex_pattern . '/s', $post->post_content, $regex_matches );

			foreach ( $regex_matches[2] as $i => $match ) {
				$table_id = null;
				$chart_id = null;
				if ( 'wpdatachart' === $match ) {

					// Found a wpdatatable, find out what ID
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

					$dbtable  = $wpdb->prefix . 'wpdatacharts';
					$table_id = $wpdb->get_var(
						$wpdb->prepare(
							"
                        SELECT wpdatatable_id
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

				if ( 'wpdatatable' === $match ) {

					// Found a wpdatatable, find out what ID
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

				$api_data = $this->update_file_contents( $endpoint, $table_id );
			}
		}
	}


	/**
	 * check if we have the table in wpforms confirmation
	 *
	 * @since 1.0.0
	 */
	public function wpforms_exists( $confirmation, $form_data, $fields, $entry_id ) {

		if ( isset( $confirmation['message'] ) && has_shortcode( $confirmation['message'], 'wpdatatable' ) ) {

			$id            = null;
			$regex_pattern = get_shortcode_regex();

			preg_match( '/' . $regex_pattern . '/s', $confirmation['message'], $regex_matches );

			if ( 'wpdatatable' === $regex_matches[2] ) {
				// Found a wpdatatable, find out what ID
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

			$api_data = $this->update_file_contents( $endpoint, $table_id );

			$confirmation = do_shortcode( '[wpdatatable id=' . $table_id . ']' );

		}

		return $confirmation;
	}

	/**
	 * check if we have the table in wpforms confirmation
	 *
	 * @since 1.0.0
	 */
	public function formidable_forms_exists( $type, $form ) {

		if ( isset( $_POST ) && isset( $_POST['frm_action'] ) && 'create' === $_POST['frm_action'] ) {

			if ( isset( $form->options['success_msg'] ) && has_shortcode( $form->options['success_msg'], 'wpdatatable' ) ) {

				$id            = null;
				$regex_pattern = get_shortcode_regex();

				preg_match( '/' . $regex_pattern . '/s', $form->options['success_msg'], $regex_matches );

				if ( 'wpdatatable' === $regex_matches[2] ) {

					// Found a wpdatatable, find out what ID
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

				$api_data = $this->update_file_contents( $endpoint, $table_id );

			}
		}

		return $type;
	}

	/**
	 * Check endpoints for table
	 */
	public function look_for_table( $table = '' ) {

		$setup = get_option( 'wpgetapi_setup' );

		// if no apis, bail
		if ( ! isset( $setup['apis'] ) ) {
			return;
		}

		$data  = array();
		$count = 0;

		// loop through APIs
		foreach ( $setup['apis'] as $i1 => $api ) {

			$endpoints = get_option( 'wpgetapi_' . $api['id'] );

			// if no endpoints, bail
			if ( ! isset( $endpoints['endpoints'][0] ) ) {
				continue;
			}

			// loop through endpoints
			foreach ( $endpoints['endpoints'] as $i2 => $endpoint ) {

				// if table not set, bail
				if ( ! isset( $endpoint['wpdatatables'] ) || '' === $endpoint['wpdatatables'] || $endpoint['wpdatatables'] !== $table ) {
					continue;
				}

				$data['api_id']      = $api['id'];
				$data['endpoint_id'] = $endpoint['id'];
				$data['endpoint']    = $endpoint;
				++$count;

			}
		}

		if ( empty( $data ) ) {
			return false;
		}

		return $data;
	}




	/**
	 * create our table
	 *
	 * @since 1.0.0
	 */
	public function create_initial_file() {

		// sleep(5);
		global $wpdb;

		$dbtable  = $wpdb->prefix . 'wpdatatables';
		$table_id = $wpdb->get_var(
			"
            SELECT MAX(id)
            FROM $dbtable
        	"
		);

		// pp($table_id);
		$api_id         = sanitize_text_field( $_POST['api_id'] );
		$endpoint_id    = sanitize_text_field( $_POST['endpoint_id'] );
		$page           = $api_id;
		$api_id         = str_replace( 'wpgetapi_', '', $page );
		$title          = 'WPGetAPI - ' . $api_id . ' - ' . $endpoint_id;
		$endpoints      = get_option( $page );
		$root           = 'root';
		$nonce          = substr( md5( time() . rand() ), 20 );
		$save_file_name = $table_id . '-' . $nonce;

		if ( empty( $endpoints['endpoints'] ) ) {
			return false;
		}

		// loop through endpoints to get ours
		foreach ( $endpoints['endpoints'] as $i => $endpoint ) {

			// if id == id
			if ( $endpoint['id'] === $endpoint_id ) {

				// set these
				$endpoint['api_id']      = $api_id;
				$endpoint['endpoint_id'] = $endpoint['id'];

				$root = isset( $endpoint['wpdatatables_root'] ) ? $endpoint['wpdatatables_root'] : $root;

				$endpoint['wpdatatables_file_name'] = $save_file_name;

				$api_data = $this->update_file_contents( $endpoint, $table_id );

				// set the table id and save file name in our endpoint
				$endpoints['endpoints'][ $i ]['wpdatatables']           = $table_id;
				$endpoints['endpoints'][ $i ]['wpdatatables_file_name'] = $save_file_name;

			}
		}

		// update the content
		$content = array(
			'url'           => $this->upload_url() . '/wpdatatables-' . $save_file_name . '.json',
			'method'        => 'get',
			'authOption'    => '',
			'username'      => '',
			'password'      => '',
			'customHeaders' => array(),
			'root'          => $root,
		);

		// update the table
		$updated = $wpdb->update(
			$wpdb->prefix . 'wpdatatables',
			array(
				'content' => json_encode( $content ),
			),
			array( 'id' => $table_id )
		);

		update_option( $page, $endpoints );

		$output = array(
			'page'        => $page,
			'endpoint_id' => $endpoint_id,
			'api_id'      => $api_id,
		);
		echo json_encode( $output );

		wp_die();
	}

	/**
	 * create the dummy file first
	 *
	 * @since 1.0.0
	 */
	public function create_dummy_file() {

		$endpoint_id = sanitize_text_field( $_POST['endpoint_id'] );
		$api_id      = sanitize_text_field( $_POST['api_id'] );
		$api_id      = str_replace( 'wpgetapi_', '', $api_id );

		if ( ! file_exists( $this->upload_dir() ) ) {
			wp_mkdir_p( $this->upload_dir() );
		}

		// call our endpoint
		$data = wpgetapi_endpoint(
			$api_id,
			$endpoint_id,
			array(
				'results_format' => 'json_string',
				'debug'          => false,
			)
		);

		file_put_contents( $this->upload_dir() . '/wpdatatables-dummy.json', $data );

		$output = array(
			'endpoint_id' => $endpoint_id,
			'api_id'      => $api_id,
		);
		echo json_encode( $output );

		wp_die();
	}



	/**
	 * update an existing tables json file
	 *
	 * @since 1.0.0
	 */
	public function update_existing_file() {

		global $wpdb;

		$api_id      = sanitize_text_field( $_POST['api_id'] );
		$page        = $api_id;
		$api_id      = str_replace( 'wpgetapi_', '', $page );
		$endpoint_id = sanitize_text_field( $_POST['endpoint_id'] );
		$table_id    = sanitize_text_field( $_POST['table_id'] );
		$endpoints   = get_option( $page );

		// loop through endpoints to get ours
		foreach ( $endpoints['endpoints'] as $i => $endpoint ) {
			if ( $endpoint['id'] == $endpoint_id ) {
				// if wpdatatables_file_name is not empty then set wpdatatables_file_name in the $endpoint
				if ( ! empty( $endpoint['wpdatatables_file_name'] ) ) {
					$endpoint['wpdatatables_file_name'] = $endpoint['wpdatatables_file_name'];
				}
				break;
			}
		}

		// set these
		$endpoint['api_id']      = $api_id;
		$endpoint['endpoint_id'] = $endpoint_id;

		$api_data = $this->update_file_contents( $endpoint, $table_id );

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
	* create json file with API data
	*
	* @param array $endpoint Array of endpoint data
	* @param int   $table_id ID of wpDataTable
	*
	* @return array table id of wpDataTable
	*/
	public function update_file_contents( $endpoint, $table_id ) {

		// get the dir
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'] . '/wpgetapi';
		if ( ! file_exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		// call our endpoint
		$data = wpgetapi_endpoint(
			$endpoint['api_id'],
			$endpoint['endpoint_id'],
			array(
				'results_format' => 'json_string',
				'debug'          => false,
			)
		);

		if ( ! empty( $endpoint['wpdatatables_file_name'] ) ) {
			$wp_data_table_file_name = 'wpdatatables-' . $endpoint['wpdatatables_file_name'] . '.json';
		} else {
			$wp_data_table_file_name = 'wpdatatables-' . $table_id . '.json';
		}
		file_put_contents( $upload_dir . '/' . $wp_data_table_file_name, $data );

		return array(
			'table_id' => $table_id,
		);
	}

	/**
	 * create file
	 */
	public function update_file_contents_from_action( $table_id, $data ) {

		// get the dir
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'] . '/wpgetapi';
		if ( ! file_exists( $upload_dir ) ) {
			wp_mkdir_p( $upload_dir );
		}

		if ( is_array( $data ) ) {
			$data = json_encode( $data );
		}

		file_put_contents( $upload_dir . '/wpdatatables-' . $table_id . '.json', $data );

		return array(
			'table_id' => $table_id,
		);
	}
}

return new WpGetApi_Extras_Wpdatatables();


