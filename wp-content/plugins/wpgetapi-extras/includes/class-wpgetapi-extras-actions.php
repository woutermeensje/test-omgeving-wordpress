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
class WpGetApi_Extras_Actions {

	public $endpoint_value     = array();
	public $query_string_value = array();
	public $headers_value      = array();
	public $body_value         = array();
	public $response_code;
	public $final_url;

	public $confirmation;
	public $redirect;

	/**
	 * Ultimate Member form error message
	 *
	 * @var string
	 */
	private $ultimatemember_error_message;

	/**
	 * Main constructor
	 *
	 */
	public function __construct() {

		// posts
		add_action( 'transition_post_status', array( $this, 'new_post_published' ), 999, 3 );
		add_action( 'transition_post_status', array( $this, 'transition_post_status' ), 10, 3 );
		add_action( 'delete_post', array( $this, 'delete_post' ), 10, 2 );

		// users
		add_action( 'user_register', array( $this, 'user_register' ), 10, 2 );
		add_action( 'wp_login', array( $this, 'user_login' ), 10, 2 );
		add_action( 'delete_user', array( $this, 'delete_user' ), 10, 3 );
		add_filter( 'registration_errors', array( $this, 'pre_user_registered' ), 10, 3 ); // before user is registered

		// woocommerce
		add_action( 'woocommerce_update_product', array( $this, 'woocommerce_new_product' ), 10, 2 );
		add_action( 'woocommerce_new_order', array( $this, 'woocommerce_new_order' ), 10, 2 );
		add_action( 'woocommerce_order_status_changed', array( $this, 'woocommerce_order_status_changed' ), 10, 4 );

		// cf7
		// add_filter( 'wpcf7_validate', array( $this, 'contact_form_7_test' ), 10, 2 );
		// add_filter( 'wpcf7_validate_select*', array( $this, 'contact_form_7_test' ), 10, 2 );
		add_action( 'wpcf7_before_send_mail', array( $this, 'contact_form_7' ), 1, 3 );
		add_action( 'wp_print_footer_scripts', array( $this, 'dd_add_cf7_redirect' ) );

		// gravity forms
		add_filter( 'gform_validation', array( $this, 'gravity_forms_before' ), 10, 99999 );
		add_filter( 'gform_confirmation', array( $this, 'gravity_forms' ), 10, 4 );

		// wpforms
		add_filter( 'wpforms_frontend_confirmation_message', array( $this, 'wpforms' ), 10, 4 );
		add_filter( 'wpforms_process_complete', array( $this, 'wpforms_redirect' ), 10, 4 );

		// jetformbuilder
		add_action( 'jet-form-builder/custom-action/wpgetapi', array( $this, 'jet_form_builder' ), 10, 2 );
		add_action( 'jet-form-builder/custom-action/wpgetapi1', array( $this, 'jet_form_builder' ), 10, 2 );
		add_action( 'jet-form-builder/custom-action/wpgetapi2', array( $this, 'jet_form_builder' ), 10, 2 );
		add_action( 'jet-form-builder/custom-action/wpgetapi3', array( $this, 'jet_form_builder' ), 10, 2 );
		add_action( 'jet-form-builder/custom-action/wpgetapi4', array( $this, 'jet_form_builder' ), 10, 2 );
		add_action( 'jet-form-builder/custom-action/wpgetapi5', array( $this, 'jet_form_builder' ), 10, 2 );

		// formidable forms
		add_action( 'frm_after_create_entry', array( $this, 'formidable_forms' ), 10, 2 );

		// elementor forms
		add_action( 'elementor_pro/forms/new_record', array( $this, 'elementor_forms' ), 10, 2 );

		// fluent forms
		add_action( 'fluentform/submission_inserted', array( $this, 'fluent_forms' ), 10, 3 );

		// WSForms
		add_action( 'wsf_submit_post_complete', array( $this, 'ws_forms' ), 10, 1 );

		// Ultimate Member
		add_action( 'um_submit_form_login', array( $this, 'ultimatemember' ), 10, 1 );
		add_action( 'um_submit_form_register', array( $this, 'ultimatemember' ), 10, 1 );
		add_action( 'um_submit_form_profile', array( $this, 'ultimatemember' ), 10, 1 );

		// Ninja Forms
		add_filter( 'ninja_forms_post_run_action_type_successmessage', array( $this, 'ninja_forms' ), 10, 1 );

		// pmp
		add_action( 'pmpro_after_checkout', array( $this, 'paid_memberships_pro_after_checkout' ), 10, 2 );

		// edd
		add_action( 'edd_complete_purchase', array( $this, 'easy_digital_downloads_complete_purchase' ), 10, 1 );
	}


	/**
	 * CF7 submission.
	 *
	 */
	public function contact_form_7( $contact_form, &$abort, $submission ) {

		$form_id     = $contact_form->id();
		$form        = $contact_form->get_properties();
		$posted_data = $submission->get_posted_data();
		$result      = null;
		$args        = array();

		$action = __FUNCTION__ . '_' . $form_id;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( $endpoints ) {

			foreach ( $endpoints as $i => $the_endpoint ) {

				$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

				if ( ! $endpoint || $endpoint['actions'] !== $action ) {
					continue;
				}

				// pass the functions args
				$data = array(
					'posted_data' => $posted_data,
					'entry_id'    => $form_id,
					'ip_address'  => $this->get_ip(),
				);

				// make sure the array is array and not objects
				$args['action_args'] = $data;

				// set our output to be the api response and and response code
				$this->output_api_response( $action );
				$api_data = $this->call_endpoint( $the_endpoint, $args, $action );

				// so we can do validation
				if ( isset( $endpoint['forms_validation'] ) && $endpoint['forms_validation'] == 'before' ) {

					$is_valid = $this->is_form_valid( $endpoint, $api_data );

					if ( ! $is_valid ) {

						// remove action to save if Contact Form CFDB7 is installed
						remove_action( 'wpcf7_before_send_mail', 'cfdb7_before_send_mail' );

						$message = isset( $endpoint['forms_validation_message'] ) ? $endpoint['forms_validation_message'] : 'There was an error. Check all fields are correct.';
						$abort   = true;
						$submission->set_response( $message );

					}
				}

				// display data from API response
				// sets $this->confirmation or $this->redirect
				$this->form_display( $the_endpoint, $api_data, $action );

			}
		}

		if ( $this->redirect ) {
			$submission->add_result_props( array( 'wpgetapi_cf7_redirect' => $this->redirect ) );
			return;
		}

		// get the form properties
		$properties = $contact_form->get_properties();

		$this->confirmation = $this->confirmation ? $this->confirmation : $properties['messages']['mail_sent_ok'];

		// set the success message
		$properties['messages']['mail_sent_ok'] = apply_filters( 'wpgetapi_action_contact_form_7_display', $this->confirmation, $api_data, $args );

		$contact_form->set_properties( $properties );
	}


