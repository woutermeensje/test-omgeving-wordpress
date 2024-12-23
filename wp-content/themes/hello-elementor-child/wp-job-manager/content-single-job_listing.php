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
        <div class="cover-image-top">
            <?php 
                            // Display Cover Image if available
                            $cover_image_id = get_post_meta(get_the_ID(), '_cover_image', true); 
                            if ($cover_image_id) : 
                                $cover_image_url = wp_get_attachment_image_url($cover_image_id, 'large'); 
                            ?>
                                <div class="job-cover-image">
                                    <img src="<?php echo esc_url($cover_image_url); ?>" alt="<?php the_title_attribute(); ?>" class="cover-image">
                                </div>
                            <?php endif; ?>
            <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
                <div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
            <?php else : ?>
        </div>

        <div class="content-part-job-description">



    

        <div class="top-div">


        <div class="meta-information-single">
            <p><?php the_job_publish_date(); ?></p>
            <p><?php the_job_type(); ?></p>
            <p><?php the_company_name(); ?></p>
            <p><?php the_job_location(); ?></p> 
        </div>
           
            
            

            
       
                 <div class="job-title">
                    <h1><?php wpjm_the_job_title(); ?></h1>

                 </div>


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
    max-width: 80%;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 5px;
    border: 1px solid #0a6b8d;
}


.cover-image-top {
    position: relative;
    overflow: hidden;
    height: 300px;
    border-bottom: 2px solid #0a6b8d;
}


.cover-image {
    width: auto; /* Let the width scale based on the image's aspect ratio */
    height: 100%; /* Ensure the image fills the container's height */
    object-fit: contain; /* Ensure the entire image fits within the container */
}

.meta-information-single {
    display: flex; 
    position: left; 

}

.meta-information-single p {
    font-family: Poppins;
    font-size: 15px; 
    color: #0a6b8d;
    font-weight: 500;
    margin-right: 10px; /* Add space between the elements */
}


.job-title {
    padding-bottom: 10px;
    border-bottom: 2px solid #0a6b8d;
    font-family: Balgin Bold;
    padding-top: 20px;
}



.content-part-job-description {
    padding: 20px;
    position: relative; 
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
    font-family: Poppins;  
    font-size: 16px;
    font-weight: 400;
    line-height: 1.6;
    color: #333;

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

.application_button.button {
    width: 100%;
    background: #007bff;
    text-align: center;
    font-family: Balgin Bold; 
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