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
class WpGetApi_Extras_Tokens {

	// this is so we can use ajax from CF7
	public $user_id;

	// for ajax
	public $post_id;

	// for using the args within an action as a token value
	public $action_args;

	/**
	 * Main constructor
	 *
	 * @since 1.0.0
	 *
	 */
	public function __construct() {

		add_filter( 'wpgetapi_endpoint', array( $this, 'endpoint_tokens' ), 5, 2 );
		add_filter( 'wpgetapi_query_parameters', array( $this, 'query_string_tokens' ), 5, 2 );
		add_filter( 'wpgetapi_header_parameters', array( $this, 'header_tokens' ), 5, 2 );
		add_filter( 'wpgetapi_body_parameters', array( $this, 'body_tokens' ), 99, 2 );
	}


	public function replace_tokens( $text, $action_args = null ) {

		// this is for when using float(), integer() etc coming direct from free version.
		if ( isset( $action_args ) ) {
			$this->action_args = $action_args;
		}

		// strip out any spaces, just a safety
		$text = str_replace( ' ', '', $text );

		//$text = $this->maybe_do_nested_tokens( $text );

		$text = $this->replace_system_token( $text );
		$text = $this->replace_date_token( $text );
		$text = $this->replace_post_token( $text );
		$text = $this->replace_user_token( $text );
		$text = $this->replace_woocommerce_order_token( $text );
		$text = $this->replace_lifter_lms_token( $text );
		$text = $this->replace_chain_token( $text );
		$text = $this->replace_action_token( $text );

		return $text;
	}

	public function maybe_do_nested_tokens( $text ) {

		$orig = $text;

		$count1 = substr_count( $text, '(' );
		$count2 = substr_count( $text, ')' );

		if ( $count1 > 1 && $count2 > 1 ) {

			$result  = null;
			$pattern = '/\([^)]+\)/';

			if ( preg_match_all( $pattern, $text, $matches ) ) {

				$all_tokens = isset( $matches[0] ) ? $matches[0] : null;
				if ( is_array( $all_tokens ) ) {

					foreach ( $all_tokens as $key => $token ) {

						$count = substr_count( $token, '(' );

						if ( $count > 1 ) {

							$result  = null;
							$pattern = '/\([^)]+\)/';

							if ( preg_match_all( $pattern, $token, $matches ) ) {
								$result = end( $matches[0] );
								$pos    = strpos( $result, '(', strpos( $result, '(' ) + 1 );

								if ( false !== $pos ) {
									$internal_token     = substr( $result, $pos );
									$internal_token_val = $this->replace_tokens( $internal_token );
									$text               = str_replace( $internal_token, $internal_token_val, $orig );
								}
							}
						}
					}
				}
			}
		}

		return $text;
	}

	// public function return_specific_key( $text ) {

	//     // get a key
	//     if ( strpos( $value, 'get_key(' ) !== false && substr( $value, -1 ) === ')' ) {
	//         preg_match("/\(([^\)]*)\)/", $value, $match );
	//         $result = $match[1];
	//         $new_array[ $key ] = absint( $result );
	//     }
	// }

	public function get_string_between( $line_string, $start, $end ) {
		$line_string = ' ' . $line_string;
		$ini         = strpos( $line_string, $start );
		if ( $ini == 0 ) {
			return '';
		}
		$ini += strlen( $start );
		$len  = strpos( $line_string, $end, $ini ) - $ini;
		return substr( $line_string, $ini, $len );
	}