	/**
	 * Gravity Forms before submission.
	 *
	 */
	public function gravity_forms_before( $validation_result ) {

		if ( isset( $validation_result['is_valid'] ) && ! $validation_result['is_valid'] ) {
			return $validation_result;
		}

		$form = $validation_result['form'];

		$action = 'gravity_forms_' . $form['id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return $validation_result;
		}

		$args = array();

		foreach ( $endpoints as $i => $the_endpoint ) {

			$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

			if ( ! $endpoint || $endpoint['actions'] !== $action ) {
				continue;
			}

			// sends to API 'before' form submission
			// the form has not yet been submitted
			if ( ! isset( $endpoint['forms_validation'] ) || $endpoint['forms_validation'] !== 'before' ) {
				continue;
			}

			// pass the functions args
			$entry                               = GFFormsModel::get_current_lead();
			$args['action_args']                 = $entry;
			$args['action_args']['date_created'] = $form['date_created'];

			// set our output to be the api response and and response code
			$this->output_api_response( $action );
			$api_data = $this->call_endpoint( $the_endpoint, $args, $action );

			$is_valid = $this->is_form_valid( $endpoint, $api_data );

			// do our validation field if not valid
			if ( ! $is_valid ) {

				$validate_field = isset( $endpoint['forms_validation_field'] ) ? $endpoint['forms_validation_field'] : null;
				$message        = isset( $endpoint['forms_validation_message'] ) ? $endpoint['forms_validation_message'] : 'There was an error. Check all fields are correct.';

				foreach ( $form['fields'] as &$field ) {
					if ( (int) $field->id == (int) $validate_field ) {
						$field->failed_validation  = true;
						$field->validation_message = $message;
						break;
					}
				}

				if ( $field->failed_validation ) {
					//Assign modified $form object back to the validation result
					$validation_result['is_valid'] = false;
					$validation_result['form']     = $form;
					return $validation_result;
				}
			}

			// display data from API response
			// sets $this->confirmation or $this->redirect
			$this->form_display( $the_endpoint, $api_data, $action );

		}

		// set the confirmation
		add_filter(
			'gform_confirmation',
			function ( $confirmation, $form, $entry ) {

				if ( $this->redirect ) {
					wp_redirect( $this->redirect );
					exit;
				}

				return $this->confirmation ? $this->confirmation : $confirmation;
			},
			11,
			3
		);

		return $validation_result;
	}

	/**
	 * Gravity Forms submission.
	 *
	 */
	public function gravity_forms( $confirmation, $form, $entry, $ajax ) {

		$action = __FUNCTION__ . '_' . $form['id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return $confirmation;
		}

		$args = array();

		foreach ( $endpoints as $i => $the_endpoint ) {

			$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

			if ( ! $endpoint || $endpoint['actions'] !== $action ) {
				continue;
			}

			// sends to API 'after' form submission
			// the form has already been submitted
			if ( ! isset( $endpoint['forms_validation'] ) || $endpoint['forms_validation'] !== 'after' ) {
				continue;
			}

			// pass the functions args
			$args['action_args'] = $entry;

			// get our display option
			// null if not set - likely means older version
			$display = isset( $endpoint['forms_display'] ) ? $endpoint['forms_display'] : null;

			// set our output to be the api response and and response code
			$this->output_api_response( $action );
			$api_data = $this->call_endpoint( $the_endpoint, $args, $action );

			// display data from API response
			// sets $this->confirmation or $this->redirect
			$this->form_display( $the_endpoint, $api_data, $action );

		}

		// set the confirmation
		add_filter(
			'gform_confirmation',
			function ( $confirmation, $form, $entry ) {

				if ( $this->redirect ) {
					wp_redirect( $this->redirect );
					exit;
				}

				return $this->confirmation ? $this->confirmation : $confirmation;
			},
			11,
			3
		);

		return $confirmation;
	}




	/**
	 * WPForms - Filters confirmation message output site-wide.
	 *
	 * @link   https://wpforms.com/developers/wpforms_frontend_confirmation_message/
	 *
	 * @param  string   $message     Confirmation message including Smart Tags.
	 * @param  array    $form_data   Form data and settings.
	 * @param  array    $fields      Sanitized field data.
	 * @param  int      $entry_id    Entry ID.
	 *
	 * @return string
	 */
	public function wpforms( $message, $form_data, $fields, $entry_id ) {

		$action = __FUNCTION__ . '_' . $form_data['id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return $message;
		}

		foreach ( $endpoints as $i => $the_endpoint ) {

			$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

			if ( ! $endpoint || $endpoint['actions'] !== $action ) {
				continue;
			}

			// don't process if doing redirect
			if ( ! isset( $endpoint['forms_display'] ) || $endpoint['forms_display'] == 'redirect' ) {
				continue;
			}

			// add the entry id to entry data
			$data               = $fields;
			$data['entry_id']   = $entry_id;
			$data['id']         = $form_data['id'];
			$data['ip_address'] = $this->get_ip();

			// jsut set them as values as well
			if ( $fields ) {
				foreach ( $fields as $id => $field ) {
					$data['fields'][ $id ] = $field['value'];
				}
			}

			// pass the functions args
			$args['action_args'] = $data;

			// get our display option
			// null if not set - likely means older version
			$display = isset( $endpoint['forms_display'] ) ? $endpoint['forms_display'] : null;

			// set our output to be the api response and and response code
			$this->output_api_response( $action );
			$api_data = $this->call_endpoint( $the_endpoint, $args, $action );

			// display data from API response
			// sets $this->confirmation or $this->redirect
			$this->form_display( $the_endpoint, $api_data, $action );

		}

		// set the message
		$message = $this->confirmation ? $this->confirmation : $message;
		$message = apply_filters( 'wpgetapi_action_wpforms_display', $message, $api_data, $args['action_args'] );

		return $message;
	}




	/**
	 * Action that fires during form entry processing, after initial field validation has passed.
	 *
	 * Redirect to the Sucess and Failed url based on the WPGetAPI endpoint settings.
	 *
	 * @link https://wpforms.com/developers/wpforms_process/
	 *
	 * @param array $fields    Sanitized entry field. values/properties.
	 * @param array $entry     Original $_POST global.
	 * @param array $form_data Form data and settings.
	 * @param int   $entry_id  The Entry Id
	 * @return void
	 */
	public function wpforms_redirect( $form_fields, $entry, $form_data, $entry_id ) {

		$action = 'wpforms_' . $form_data['id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $the_endpoint ) {

			$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

			if ( ! $endpoint || $endpoint['actions'] !== $action ) {
				continue;
			}

			// only process if doing redirect
			if ( ! isset( $endpoint['forms_display'] ) || $endpoint['forms_display'] !== 'redirect' ) {
				continue;
			}

			// add the entry id to entry data
			$data               = $form_fields;
			$data['entry_id']   = $entry_id;
			$data['id']         = $form_data['id'];
			$data['ip_address'] = $this->get_ip();

			// jsut set them as values as well
			if ( $form_fields ) {
				foreach ( $form_fields as $id => $field ) {
					$data['fields'][ $id ] = $field['value'];
				}
			}

			// pass the functions args
			$args['action_args'] = $data;

			// get our display option
			// null if not set - likely means older version
			$display = isset( $endpoint['forms_display'] ) ? $endpoint['forms_display'] : null;

			// set our output to be the api response and and response code
			$this->output_api_response( $action );
			$api_data = $this->call_endpoint( $the_endpoint, $args, $action );

			// display data from API response
			// sets $this->confirmation or $this->redirect
			$this->form_display( $the_endpoint, $api_data, $action );

		}

		if ( $this->redirect ) {
			wp_redirect( $this->redirect );
			exit;
		}
	}

