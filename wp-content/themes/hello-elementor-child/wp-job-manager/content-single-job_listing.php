<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Document</title>
</head>
<body>


<?php
/**
 * Single job listing.
 *
 * This template can be overridden by copying it to yourtheme/job_manager/content-single-job_listing.php.
 *
 * @see         https://wpjobmanager.com/document/template-overrides/
 * @author      Automattic
 * @package     wp-job-manager
 * @category    Template
 * @since       1.0.0
 * @version     1.37.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>
	<div class="single_job_listing">
		<?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
			<div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
		<?php else : ?>
			<?php
				/**
				 * single_job_listing_start hook
				 *
				 * @hooked job_listing_meta_display - 20
				 * @hooked job_listing_company_display - 30
				 */
				do_action( 'single_job_listing_start' );
			?>

			<div class="job_description">
				<?php wpjm_the_job_description(); ?>
			</div>

			<?php if ( candidates_can_apply() ) : ?>
				<?php get_job_manager_template( 'job-application.php' ); ?>
			<?php endif; ?>

			<?php
				/**
				 * single_job_listing_end hook
				 */
				do_action( 'single_job_listing_end' );
			?>
		<?php endif; ?>






	</div>
<?php else : ?>

	<?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>

<?php endif; ?>

	
</body>
</html>



<style>

	/* General Body Styling */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f4f4f4;
    color: #333;
}

/* Single Job Listing Container */
.single_job_listing {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    border: 1px solid #eaeaea;
}

/* Job Manager Info (e.g., Expired Message) */
.job-manager-info {
    background-color: #ffdddd;
    color: #cc0000;
    border: 1px solid #cc0000;
    padding: 10px 15px;
    border-radius: 4px;
    text-align: center;
    margin-bottom: 20px;
    font-weight: 600;
}

/* Job Description Section */
.job_description {
    margin-top: 20px;
    line-height: 1.6;
    color: #555;
}

/* Buttons (e.g., Apply Button) */
.single_job_listing .job-application .application_button {
    display: inline-block;
    background-color: #007bff;
    color: #ffffff;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 600;
    margin-top: 20px;
    transition: background-color 0.3s ease;
}

.single_job_listing .job-application .application_button:hover {
    background-color: #0056b3;
}

/* Hooks (Start/End) */
.single_job_listing .single_job_listing_start,
.single_job_listing .single_job_listing_end {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .single_job_listing {
        padding: 15px;
    }

    .single_job_listing .job-application .application_button {
        width: 100%;
        text-align: center;
    }
}



</style>