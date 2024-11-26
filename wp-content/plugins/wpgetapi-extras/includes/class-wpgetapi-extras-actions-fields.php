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
class WpGetApi_Extras_Actions_Fields {


	/**
	 * Main constructor
	 *
	 */
	public function __construct() {

		// add new actions field
		add_filter( 'wpgetapi_fields_endpoints', array( $this, 'actions_field' ), 10, 1 );
	}


	/**
	 * Add new fields for actions
	 */
	public function actions_field( $endpoint_fields ) {

		if ( isset( $_GET['page'] ) && strpos( $_GET['page'], 'wpgetapi_' ) !== false ) {

			$action_field = $this->get_action_field();

			$conditionals[] = array(
				'name'       => 'Action Options',
				'id'         => 'conditionals_before',
				'type'       => 'title',
				'classes'    => '',
				'before_row' => '<div class="wpgetapi-actions-before">',

			);

			$conditionals[] = array(
				'name'              => __( 'Post Type', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Choose the post type to use for this action.', 'wpgetapi-extras' ) . '"></span>',
				'id'                => 'post_type',
				'type'              => 'multicheck',
				'select_all_button' => false,
				'classes'           => 'field-post-type',
				'desc'              => __( 'Select post type', 'wpgetapi-extras' ),
				'options_cb'        => 'wpgetapi_pro_get_post_types',
				'attributes'        => array(
					'data-conditional-id'    => 'actions',
					'data-conditional-value' => wp_json_encode( array( 'transition_post_status', 'new_post_published', 'delete_post' ) ),
				),
			);

			$conditionals[] = array(
				'name'              => __( 'Old Post Status', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Run this action only when the post transitions from this status.', 'wpgetapi-extras' ) . '"></span>',
				'id'                => 'old_post_status',
				'type'              => 'multicheck',
				'select_all_button' => false,
				'classes'           => 'field-old-status',
				'desc'              => __( 'Old post status', 'wpgetapi-extras' ),
				'options_cb'        => 'wpgetapi_pro_get_post_statuses',
				'attributes'        => array(
					'data-conditional-id'    => 'actions',
					'data-conditional-value' => 'transition_post_status',
				),
			);

			$conditionals[] = array(
				'name'              => __( 'New Post Status', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Run this action only when the post transitions to this new status.', 'wpgetapi-extras' ) . '"></span>',
				'id'                => 'new_post_status',
				'type'              => 'multicheck',
				'select_all_button' => false,
				'classes'           => 'field-new-status',
				'desc'              => __( 'New post status', 'wpgetapi-extras' ),
				'options_cb'        => 'wpgetapi_pro_get_post_statuses',
				'attributes'        => array(
					'data-conditional-id'    => 'actions',
					'data-conditional-value' => 'transition_post_status',
				),
			);

			// if woocommerce installed
			if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				$conditionals[] = array(
					'name'              => __( 'Old Order Status', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Run this action only when the order transitions from this status.', 'wpgetapi-extras' ) . '"></span>',
					'id'                => 'old_order_status',
					'type'              => 'multicheck',
					'select_all_button' => false,
					'classes'           => 'field-old-status',
					'desc'              => __( 'Old Order Status', 'wpgetapi-extras' ),
					'options_cb'        => 'wpgetapi_pro_get_woo_order_statuses',
					'attributes'        => array(
						'data-conditional-id'    => 'actions',
						'data-conditional-value' => 'woocommerce_order_status_changed',
					),
				);
				$conditionals[] = array(
					'name'              => __( 'New Order Status', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Run this action only when the order transitions to this new status.', 'wpgetapi-extras' ) . '"></span>',
					'id'                => 'new_order_status',
					'type'              => 'multicheck',
					'select_all_button' => false,
					'classes'           => 'field-new-status',
					'desc'              => __( 'New Order Status', 'wpgetapi-extras' ),
					'options_cb'        => 'wpgetapi_pro_get_woo_order_statuses',
					'attributes'        => array(
						'data-conditional-id'    => 'actions',
						'data-conditional-value' => 'woocommerce_order_status_changed',
					),
				);
			}

			// if elementor pro installed
			if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {
				$conditionals[] = array(
					'name'       => __( 'Elementor Form Name', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The exact name of the form you want to use for this endpoint.', 'wpgetapi-extras' ) . ' ' . esc_attr__( 'You should ensure your form name is unique.', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'elementor_form_name',
					'type'       => 'text',
					'classes'    => 'field-elementor-form-name',
					'desc'       => __( 'Elementor Form Name', 'wpgetapi-extras' ),
					'attributes' => array(
						'data-conditional-id'    => 'actions',
						'data-conditional-value' => 'elementor_forms',
					),
				);

			}

			// if these forms plugins are installed
			if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ||
				is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ||
				is_plugin_active( 'wpforms/wpforms.php' ) || is_plugin_active( 'wpforms-lite/wpforms.php' ) ||
				is_plugin_active( 'ultimate-member/ultimate-member.php' ) ||
				is_plugin_active( 'ninja-forms/ninja-forms.php' )
			) {

				// $forms = isset( $action_field[0]['options'] ) ? $action_field[0]['options'] : null;
				// $gf = array();
				// $cf7 = array();
				// $wpf = array();
				// foreach ( $forms as $key => $form ) {
				//     if ( $key && strpos( $key, 'gravity_forms_' ) !== false )
				//         $gf[] = $key;
				//     if ( $key && strpos( $key, 'contact_form_7_' ) !== false )
				//         $cf7[] = $key;
				//     if ( $key && strpos( $key, 'wpforms_' ) !== false )
				//         $wpf[] = $key;
				// }

				/**
				 * Conditional sending
				 *
				 */
				$conditionals[] = array(
					'name'    => __( 'Validation', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'If validating, the API is called before the form is submitted allowing you to send or not send the form based on API response.', 'wpgetapi-extras' ) . '"></span>',
					'id'      => 'forms_validation',
					'type'    => 'select',
					'default' => 'after',
					'options' => array(
						'after'  => 'No validation',
						'before' => 'Validate using API data',
					),
					'classes' => 'field-forms-validation',
					'desc'    => __( 'Validation', 'wpgetapi-extras' ),
				);

				$conditionals[] = array(
					'name'    => __( 'Type of validation', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Choose how to validate the form submission.', 'wpgetapi-extras' ) . '"></span>',
					'id'      => 'forms_validation_type',
					'type'    => 'select',
					'default' => 'send_if_200',
					'options' => array(
						'send_if_200'               => 'Only submit form if API response code is success',
						'send_if_api_response'      => 'Only submit form if API response contains...',
						'dont_send_if_api_response' => 'Don\'t submit form if API response contains...',
					),
					'classes' => 'field-forms-validation-type',
					'desc'    => __( 'Type of validation', 'wpgetapi-extras' ),
				);

				$conditionals[] = array(
					'name'       => __( 'Value', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The value that you are looking for within the API response.', 'wpgetapi-extras' ) . ' ' . esc_attr__( 'It can be a certain key or a certain value within the response.', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'forms_validation_value',
					'type'       => 'text',
					'classes'    => 'field-forms-validation-value',
					'desc'       => __( 'Value', 'wpgetapi-extras' ),
					'attributes' => array(
						'placeholder' => 'Search for value in API response',
					),
				);

				$conditionals[] = array(
					'name'    => __( 'Error Message', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The validation error message to display to the user.', 'wpgetapi-extras' ) . '"></span>',
					'id'      => 'forms_validation_message',
					'type'    => 'text',
					'classes' => 'field-forms-validation-message',
					'desc'    => __( 'Error Message', 'wpgetapi-extras' ),

				);

				$conditionals[] = array(
					'name'    => __( 'Error Field ID', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The field ID that you want to display the error message on.', 'wpgetapi-extras' ) . '"></span>',
					'id'      => 'forms_validation_field',
					'type'    => 'text',
					'classes' => 'field-forms-validation-field',
					'desc'    => __( 'Error Field ID', 'wpgetapi-extras' ),

				);

				/**
				 * Display output conditionals
				 *
				 */
				$display_options = array(
					'form_confirmation' => 'Confirmation as set within form',
					'api_response'      => 'Data from the API response',
					'redirect'          => 'Redirect',
				);
				$wpdatatables    = is_plugin_active( 'wpdatatables/wpdatatables.php' );
				if ( $wpdatatables ) {
					$display_options['wpdatatables'] = 'Connected wpDataTable';
				}

				$conditionals[] = array(
					'name'    => __( 'Display', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'What do we want to display to the user, or it can be a redirect.', 'wpgetapi-extras' ) . '"></span>',
					'id'      => 'forms_display',
					'type'    => 'select',
					'default' => 'form_confirmation',
					'options' => $display_options,
					'classes' => 'field-forms-display',
					'desc'    => __( 'Display', 'wpgetapi-extras' ),
				);

				$conditionals[] = array(
					'name'       => __( 'Key', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . __( 'If the API response you need is within an array, you can step down into the array and select a key to get the corresponding value you need.', 'wpgetapi-extras' ) . ' ' . __( 'Works the same way as Nested Data but only supports getting single value from a single key.<br><a href=\'https://wpgetapi.com/docs/retrieve-nested-data/\' target=\'_blank\'>More Info</a>', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'forms_display_keys',
					'type'       => 'text',
					'classes'    => 'field-forms-display-keys',
					'desc'       => __( 'Key', 'wpgetapi-extras' ),
					'attributes' => array(
						'placeholder' => '{some_key|0|value}',
					),
				);

				$conditionals[] = array(
					'name'       => __( 'Success URL', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The URL to go to, based on the success value from the API.', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'forms_success_url',
					'type'       => 'text',
					'classes'    => 'field-forms-success-url',
					'desc'       => __( 'Success URL', 'wpgetapi-extras' ),
					'attributes' => array(
						'placeholder' => 'https://yoururl.com/success',
					),
				);

				$conditionals[] = array(
					'name'       => __( 'Success Value', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The key or value to look for in the API when it is a successful API call.', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'forms_success_value',
					'type'       => 'text',
					'classes'    => 'field-forms-success-value',
					'desc'       => __( 'Success Value', 'wpgetapi-extras' ),
					'attributes' => array(
						'placeholder' => 'Search for value in API response',
					),
				);

				$conditionals[] = array(
					'name'       => __( 'Error URL', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The URL to go to, based on the error value from the API.', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'forms_error_url',
					'type'       => 'text',
					'classes'    => 'field-forms-error-url',
					'desc'       => __( 'Error URL', 'wpgetapi-extras' ),
					'attributes' => array(
						'placeholder' => 'https://yoururl.com/error',
					),
				);

				$conditionals[] = array(
					'name'       => __( 'Error Value', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'The key or value to look for in the API when it is a non-successful API call.', 'wpgetapi-extras' ) . '"></span>',
					'id'         => 'forms_error_value',
					'type'       => 'text',
					'classes'    => 'field-forms-error-value',
					'desc'       => __( 'Error Value', 'wpgetapi-extras' ),
					'attributes' => array(
						'placeholder' => 'Search for value in API response',
					),
				);

			}

			$conditionals[] = array(
				'name'      => '',
				'id'        => 'conditionals_after',
				'type'      => 'title',
				'classes'   => '',
				'after_row' => '</div>',

			);

			$endpoint_fields = array_merge( $endpoint_fields, $action_field, $conditionals );

		}

		return $endpoint_fields;
	}

	public function get_action_field() {

		$field = array(
			array(
				'name'    => __( 'Actions', 'wpgetapi-extras' ) . '<span class="dashicons dashicons-editor-help" data-tip="' . esc_attr__( 'Call this endpoint when your chosen action runs.', 'wpgetapi-extras' ) . '"></span>',
				'id'      => 'actions',
				'type'    => 'select',
				'classes' => 'field-actions',
				'desc'    => __( 'Actions', 'wpgetapi-extras' ),
				'options' => $this->get_actions_field_options(),
				'default' => '',
			),
		);

		return $field;
	}

	public function get_actions_field_options() {

		$options = apply_filters(
			'wpgetapi_pro_actions_field_options',
			array(
				''                       => __( '-- No Action --', 'wpgetapi-extras' ),

				'new_post_published'     => __( 'Post/Custom Post - New Post Published', 'wpgetapi-extras' ),
				'transition_post_status' => __( 'Post/Custom Post - Status Changed', 'wpgetapi-extras' ),
				'delete_post'            => __( 'Post/Custom Post - Delete Post', 'wpgetapi-extras' ),

				'user_register'          => __( 'User - New User Registered', 'wpgetapi-extras' ),
				'pre_user_registered'    => __( 'User - Before User Registered', 'wpgetapi-extras' ),
				'delete_user'            => __( 'User - Delete User', 'wpgetapi-extras' ),
				'user_login'             => __( 'User - User Logs In', 'wpgetapi-extras' ),

			)
		);

		// if woocommerce installed
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {

			$woocommerce = array(
				'woocommerce_new_product'          => __( 'WooCommerce - New Product Created', 'wpgetapi-extras' ),
				'woocommerce_new_order'            => __( 'WooCommerce - New Order Created', 'wpgetapi-extras' ),
				'woocommerce_order_status_changed' => __( 'WooCommerce - Order Status Changed', 'wpgetapi-extras' ),
				// 'woo_product_updated' => __( 'WooCommerce - Product Updated', 'wpgetapi-extras' ),
				// 'woo_product_deleted' => __( 'WooCommerce - Product Deleted', 'wpgetapi-extras' ),
			);

			$options = array_merge( $options, $woocommerce );

		}

		// if cf7 installed
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) {

			$args      = array(
				'post_type'      => 'wpcf7_contact_form',
				'posts_per_page' => -1,
			);
			$forms     = array();
			$all_forms = get_posts( $args );

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'contact_form_7_' . $form->ID ] = esc_html__( 'Contact Form 7', 'wpgetapi-extras' ) . ' - ' . $form->post_title;
				}
			} else {
				$forms[] = esc_html__( 'Contact Form 7 - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if gravity forms installed
		if ( is_plugin_active( 'gravityforms/gravityforms.php' ) ) {

			$all_forms = GFAPI::get_forms();
			$forms     = array();

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'gravity_forms_' . $form['id'] ] = esc_html__( 'Gravity Forms', 'wpgetapi-extras' ) . ' - ' . $form['title'];
				}
			} else {
				$forms[] = esc_html__( 'Gravity Forms - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if WPForms installed
		if ( is_plugin_active( 'wpforms/wpforms.php' ) || is_plugin_active( 'wpforms-lite/wpforms.php' ) ) {

			$args      = array(
				'post_type'      => 'wpforms',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$forms     = array();
			$all_forms = get_posts( $args );

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'wpforms_' . $form->ID ] = esc_html__( 'WPForms', 'wpgetapi-extras' ) . ' - ' . $form->post_title;
				}
			} else {
				$forms[] = esc_html__( 'WPForms - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if JetFormBuilder installed
		if ( is_plugin_active( 'jetformbuilder/jet-form-builder.php' ) ) {

			$args      = array(
				'post_type'      => 'jet-form-builder',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$forms     = array();
			$all_forms = get_posts( $args );

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'jet_form_builder_' . $form->ID ] = esc_html__( 'JetFormBuilder', 'wpgetapi-extras' ) . ' - ' . $form->post_title;
				}
			} else {
				$forms[] = esc_html__( 'JetFormBuilder - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if formidable installed
		if ( is_plugin_active( 'formidable/formidable.php' ) ) {

			global $wpdb;

			$all_forms = FrmDb::get_results( $wpdb->prefix . 'frm_forms', array( 'status' => 'published' ), 'id, name' );
			$forms     = array();

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'formidable_forms_' . $form->id ] = esc_html__( 'Formidable Forms', 'wpgetapi-extras' ) . ' - ' . $form->name;
				}
			} else {
				$forms[] = esc_html__( 'Formidable Forms - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if elementor installed
		if ( is_plugin_active( 'elementor-pro/elementor-pro.php' ) ) {

			$forms['elementor_forms'] = esc_html__( 'Elementor Forms', 'wpgetapi-extras' );

			$options = array_merge( $options, $forms );

		}

		// if FluentForm installed
		if ( is_plugin_active( 'fluentform/fluentform.php' ) ) {

			global $wpdb;

			$table     = $wpdb->prefix . 'fluentform_forms';
			$all_forms = $wpdb->get_results(
				"
                SELECT * 
                FROM $table
                WHERE status = 'published'
                "
			);

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'fluent_forms_' . $form->id ] = esc_html__( 'Fluent Forms', 'wpgetapi-extras' ) . ' - ' . $form->title;
				}
			} else {
				$forms[] = esc_html__( 'Fluent Forms - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if WSForms installed
		if ( is_plugin_active( 'ws-form/ws-form.php' ) || is_plugin_active( 'ws-form-pro/ws-form.php' ) ) {

			global $wpdb;
			$all_forms = WS_Form_Common::get_forms_array( false );
			$forms     = array();

			if ( $all_forms ) {
				foreach ( $all_forms as $key => $form ) {
					$forms[ 'ws_forms_' . $key ] = esc_html__( 'WS Forms', 'wpgetapi-extras' ) . ' - ' . $form;
				}
			} else {
				$forms[] = esc_html__( 'WS Forms - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );
		}

		// if Ultimate Member installed and activated
		if ( is_plugin_active( 'ultimate-member/ultimate-member.php' ) ) {

			$args      = array(
				'post_type'      => 'um_form',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
			);
			$forms     = array();
			$all_forms = get_posts( $args );

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'ultimatemember_' . $form->ID ] = esc_html__( 'Ultimate Member', 'wpgetapi-extras' ) . ' - ' . $form->post_title;
				}
			} else {
				$forms[] = esc_html__( 'Ultimate Member - No Form found', 'wpgetapi-extras' );
			}

			$options = array_merge( $options, $forms );

		}

		// if Ninja form installed
		if ( is_plugin_active( 'ninja-forms/ninja-forms.php' ) ) {

			$all_forms = Ninja_Forms()->form()->get_forms();
			$forms     = array();

			if ( $all_forms ) {
				foreach ( $all_forms as $form ) {
					$forms[ 'ninja_forms_' . $form->get_id() ] = esc_html__( 'Ninja Forms -', 'wpgetapi-extras' ) . ' ' . esc_html( $form->get_setting( 'title' ) );
				}
			} else {
				$forms[] = esc_html__( 'Ninja Forms - No Form found', 'wpgetapi-extras' );
			}
			$options = array_merge( $options, $forms );
		}

		// if pmp installed
		if ( is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ) {

			$pmp = array(
				'paid_memberships_pro_after_checkout' => __( 'Paid Memberships Pro - After Checkout', 'wpgetapi-extras' ),
			);

			$options = array_merge( $options, $pmp );

		}

		// if edd installed
		if ( is_plugin_active( 'easy-digital-downloads/easy-digital-downloads.php' ) ) {

			$edd = array(
				'easy_digital_downloads_complete_purchase' => __( 'Easy Digital Downloads - Complete Purchase', 'wpgetapi-extras' ),
			);

			$options = array_merge( $options, $edd );

		}

		return $options;
	}
}

return new WpGetApi_Extras_Actions_Fields();
