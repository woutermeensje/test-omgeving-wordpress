<?php
if ( ! defined( 'WPGETAPIDIR' ) ) {
	die( 'No direct access allowed' );
}
?>

<div id="wpgetapi-dashnotice" class="updated">

	<div style="float: right;"><a href="#" onclick="jQuery( '#wpgetapi-dashnotice' ).slideUp(); jQuery.post(ajaxurl, {action: 'wpgetapi_notice_dismiss', subaction: 'wpgetapi_dismiss_dash_notice_until', nonce: '<?php echo esc_js( wp_create_nonce( 'wpgetapi-notice-ajax-nonce' ) ); ?>'});"><?php /* translators: %1s represents the notice dismiss 'month' value. */ printf( __( 'Dismiss (for %s months)', 'wpgetapi' ), 12 ); ?></a></div>
	
	<h3><?php esc_html_e( 'Thank you for installing WPGetAPI.', 'wpgetapi' ); ?></h3>	

	<a href="https://wpgetapi.com" target="_blank"><img id="wpgetapi-notice-logo" alt="WPGetAPI" title="WPGetAPI" src="<?php echo esc_url( WPGETAPIURL . 'assets/img/plugin-logos/wpgetapi-sm.png' ); ?>"></a>

	<p>
		<?php
			esc_html_e( 'Connect WordPress to external APIs, without code.', 'wpgetapi' );
			echo '&nbsp;';
			esc_html_e( 'WPGetAPI Pro or get more rated plugins below:', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://wpgetapi.com/downloads/pro-plugin/" target="_blank">' . __( 'WPGetAPI PRO', 'wpgetapi' ) . '</a>: </strong>' . __( 'Automate your API calls with Actions, format data as HTML or tables, caching, send form data to your API, send WooCommerce orders, send logged-in user info to your API and so much more.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://wpgetapi.com/downloads/api-to-posts/" target="_blank">' . __( 'WPGetAPI API to Posts', 'wpgetapi' ) . '</a>: </strong>' . __( 'Import data from your API & easily create custom posts or simple WooCommerce products within WordPress.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://wpgetapi.com/downloads/api-to-posts/" target="_blank">' . __( 'WPGetAPI OAuth 2.0', 'wpgetapi' ) . '</a>: </strong>' . __( 'Allows you to connect to an API that requires OAuth 2.0 authorization.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://aiosplugin.com/" target="_blank">' . __( 'All-In-One Security (AIOS)', 'wpgetapi' ) . '</a>: </strong>' . __( 'Still on the fence?', 'wpgetapi' ) . ' ' . __( 'Secure your WordPress website with AIOS.', 'wpgetapi' ) . ' ' . __( 'Comprehensive, cost-effective, 5* rated and easy to use.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://getwpo.com/buy/" target="_blank">WP-Optimize</a>: </strong>' . __( 'Speed up and optimize your WordPress website.', 'wpgetapi' ) . ' ' . __( 'Cache your site, clean the database and compress images.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://updraftplus.com" target="_blank">UpdraftPlus</a>: </strong>' . __( 'Back up your website with the world’s leading backup and migration plugin.', 'wpgetapi' ) . ' ' . __( 'Actively installed on more than 3 million WordPress websites.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://www.internallinkjuicer.com/" target="_blank">' . __( 'Internal Link Juicer', 'wpgetapi' ) . '</a>: </strong>' . __( 'Automate the building of internal links on your WordPress website.', 'wpgetapi' ) . ' ' . __( 'Save time and boost SEO.', 'wpgetapi' ) . ' ' . __( 'You don’t need to be an SEO expert to use this plugin.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong><a href="https://wpovernight.com/" target="_blank">' . __( 'WP Overnight', 'wpgetapi' ) . '</a>: </strong>' . __( 'Quality add-ons for WooCommerce.', 'wpgetapi' ) . ' ' . __( 'Designed to optimize your store, enhance user experience  and increase revenue.', 'wpgetapi' );
		?>
	</p>
	<p>
		<?php
			echo '<strong>' . esc_html__( 'More quality plugins', 'wpgetapi' ) . ': </strong><a href="https://www.simbahosting.co.uk/s3/shop/" target="_blank" >' . __( 'Premium WooCommerce plugins', 'wpgetapi' ) . '</a>';
		?>
	</p>
	<div style="float: right;"><a href="#" onclick="jQuery( '#wpgetapi-dashnotice' ).slideUp(); jQuery.post(ajaxurl, {action: 'wpgetapi_notice_dismiss', subaction: 'wpgetapi_dismiss_dash_notice_until', nonce: '<?php echo esc_js( wp_create_nonce( 'wpgetapi-notice-ajax-nonce' ) ); ?>'});"><?php /* translators: %1s represents the notice dismiss 'month' value. */ printf( __( 'Dismiss (for %s months)', 'wpgetapi' ), 12 ); ?></a></div>
	<p>&nbsp;</p>
</div>