	/**
	 * Action on submit form
	 *
	 * https://docs.ultimatemember.com/article/1281-umsubmitformmode
	 *
	 * @param array $post Submitted data.
	 * @return void
	 */
	public function ultimatemember( $post ) {

		if ( ! $post ) {
			return;
		}

		// Stop execution if the Ultimate Member form has error
		if ( UM()->form()->count_errors() > 0 ) {
			return;
		}

		$action = __FUNCTION__ . '_' . $post['form_id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $the_endpoint ) {

			$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = $post;

			// set our output to be the api response and and response code
			$this->output_api_response( $action );
			$api_data = $this->call_endpoint( $the_endpoint, $args, $action );

			if ( isset( $endpoint['forms_validation'] ) && 'before' == $endpoint['forms_validation'] ) {

				$is_valid = $this->is_form_valid( $endpoint, $api_data );

				if ( ! $is_valid ) {

					$validate_field = isset( $endpoint['forms_validation_field'] ) ? $endpoint['forms_validation_field'] : null;
					$message        = isset( $endpoint['forms_validation_message'] ) ? $endpoint['forms_validation_message'] : __( 'There was an error.', 'wpgetapi-extras' ) . __( 'Check all fields are correct.', 'wpgetapi-extras' );

					$this->ultimatemember_error_message = $message;

					if ( $validate_field ) {
						// if the Error field id is set then display error message on that field to form submission
						UM()->form()->add_error( $validate_field, $message );
					} else {
						UM()->form()->add_error( 'wpgetapi_um_error', $message ); // Add an error message to stop form submission.
					}

					/**
					 * Skip the display error message hook when the Login Form is submitted or
					 * $validate_field is set because the error message by default displays on
					 * on the Login Form and the error message is set on the field_id.
					 */
					$posted_data = UM()->query()->post_data( $post['form_id'] );
					if ( 'login' != $posted_data['mode'] && ! $validate_field ) {
						add_action( 'um_before_form', array( $this, 'wpgetapi_um_before_form_error_message' ), 500 );
					}
				}
			}

			if ( isset( $endpoint['forms_display'] ) && 'redirect' == $endpoint['forms_display'] && '' == $this->ultimatemember_error_message ) {
				// display data from API response
				// sets $this->redirect
				$this->form_display( $the_endpoint, $api_data, $action );
			}

			if ( $this->redirect ) {
				add_action( 'um_on_login_before_redirect', array( $this, 'wpgetapi_um_redirect' ), 10 );
				add_action( 'um_registration_complete', array( $this, 'wpgetapi_um_redirect' ), 10 );
				add_action( 'um_user_after_updating_profile', array( $this, 'wpgetapi_um_redirect' ), 10 );
			}
		}
	}

	/**
	 * Display an error message for the Ultimate Members form.
	 *
	 * @link https://docs.ultimatemember.com/article/1059-umbeforeform
	 *
	 * @return string HTML of error message.
	 */
	public function wpgetapi_um_before_form_error_message() {

		if ( ! empty( $this->ultimatemember_error_message ) ) {
			echo '<p class="um-notice err"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . $this->ultimatemember_error_message . '</p>';
		}
	}

	/**
	 * Ultimate Member redirect to url.
	 *
	 * @link https://docs.ultimatemember.com/article/1188-umonloginbeforeredirect
	 * @link https://docs.ultimatemember.com/article/1234-umregistrationcomplete
	 * @link https://docs.ultimatemember.com/article/1290-umuserafterupdatingprofile
	 *
	 * @return void
	 */
	public function wpgetapi_um_redirect() {
		if ( $this->redirect ) {
			wp_redirect( $this->redirect );
			exit;
		}
	}