	/*
	 * Loop token
	 *
	 */
	public function do_loop_line_items( $tokens, $action_args, $orig_params, $api ) {

		$type   = 'line_items';
		$result = null;
		$found  = false;

		$param_value = $this->get_string_between( $orig_params, '(line_items:start)', '(line_items:end)' );
		$before      = strstr( $orig_params, '(line_items:start)', true );
		$after       = substr( $orig_params, strpos( $orig_params, '(line_items:end)' ) + 16 );

		if ( $tokens ) {
			foreach ( $tokens as $key => $value ) {
				if ( '(line_items:start)' === $value ) {
					$found = true;
				}
			}
		}

		if ( ! $found ) {
			return false;
		}

		foreach ( $tokens as $i => $token ) {

			if ( '(line_items:start)' === $token ) {
				unset( $tokens[ $i ] );
				continue;
			}
			if ( '(line_items:end)' === $token ) {
				unset( $tokens[ $i ] );
				continue;
			}

			if ( strpos( $token, '(get:' ) !== false ) {

				$orig_token = $token;

				// remove the get from the token and brackets
				$token = ltrim( $token, '(get:' );
				$token = rtrim( $token, ')' );
				$keys  = explode( ':', $token );

				$our_keys[] = $keys[0]; // eg: sku

			}
		}

		// eg: line_items
		$replaced = '';
		if ( isset( $our_keys ) && isset( $action_args['line_items'] ) ) {
			if ( is_array( $action_args['line_items'] ) ) {

				$count_items = count( $action_args['line_items'] );

				// could be looping through line_items
				foreach ( $action_args['line_items'] as $i => $line_item ) {

					$param_value = $this->get_string_between( $orig_params, '(line_items:start)', '(line_items:end)' );

					foreach ( $our_keys as $key ) {
						if ( isset( $line_item[ $key ] ) ) {
							$param_value = str_replace( '(get:' . $key . ')', $line_item[ $key ], $param_value );
						}
					}

					// if all replaced
					if ( strpos( $param_value, '(get:' ) === false ) {
						if ( true === $api->body_xml_format ) {
							$replaced .= $param_value;
						} else {
							$replaced .= apply_filters( 'wpgetapi_line_items_wrapper', '{' . $param_value . '},' );
						}
					}
				}
			}
		}

		if ( $replaced ) {
			$replaced = rtrim( $replaced, ',' );
			$result   = $before . $replaced . $after;
		}

		return $result;
	}

	/*
	 * User Tokens
	 * Format: (type:id of user:user field|format for date_registered or an array key)
	 * Examples: (user:current:first_name), (user:2:date_registered|y-m-d), (user:2:some_meta_field_array|array_key_name)
	 *
	 */
	public function replace_user_token( $text ) {

		$type = '(user:';

		if ( strpos( $text, $type ) !== false ) {

			$user_id = $this->user_id ? $this->user_id : get_current_user_id();

			$filter = null;

			// remove the start & end
			$text = strstr( $text, $type ); // remove anything before type
			$text = str_replace( $type, '', $text );  // remove type
			$text = rtrim( $text, ')' ); // remove last bracket

			// extract the filter if any (date filter)
			if ( strpos( $text, '|' ) !== false ) {
				$filter = (string) substr( $text, strpos( $text, '|' ) + 1 );
			}

			// remove the filter pipe
			if ( $filter || '0' === $filter ) {
				$text = str_replace( '|' . $filter, '', $text );
			}

			// get sub keys
			$keys = explode( ':', $text );

			// send errors
			if ( count( $keys ) === 1 ) {
				return 'Missing a token parameter.';
			}

			if ( count( $keys ) >= 3 ) {
				return 'Too many token parameters.';
			}

			// get our id - it must only using 2 params
			if ( count( $keys ) === 2 ) {
				$user_id = 'current' === $keys[0] ? $user_id : absint( $keys[0] );
				$text    = $keys[1];
			}

			$user = get_userdata( $user_id );
			$user = isset( $user->data ) ? get_object_vars( $user->data ) : null;

			if ( null === $user ) {
				return 'No user found';
			}

			// date fields with date format filter
			if ( $filter && in_array( $text, array( 'user_registered' ), true ) ) {
				return date( $filter, strtotime( $user[ $text ] ) );
			}

			// standard user fields
			if ( isset( $user[ $text ] ) ) {
				return wp_kses_post( $user[ $text ] );
			}

			// ignore password
			if ( 'user_pass' === $text ) {
				return 'Password not allowed.';
			}

			// try for a meta field
			$meta = get_user_meta( $user['ID'], $text, true );
			if ( $meta && ( $filter || '0' === $filter ) ) {
				$text = maybe_unserialize( $meta );
				if ( $text ) {
					$text = $text[ $filter ];
					return wp_kses_post( $text );
				}
			}

			if ( $meta && ! $filter ) {
				return wp_kses_post( $meta );
			}
		}

		return (string) $text;
	}

