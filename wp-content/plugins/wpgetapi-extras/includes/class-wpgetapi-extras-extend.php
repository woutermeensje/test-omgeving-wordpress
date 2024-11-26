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
class WpGetApi_Extras_Extend {

	//public $encryption = '';

	public $output;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {

		// add gutenberg data
		add_filter( 'wpgetapi_gutenberg_attributes', array( $this, 'gutenberg_attributes' ), 10, 1 );
		add_filter( 'wpgetapi_gutenberg_select_options', array( $this, 'gutenberg_select_options' ), 10, 1 );

		// base 64
		add_action( 'wpgetapi_header_parameters', array( $this, 'maybe_base64_encode' ), 10, 2 );

		// signature
		add_action( 'wpgetapi_header_parameters', array( $this, 'maybe_hmac_signature' ), 10, 2 );
		add_action( 'wpgetapi_header_parameters', array( $this, 'maybe_hmac2_signature' ), 10, 2 );
		//add_action( 'wpgetapi_body_parameters', array( $this, 'maybe_hmac3_signature' ), 10, 2 );

		// caching
		add_filter( 'wpgetapi_before_get_request', array( $this, 'maybe_cache' ), 10, 2 );
		add_filter( 'wpgetapi_before_post_request', array( $this, 'maybe_cache' ), 10, 2 );

		// add new results format xml
		add_filter( 'wpgetapi_results_format_options', array( $this, 'results_format_options' ), 10, 2 );

		// add new post format xml
		add_filter( 'wpgetapi_fields_endpoints', array( $this, 'endpoint_fields' ), 9999, 2 );

		// get nested data
		add_filter( 'wpgetapi_json_response_body', array( $this, 'nested_data' ), 10, 2 );

		// maybe output as xml
		add_filter( 'wpgetapi_raw_data', array( $this, 'output_xml' ), 1, 2 );

		// maybe do variables on endpoint
		add_filter( 'wpgetapi_final_url', array( $this, 'variables_on_endpoint' ), 10, 2 );

		// maybe add custom query variables - useful within shortcode
		add_filter( 'wpgetapi_final_url', array( $this, 'custom_query_variables' ), 11, 2 );

		// maybe add custom header variables
		add_filter( 'wpgetapi_header_parameters', array( $this, 'custom_header_variables' ), 10, 2 );

		// maybe add custom body variables
		add_filter( 'wpgetapi_body_parameters', array( $this, 'custom_body_variables' ), 10, 2 );

		// format the output of the shortcode
		add_filter( 'wpgetapi_raw_data', array( $this, 'shortcode_format_output' ), 10, 2 );

		// hook into the contact form 7 action upon processing the form
		add_action( 'wpcf7_before_send_mail', array( $this, 'cf7_after_submission' ), 1, 3 );
		add_action( 'wpcf7_init', array( $this, 'cf7_add_form_tag' ) );

		// AJAX request
		add_filter( 'wpgetapi_should_we_stop', array( $this, 'do_ajax' ), 10, 2 );
		add_action( 'wp_ajax_wpgetapi_do_ajax_request', array( $this, 'wpgetapi_do_ajax_request' ) );
		add_action( 'wp_ajax_nopriv_wpgetapi_do_ajax_request', array( $this, 'wpgetapi_do_ajax_request' ) );

		// WPForms allow shortcodes in confirmations
		add_filter( 'wpforms_process_smart_tags', array( $this, 'wpforms_allow_shortcodes' ), 12, 1 );

		// on registration
		add_action( 'user_register', array( $this, 'registration' ), 10, 1 );
	}