	/**
	 * For maybe redirecting CF7
	 *
	 */
	public function dd_add_cf7_redirect() {

		if ( ! function_exists( 'is_plugin_active' ) ) {
			include_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {
			?>
			<script type="text/javascript">
				document.addEventListener('wpcf7mailsent', function (event) {
					if( event.detail.apiResponse.wpgetapi_cf7_redirect )
						location.href = event.detail.apiResponse.wpgetapi_cf7_redirect;
				}, false);
			</script>
			<?php
		}
	}


	/**
	 * Validate a form
	 *
	 */
	public function is_form_valid( $endpoint, $api_data ) {

		$is_valid     = true;
		$validate     = isset( $endpoint['forms_validation_type'] ) ? $endpoint['forms_validation_type'] : null;
		$validate_val = isset( $endpoint['forms_validation_value'] ) ? $endpoint['forms_validation_value'] : null;

		// if we are checking for 200 response code
		if ( $validate == 'send_if_200' && ( $api_data['response_code'] < 200 || $api_data['response_code'] > 299 ) ) {
			$is_valid = false;
		} elseif ( $validate == 'send_if_api_response' ) { // Only submit if API response contains...
			$is_valid = $this->recursive_array_search( $validate_val, $api_data['api_result'] ) ? true : false;
		} elseif ( $validate == 'dont_send_if_api_response' ) { // Don't submit if API response contains...
			$is_valid = $this->recursive_array_search( $validate_val, $api_data['api_result'] ) ? false : true;
		}

		return $is_valid;
	}


	/**
	 * Display after form success
	 *
	 */
	public function form_display( $the_endpoint, $api_data, $action ) {

		$endpoint = isset( $the_endpoint['endpoint'] ) ? $the_endpoint['endpoint'] : null;

		// display data from API response
		$display = isset( $endpoint['forms_display'] ) ? $endpoint['forms_display'] : null;
		if ( $display && $display == 'api_response' ) {

			if ( isset( $endpoint['forms_display_keys'] ) ) {

				// get all our curly braces
				preg_match_all( '/{(.*?)}/', $endpoint['forms_display_keys'], $matches );

				if ( $matches[0] ) {
					$result             = $this->nested_data( $api_data['api_result'], $matches[0] );
					$this->confirmation = is_array( $result ) ? json_encode( $result ) : $result;
				}
			} else {

				$this->confirmation = is_array( $api_data['api_result'] ) ? json_encode( $api_data['api_result'] ) : $api_data['api_result'];

			}
		}

		if ( $display && $display == 'redirect' ) {

			$success_contains = false;
			$error_contains   = false;
			$success_value    = isset( $endpoint['forms_success_value'] ) ? $endpoint['forms_success_value'] : null;
			$success_redirect = isset( $endpoint['forms_success_url'] ) ? esc_url_raw( $endpoint['forms_success_url'] ) : null;
			$error_value      = isset( $endpoint['forms_error_value'] ) ? $endpoint['forms_error_value'] : null;
			$error_redirect   = isset( $endpoint['forms_error_url'] ) ? esc_url_raw( $endpoint['forms_error_url'] ) : null;

			// success redirect
			if ( $success_value ) {
				// first look for response code
				if ( (string) $success_value === (string) $api_data['response_code'] ) {
					$this->redirect = $success_redirect;
				} else {
					$success_contains = $this->recursive_array_search( $success_value, $api_data['api_result'] );
					if ( $success_contains ) {
						$this->redirect = $success_redirect;
					}
				}
			}

			// error redirect
			if ( $error_value ) {
				// first look for response code
				if ( (string) $error_value === (string) $api_data['response_code'] ) {
					$this->redirect = $error_redirect;
				} else {
					$error_contains = $this->recursive_array_search( $error_value, $api_data['api_result'] );
					if ( $error_contains ) {
						$this->redirect = $error_redirect;
					}
				}
			}
		}

		if ( $display && $display == 'wpdatatables' ) {

			if ( isset( $endpoint['wpdatatables'] ) ) {

				//$this->update_wpdatatable( $action );
				$this->confirmation = do_shortcode( '[wpdatatable id=' . $endpoint['wpdatatables'] . ']' );

			}
		}
	}


	/**
	 * Update a wpdatatable
	 *
	 */
	public function update_wpdatatable( $action ) {
		// pp($action);
		// die;

		// add_filter( 'wpgetapi_' . $action . '_return_value', function( $return, $result, $args, $values_sent, $endpoint, $response_code ) {
		//     pp($endpoint);
		//     die;
		//     $wpdatatables = new WpGetApi_Extras_Wpdatatables();
		//     $wpdatatables->update_file_contents_from_action( $result );

		// });

		// // if we can't get the id
		// if( ! $table_id )
		//     return;

		// $endpoint = $wpdatatables->look_for_table( $table_id );
		// if( ! $endpoint )
		//     return;
		//$wpdatatables = new WpGetApi_Extras_Wpdatatables();
		// $api_data = $wpdatatables->update_file_contents( $endpoint, $table_id );
	}


	/**
	 * EDD complete purchase.
	 *
	 * https://easydigitaldownloads.com/docs/edd_complete_purchase/
	 *
	 * @param int  $payment_id
	 *
	 */
	public function easy_digital_downloads_complete_purchase( $payment_id ) {

		if ( ! $payment_id ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// Basic payment meta
			$payment_meta = edd_get_payment_meta( $payment_id );

			// pass the functions args & make sure the array is array and not objects
			$args['action_args']               = $payment_meta;
			$args['action_args']['payment_id'] = $payment_id;

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Paid memberships pro after checkout.
	 *
	 * https://www.paidmembershipspro.com/hook/pmpro_after_checkout/
	 *
	 * @param int  $user_id
	 * @param object  $morder.
	 *
	 */
	public function paid_memberships_pro_after_checkout( $user_id, $morder ) {

		if ( ! $user_id || ! $morder ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			$form_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			// pass the functions args & make sure the array is array and not objects
			$args['action_args']             = $form_data;
			$args['action_args']['user_id']  = $morder->id;
			$args['action_args']['order_id'] = $morder->id;
			$args['action_args']['gateway']  = $morder->gateway;

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Elementor Forms submission.
	 *
	 * https://developers.elementor.com/docs/hooks/forms/#form-submission
	 *
	 * @param int  $record
	 * @param int  $handler
	 *
	 */
	public function elementor_forms( $record, $handler ) {

		if ( ! $record || ! $handler ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			$form_name = $record->get_form_settings( 'form_name' );

			if ( $endpoint['endpoint']['elementor_form_name'] !== $form_name ) {
				continue;
			}

			$raw_fields = $record->get( 'fields' );
			$fields     = array();
			foreach ( $raw_fields as $id => $field ) {
				$fields[ $id ] = $field['value'];
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = $fields;

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Fluent Forms submission.
	 *
	 * https://fluentforms.com/docs/fluentform_submission_inserted/
	 *
	 * @param int  $record
	 * @param int  $handler
	 *
	 */
	public function fluent_forms( $entry_id, $form_data, $form ) {

		if ( ! $entry_id || ! $form_data ) {
			return;
		}

		$action = __FUNCTION__ . '_' . $form->id;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			//$form_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS  );

			// pass the functions args & make sure the array is array and not objects
			$args['action_args']             = $form_data;
			$args['action_args']['entry_id'] = $entry_id;

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Formidable Forms submission.
	 *
	 * https://formidableforms.com/knowledgebase/frm_after_create_entry/
	 *
	 * @param int  $entry_id
	 * @param int  $form_id
	 *
	 */
	public function formidable_forms( $entry_id, $form_id ) {

		if ( ! $entry_id || ! $form_id ) {
			return;
		}

		$action = __FUNCTION__ . '_' . $form_id;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			$form_data = filter_input_array( INPUT_POST, FILTER_SANITIZE_FULL_SPECIAL_CHARS );

			// pass the functions args & make sure the array is array and not objects
			$args['action_args']             = $form_data;
			$args['action_args']['entry_id'] = $entry_id;

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}

	/**
	 * JetFormBuilder submission.
	 *
	 *
	 * @param array  $action_handler
	 * @param array  $request     the form entry.
	 *
	 */
	public function jet_form_builder( $request, $action_handler ) {

		if ( ! $request || ! $action_handler ) {
			return;
		}

		$action = __FUNCTION__ . '_' . $request['__form_id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = $request;

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}

	/**
	 * WSForms submission.
	 *
	 * https://wsform.com/knowledgebase/wsf_submit_post_complete/
	 *
	 * @param object  $submit
	 *
	 */
	public function ws_forms( $submit ) {

		if ( ! $submit ) {
			return;
		}

		$action = __FUNCTION__ . '_' . $submit->form_id;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			$raw_fields = $submit->meta;
			$fields     = array();
			foreach ( $raw_fields as $id => $field ) {
				if ( ! empty( $field['value'] ) ) {
					$fields[ $id ] = $field['value'];
				}
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = $fields;

			$this->call_endpoint( $endpoint, $args, $action );
		}
	}

	/**
	 * Ninja forms submission.
	 *
	 * @param  array $form_data
	 * @return array
	 */
	public function ninja_forms( $form_data ) {

		if ( ! $form_data ) {
			return;
		}

		$action = __FUNCTION__ . '_' . $form_data['form_id'];

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			$raw_fields = $form_data['fields'];
			$fields     = array();
			foreach ( $raw_fields as $field ) {
				if ( ! empty( $field['value'] ) ) {
					$fields[ $field['key'] ] = $field['value'];
				}
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = $fields;

			// set our output to be the api response and and response code
			$this->output_api_response( $action );
			$api_data = $this->call_endpoint( $endpoint, $args, $action );

			// display data from API response
			// sets $this->confirmation or $this->redirect
			$this->form_display( $endpoint, $api_data, $action );
		}

		if ( $this->redirect ) {
			unset( $form_data['actions']['success_message'] ); // unset confirmation message if it's set from the form settings.
			$form_data['actions']['redirect'] = $this->redirect;
		}

		if ( $this->confirmation ) {
			unset( $form_data['actions']['redirect'] ); // unset redirection if it's set from the form settings.
			$form_data['actions']['success_message'] = $this->confirmation;
		}

		return $form_data;
	}

	/**
	 * User login.
	 * Any time a user logs in.
	 *
	 * https://developer.wordpress.org/reference/hooks/wp_login/
	 *
	 * @param string  $user_login Username.
	 * @param object  $user WP_User object of the logged-in user.
	 */
	public function user_login( $user_login, $user ) {

		if ( ! $user_login || ! $user ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = maybe_unserialize( json_decode( json_encode( $user ), true ) );

			// add meta data
			$args['action_args']['meta'] = $this->format_meta( $user->ID );

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Filters the errors encountered when a new user is being registered.
	 *
	 * https://core.trac.wordpress.org/browser/tags/6.3/src/wp-includes/user.php#L3386
	 *
	 * The filtered WP_Error object may, for example, contain errors for an invalid
	 * or existing username or email address. A WP_Error object should always be returned,
	 * but may or may not contain errors.
	 *
	 * @param WP_Error $errors               A WP_Error object containing any errors encountered
	 *                                       during registration.
	 * @param string   $sanitized_user_login User's username after it has been sanitized.
	 * @param string   $user_email           User's email.
	 */
	public function pre_user_registered( $errors, $sanitized_user_login, $user_email ) {

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return $errors;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args']['user_login'] = $sanitized_user_login;
			$args['action_args']['user_email'] = $user_email;
			$args['action_args']['errors']     = maybe_unserialize( json_decode( json_encode( $errors ), true ) );

			if ( is_wp_error( $errors ) && ! empty( $errors->errors ) ) {
				return $errors;
			}

			$errors = $this->call_endpoint( $endpoint, $args, $action );

		}

		return $errors;
	}



	/**
	 * Delete user.
	 * Fires immediately before a user is deleted from the database.
	 *
	 * https://developer.wordpress.org/reference/hooks/delete_user/
	 *
	 * @param string  $id Username.
	 * @param string  $reassign ID of the user to reassign posts and links to. Default null, for no reassignment.
	 * @param object  $user WP_User object of the user to delete..
	 */
	public function delete_user( $id, $reassign, $user ) {

		if ( ! $id || ! $user ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = maybe_unserialize( json_decode( json_encode( $user ), true ) );

			// add meta data
			$args['action_args']['meta'] = $this->format_meta( $user->ID );

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}



	/**
	 * After a user is registered.
	 *
	 * https://developer.wordpress.org/reference/hooks/user_register/
	 *
	 * @param int  $user_id User ID.
	 * @param array  $userdata The raw array of data passed to wp_insert_user() .
	 */
	public function user_register( $user_id, $userdata ) {

		if ( ! $user_id || ! $userdata ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args']['data']       = maybe_unserialize( json_decode( json_encode( $userdata ), true ) );
			$args['action_args']['data']['ID'] = $user_id;

			// add meta data
			$args['action_args']['data']['meta'] = $this->format_meta( $user_id );

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}





	/**
	 * New WooCommerce product.
	 * Any time a new product is created.
	 *
	 * https://woocommerce.github.io/code-reference/files/woocommerce-includes-data-stores-class-wc-product-data-store-cpt.html#source-view.275
	 *
	 * @param string  $product_id ID of product.
	 * @param array $product Product.
	 */
	public function woocommerce_new_product( $product_id, $product ) {

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// this action firest twice, so we need this
			global $previous_product_id;
			if ( $previous_product_id === $product_id ) {
				return;
			}

			$previous_product_id = $product_id;

			$product_data = $product->get_data();

			// only on publish
			if ( $product_data['status'] !== 'publish' ) {
				return;
			}

			// only newly created (adding 1 second)
			if ( $product->get_date_modified()->getTimestamp() > ( $product->get_date_created()->getTimestamp() + 2 ) ) {
				return;
			}

			// pass the functions args

			// modify the post argument
			$args['action_args'][1] = $product_data;
			if ( isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
				$args['action_args']['acf'] = $_POST['acf'];
			}

			// make sure the array is array and not objects
			$args['action_args'] = json_decode( json_encode( $args['action_args'] ), true );

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * New post/custom post published.
	 * Any time a new post of the chosen post type is published.
	 *
	 * https://developer.wordpress.org/reference/hooks/transition_post_status/
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function new_post_published( $new_status, $old_status, $post ) {

		// don't run when landing on the post-new.php screen
		if ( $new_status === 'auto-draft' && $old_status === 'new' ) {
			return;
		}

		// this ignores when a post is publish to publish
		if ( $old_status == 'publish' ) {
			return;
		}

		// this ignores when post is not publish
		if ( $new_status !== 'publish' ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// if post type doesn't match, bail
			if ( ! in_array( $post->post_type, $endpoint['endpoint']['post_type'] ) ) {
				return;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = json_decode( json_encode( $post ), true );

			// add meta data
			$args['action_args']['meta']       = $this->format_meta( $post->ID );
			$args['action_args']['old_status'] = $old_status;

			if ( isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
				$args['action_args']['acf'] = $_POST['acf'];
			}

			// get taxonomies and terms of post
			$taxs = get_post_taxonomies( $post->ID );
			if ( $taxs ) {
				foreach ( $taxs as $i => $tax ) {
					$terms = get_the_terms( $post->ID, $tax );
					if ( $terms ) {
						$args['action_args'][ $tax ] = json_decode( json_encode( $terms ), true );
					}
				}
			}

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Transition post status
	 * Any time the post status of chosen post type changes from old to new status.
	 *
	 * https://developer.wordpress.org/reference/hooks/transition_post_status/
	 *
	 * @param string  $new_status New post status.
	 * @param string  $old_status Old post status.
	 * @param WP_Post $post       Post object.
	 */
	public function transition_post_status( $new_status, $old_status, $post ) {

		// don't run when landing on the post-new.php screen
		if ( $new_status === 'auto-draft' && $old_status === 'new' ) {
			return;
		}

		// this ignores when a post is updated (publish to publish)
		// and any other time it has same old and new status
		if ( $old_status == $new_status ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// if post type doesn't match, bail
			if ( ! in_array( $post->post_type, $endpoint['endpoint']['post_type'] ) ) {
				return;
			}

			// if we have the statuses we want, then do it
			if ( ( in_array( 'any', $endpoint['endpoint']['old_post_status'] ) || in_array( $old_status, $endpoint['endpoint']['old_post_status'] ) ) ||
				( in_array( 'any', $endpoint['endpoint']['new_post_status'] ) || in_array( $new_status, $endpoint['endpoint']['new_post_status'] ) )
			) {

				// pass the functions args & make sure the array is array and not objects
				$args['action_args'] = json_decode( json_encode( $post ), true );

				// add meta data
				$args['action_args']['meta']       = $this->format_meta( $post->ID );
				$args['action_args']['old_status'] = $old_status;

				if ( isset( $_POST['acf'] ) && is_array( $_POST['acf'] ) ) {
					$args['action_args']['acf'] = $_POST['acf'];
				}

				// get taxonomies and terms of post
				$taxs = get_post_taxonomies( $post->ID );
				if ( $taxs ) {
					foreach ( $taxs as $i => $tax ) {
						$terms = get_the_terms( $post->ID, $tax );
						if ( $terms ) {
							$args['action_args'][ $tax ] = json_decode( json_encode( $terms ), true );
						}
					}
				}

				$this->call_endpoint( $endpoint, $args, $action );

			}
		}
	}


	/**
	 * Delete post.
	 * Fires immediately before a post is deleted from the database.
	 *
	 * https://developer.wordpress.org/reference/hooks/delete_post/
	 *
	 * @param int  $post_id Post ID.
	 * @param object  $post Post object.
	 */
	public function delete_post( $post_id, $post ) {

		if ( ! $post_id || ! $post ) {
			return;
		}

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// pass the functions args & make sure the array is array and not objects
			$args['action_args'] = json_decode( json_encode( $post ), true );

			// add meta data
			$args['action_args']['meta'] = $this->format_meta( $post->ID );

			// get taxonomies and terms of post
			$taxs = get_post_taxonomies( $post->ID );
			if ( $taxs ) {
				foreach ( $taxs as $i => $tax ) {
					$terms = get_the_terms( $post->ID, $tax );
					if ( $terms ) {
						$args['action_args'][ $tax ] = json_decode( json_encode( $terms ), true );
					}
				}
			}

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Woo - new order
	 *
	 * https://woocommerce.github.io/code-reference/files/woocommerce-includes-data-stores-class-wc-order-data-store-cpt.html#source-view.89
	 *
	 * @param integer   $order_id the ID of the order.
	 * @param string    $old_status Old order status.
	 * @param string    $new_status New order status.
	 */
	public function woocommerce_new_order( $order_id, $order ) {

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			// modify the order argument
			$args['action_args'] = $order->get_base_data();

			// add our items
			$args['action_args']['order_type']     = $order->get_type();
			$args['action_args']['line_items']     = $this->woo_get_order_line_items( $order_id, $order );
			$args['action_args']['shipping_items'] = $this->woo_get_order_shipping_items( $order_id, $order );
			$args['action_args']['coupon_items']   = $this->woo_get_order_coupon_items( $order_id, $order );
			$args['action_args']['fee_items']      = $this->woo_get_order_fee_items( $order_id, $order );
			$args['action_args']['meta_data']      = $this->woo_get_order_meta_data( $order_id, $order );

			// make sure the array is array and not objects
			$args['action_args'] = json_decode( json_encode( $args['action_args'] ), true );

			// filter our action data before sending
			$args['action_args'] = apply_filters( 'wpgetapi_' . $action . '_action_data', $args['action_args'] );

			$this->call_endpoint( $endpoint, $args, $action );

		}
	}


	/**
	 * Woo - order status changed
	 *
	 * https://woocommerce.github.io/code-reference/files/woocommerce-includes-class-wc-order.html#source-view.374
	 *
	 * @param integer   $order_id the ID of the order.
	 * @param string    $old_status Old order status.
	 * @param string    $new_status New order status.
	 */
	public function woocommerce_order_status_changed( $order_id, $old_status, $new_status, $order ) {

		$action = __FUNCTION__;

		// check for this action within an endpoint and return the endpoint data
		$endpoints = $this->look_for_action( $action );

		// if we don't have this action, bail
		if ( ! $endpoints ) {
			return;
		}

		// this ignores when a post is updated (publish to publish)
		// and any other time it has same old and new status
		if ( $old_status == $new_status ) {
			return;
		}

		foreach ( $endpoints as $i => $endpoint ) {

			if ( ! isset( $endpoint['endpoint'] ) || $endpoint['endpoint']['actions'] !== $action ) {
				continue;
			}

			$old_status = strpos( $old_status, 'wc-' ) !== false ? $old_status : 'wc-' . $old_status;
			$new_status = strpos( $new_status, 'wc-' ) !== false ? $new_status : 'wc-' . $new_status;

			// if we have the statuses we want, then do it
			if ( ( in_array( 'any', $endpoint['endpoint']['old_order_status'] ) ||
				in_array( $old_status, $endpoint['endpoint']['old_order_status'] ) ) &&

				( in_array( 'any', $endpoint['endpoint']['new_order_status'] ) ||
				in_array( $new_status, $endpoint['endpoint']['new_order_status'] ) )
			) {

				// modify the order argument
				$args['action_args'] = $order->get_base_data();

				// add our items
				$args['action_args']['order_type']     = $order->get_type();
				$args['action_args']['line_items']     = $this->woo_get_order_line_items( $order_id, $order );
				$args['action_args']['shipping_items'] = $this->woo_get_order_shipping_items( $order_id, $order );
				$args['action_args']['coupon_items']   = $this->woo_get_order_coupon_items( $order_id, $order );
				$args['action_args']['fee_items']      = $this->woo_get_order_fee_items( $order_id, $order );
				$args['action_args']['meta_data']      = $this->woo_get_order_meta_data( $order_id, $order );

				// make sure the array is array and not objects
				$args['action_args'] = json_decode( json_encode( $args['action_args'] ), true );

				// filter our action data before sending
				$args['action_args'] = apply_filters( 'wpgetapi_' . $action . '_action_data', $args['action_args'] );

				$this->call_endpoint( $endpoint, $args, $action );

			}
		}
	}



	// gets woocommerce order meta data
	public function woo_get_order_meta_data( $order_id, $order ) {

		$data = array();

		foreach ( $order->get_meta_data() as $object ) {

			$object_array = array_values( (array) $object );
			foreach ( $object_array as $item ) {
				$data[ $item['key'] ] = $item['value'];
			}
		}

		return $data;
	}



	// gets woocommerce order line items
	public function woo_get_order_line_items( $order_id, $order ) {

		$order_items = $order->get_items( 'line_item' );

		$our_items = array();

		if ( $order_items ) {

			$count = 0;
			foreach ( $order_items as $order_item_id => $item ) {

				if ( is_bool( $item ) ) {
					continue;
				}

				$product = $item->get_product() ? $item->get_product()->get_data() : null;

				if ( ! $product ) {
					continue;
				}

				// do meta data
				$meta_data = $product['meta_data'];

				unset( $product['date_created'], $product['date_modified'], $product['upsell_ids'], $product['cross_sell_ids'], $product['reviews_allowed'], $product['post_password'], $product['gallery_image_ids'], $product['downloads'], $product['download_limit'], $product['download_expiry'], $product['rating_counts'], $product['average_rating'], $product['review_count'], $product['menu_order'], $product['virtual'], $product['meta_data'], $product['attributes'] );

				// meta data from actual product
				if ( $meta_data ) {
					foreach ( $meta_data as $i => $meta ) {
						$product['meta_data'][ $meta->key ] = $meta->value;
					}
				}

				// Extra product options For WooCommerce plugin
				if ( isset( $item->legacy_values['thwepof_options'] ) ) {
					foreach ( $item->legacy_values['thwepof_options'] as $key => $meta_item ) {
						$product['meta_data'][ $key ] = $meta_item['value'];
					}
				}

				// meta data from order product
				// such as when a custom field is added to the cart/product page
				$product_order_meta = $item->get_meta_data();
				if ( $product_order_meta ) {
					foreach ( $product_order_meta as $i => $meta ) {
						$meta_item                                 = $meta->get_data();
						$product['meta_data'][ $meta_item['key'] ] = $meta_item['value'];
					}
				}

				// get attributes
				$prod = $item->get_product();
				if ( $prod->get_attributes() !== null ) {

					$prod_attrs   = array();
					$wc_attr_objs = $prod->get_attributes();

					foreach ( $wc_attr_objs as $wc_attr => $wc_term_objs ) {

						if ( ! is_string( $wc_term_objs ) && $wc_term_objs->get_data() !== null ) {

							$wc_term   = $wc_term_objs->get_data();
							$term_name = '';
							$term_id   = isset( $wc_term['options'][0] ) ? $wc_term['options'][0] : null;
							$term      = get_term( $term_id );

							if ( $term !== false && ! is_wp_error( $term ) ) {
								$term_name = $term->name;
							}

							$prod_attrs[ $wc_term['name'] ] = $term_name;

						}
					}

					$product['attributes'] = $prod_attrs;

				}

				$prod = $item->get_product();

				$our_items[ $count ]                  = $product;
				$our_items[ $count ]['image_url']     = wp_get_attachment_image_url( $product['image_id'] );
				$our_items[ $count ]['qty']           = $item->get_quantity();
				$our_items[ $count ]['subtotal']      = $item->get_subtotal();
				$our_items[ $count ]['subtotal_tax']  = $item->get_subtotal_tax();
				$our_items[ $count ]['total']         = $item->get_total();
				$our_items[ $count ]['total_tax']     = $item->get_total_tax();
				$our_items[ $count ]['tax_class']     = $item->get_tax_class();
				$our_items[ $count ]['tax_status']    = $item->get_tax_status();
				$our_items[ $count ]['line_discount'] = $item->get_subtotal() - $item->get_total();

				if ( $our_items[ $count ]['description'] ) {
					$our_items[ $count ]['description'] = mb_strimwidth( $our_items[ $count ]['description'], 0, 300, '... **truncated in the action log only**' );
				}

				++$count;

			}
		}

		$our_items = array_values( $our_items );

		return $our_items;
	}


	// gets woocommerce order shipping items
	public function woo_get_order_shipping_items( $order_id, $order ) {

		$order_items = $order->get_items( 'shipping' );
		$our_items   = array();

		if ( $order_items ) {
			foreach ( $order_items as $order_item_id => $item ) {
				$our_items = $item->get_data();
				unset( $our_items['meta_data'] );
			}
		}

		return $our_items;
	}

	// gets woocommerce order coupon items
	public function woo_get_order_coupon_items( $order_id, $order ) {

		$order_items = $order->get_items( 'coupon' );
		$our_items   = array();

		if ( $order_items ) {
			foreach ( $order_items as $order_item_id => $item ) {
				$our_items = $item->get_data();
				unset( $our_items['meta_data'] );
			}
		}

		return $our_items;
	}

	// gets woocommerce order fee items
	public function woo_get_order_fee_items( $order_id, $order ) {

		$order_items = $order->get_items( 'fee' );
		$our_items   = array();

		if ( $order_items ) {
			foreach ( $order_items as $order_item_id => $item ) {
				$our_items = $item->get_data();
				unset( $our_items['meta_data'] );
			}
		}

		return $our_items;
	}

	/**
	 * Check endpoints for action
	 *
	 */
	public function look_for_action( $action = '' ) {

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

				// if actions not set, bail
				if ( ! isset( $endpoint['actions'] ) || $endpoint['actions'] == '' || $endpoint['actions'] != $action ) {
					continue;
				}

				$data[ $count ]['api_id']      = $api['id'];
				$data[ $count ]['endpoint_id'] = $endpoint['id'];
				$data[ $count ]['endpoint']    = $endpoint;

				++$count;

			}
		}

		if ( empty( $data ) ) {
			return;
		}

		return $data;
	}



	/**
	 * Calls the endpoint and logs it.
	 *
	 *
	 */
	public function call_endpoint( $endpoint, $argument_values, $action ) {

		// get all our values as they are sent
		$headers_value = add_filter(
			'wpgetapi_header_parameters',
			function ( $params, $api ) {
				$this->headers_value = $params;
				return $params;
			},
			9999,
			2
		);
		// REMINDER: body does not work with GET
		$body_value         = add_filter(
			'wpgetapi_final_body_parameters',
			function ( $params, $api ) {
				$this->body_value = $params;
				return $params;
			},
			9999,
			2
		);
		$query_string_value = add_filter(
			'wpgetapi_query_parameters',
			function ( $params, $api ) {
				$this->query_string_value = $params;
				return $params;
			},
			9999,
			2
		);
		$endpoint_value     = add_filter(
			'wpgetapi_endpoint',
			function ( $endpoint, $api ) {
				$this->endpoint_value = $endpoint;
				return $endpoint;
			},
			9999,
			2
		);

		$response_code = add_filter(
			'wpgetapi_before_retrieve_body',
			function ( $response, $response_code, $api ) {
				$this->response_code = $response_code;
				return $response;
			},
			9999,
			3
		);

		$final_url = add_filter(
			'wpgetapi_final_url',
			function ( $final_url, $api ) {
				$this->final_url = $final_url;
				return $final_url;
			},
			9999,
			2
		);

		/**
		 * Actions that can be run before calling API
		 * example: 'wpgetapi_before_woocommerce_order_status_changed' or 'wpgetapi_before_contact_form_7_57685'
		 *
		 * @param array         $action_args the values from the action
		 * @param array         $endpoint the endpoint setting values
		 *
		 */
		do_action( 'wpgetapi_before_' . $action, $argument_values['action_args'], $endpoint );

		/**
		 * Filter that can be run before calling API.
		 * Allows us to stop before doing the endpoint.
		 *
		 * example: 'wpgetapi_argument_values_before_woocommerce_order_status_changed' or 'wpgetapi_argument_values_before_contact_form_7_57685'
		 *
		 * @param array         $action_args the values from the action
		 * @param array         $endpoint the endpoint setting values
		 *
		 */
		$argument_values['action_args'] = apply_filters( 'wpgetapi_argument_values_before_' . $action, $argument_values['action_args'], $endpoint );

		// stops the api call
		// returns null or WP_Error
		if ( ! $argument_values['action_args'] || is_wp_error( $argument_values ) ) {
			return $argument_values['action_args'];
		}

		$result = wpgetapi_endpoint( $endpoint['api_id'], $endpoint['endpoint_id'], $argument_values );

		// if displaying wpdatatables,
		// we update the file from here
		if ( isset( $endpoint['endpoint']['forms_display'] ) && $endpoint['endpoint']['forms_display'] == 'wpdatatables' && isset( $endpoint['endpoint']['wpdatatables'] ) ) {

			$wpdatatables = new WpGetApi_Extras_Wpdatatables();
			$table_id     = $endpoint['endpoint']['wpdatatables'];
			if ( isset( $endpoint['endpoint']['wpdatatables_file_name'] ) && ! empty( $endpoint['endpoint']['wpdatatables_file_name'] ) ) {
				$table_id = $endpoint['endpoint']['wpdatatables_file_name'];
			}
			$wpdatatables->update_file_contents_from_action( $table_id, $result );

		}

		$values_sent = array(
			'endpoint'     => $this->endpoint_value,
			'query_string' => $this->query_string_value,
			'headers'      => $this->headers_value['headers'],
			'body'         => $this->body_value,
			'final_url'    => $this->final_url,
		);

		// truncate long values like page/post descriptions otherwise it fills up log
		if ( is_array( $argument_values['action_args'] ) ) {
			foreach ( $argument_values['action_args'] as $i => $args ) {

				if ( is_array( $args ) ) {
					foreach ( $args as $key => $value ) {
						if ( ! is_array( $value ) ) {
							$args[ $key ] = maybe_unserialize( mb_strimwidth( $value, 0, 300, '... **truncated in the action log only**' ) );
						}
					}
				}

				$argument_values['action_args'][ $i ] = $args;

			}
		}

		$endpoint = $this->maybe_decrypt( $endpoint );

		wpgetapi_action_log(
			$action, // ACTION
			$argument_values['action_args'], // ACTION DATA
			$values_sent, // VALUES SENT
			$endpoint, // ENDPOINT SETTINGS
			$this->response_code, // API RESPONSE CODE
			$result // API RESULT
		);

		/**
		 * Actions that can be run after calling API
		 * example: 'wpgetapi_after_woocommerce_order_status_changed' or 'wpgetapi_after_contact_form_7_57685'
		 *
		 * @param array|string  $result the data returned from the API
		 * @param integer       $response_code the response code from the API
		 * @param array         $action_args the values from the action
		 * @param array         $values_sent the values sent to the API
		 * @param array         $endpoint the endpoint setting values
		 *
		 */
		do_action( 'wpgetapi_after_' . $action, $result, $argument_values['action_args'], $values_sent, $endpoint, $this->response_code );

		/**
		 * Filter that can return a value. null is the default.
		 * example: 'wpgetapi_after_woocommerce_order_status_changed' or 'wpgetapi_after_contact_form_7_57685'
		 *
		 * @param array|string  $result the data returned from the API
		 * @param array         $action_args the values from the action
		 * @param array         $values_sent the values sent to the API
		 * @param array         $endpoint the endpoint setting values
		 *
		 */
		$return = apply_filters( 'wpgetapi_' . $action . '_return_value', null, $result, $argument_values['action_args'], $values_sent, $endpoint, $this->response_code );

		return $return;
	}


	/**
	 * modify post or user meta to remove the superfluous 0 indexed arrays
	 *
	 */
	public function format_meta( $id ) {
		$meta = get_post_meta( $id );
		if ( $meta ) {
			foreach ( $meta as $key => $value ) {
				$meta[ $key ] = maybe_unserialize( $value[0] );
			}
		}
		return $meta;
	}


	/**
	 * Maybe decrypt
	 * If endpoint contains any encrypted values, decrypt them.
	 *
	 */
	public function maybe_decrypt( $endpoint ) {

		if ( isset( $endpoint['endpoint']['header_parameters'] ) && is_array( $endpoint['endpoint']['header_parameters'] ) ) {
			$wpgetapi                                  = new WpGetApi_Api();
			$endpoint['endpoint']['header_parameters'] = $wpgetapi->decrypt( $endpoint['endpoint']['header_parameters'] );
		}

		// if( isset( $endpoint['endpoint']['body_parameters'] ) && is_array( $endpoint['endpoint']['body_parameters'] ) ) {
		//     $wpgetapi = new WpGetApi_Api();
		//     $endpoint['endpoint']['body_parameters'] = $wpgetapi->decrypt( $endpoint['endpoint']['body_parameters'] );
		// }

		if ( isset( $endpoint['endpoint']['query_parameters'] ) && is_array( $endpoint['endpoint']['query_parameters'] ) ) {
			$wpgetapi                                 = new WpGetApi_Api();
			$endpoint['endpoint']['query_parameters'] = $wpgetapi->decrypt( $endpoint['endpoint']['query_parameters'] );
		}

		return $endpoint;
	}


	public function get_ip( $ip = null, $deep_detect = true ) {
		if ( filter_var( $ip, FILTER_VALIDATE_IP ) === false ) {
			$ip = $_SERVER['REMOTE_ADDR'];
			if ( $deep_detect ) {
				if ( isset( $_SERVER['HTTP_X_FORWARDED_FOR'] ) && filter_var( $_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP ) ) {
					$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
				}
				if ( isset( $_SERVER['HTTP_CLIENT_IP'] ) && filter_var( $_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP ) ) {
					$ip = $_SERVER['HTTP_CLIENT_IP'];
				}
			}
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}


	// set our data from this filter
	// this will be returned below in the $result
	public function output_api_response( $action ) {
		add_filter(
			'wpgetapi_' . $action . '_return_value',
			function ( $null_value, $result, $arg_values, $values_sent, $endpoint, $response_code ) {
				return array(
					'api_result'    => $result,
					'response_code' => $response_code,
				);
			},
			10,
			6
		);
	}

	public function recursive_array_search( $needle, $haystack ) {

		$haystack = is_array( $haystack ) ? json_encode( $haystack ) : (string) ( $haystack );

		if ( strpos( $haystack, $needle ) !== false ) {
			return true;
		}

		return false;
	}

	/**
	 * Get the nested data
	 */
	public function nested_data( $data = array(), $keys = array() ) {

		if ( ! empty( $keys ) && ! is_array( $keys ) ) {
			// Create our array of values for keys
			// First, sanitize the data and remove white spaces
			$no_whitespaces_keys = preg_replace( '/\s*,\s*/', ',', filter_var( $keys, FILTER_SANITIZE_FULL_SPECIAL_CHARS ) );
			$keys                = explode( ',', $no_whitespaces_keys );
		}

		// if we have keys
		if ( $keys && is_array( $keys ) ) {

			$keys = wpgetapi_sanitize_text_or_array( $keys );

			foreach ( $keys as $i => $value ) {

				// check if we have curly braces
				if ( strpos( $value, '{' ) !== false ) {

					// remove them
					$value = str_replace( '{', '', $value );
					$value = str_replace( '}', '', $value );

					// extract the pipe seperator if any
					if ( strpos( $value, '|' ) !== false ) {
						$value = explode( '|', $value );
					}

					$value = ! is_array( $value ) ? array( $value ) : $value;

					// this gets the last key so we can still use it for urls and images
					$last = end( $value );

					$return[] = $this->get_the_keys( $data, $value );

				} else {

					$return = $this->get_the_keys( $data, $keys );

				}
			}
		} else {

			$return = $data;
		}

		if ( is_array( $return ) && count( $return ) == 1 ) {
			$return = $return[0];
		}

		return $return;
	}

	public function get_the_keys( $data, $keys ) {

		// Check if the $data parameter is set
		if ( ! isset( $data ) ) {
			return null;
		}

		// Use array_reduce() to access the keys based on $count
		$result = array_reduce(
			$keys,
			function ( $carry, $key ) {
				return isset( $carry[ $key ] ) ? $carry[ $key ] : null;
			},
			$data
		);

		return $result;
	}
}

return new WpGetApi_Extras_Actions();


