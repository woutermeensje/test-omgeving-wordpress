<?php

function wpgetapi_pro_get_post_types( $field ) {
	$args       = array(
		'public'   => true,
		'_builtin' => false,
	);
	$output     = 'objects'; // names or objects, note names is the default
	$operator   = 'and'; // 'and' or 'or'
	$post_types = get_post_types( $args, $output, $operator );
	$items      = array(
		'post' => 'Post',
		'page' => 'Page',
	);
	foreach ( $post_types  as $post_type ) {
		$items[ $post_type->name ] = esc_html( $post_type->labels->singular_name );
	}
	return $items;
}

/**
 * Post statuses
 *
 */
function wpgetapi_pro_get_post_statuses( $field ) {
	$items = apply_filters( 'wpgetapi_pro_get_post_statuses', array( 'any' => 'Any Status' ) );
	return array_merge( $items, get_post_stati() );
}

/**
 * WooCommerce order statuses
 *
 */
function wpgetapi_pro_get_woo_order_statuses( $field ) {
	$items = apply_filters( 'wpgetapi_pro_get_woo_order_statuses', array( 'any' => 'Any Status' ) );
	return array_merge( $items, wc_get_order_statuses() );
}


/**
 * Simple log function
 *
 */
function wpgetapi_action_log( $action = '', $arg_values = array(), $values_sent = array(), $endpoint = array(), $response_code = 200, $api_response = array() ) {

	if ( is_array( $api_response ) ) {
		$api_response = wpgetapi_extras_var_export_short( wpgetapi_format_action_array( $api_response ) );
	}

	if ( is_array( $values_sent ) ) {
		$values_sent = wpgetapi_extras_var_export_short( wpgetapi_format_action_array( $values_sent ) );
	}

	if ( is_array( $arg_values ) ) {
		$arg_values = wpgetapi_extras_var_export_short( wpgetapi_format_action_array( $arg_values ) );
	}

	if ( is_string( $values_sent ) ) {
		$values_sent = str_replace( '\n', '', $values_sent );
		$values_sent = str_replace( '\r', '', $values_sent );
		$values_sent = stripslashes( $values_sent );
	}

	// create the output
	$output  = '[' . date( 'Y-m-d h:i:s' ) . '] :: ' . $action . "\n";
	$output .= 'ACTION DATA :: ' . $arg_values . "\n";
	$output .= 'API ID :: ' . $endpoint['api_id'] . "\n";
	$output .= 'ENDPOINT ID :: ' . $endpoint['endpoint_id'] . "\n";
	$output .= 'SENT TO API :: ' . $values_sent . "\n";
	$output .= 'API RESPONSE CODE :: ' . $response_code . "\n";
	$output .= 'API RESULT :: ' . mb_strimwidth( $api_response, 0, 2000, '... **truncated in the action log only**' ) . "\n";
	$output .= '----------------------------------------------------------' . "\n";

	// get the dir
	$upload     = wp_upload_dir();
	$upload_dir = $upload['basedir'] . '/wpgetapi-logs';
	if ( ! file_exists( $upload_dir ) ) {
		wp_mkdir_p( $upload_dir );
	}

	// write to the file
	$file = fopen( $upload_dir . '/actions.log', 'a' );
	fwrite( $file, "\n" . $output ) . "\n";
	fclose( $file );
}

function wpgetapi_extras_var_export_short( $variable, $indent = '' ) {
	switch ( gettype( $variable ) ) {
		case 'string':
			return '"' . addcslashes( $variable, "\$\r\n\t\v\f" ) . '"';
		case 'array':
			$indexed = array_keys( $variable ) === range( 0, count( $variable ) - 1 );
			$r       = array();
			foreach ( $variable as $key => $value ) {
				$r[] = "$indent    "
					. ( wpgetapi_extras_var_export_short( $key ) . ' => ' )
					. wpgetapi_extras_var_export_short( $value, "$indent    " );
			}
			return "[\n" . implode( ",\n", $r ) . "\n" . $indent . ']';
		case 'boolean':
			return $variable ? 'TRUE' : 'FALSE';
		default:
			return var_export( $variable, true );
	}
}


/**
 * Remove elements from array by exact values list recursively
 *
 * @param array $haystack
 * @param array $values
 *
 * @return array
 */
function wpgetapi_format_action_array( array $haystack ) {

	$nulls     = array( '', null, array() );
	$sensitive = array( 'cvv', 'user_pass', 'password', 'authorization', 'apikey', 'api_key', 'clientid', 'client_id', 'clientsecret', 'client_secret' );

	foreach ( $haystack as $key => $value ) {

		if ( is_array( $value ) ) {
			$haystack[ $key ] = wpgetapi_format_action_array( $haystack[ $key ], $nulls );
		}

		// remove empty values
		if ( in_array( $haystack[ $key ], $nulls, true ) ) {
			unset( $haystack[ $key ] );
		}

		// hide sensitive values
		if ( in_array( strtolower( $key ), $sensitive, true ) ) {
			$haystack[ $key ] = '-- sensitive value hidden --';
		}
	}

	return $haystack;
}