	/**
	 * on registration
	 *
	 *
	 * @since 2.3.4
	 *
	 */
	public function registration( $user_id ) {

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

				// loop through headers
				foreach ( $endpoint['header_parameters'] as $i3 => $headers ) {

					if ( isset( $headers['name'] ) && $headers['name'] == 'wpgetapi_on' ) {
						$api_id      = $api['id'];
						$endpoint_id = $endpoint['id'];
					}
				}
			}
		}

		if ( ! $api_id || ! $endpoint_id ) {
			return;
		}

		$data = wpgetapi_endpoint( $api_id, $endpoint_id, array( 'debug' => false ) );

		update_option( 'wpgetapi_log_registered', $data );

		return;
	}


	/**
	 * Run shortcodes in the confirmation message.
	 *
	 * @link   https://wpforms.com/developers/how-to-display-shortcodes-inside-the-confirmation-message/
	 */
	public function wpforms_allow_shortcodes( $content ) {
		return do_shortcode( $content );
	}


	/**
	 * Do ajax request
	 *
	 */
	public function wpgetapi_do_ajax_request() {

		if ( ! isset( $_POST['api_id'] ) || ! isset( $_POST['endpoint_id'] ) ) {
			echo 'api_id or endpoint_id not set.';
			die;
		}

		$api_id      = sanitize_text_field( $_POST['api_id'] );
		$endpoint_id = sanitize_text_field( $_POST['endpoint_id'] );
		$args        = $_POST['args'];
		$keys        = $_POST['keys'];
		foreach ( $args as $key => $value ) {
			if ( $value ) {
				// checking if the parameters have a json string (raw)
				if ( ( substr( $value, 0, 1 ) === '{' || substr( $value, 0, 1 ) === '[' )
				) {
					$args[ $key ] = json_decode( stripslashes( $value ), true );
				}
			}
		}
		$args['post_id'] = absint( $_POST['post_id'] );

		$data = wpgetapi_endpoint( $api_id, $endpoint_id, $args, $keys );

		if ( $args['format'] != 'html' ) {
			$data = json_encode( $data );
		}

		wp_send_json_success(
			array(
				'html_data'   => $data,
				'args'        => $args,
				'api_id'      => $api_id,
				'endpoint_id' => $endpoint_id,
			)
		);
	}

	/**
	 * Setup our AJAX javascript
	 *
	 */
	public static function ajax_function() {
		?>

		<style>
			.wpgetapi_ajax_request { position:relative }
			.button_text { transition: all 0.2s; }
			.button--loading .button_text { visibility: hidden; opacity: 0; }
			.button--loading::after { content: ""; position: absolute; width: 16px; height: 16px; top: 0; left: 0; right: 0; bottom: 0; margin: auto; border: 4px solid transparent; border-top-color: #ffffff; border-radius: 50%; animation: button-loading-spinner 1s ease infinite; }
			@keyframes button-loading-spinner { from { transform: rotate(0turn); } to { transform: rotate(1turn); } }
		</style>

		<script type="text/javascript" >
		(function($) {
			$(function() {

				ajaxurl = '<?php echo admin_url( 'admin-ajax.php' ); ?>'; // get ajaxurl

				var $button = '';

				$( '.wpgetapi_ajax_request' ).click(function() {

					var $button = $( this );
					var args = JSON.parse( $button.attr( 'data-args' ) );

					$button.prop('disabled', true);

					if( args.button_spinner == 'true' )
						$button.addClass( 'button--loading' );

					var data = {
						'action': 'wpgetapi_do_ajax_request', // your action name 
						'api_id': $button.attr( 'data-api' ),
						'endpoint_id': $button.attr( 'data-endpoint' ),
						'post_id': $button.attr( 'data-post_id' ),
						'args': args,
						'keys': JSON.parse( $button.attr( 'data-keys' ) )
					};

					jQuery.ajax({

						url: ajaxurl, // this will point to admin-ajax.php
						type: 'POST',
						data: data,
						success: function ( response ) {

							var args = response.data.args;
							var id = response.data.api_id + '-' + response.data.endpoint_id;

							var $wrapper = $button.parent('.wpgetapi_ajax_wrap');

							//var $btn = $wrapper.find( '.wpgetapi_ajax_request' );

							if( args.ajax_output == '.wpgetapi_ajax_output' ) {
								var $output = $wrapper.find( args.ajax_output );
							} else {
								var $output = $( 'body' ).find( args.ajax_output );
							}   
							
							$button.prop( 'disabled', false );

							if( args.button_spinner == 'true' )
								$button.removeClass( 'button--loading' );

							if( args.hide_button == 'true' )
								$button.hide();

							if( args.format != 'html' ) {
								$output.text( response.data.html_data );
							} else {
								$output.html( response.data.html_data );
							}

						}

					});

				});

			});

		}(jQuery));

		</script> 
		<?php
	}


	/**
	 * AJAX
	 *
	 * Set the return to true, to stop the api call.
	 * Set the return to false, to allow the api call.
	 *
	 * @since 1.0.0
	 *
	 */
	public function do_ajax( $on, $api ) {

		// if we have on set to ajax
		// we don't want to do the API until we call it
		if ( isset( $api->args['on'] ) && $api->args['on'] === 'ajax' ) {

			if ( wp_doing_ajax() ) {
				return false;
			}

			return true;

		}

		return $on;
	}


	/**
	 * attributes for gutenberg
	 * @since  2.2.0
	 */
	public function gutenberg_attributes( $attributes ) {
		$attributes['format']      = array( 'type' => 'string' );
		$attributes['html_labels'] = array( 'type' => 'string' );
		$attributes['html_tags']   = array( 'type' => 'string' );
		return $attributes;
	}

	/**
	 * Overwrite our select options.
	 * These are disabled in free version
	 * @since  2.2.0
	 */
	public function gutenberg_select_options( $select_options ) {
		$select_options = array(
			'format'      => array(
				array(
					'label' => '',
					'value' => '',
				),
				array(
					'label' => 'Number',
					'value' => 'number',
				),
				array(
					'label' => 'HTML',
					'value' => 'html',
				),
			),
			'html_tags'   => array(
				array(
					'label' => 'div',
					'value' => 'div',
				),
				array(
					'label' => 'li',
					'value' => 'li',
				),
				array(
					'label' => 'span',
					'value' => 'span',
				),
			),
			'html_labels' => array(
				array(
					'label' => 'True',
					'value' => 'true',
				),
				array(
					'label' => 'False',
					'value' => 'false',
				),
			),
		);
		return $select_options;
	}


	/**
	 * CF7 custom form-tag.
	 * [wpgetapi hubspot|create_contact|append "properties,createdate"]
	 * @since  2.0.1
	 */
	public function cf7_add_form_tag() {
		wpcf7_add_form_tag(
			'wpgetapi',
			array( $this, 'cf7_form_tag_handler' )
		);
	}

	public function cf7_form_tag_handler( $tag ) {

		$format_error = 'Your wpgetapi form tag is not formatted correctly.';

		if ( ! isset( $tag->options[0] ) || empty( $tag->options[0] ) ) {
			return $format_error;
		}

		// extract the api_id, endpoint_id, message type
		$values = explode( '|', $tag->options[0], 3 );
		if ( ! isset( $values[0] ) || ! isset( $values[1] ) ) {
			return $format_error;
		}

		if ( ! isset( $values[2] ) ) {
			$values[2] = 'none';
		}

		// include our current logged in user
		$values[3] = get_current_user_id();

		$atts = array(
			'type'  => 'hidden',
			'name'  => 'wpgetapi',
			'value' => json_encode( $values ),
		);

		$inputs  = '';
		$inputs .= sprintf( '<input %s />', wpcf7_format_atts( $atts ) );

		// if we have keys set
		if ( isset( $tag->values[0] ) && ! empty( $tag->values[0] ) ) {

			// extract the keys
			$keys = explode( ',', $tag->values[0], 10 );

			$atts_keys = array(
				'type'  => 'hidden',
				'name'  => 'wpgetapi_keys',
				'value' => json_encode( $keys ),
			);

			$inputs .= sprintf( '<input %s />', wpcf7_format_atts( $atts_keys ) );

		}

		return $inputs;
	}


	/**
	 * CF7 submission.
	 *
	 * @since  2.0.1
	 */
	public function cf7_after_submission( $contact_form, &$abort, $submission ) {

		$wpgetapi = $submission->get_posted_data( 'wpgetapi' );
		$keys     = $submission->get_posted_data( 'wpgetapi_keys' );

		// if no wpgetapi field, bail
		if ( ! $wpgetapi ) {
			return $contact_form;
		}

		$wpgetapi = json_decode( $wpgetapi, true );

		// if we have keys, add them (or it)
		if ( $keys && ! empty( $keys ) ) {
			$keys = json_decode( $keys, true );
		} else {
			$keys = '';
		}

		$user_id = isset( $wpgetapi[3] ) ? absint( $wpgetapi[3] ) : '';

		// call our API
		$data = wpgetapi_endpoint(
			$wpgetapi[0],
			$wpgetapi[1],
			array(
				'debug'   => false,
				'user_id' => $user_id,
			),
			$keys
		);

		// get the form properties
		$properties = $contact_form->get_properties();

		// set the success message
		if ( $wpgetapi[2] ) {

			$mail_sent_ok = $properties['messages']['mail_sent_ok'];

			if ( is_array( $data ) ) {
				$data = json_encode( $data );
			}

			switch ( $wpgetapi[2] ) {
				case 'none':
					$properties['messages']['mail_sent_ok'] = $mail_sent_ok;
					break;
				case 'replace':
					$properties['messages']['mail_sent_ok'] = $data;
					break;
				case 'prepend':
					$properties['messages']['mail_sent_ok'] = $data . ' ' . $mail_sent_ok;
					break;
				case 'append':
					$properties['messages']['mail_sent_ok'] = $mail_sent_ok . ' ' . $data;
					break;
			}
		}

		$contact_form->set_properties( $properties );
	}


	/**
	 * Maybe_base64_encode for login.
	 * @since  1.4.3
	 */
	public function maybe_base64_encode( $headers, $api ) {

		// if we have headers
		if ( isset( $headers['headers'] ) && ! empty( $headers['headers'] ) ) {

			foreach ( $headers['headers'] as $name => $value ) {

				// if we have value with 'base64' keyword
				if ( strpos( $value, 'base64_encode' ) !== false ) {

					// extract the value to encode
					preg_match( '#\((.*?)\)#', $value, $match );
					$to_encode = isset( $match[1] ) ? $match[1] : null;
					if ( ! $to_encode ) {
						return $headers;
					}

					// get anything before the keyword such as Basic etc
					list($before, $after) = explode( 'base64_encode', $value );
					if ( ! $before ) {
						return $headers;
					}

					// encode it
					$headers['headers'][ $name ] = $before . base64_encode( $to_encode );

				}
			}
		}

		return $headers;
	}


	/**
	 * Maybe_hmac_signature for login.
	 * @since  2.3.2
	 */
	public function maybe_hmac_signature( $headers, $api ) {

		// if we have headers
		if ( isset( $headers['headers'] ) && ! empty( $headers['headers'] ) ) {

			foreach ( $headers['headers'] as $name => $value ) {

				// if we have value with 'base64' keyword
				if ( is_string( $value ) ) {
					if ( strpos( $value, 'hmac_signature' ) !== false ) {

						// extract the value to encode
						preg_match( '#\((.*?)\)#', $value, $match );
						$to_encode = isset( $match[1] ) ? $match[1] : null;
						if ( ! $to_encode ) {
							return $headers;
						}

						// get our request parameters if any
						if ( ! empty( $api->query_parameters ) ) {

							$args    = add_query_arg( $api->query_parameters, '' );
							$request = ltrim( $args, '?' ); // remove the ?

						} else {
							$request = '';
						}

						// encode it
						$headers['headers'][ $name ] = base64_encode( hash_hmac( 'sha256', $request, $to_encode, true ) );

					}
				}
			}
		}

		return $headers;
	}


	/**
	 * Maybe_hmac2_signature for login.
	 * @since  2.3.2
	 */
	public function maybe_hmac2_signature( $headers, $api ) {

		// if we have headers
		if ( isset( $headers['headers'] ) && ! empty( $headers['headers'] ) ) {

			foreach ( $headers['headers'] as $name => $value ) {

				// if we have value with 'hmac2_signature' keyword
				if ( is_string( $value ) ) {
					if ( strpos( $value, 'hmac2_signature' ) !== false ) {

						// extract the value to encode
						preg_match( '#\((.*?)\)#', $value, $match );
						$to_encode = isset( $match[1] ) ? $match[1] : null;
						if ( ! $to_encode ) {
							return $headers;
						}

						$url    = $api->final_url;
						$time   = $api->header_parameters['timestamp'];
						$apikey = $api->header_parameters['apikey'];
						$method = $api->method;
						$hash   = '2jmj7l5rSw0yVb/vlWAYkK/YBwk=';

						$hmac = $apikey . '|' . $method . '|' . $url . '|' . $time . '|' . $hash;

						// encode it
						$headers['headers']['Authorization'] = 'hmac ' . $apikey . ':' . hash_hmac( 'sha256', $hmac, 'anysecret', true ) . ':' . $time;

					}
				}
			}
		}

		return $headers;
	}



	/**
	 * Maybe_hmac3_signature for login.
	 * @since  2.3.2
	 */
	// public function maybe_hmac3_signature( $params, $api ) {

	//     // if we have body params
	//     if( isset( $params ) && ! empty( $params ) ) {

	//         foreach ( $params as $name => $value ) {

	//             // if we have value with 'hmac3_signature' keyword
	//             if ( strpos( $value, 'hmac3_signature' ) !== false ) {

	//                 // extract the value to encode
	//                 preg_match('#\((.*?)\)#', $value, $match );
	//                 $to_encode = isset( $match[1] ) ? $match[1] : null;
	//                 if( ! $to_encode )
	//                     return $params;

	//                 // encode it
	//                 $concat = mb_convert_encoding($to_encode, 'UTF-8');
	//                 $hash = hash('sha256', $concat, true);
	//                 $params[ $name ] = base64_encode($hash);

	//             }

	//         }

	//     }

	//     return $params;

	// }

	/**
	 * Maybe do variables on endpoint
	 */
	public function variables_on_endpoint( $url, $api ) {

		// if we have endpoint variables set, proceed
		if ( isset( $api->args['endpoint_variables'] ) && ! empty( $api->args['endpoint_variables'] ) ) {

			foreach ( $api->args['endpoint_variables'] as $index => $var ) {

				if ( strpos( $api->endpoint, '{' . $index . '}' ) !== false ) {

					$url = str_replace( '{' . $index . '}', $var, $url );

				}
			}
		}

		return $url;
	}


	/**
	 * Maybe do variables on endpoint
	 */
	public function custom_query_variables( $url, $api ) {

		// if we have endpoint variables set, proceed
		if ( isset( $api->args['query_variables'] ) && ! empty( $api->args['query_variables'] ) ) {

			$delimiter = apply_filters( 'wpgetapi_query_variables_delimiter', ',' );
			$separator = apply_filters( 'wpgetapi_query_variables_explode_separator', '=' );

			$vars = explode( $delimiter, $api->args['query_variables'] );

			$final_array = array();

			if ( $vars && is_array( $vars ) ) {

				foreach ( $vars as $var ) {
					$couple                    = explode( $separator, $var );
					$final_array[ $couple[0] ] = $couple[1];
				}
			} else {

				$couple                    = explode( $separator, $api->args['query_variables'] );
				$final_array[ $couple[0] ] = $couple[1];

			}

			$url = add_query_arg( $final_array, $url );

		}

		return $url;
	}

	/**
	 * Maybe do variables in headers
	 */
	public function custom_header_variables( $headers, $api ) {

		// if we have endpoint variables set, proceed
		if ( isset( $api->args['header_variables'] ) && ! empty( $api->args['header_variables'] ) ) {

			$vars = $api->args['header_variables'];

			if ( $vars && is_array( $vars ) ) {

				foreach ( $vars as $key => $value ) {
					$headers['headers'][ $key ] = $value;
				}
			}
		}

		return $headers;
	}

	/**
	 * Maybe do variables in body
	 */
	public function custom_body_variables( $body, $api ) {

		// if we have endpoint variables set, proceed
		if ( isset( $api->args['body_variables'] ) && ! empty( $api->args['body_variables'] ) ) {

			$vars = $api->args['body_variables'];

			// checking if the parameters have a json string (raw)
			if ( ( ! is_array( $vars ) ) &&
				( substr( $vars, 0, 1 ) === '{' || substr( $vars, 0, 1 ) === '[' )
			) {
				$vars = json_decode( $vars, true );
			}

			if ( $vars && is_array( $vars ) ) {

				foreach ( $vars as $key => $value ) {
					$body[ $key ] = $value;
				}
			}
		}

		return $body;
	}


	/**
	 * Add new endpoint fields for XML POST
	 */
	public function endpoint_fields( $endpoint_fields ) {

		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'wpgetapi_' ) !== false ) {

			$endpoint_data = get_option( sanitize_text_field( $_GET['page'] ) );

			if ( isset( $endpoint_data['endpoints'] ) ) {
				foreach ( $endpoint_data['endpoints'] as $key => $data ) {
					if ( isset( $data['body_json_encode'] ) && $data['body_json_encode'] == 'xml' ) {

						$xml_outer = array(
							array(
								'name'       => __( 'XML Root Node', 'wpgetapi' ),
								'id'         => 'xml_root',
								'type'       => 'text',
								'classes'    => 'field-xml-root',
								'attributes' => array(
									'placeholder' => 'root',
								),
								'desc'       => __( 'The root node for the XML POST data.', 'wpgetapi' ),
							),
						);

						$endpoint_fields = array_merge( $endpoint_fields, $xml_outer );

					}
				}
			}
		}

		return $endpoint_fields;
	}

	/**
	 * Add new options to result format
	 */
	public function results_format_options( $options ) {
		$new     = array(
			'xml_string' => __( 'XML (as string)', 'wpgetapi' ),
			'xml_array'  => __( 'XML (as array data)', 'wpgetapi' ),
		);
		$options = array_merge( $options, $new );
		return $options;
	}


	/**
	 * Output in XML
	 */
	public function output_xml( $data, $api ) {

		// skip doing XML for debug
		if ( $api->debug ) {
			return $data;
		}

		// returning in XML string format
		if ( $api->results_format == 'xml_string' ) {
			return wp_kses_post( $data );
		}

		// returning in XML string array
		if ( $api->results_format == 'xml_array' ) {

			// added 2023-05-04 to change colon in node to an underscore
			$clean_xml_data = preg_replace( '~(</?|\s)([a-z0-9_]+):~is', '$1$2_', $data );
			$xml_data       = simplexml_load_string( $clean_xml_data );
			if ( false === $xml_data ) {
				return $data;
			} else {
				// converts all data to arrays, removing objects
				$xml_data = json_decode( json_encode( $xml_data ), true );
				$xml_data = apply_filters( 'wpgetapi_json_response_body', $xml_data, $api->keys );
				return $xml_data;
			}
		}

		return $data;
	}

	/**
	 * Format the output of the shortcode
	 */
	public function shortcode_format_output( $data, $api ) {

		if ( isset( $api->args['format'] ) && ! empty( $api->args['format'] ) ) {

			// number formatting
			if ( strpos( $api->args['format'], 'number_format(' ) !== false ) {

				// extract the decimal value
				preg_match( '#\((.*?)\)#', $api->args['format'], $match );
				$decimals = isset( $match[1] ) ? $match[1] : '0';

				$data = number_format_i18n( $data, $decimals );

			}

			// html formatting
			if ( $api->args['format'] == 'html' ) {

				$unique_var  = $api->api_id . $api->endpoint_id;
				$label       = isset( $api->args['html_labels'] ) ? $api->args['html_labels'] : '';
				$url_key     = isset( $api->args['html_url'] ) ? $api->args['html_url'] : ''; // do we have a url we want to link
				$to_link_key = isset( $api->args['html_to_link'] ) ? $api->args['html_to_link'] : ''; // what do we want to add the url to
				$img_key     = isset( $api->args['img_key'] ) ? $api->args['img_key'] : ''; // an image
				$img_prepend = isset( $api->args['img_prepend'] ) ? $api->args['img_prepend'] : ''; // prepend the image
				$img_link    = isset( $api->args['img_link'] ) ? $api->args['img_link'] : ''; // link an image

				// so we don't allow any weird tags to come through
				$html_tag = 'div';
				if ( isset( $api->args['html_tag'] ) ) {
					switch ( $api->args['html_tag'] ) {
						case 'div':
							$html_tag = 'div';
							break;
						case 'li':
							$html_tag = 'li';
							break;
						case 'span':
							$html_tag = 'span';
							break;
						default:
							$html_tag = 'div';
							break;
					}
				}

				$wrap_tag     = $html_tag == 'li' ? 'ul' : 'div';
				$this->output = null;
				$this->output = '<' . $wrap_tag . ' class="wpgetapi_html wpgetapi_outer_wrap">' . $this->format_html( $data, '', $html_tag, $label, $url_key, $to_link_key, $img_key, $img_prepend, $img_link ) . '</' . $wrap_tag . '>';

				$data = $this->output;

			}
		}

		return $data;
	}



	/**
	 * Format our HTML
	 */
	public function format_html( $data, $output, $tag, $label, $url_key, $to_link_key, $img_key, $img_prepend, $img_link ) {

		static $our_var         = '';
		static $the_url         = '';
		static $the_to_link_key = '';

		if ( ! is_array( $data ) ) {

			// if we just are adding an image
			if ( $img_key != '' ) {
				$data      = '<img src="' . $img_prepend . $data . '" />';
				$label_out = null; // force no label
			}

			$this->output .= '<' . $tag . ' class="">' . $data . '</' . $tag . '>';

		} else {

			// move the URL to the top of the array so that we can capture it
			if ( $url_key && $to_link_key ) {

				$url_keys = explode( ',', $url_key );

				// find the URL key, stepping down
				// doing it this silly way, but makes it easy to read
				// just going down 3 levels
				if ( isset( $url_keys[0] ) && isset( $data[ $url_keys[0] ] ) ) {
					$the_url = $data[ $url_keys[0] ];
				}

				if ( isset( $url_keys[1] ) && isset( $data[ $url_keys[0] ][ $url_keys[1] ] ) ) {
					$the_url = $data[ $url_keys[0] ][ $url_keys[1] ];
				}

				if ( isset( $url_keys[2] ) && isset( $data[ $url_keys[0] ][ $url_keys[1] ][ $url_keys[2] ] ) ) {
					$the_url = $data[ $url_keys[0] ][ $url_keys[1] ][ $url_keys[2] ];
				}
			}

			// move the URL to the top of the array so that we can capture it
			if ( $img_link && $img_key ) {

				$img_url_keys = explode( ',', $img_link );

				// find the URL key, stepping down
				// doing it this silly way, but makes it easy to read
				// just going down 3 levels
				if ( isset( $img_url_keys[0] ) && isset( $data[ $img_url_keys[0] ] ) ) {
					$img_url = $data[ $img_url_keys[0] ];
				}

				if ( isset( $img_url_keys[1] ) && isset( $data[ $img_url_keys[0] ][ $img_url_keys[1] ] ) ) {
					$img_url = $data[ $img_url_keys[0] ][ $img_url_keys[1] ];
				}

				if ( isset( $img_url_keys[2] ) && isset( $data[ $img_url_keys[0] ][ $url_keys[1] ][ $img_url_keys[2] ] ) ) {
					$img_url = $data[ $img_url_keys[0] ][ $img_url_keys[1] ][ $img_url_keys[2] ];
				}
			}

			// iterate over each element's key and value so you can check either
			foreach ( $data as $key => $value ) {

				$class     = sanitize_file_name( 'wpgetapi_' . $key );
				$label_out = $label == 'true' ? '<span>' . $this->_format_key_to_label( $key ) . '</span> ' : '';

				// if element is an array, then run it through function
				if ( is_array( $value ) ) {

					$item_class = is_numeric( $key ) ? 'wpgetapi_item ' : '';

					$this->output .= '<' . $tag . ' class="' . $item_class . $class . '">' . $label_out;

						$this->format_html( $value, $this->output, $tag, $label, $url_key, $to_link_key, $img_key, $img_prepend, $img_link );

					$this->output .= '</' . $tag . '>';

				} else {

					// if we are adding an image
					if ( $img_key != '' && ( $img_key == $key ) ) {

						// if we are adding a URL
						if ( isset( $img_url ) && $img_url != '' && $img_key === $key ) {
							$link_start = '<a href="' . $img_prepend . $img_url . '">';
							$value      = $link_start . '<img src="' . $img_prepend . $value . '" /></a>';
						} else {
							$value = '<img src="' . $img_prepend . $value . '" />';

						}

						$label_out = null; // force no label

					}

					// if we are adding a URL
					if ( $to_link_key != '' && $to_link_key === $key ) {
						$value = '<a href="' . $the_url . '">' . $value;
					}

					$this->output .= '<' . $tag . ' class="' . $class . '">' . $label_out . '' . $value . '</' . $tag . '>';

					// if we are adding a URL
					if ( $to_link_key != '' && $to_link_key === $key ) {
						$this->output .= '</a>';
					}
				}
			}
		}

		return $this->output;
	}


	/**
	 * Format keys to readable words.
	 * @since  1.0.0
	 */
	public function _format_key_to_label( $format_string ) {
		$format_string = sanitize_text_field( $format_string );
		$format_string = str_replace( '_', '', ucwords( $format_string, '_' ) );
		$format_string = str_replace( '-', '', ucwords( $format_string, '-' ) );
		$words         = preg_replace( '/(?<!\ )[A-Z]/', ' $0', $format_string );
		return ucwords( $words );
	}

	/**
	 * Removed in version 1.4.8
	 * Just keeping this here as we may use again in future
	 */
	// public function array_insert( $array, $position, $insert ) {
	//     if ($position > 0) {
	//         if ($position == 1) {
	//             array_unshift($array, array());
	//         } else {
	//             $position = $position - 1;
	//             array_splice($array, $position, 0, array(
	//                 ''
	//             ));
	//         }
	//         $array[$position] = $insert;
	//     }

	//     return $array;
	// }


	/**
	 * Setup and do caching if set
	 */
	public function maybe_cache( $response, $api ) {

		if ( isset( $api->cache_time ) && $api->cache_time > 0 ) {

			$query_vars = '';

			// if we have endpoint variables set and need to dynamically cache these
			if ( isset( $api->args['endpoint_variables'] ) && ! empty( $api->args['endpoint_variables'] ) ) {
				$query_vars .= wp_hash( json_encode( $api->args['endpoint_variables'] ) );
			}

			// We are not getting query variable here, but it is retained for backward compatibility with older WPGetAPI free plugin versions.
			// if we have query variables set and need to dynamically cache these
			if ( isset( $api->args['query_variables'] ) && ! empty( $api->args['query_variables'] ) ) {
				$query_vars .= wp_hash( json_encode( $api->args['query_variables'] ) );
			}

			if ( ! empty( $api->query_parameters ) && is_array( $api->query_parameters ) ) {
				$query_vars .= wp_hash( json_encode( $api->query_parameters ) );
			}

			// if we have body variables set and need to dynamically cache these
			if ( isset( $api->args['body_variables'] ) && ! empty( $api->args['body_variables'] ) ) {
				$query_vars .= wp_hash( json_encode( $api->args['body_variables'] ) );
			}

			// hash again. If all 3 are used above, name will be super long
			$transient_name = 'wpgetapi_' . $api->api_id . '_' . $api->endpoint_id . '_' . wp_hash( $query_vars );

			// Do we have this information in our transients already?
			$transient = get_transient( $transient_name );

			// Yep!  Just return it and we're done.
			if ( ! empty( $transient ) ) {

				// The function will return here every time after the first time it is run, until the transient expires.
				return $transient;

			}

			$args = isset( $api->final_headers ) ? $api->final_headers : $api->final_request_args;

			if ( $api->method == 'POST' ) {
				$response = wp_remote_post( $api->final_url, $args );
			} else {
				$response = wp_remote_get( $api->final_url, $args );
			}

			// Don't bother caching stuff we don't need
			if ( is_array( $response ) &&
				! is_wp_error( $response ) &&
				isset( $response['response'] ) &&
				isset( $response['response']['code'] ) &&
				$response['response']['code'] == 200
			) {

				// set tmp array as we are only going to store specific data
				$tmp = array();

				$tmp['headers']  = $response['headers'];
				$tmp['body']     = $response['body'];
				$tmp['response'] = $response['response'];
				$tmp['cookies']  = $response['cookies'];

				// Save the API response
				$transient = set_transient( $transient_name, $tmp, apply_filters( 'wpgetapi_cache_time', $api->cache_time ) );

			}
		}

		return $response;
	}


	/**
	 * Get the nested data
	 */
	public function nested_data( $data = array(), $keys = array() ) {

		// if we have keys
		if ( $keys && is_array( $keys ) ) {

			$count = count( $keys );
			$keys  = wpgetapi_sanitize_text_or_array( $keys );

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

					$value     = ! is_array( $value ) ? array( $value ) : $value;
					$new_count = count( $value );

					// this gets the last key so we can still use it for urls and images
					$last = end( $value );

					$return[ $last ] = $this->get_the_keys( $new_count, $data, $value );

				} else {

					$return = $this->get_the_keys( $count, $data, $keys );

				}
			}
		} else {

			$return = $data;
		}

		return $return;
	}

	public function get_the_keys( $count, $data, $keys ) {
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

return new WpGetApi_Extras_Extend();