	/*
	 * Post Tokens
	 * Format: (type:id of post:post field|format for date)
	 * Examples: (post:current:post_title), (post:current:ID), (post:453:post_date|Y-m-d), (post:453:_yoast_wpseo_metadesc)
	 *
	 */
	public function replace_post_token( $text ) {

		$type = '(post:';

		if ( strpos( $text, $type ) !== false ) {

			$filter = '';

			// remove the start & end
			$text = strstr( $text, $type ); // remove anything before type
			$text = str_replace( $type, '', $text );  // remove type
			$text = rtrim( $text, ')' ); // remove last bracket

			// extract the filter if any (date filter)
			if ( strpos( $text, '|' ) !== false ) {
				$filter = substr( $text, strpos( $text, '|' ) + 1 );
			}

			// remove the filter pipe
			if ( $filter ) {
				$text = str_replace( '|' . $filter, '', $text );
			}

			// get sub keys
			$keys = explode( ':', $text );

			// send errors
			if ( count( $keys ) === 1 ) {
				return 'Missing a token parameter.';
			}

			if ( count( $keys ) >= 3 ) {
				return 'Too many token parameters.';
			}

			// get our id - it must only using 2 params
			if ( count( $keys ) === 2 ) {
				$post_id = 'current' === $keys[0] ? get_the_ID() : absint( $keys[0] );
				$text    = $keys[1];
			}

			// if we don't have a post id here, probably doing ajax
			$post_id = ! $post_id ? $this->post_id : $post_id;

			$post = get_post( $post_id, ARRAY_A );

			// date fields with date format filter
			if ( $filter && in_array( $text, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ), true ) ) {
				return date( $filter, strtotime( $post[ $text ] ) );
			}

			// permalink
			if ( 'permalink' === $text ) {
				return get_the_permalink( $post['ID'] );
			}

			// standard post fields
			if ( isset( $post[ $text ] ) ) {
				return wp_kses_post( $post[ $text ] );
			}

			// try for a meta field
			$meta = get_post_meta( $post['ID'], $text, true );
			if ( $meta && ( $filter || '0' === $filter ) ) {
				$text = maybe_unserialize( $meta );
				if ( $text ) {
					$text = $text[ $filter ];
					return wp_kses_post( $text );
				}
			}

			if ( $meta && ! $filter ) {
				return wp_kses_post( $meta );
			}
		}

