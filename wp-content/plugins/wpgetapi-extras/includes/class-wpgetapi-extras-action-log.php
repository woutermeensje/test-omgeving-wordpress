<?php

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;


/**
 * Wpgetapi_Extras_Action_Log Class
 */
class Wpgetapi_Extras_Action_Log {

	/**
	 * Main constructor
	 * @since 2.7.0
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks
	 */
	public function hooks() {
		add_action( 'admin_menu', array( $this, 'action_log_menu' ) );
		add_action( 'admin_post_wpgetapi_delete_logs', array( $this, 'delete_logs' ) );
	}

	/**
	 * get file location
	 *
	 */
	public function get_file_location() {
		$upload     = wp_upload_dir();
		$upload_dir = $upload['basedir'] . '/wpgetapi-logs';
		return $upload_dir . '/actions.log';
	}

	/**
	 * Checks if log file exists
	 *
	 */
	public function has_log_file() {
		if ( file_exists( $this->get_file_location() ) ) {
			return true;
		}
	}


	/**
	 * Adds the plugin license page to the admin menu.
	 *
	 * @return void
	 */
	public function action_log_menu() {

		add_submenu_page(
			'wpgetapi_setup',
			__( 'Actions Log' ),
			__( 'Actions Log' ),
			'manage_options',
			'wpgetapi_actions_log',
			array( $this, 'actions_log_page' )
		);
	}


	public function actions_log_page() {

		add_settings_section(
			'wpgetapi_actions_log_section',
			__( 'Action Logs' ),
			array( $this, 'action_log_settings_section' ),
			'wpgetapi_actions_log'
		);

		?>
		<div class="wrap">
			<h2><?php esc_html_e( 'WPGetAPI Actions Log' ); ?></h2>
			<!-- <form method="post" action="options.php"> -->

				<?php
				do_settings_sections( 'wpgetapi_actions_log' );
				settings_fields( 'wpgetapi_actions_log_section' );

				// only show save button if extensions installed
				// if ( $this->has_extension() )
				//  submit_button();

				?>

			<!-- </form> -->
		<?php
	}


	/**
	 * Adds content to the settings section.
	 *
	 * @return void
	 */
	public function action_log_settings_section() {

		?>

		<div class="intro">
			<p>A log of data for each time an endpoint is called using an Action.</p>
		</div>

		<?php if ( $this->has_log_file() ) { ?>

			<a class="button" href="<?php echo admin_url( 'admin-post.php' ); ?>?action=wpgetapi_delete_logs">Delete Logs</a>

			<textarea style="margin-top:30px;width:100%;min-height:600px">
				<?php echo file_get_contents( $this->get_file_location() ); ?>
			</textarea>

		<?php } else { ?>

			<h3>No Logs Found</h3>
			<p>The log file could not be found.<br>
			This is either because you have not setup any actions, your action has not been fired yet or there is a permissions issue preventing the logging of actions.</p>

			<?php
		}
	}

	/**
	 * Delete the logs
	 *
	 * @return void
	 */
	public function delete_logs() {
		file_put_contents( $this->get_file_location(), '' );
		wp_redirect( admin_url( '/admin.php?page=wpgetapi_actions_log' ) );
		exit;
	}
}

return new Wpgetapi_Extras_Action_Log();