		return (string) $text;
	}


	/*
	 * Woocommerce Order Tokens
	 * Format: (type:id of post:post field|format for date)
	 * Examples: (post:current:post_title), (post:current:ID), (post:453:post_date|Y-m-d), (post:453:_yoast_wpseo_metadesc)
	 *
	 */
	public function replace_woocommerce_order_token( $text ) {

		if ( ! function_exists( 'is_wc_endpoint_url' ) ) {
			return $text;
		}

		$current_order_id = false;

		// get our order_id or else bail
		if ( is_wc_endpoint_url( 'order-received' ) ||
			( isset( $_GET['order_id'] ) && ( isset( $_GET['key'] ) && strpos( 'wc_order_', $_GET['key'] ) !== false ) )
		) {

			global $wp;
			$current_order_id = intval( str_replace( 'checkout/order-received/', '', $wp->request ) );

			if ( ! $current_order_id ) {
				$current_order_id = isset( $_GET['order_id'] ) ? absint( $_GET['order_id'] ) : false;
			}

			// if we couldn't get an order_id, bail
			if ( ! $current_order_id ) {
				return (string) $text;
			}
		}

		$type = '(woo:';

		if ( strpos( $text, $type ) !== false ) {

			$meta = '';

			// remove the start & end
			$text = strstr( $text, $type ); // remove anything before type
			$text = str_replace( $type, '', $text );  // remove type
			$text = rtrim( $text, ')' ); // remove last bracket

			$filter = '';

			// extract the filter if any (date filter)
			if ( strpos( $text, '|' ) !== false ) {
				$filter = substr( $text, strpos( $text, '|' ) + 1 );
			}

			// remove the filter pipe
			if ( $filter ) {
				$text = str_replace( '|' . $filter, '', $text );
			}

			// get sub keys
			$keys = explode( ':', $text );

			// send errors if keys are not equal to 2
			if ( count( $keys ) == 1 ) {
				return 'Missing a token parameter.';
			}

			if ( count( $keys ) >= 3 ) {
				return 'Too many token parameters.';
			}

			$type = $keys[0]; // should be order or product
			$text = $keys[1];

			$post = get_post( $current_order_id, ARRAY_A );

			if ( $type == 'order' ) {

				// date fields with date format filter
				if ( $filter && in_array( $text, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ) ) ) {
					return date( $filter, strtotime( $post[ $text ] ) );
				}

				// standard post fields for the order
				if ( isset( $post[ $text ] ) ) {
					return wp_kses_post( $post[ $text ] );
				}

				// try for a meta field for the order
				$meta = get_post_meta( $post['ID'], $text, true );
				if ( $meta && ( $filter || $filter === '0' ) ) {
					$text = maybe_unserialize( $meta );
					if ( $text ) {
						$text = $text[ $filter ];
						return wp_kses_post( $text );
					}
				}
			}

			if ( $type == 'product' ) {

				// get items that were ordered
				// create the order object
				if ( $current_order_id ) {

					$order = wc_get_order( $current_order_id );
					// retrieve the items associated with that order
					$order_items = $order->get_items();

					if ( is_array( $order_items ) ) {

						foreach ( $order_items as $order_item_id => $item ) {

							$data       = $item->get_data();
							$product_id = $data['product_id'];

							$product = get_post( $product_id, ARRAY_A );

							// standard post fields for the order
							if ( isset( $product[ $text ] ) ) {
								return wp_kses_post( $product[ $text ] );
							}

							if ( isset( $data[ $text ] ) ) {
								return wp_kses_post( $data[ $text ] );
							}

							// try for a meta field for the order
							$meta = get_post_meta( $product['ID'], $text, true );

							if ( ! $meta ) {
								$meta = wc_get_order_item_meta( $order_item_id, $text, true );
							}

							if ( $meta && ( $filter || $filter === '0' ) ) {
								$text = maybe_unserialize( $meta );
								if ( $text ) {
									$text = $text[ $filter ];
									return wp_kses_post( $text );
								}
							}
							// if no filter in meta
							if ( $meta && ! $filter ) {
								return wp_kses_post( $meta );
							}
						}
					}
				}
			}

			if ( $meta && ! $filter ) {
				return wp_kses_post( $meta );
			}
		}

		return (string) $text;
	}



	/*
	 * Lifter LMS Tokens
	 * Format: (type:id of post:post field|format for date)
	 * Examples: (post:current:post_title), (post:current:ID), (post:453:post_date|Y-m-d), (post:453:_yoast_wpseo_metadesc)
	 *
	 */
	public function replace_lifter_lms_token( $text ) {

		$current_order_id = false;

		// get our order_id or else bail
		if ( isset( $_GET['order-complete'] ) ) {

			$current_order_id = sanitize_text_field( $_GET['order-complete'] );

			if ( ! $current_order_id ) {
				return;
			}

			// if we couldn't get an order_id, bail
			if ( ! $current_order_id ) {
				return (string) $text;
			}
		}

		$type = '(lifter:order';

		if ( strpos( $text, $type ) !== false ) {

			// remove the start & end
			$text = strstr( $text, $type ); // remove anything before type
			$text = str_replace( $type, '', $text );  // remove type
			$text = rtrim( $text, ')' ); // remove last bracket

			$filter = '';

			// extract the filter if any (date filter)
			if ( strpos( $text, '|' ) !== false ) {
				$filter = substr( $text, strpos( $text, '|' ) + 1 );
			}

			// remove the filter pipe
			if ( $filter ) {
				$text = str_replace( '|' . $filter, '', $text );
			}

			// get sub keys
			$keys = explode( ':', $text );

			// send errors
			if ( count( $keys ) == 1 ) {
				return 'Missing a token parameter.';
			}

			if ( count( $keys ) >= 3 ) {
				return 'Too many token parameters.';
			}

			$text = $keys[1];

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

			$post = get_post( $post_id, ARRAY_A );

			// date fields with date format filter
			if ( $filter && in_array( $text, array( 'post_date', 'post_date_gmt', 'post_modified', 'post_modified_gmt' ) ) ) {
				return date( $filter, strtotime( $post[ $text ] ) );
			}

			// standard post fields
			if ( isset( $post[ $text ] ) ) {
				return wp_kses_post( $post[ $text ] );
			}

			// try for a meta field
			$meta = get_post_meta( $post['ID'], $text, true );
			if ( $meta && ( $filter || $filter === '0' ) ) {
				$text = maybe_unserialize( $meta );
				if ( $text ) {
					$text = $text[ $filter ];
					return wp_kses_post( $text );
				}
			}

			if ( $meta && ! $filter ) {
				return wp_kses_post( $meta );
			}
		}

		return (string) $text;
	}


	/*
	 * Date Tokens
	 * Format: (type|format for date)
	 * Examples: (date), (time), (date|Y-m-d), (date:+1 month), (date:+1 month|y-md)
	 *
	 */
	public static function replace_date_token( $text ) {

		// just the time
		if ( $text == '(time)' ) {
			return date( get_option( 'time_format' ) );
		}

		$type = '(date';

		if ( strpos( $text, $type ) !== false ) {

			if ( $text == '(date)' ) {
				return date( get_option( 'date_format' ) );
			}

			// remove the start & end
			$text   = strstr( $text, $type ); // remove anything before type
			$text   = str_replace( $type, '', $text );  // remove type
			$text   = rtrim( $text, ')' ); // remove last bracket
			$format = '';

			// extract the format if any (date format)
			$pos = strpos( $text, '|' );
			if ( $pos !== false ) {
				$format = substr( $text, $pos + 1 );
				$text   = str_replace( '|' . $format, '', $text ); // remove the format pipe
			}

			// extract the param if any (+1 month)
			$pos = strpos( $text, ':' );
			if ( $pos !== false ) {
				$text = substr( $text, $pos + 1 );
			}

			// if we have the date plus a param
			if ( $format && $text ) {
				return date( $format, strtotime( $text ) );
			}

			// if we just have the date and no param
			if ( $format && ! $text ) {
				return date( $format );
			}

			// if we just have the date and no param
			if ( ! $format && $text ) {
				return date( get_option( 'date_format' ), strtotime( $text ) );
			}
		}

		return (string) $text;
	}

	/*
	 * System Tokens
	 * Format: (type:variable:query var)
	 * Examples: (system:get:my_query_var)
	 *
	 */
	public function replace_system_token( $text ) {

		$pieces = explode( '(system:', $text );

		if ( count( $pieces ) > 1 ) {
			foreach ( $pieces as $i => $value ) {

				$raw    = explode( ')', $value );
				$params = reset( $raw );
				$params = rtrim( $params, ')' ); // remove last bracket

				// extract the return key
				// added for WPForms files
				$return_key = '';
				$pos        = strpos( $params, '|' );
				if ( $pos !== false ) {
					$return_key = substr( $params, $pos + 1 );
					$params     = str_replace( '|' . $return_key, '', $params ); // remove the format pipe
				}

				// get sub keys
				$keys = explode( ':', $params );

				if ( ! isset( $keys[0] ) || ! isset( $keys[1] ) ) {
					continue;
				}

				$type = $keys[0];
				$var  = sanitize_text_field( $keys[1] );

				switch ( $type ) {
					case 'get':
					case '_get':
					case '_GET':
					case 'GET':
						$text = isset( $_GET[ $var ] ) ? $_GET[ $var ] : '';
						break;
					case 'post':
					case '_post':
					case '_POST':
					case 'POST':
						// elementor
						if ( isset( $_POST['form_fields'] ) ) {
							$text = $_POST['form_fields'][ $var ];
						} else {                        // wpforms
							if ( isset( $_POST['wpforms']['fields'] ) ) {

								if ( isset( $_POST['wpforms']['fields'][ $var ][ $return_key ] ) ) {

									$text = $_POST['wpforms']['fields'][ $var ][ $return_key ];

								} elseif ( isset( $_POST['wpforms']['fields'][ $var ]['date'] ) ) {

									$text = $_POST['wpforms']['fields'][ $var ]['date'];

								} elseif ( isset( $_POST['wpforms']['fields'][ $var ] ) && is_array( $_POST['wpforms']['fields'][ $var ] ) ) {

									$text = implode( ',', $_POST['wpforms']['fields'][ $var ] );

								} else {

									// $_POST[ $var ] is for WPForms form field
									$text = isset( $_POST['wpforms']['fields'][ $var ] ) ? $_POST['wpforms']['fields'][ $var ] : $_POST[ $var ];

									if ( isset( $return_key ) && $return_key ) {
										$text = stripslashes( $text );
										$text = json_decode( $text, true, JSON_UNESCAPED_SLASHES );

										$text = isset( $text[ $return_key ] ) ? $text[ $return_key ] : $text[0][ $return_key ];
									}
								}
							} elseif ( isset( $_POST['frm_action'] ) && isset( $_POST['item_meta'] ) ) { // formidable forms
								$text = $_POST['item_meta'][ $var ];
							} else { // else just standard
								$text = isset( $_POST[ $var ] ) ? $_POST[ $var ] : '';
							}
						}

						break;
					case 'file':
					case 'FILE':
						if ( isset( $_POST['gform_submit'] ) ) {

							$form_id  = absint( $_POST['gform_submit'] );
							$entry_id = $var;

							// Generate the yearly and monthly dirs
							$time            = current_time( 'mysql' );
							$y               = substr( $time, 0, 4 );
							$m               = substr( $time, 5, 2 );
							$upload_root     = GFFormsModel::get_upload_url( $form_id );
							$target_root     = GFFormsModel::get_upload_path( $form_id ) . "/$y/$m/";
							$target_root_url = $upload_root . "/$y/$m/";
							$file_name       = sanitize_text_field( $_FILES[ $var ]['name'] );

							// get filename if multi-part form
							if ( isset( $_POST['gform_uploaded_files'] ) && ! empty( $_POST['gform_uploaded_files'] ) ) {

								$files = json_decode( stripslashes( $_POST['gform_uploaded_files'] ), true );

								if ( is_array( $files ) ) {
									foreach ( $files as $key => $value ) {
										if ( $key == $var ) {
											$file_name = $value;
										}
									}
								}
							}

							// modified from GF get_file_upload_path() function
							//Add the original filename to our target path.
							//Result is "uploads/filename.extension"
							$extension = pathinfo( $file_name, PATHINFO_EXTENSION );
							if ( ! empty( $extension ) ) {
								$extension = '.' . $extension;
							}

							$file_name = wp_basename( $file_name, $extension );
							$file_name = sanitize_file_name( $file_name );

							$counter  = 1;
							$new_path = $target_root . $file_name . $extension;

							for ( $i = 999; $i >= 1; $i-- ) {
								$target_path = $target_root . $file_name . "$i" . $extension;

								if ( file_exists( $target_path ) ) {
									$new_path = $target_path;
									break;
								}
							}

							//Remove '.' from the end if file does not have a file extension
							$new_path = trim( $new_path, '.' );

							//creating url
							$target_url = str_replace( $target_root, $target_root_url, $new_path );

							// from GF get_donwload_url
							// Only hide the real URL if the location of the file is in the upload root for the form.
							// The upload root is calculated using the WP Salts so if the WP Salts have changed then file can't be located during the download request.
							if ( strpos( $target_url, $upload_root ) !== false ) {
								$target_url   = str_replace( $upload_root, '', $target_url );
								$download_url = site_url( 'index.php' );
								$args         = array(
									'gf-download' => urlencode( $target_url ),
									'form-id'     => $form_id,
									'field-id'    => $entry_id,
									'hash'        => GFCommon::generate_download_hash( $form_id, $entry_id, $target_url ),
								);

								$download_url = add_query_arg( $args, $download_url );
							}

							$text = isset( $download_url ) ? $download_url : $target_url;

						}

						break;
					case '_request':
					case '_REQUEST':
					case 'request':
						$text = isset( $_REQUEST[ $var ] ) ? $_REQUEST[ $var ] : '';
						break;
					case 'cookie':
					case '_cookie':
					case '_COOKIE':
					case 'COOKIE':
						$text = isset( $_COOKIE[ $var ] ) ? $_COOKIE[ $var ] : '';
						break;
					case 'session':
					case '_session':
					case '_SESSION':
					case 'SESSION':
						$text = isset( $_SESSION[ $var ] ) ? $_SESSION[ $var ] : '';
						break;
					case 'server':
					case '_server':
					case '_SERVER':
					case 'SERVER':
						$text = isset( $_SERVER[ $var ] ) ? $_SERVER[ $var ] : '';
						break;
				}
			}
		}

		return (string) $text;
	}


	/*
	 * Chain Tokens
	 * Format: (type:name of chain)
	 * Examples: (chain:start), (chain:2), (chain:end)
	 *
	 */
	public function replace_chain_token( $text ) {

		$type = '(chain:';

		if ( strpos( $text, $type ) !== false ) {

			// remove the start & end
			$text = strstr( $text, $type ); // remove anything before type
			$text = str_replace( $type, '', $text );  // remove type
			$text = rtrim( $text, ')' ); // remove last bracket

			// get the chain name
			$keys = explode( ':', $text );

			// send errors
			if ( count( $keys ) == 0 ) {
				return 'Missing a token parameter.';
			}

			if ( count( $keys ) >= 2 ) {
				return 'Too many token parameters.';
			}

			// get our chain name
			$text = $keys[0];

			// get our transient data from the last chain
			$text = get_transient( 'wpgetapi_chain_' . $text );

			if ( is_array( $text ) ) {
				$text = json_encode( $text, true );
			}
		}

		return $text;
	}


	/*
	 * Argument Tokens
	 *
	 */
	public function replace_action_token( $text ) {

		$type = '(action:';

		if ( strpos( $text, $type ) !== false ) {

			// if( ! $this->action_args )
			//     return;

			// remove the start & end
			$text = strstr( $text, $type ); // remove anything before type
			$text = str_replace( $type, '', $text );  // remove type
			$text = rtrim( $text, ')' ); // remove last bracket

			// get the argument number
			$keys = explode( ':', $text );

			// send errors
			if ( count( $keys ) == 0 ) {
				return 'Missing a token parameter.';
			}

			// get our value
			$arg_value = $this->action_args;

			// if not array, simply return the value
			if ( ! is_array( $arg_value ) ) {

				return $arg_value;

			} else {

				$count = count( $keys );

				switch ( $count ) {
					case 1:
						$text = isset( $arg_value[ $keys[0] ] ) ? $arg_value[ $keys[0] ] : 'Key doesn\'t exist';
						break;
					case 2:
						$text = isset( $arg_value[ $keys[0] ][ $keys[1] ] ) ? $arg_value[ $keys[0] ][ $keys[1] ] : 'Key doesn\'t exist';
						break;
					case 3:
						$text = isset( $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ] ) ? $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ] : 'Key doesn\'t exist';
						break;
					case 4:
						$text = isset( $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ][ $keys[3] ] ) ? $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ][ $keys[3] ] : 'Key doesn\'t exist';
						break;
					case 5:
						$text = isset( $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ][ $keys[3] ][ $keys[4] ] ) ? $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ][ $keys[3] ][ $keys[4] ] : 'Key doesn\'t exist';
						break;
					case 6:
						$text = isset( $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ][ $keys[3] ][ $keys[4] ][ $keys[5] ] ) ? $arg_value[ $keys[0] ][ $keys[1] ][ $keys[2] ][ $keys[3] ][ $keys[4] ][ $keys[5] ] : 'Key doesn\'t exist';
						break;

				}
			}
		}

		if ( is_array( $text ) ) {
			$text = json_encode( $text, true );
		}

		return $text;
	}


	/**
	 * Maybe do tokens in endpoint
	 */
	public function endpoint_tokens( $endpoint, $api ) {

		if ( ! isset( $endpoint ) ) {
			return;
		}

		$this->user_id     = isset( $api->args['user_id'] ) ? $api->args['user_id'] : '';
		$this->post_id     = isset( $api->args['post_id'] ) ? $api->args['post_id'] : '';
		$this->action_args = isset( $api->args['action_args'] ) ? $api->args['action_args'] : '';

		if ( strpos( $endpoint, '(' ) !== false && strpos( $endpoint, ')' ) !== false ) {

			// $endpoint = str_replace( $endpo, $token_value, $endpoint );

			$tokens = $this->get_the_tokens( $endpoint );

			if ( ! empty( $tokens ) ) {

				foreach ( $tokens as $i => $token ) {
					$token_value = $this->replace_tokens( $token );
					$endpoint    = str_replace( $token, $token_value, $endpoint );
				}
			}
		}
		return $endpoint;
	}


	/**
	 * Maybe do tokens in query string
	 */
	public function query_string_tokens( $params, $api ) {

		if ( ! isset( $params ) || ! is_array( $params ) ) {
			return;
		}

		$this->user_id     = isset( $api->args['user_id'] ) ? $api->args['user_id'] : '';
		$this->post_id     = isset( $api->args['post_id'] ) ? $api->args['post_id'] : '';
		$this->action_args = isset( $api->args['action_args'] ) ? $api->args['action_args'] : '';

		foreach ( $params as $key => $value ) {

			if ( ! is_array( $value ) ) {

				if ( strpos( $value, '(' ) !== false && strpos( $value, ')' ) !== false ) {

					$tokens = $this->get_the_tokens( $value );

					if ( ! empty( $tokens ) ) {

						foreach ( $tokens as $i => $token ) {

							$token_value = $this->replace_tokens( $token );

							// if we are passing array, set error message
							// if( is_array( $token_value ) ) {
							//     $value = 'Value needs to be string. Array was passed.';
							// } else {
								$value = str_replace( $token, $token_value, $value );
							//}

						}
					}
				}
			} else {

				$value = $this->query_string_tokens( $value, $api );

			}

			$params[ $key ] = $value;

		}

		return $params;
	}

	/**
	 * Maybe do tokens in headers
	 */
	public function header_tokens( $headers, $api ) {

		if ( ! isset( $headers['headers'] ) || ! is_array( $headers['headers'] ) ) {
			return;
		}

		$this->user_id     = isset( $api->args['user_id'] ) ? $api->args['user_id'] : '';
		$this->post_id     = isset( $api->args['post_id'] ) ? $api->args['post_id'] : '';
		$this->action_args = isset( $api->args['action_args'] ) ? $api->args['action_args'] : '';

		foreach ( $headers['headers'] as $key => $value ) {

			if ( ! is_array( $value ) ) {

				if ( strpos( $value, '(' ) !== false && strpos( $value, ')' ) !== false ) {

					$tokens = $this->get_the_tokens( $value );

					if ( ! empty( $tokens ) ) {

						foreach ( $tokens as $i => $token ) {

							$token_value = $this->replace_tokens( $token );

							// if we are passing array, set error message
							// if( is_array( $token_value ) ) {
							//     $value = 'Value needs to be string. Array was passed.';
							// } else {
								$value = str_replace( $token, $token_value, $value );
							//}

						}
					}
				}
			} else {

				$value = $this->header_tokens( $value, $api );

			}

			$headers['headers'][ $key ] = $value;

		}

		return $headers;
	}

	/**
	 * Maybe do tokens in body
	 * REMINDER: does not work with GET
	 */
	public function body_tokens( $params, $api ) {

		if ( ! isset( $params ) ) {
			return $params;
		}

		$raw         = false;
		$orig_params = $params;

		if ( ! is_array( $params ) && strpos( $params, '?xml' ) !== false ) {
			$xml    = simplexml_load_string( $params, 'SimpleXMLElement', LIBXML_NOCDATA );
			$json   = json_encode( $xml );
			$params = json_decode( $json, true );
		}

		if ( ( ! is_array( $params ) ) &&
			( substr( $params, 0, 1 ) === '{' || substr( $params, 0, 1 ) === '[' )
		) {

			// check for tokens and don't bother going any further if none
			if ( strpos( $params, '(' ) === false && strpos( $params, ')' ) === false ) {
				return $params;
			}

			$raw    = true;
			$params = json_decode( $params, true );
			// if there was a value, but now there isn't, revert back
			if ( ! $params && $orig_params ) {
				$params = $orig_params;
			}
		}

		$this->user_id     = isset( $api->args['user_id'] ) ? $api->args['user_id'] : '';
		$this->post_id     = isset( $api->args['post_id'] ) ? $api->args['post_id'] : '';
		$this->action_args = isset( $api->args['action_args'] ) ? $api->args['action_args'] : '';

		if ( $this->action_args && ! is_array( $params ) ) {
			$raw    = true;
			$params = array( $params );
		}

		$params = $this->body_do_tokens( $params, $api );

		// looking for JSON
		// this is if we hvae line_items loop
		foreach ( $params as $key => $value ) {

			$orig_value = $value;

			if ( ( is_string( $value ) ) &&
				( strpos( $value, '{' ) !== false )
			) {
				$params[ $key ] = json_decode( $value, true );
			}
			// if there was a value, but now there isn't, revert back
			if ( ! $params[ $key ] && $orig_value ) {
				$params[ $key ] = $orig_value;
			}
		}

		if ( $raw ) {

			// removed 3.5.2
			// reinstated 3.5.5
			if ( ( is_string( $orig_params ) ) &&
				( substr( $orig_params, 0, 1 ) === '[' )
			) {
				$params = json_encode( $params, true );
			} else {
				$params = json_encode( $params[0], true );
			}
		}

		return $params;
	}


	public function body_do_tokens( $params, $api ) {

		$tmp = array();

		foreach ( $params as $key => $value ) {

			if ( ! is_array( $value ) ) {

				if ( ! is_string( $value ) ) {
					return $params;
				}

				if ( strpos( $value, '(' ) !== false && strpos( $value, ')' ) !== false && ( strpos( $value, ':' ) !== false || strpos( $value, '|' ) !== false ) ) {

					$tokens = $this->get_the_tokens( $value );

					if ( ! empty( $tokens ) ) {

						$loop = $this->do_loop_line_items( $tokens, $this->action_args, $value, $api );

						if ( $loop ) {
							$value = $loop;
						}

						foreach ( $tokens as $i => $token ) {

							// indicates this is not a token, but may be graphql
							if ( strpos( $token, '{' ) !== false ) {
								continue;
							}

							$token_value = $this->replace_tokens( $token );

							// if we are passing array, set error message
							if ( is_array( $token_value ) ) {
								$value = 'Value needs to be string. Array was passed.';
							} else {
								$value = str_replace( $token, $token_value, $value );
							}
						}

						$params[ $key ] = $value;

					}
				}
			} else {

				$params[ $key ] = $this->body_do_tokens( $value, $api );
				// foreach ( $value as $sub_key => $sub_value ) {
				//     $params[$key][$sub_key] =
				// }

			}
		}

		return $params;
	}


	public function get_the_tokens( $str, $start = '(', $end = ')', $with_from_to = true ) {
		$str = $this->maybe_do_nested_tokens( $str );

		$arr = array();
		if ( ! is_string( $str ) ) {
			return $arr;
		}

		$last_pos = 0;
		$last_pos = strpos( $str, $start, $last_pos );
		while ( $last_pos !== false ) {
			$t        = strpos( $str, $end, $last_pos );
			$arr[]    = ( $with_from_to ? $start : '' ) . substr( $str, $last_pos + 1, $t - $last_pos - 1 ) . ( $with_from_to ? $end : '' );
			$last_pos = strpos( $str, $start, $last_pos + 1 );
		}

		return $arr;
	}
}

return new WpGetApi_Extras_Tokens();
