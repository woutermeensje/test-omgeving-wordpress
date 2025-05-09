<?php
if ( ! defined( 'ABSPATH' ) ) exit;

global $post;

if ( job_manager_user_can_view_job_listing( $post->ID ) ) : ?>

<style>.custom-top-section { display: none !important; }</style>

<div class="custom-top-section">
    <p>Ontvang wekelijkse onze vacatures</p>
    <div class="custom-top-section-form">
        <form action=""></form>
        <input type="text" placeholder="E-mailadres.." class="search-input">
        <button class="button-top-section">Bevestigen</button>  
    </div>
</div>

<div>
    <div class="single_job_listing">
        <?php if ( get_option( 'job_manager_hide_expired_content', 1 ) && 'expired' === $post->post_status ) : ?>
            <div class="job-manager-info"><?php _e( 'This listing has expired.', 'wp-job-manager' ); ?></div>
        <?php else : ?>
            <div class="content-part-job-description">
                <div class="top-div">
                    <div class="meta-information-single">
                        <p><?php the_job_publish_date(); ?></p>
                        <p><?php the_job_type(); ?></p>
                        <p><?php the_company_name(); ?></p>
                        <p><?php the_job_location(); ?></p> 
                    </div>
                    <div class="job-title">
                        <h1><?php wpjm_the_job_title(); ?> | <?php the_company_name(); ?></h1>
                    </div>
                    <div class="job_description">
                        <?php wpjm_the_job_description(); ?>
                    </div>
                    <?php $company_website = get_post_meta( $post->ID, '_company_website', true ); ?>
                    <?php if ( ! empty( $company_website ) ) : ?>
                        <div class="job-apply-button">
                            <a href="<?php echo esc_url( $company_website ); ?>" class="apply-button" target="_blank">Bezoek de sollicitatiepagina</a>
                        </div>
                    <?php else : ?>
                        <p>No application link available.</p>
                    <?php endif; ?>
                    <?php do_action( 'single_job_listing_end' ); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php else : ?>
    <?php get_job_manager_template_part( 'access-denied', 'single-job_listing' ); ?>
<?php endif; ?>
</div>

<div class="recent-jobs-container">
    <ul class="recent-jobs-list">
        <?php
        $recent_jobs = new WP_Query([
            'post_type'      => 'job_listing',
            'posts_per_page' => 5,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ]);

        if ($recent_jobs->have_posts()) :
            while ($recent_jobs->have_posts()) : $recent_jobs->the_post(); ?>
                <li class="recent-job-item">
                    <a href="<?php the_job_permalink(); ?>" class="job-listing-link">
                        <div class="logo-and-title">
                            <div class="rounded-image">
                                <div class="logo-wrapper"><?php the_company_logo(); ?></div>
                            </div>
                            <div class="recent-job-content">
                                <div>
                                    <h3 class="recent-job-title"><?php the_title(); ?> | <?php the_company_name(); ?></h3>
                                    <?php the_job_location(); ?>
                                </div>
                                <p class="recent-job-excerpt"><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
                            </div>
                        </div>
                    </a>
                </li>
            <?php endwhile;
            wp_reset_postdata();
        else : ?>
            <li class="no-jobs-found">No recent jobs found.</li>
        <?php endif; ?>
    </ul>
</div>

<div class="job-contact-form">
    <h2>Stel een vraag over deze vacature</h2>
    <form action="" method="post" id="job-contact-form">
        <div class="form-group">
            <input type="text" id="first_name" name="first_name" placeholder="Voornaam" required>
            <input type="email" id="email" name="email" placeholder="Email" required>
        </div>
        <textarea id="message" name="message" rows="5" placeholder="Type hier je vraag" required></textarea>
        <input type="hidden" name="job_id" value="<?php echo esc_attr($post->ID); ?>">
        <button type="submit" name="submit_question">Verstuur Vraag</button>
    </form>
</div>



<style>


main#content {
    width: 100%; 
}

a.google_map_link {
    text-decoration: none; 
    font-family: Balgin Bold !important;
    color: #0a6b8d !important;
    font-size: 15px;
}

.custom-top-section {
    background-color: #0a6b8d;
    color: white;
    padding: 20px;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    border-radius: 0; /* Remove rounded corners */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow effect */
    width: 100vw; /* Full viewport width */
    margin-left: calc(-50vw + 50%); /* Adjust left margin for full stretch */
    position: relative; /* Ensure proper stacking */
    left: 0; /* Ensure it aligns with the viewport */
    display: flex; 
}


.custom-top-section p {
    color: white;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    margin-left: auto; 
    margin-top: auto;
    margin-bottom: auto;
    margin-right: 0px; 
}

.button-top-section {
    background-color: #e0d0e1;
    color: #0a6b8d;
    border: none;
    padding: 10px 20px;
    margin-left: auto;
    margin-right: auto;
    display: block;
    font-family: "Balgin Bold", sans-serif;
    font-size: 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}

.button-top-section:hover {
    background-color: #e0d0e1;
    color: #0a6b8d;
    border: none;
    padding: 10px 20px;
    margin-left: auto;
    margin-right: auto;
    display: block;
    font-family: "Balgin Bold", sans-serif;
    font-size: 15px;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s ease;
}


.custom-top-section-form {
    display: flex; 
    margin-left: 0px; 
    margin: auto; 

}

.search-input {
    padding: 10px;
    border: 1px solid #e0d0e1 !important;
    border-radius: 5px;
    font-size: 15px;
    margin-right: 10px;
    font-family: Poppins;
    font-weight: 500;
}

input[type=text]:focus {outline:none;}


.search-input:focus {
    border-color: #e0d0e1; /* Blue border */
}

.search-input:active {
    border-color: #e0d0e1; /* Blue border */
    border: 1px solid #e0d0e1 !important;

}

.search-input:hover {
    border-color: #e0d0e1; /* Blue border */
    border: 1px solid #e0d0e1 !important;

}


.search-input::placeholder {
    color: #888; /* Change placeholder text color */
    font-size: 14px; /* Adjust font size */
    font-style: italic; /* Optional: Make it italic */
    font-family: Poppins;
    opacity: 0.7; /* Adjust the opacity */
}





h1.entry-title {
    display: none; 
}



/* Single Job Listing Container */
.single_job_listing {
    max-width: 80%;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Darker and stronger shadow effect */
    padding: 25px;
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
    font-family: Balgin Bold;
    font-size: 15px; 
    color: #0a6b8d;
    border: 1px solid #0a6b8d;
    font-weight: 300;
    margin-right: 10px; /* Add space between the elements */
    background-color: #b9d1b3;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
}

.meta-information-single p:hover {
    font-family: Balgin Bold;
    font-size: 15px; 
    color: #0a6b8d;
    border: 1px solid #0a6b8d;
    font-weight: 300;
    margin-right: 10px; /* Add space between the elements */
    background-color: #b9d1b3;
    border-radius: 5px;
    padding: 5px 10px;
}

.job-title h1 {
    padding-bottom: 10px;
    border-bottom: 2px solid #0a6b8d;
    font-family: Balgin Bold;
    font-size: 20px !important; 
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
    font-size: 14px;
    font-weight: 400;
    line-height: 1.6;
    color: #333;
    margin-top: 20px !important; 

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



input.application_button.button {
	background: #0a6b8d;
	border: 1px solid #0a6b8d; 
	color: white !important; 
}

input.application_button.button:hover {
	background: white;
	border: 1px solid white; 
	color: #0a6b8d !important; 
	border: 1px solid #0a6b8d; 
}
/* Responsive Design */
@media (max-width: 768px) {
    .single_job_listing {
        width: 100%; /* Default to full width */
        max-width: 600px; /* Set the maximum width */
        min-width: 320px; /* Set the minimum width */
        margin: 0 auto; /* Center it if the width is less than 100% */
        padding: 10px; /* Optional: Add some padding */
    }


.meta-information-single {
    display: flex; 
    position: left; 

}

.meta-information-single p {
    font-family: Balgin Bold;
    font-size: 13px; 
    color: #0a6b8d;
    border: 1px solid #0a6b8d;
    font-weight: 300;
    margin-right: 10px; /* Add space between the elements */
    background-color: white;
    border-radius: 5px;
    padding: 5px;
    cursor: pointer; 
}

.meta-information-single p:hover {
    font-family: Balgin Bold;
    font-size: 13px; 
    color: #0a6b8d;
    border: 1px solid #0a6b8d;
    font-weight: 300;
    margin-right: 10px; /* Add space between the elements */
    background-color: #b9d1b3;
    border-radius: 5px;
    padding: 5px;
}

    .recent-jobs-container {
        width: 100%; /* Default to full width */
        margin: 0 auto; /* Center it if the width is less than 100% */
    }

    .single_job_listing .job-application .application_button {
        width: 100%;
        text-align: center;
    }

    .rounded-image .logo-wrapper img {
        display: none; 
    }

    .recent-jobs-list  {
        max-width: 100%;
    }

    .custom-top-section p {
    color: white;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 14px;
    margin: auto; 
}

    .button-top-section {
        background-color: #b9d1b3;
        color: #0a6b8d;
        border: none;
        padding: 10px 20px;
        margin-left: auto;
        margin-right: auto;
        display: block;
        font-family: "Balgin Bold", sans-serif;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        transition: background-color 0.3s ease;
    }
}

.recent-jobs-container {
    max-width: 80%;
    margin: 40px auto;
    
}

.recent-jobs-list {
    list-style: none;
    padding: 0;
}

.recent-job-item:hover {
    transform: scale(1.05);
    transition: transform 0.3s ease;
}


.recent-job-item {
    background: #ffffff;
    border-radius: 5px;
    border: 1px solid #0a6b8d;
    decoration: none; 
    margin-top: 15px; 
    margin-bottom: 15px;   
    display: flex; 
    padding: 20px;
    justify-content: left; /* Center other flex items */
    align-items: left; /* Align items vertically */
}

.recent-job-content {
    margin: auto;
}

.recent-job-content h3 {
    font-family: Balgin Bold;
    font-size: 18px;
    color: #333333;
    margin-top: 5px;
    margin-bottom: 5px;
    
}

a.job-listing-link {
  text-decoration: none;
}
.recent-job-content p {
    font-family: Poppins; 
    font-size: 13px;
    font-weight: 200; 
    color: #333333;
    margin-top: 5px;
    margin-bottom: 5px;
}

.recent-job-company h4 {
    color: #333333; 
    font-family: Balgin Bold;
    font-size: 15px;
}



.rounded-image .logo-wrapper img {
    max-height: 50%;
    max-width: 50%;
    border-radius: 0%;
    object-fit: cover;
    
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow effect */
    border-radius: 5px;
    margin: auto; 
    padding: 2px; 
    background: #0a6b8d; 
}

.logo-and-title {
    display: flex; 
    
}

.custom-bottom-section {
    background-color: #0a6b8d;
    color: white;
    padding: 20px;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    border-radius: 0; /* Remove rounded corners */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow effect */
    width: 100vw; /* Full viewport width */
    margin-left: calc(-50vw + 50%); /* Adjust left margin for full stretch */
    position: relative; /* Ensure proper stacking */
    left: 0; /* Ensure it aligns with the viewport */
    display: flex; 
    position: sticky; /* Makes the div sticky */
    bottom: 0; /* Sticks to the top of the viewport */
    z-index: 1000; /* Ensures it stays above other elements */
}

.custom-bottom-section p {
    color: white;
    text-align: center;
    font-family: "Balgin Bold", sans-serif;
    font-size: 18px;
    margin: auto; 
}


.job-contact-form {
    max-width: 80%;
    margin: 40px auto;
    background: #ffffff;
    border-radius: 5px;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2); /* Darker and stronger shadow effect */
    padding: 25px;
    width: 50%; 
}

.job-contact-form h2 {
    font-size: 20px;
    margin-bottom: 15px;
    color: #0a6b8d;
}

.job-contact-form label {
    display: block;
    margin-bottom: 5px;
    font-weight: bold;
}

.job-contact-form input,
.job-contact-form textarea {
    width: 100%;
    padding: 10px;
    margin-bottom: 15px;
    border: 1px solid #ccc;
    border-radius: 4px;
    font-size: 14px;
}

.job-contact-form button {
    background-color: #0a6b8d;
    color: white;
    padding: 10px 15px;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
}

.job-contact-form button:hover {
    background-color: #0073a9;
}

.form-group {
    display: flex;
    justify-content: space-between;
    gap: 5px; /* Add space between the input fields */

}

.job-contact-form h2 {
    font-family: Balgin Bold;
    font-size: 20px;
}

.form-group input {
    flex: 1; /* Each input takes equal space */
    max-width: 50%; /* Ensure inputs are 50% of the container width */
    padding: 10px; /* Add padding inside inputs */
    font-size: 16px; /* Set font size */
    border: 1px solid #ccc; /* Add border */
    border-radius: 5px; /* Round input corners */
    box-sizing: border-box; /* Include padding in width */
}

.job-contact-form button {
    background-color: #0a6b8d; /* Blue background color */
    color: white; /* White text color */
    border: none; /* Remove default border */
    padding: 10px 20px; /* Add padding inside the button */
    font-size: 16px; /* Font size for the button text */
    font-family: Balgin Bold; /* Font family */
    border-radius: 5px; /* Rounded corners */
    cursor: pointer; /* Show pointer cursor on hover */
    transition: background-color 0.3s ease; /* Smooth hover effect */
}

/* Hover effect */
.job-contact-form button:hover {
    background-color: #0073a9; /* Darker blue on hover */
    color: white; /* Ensure text remains visible */
}

.job-apply-button a {
    padding: 15px; 
    background: #e0d0e1;
    color: #0a6b8d;
    border-radius: 5px;
    margin-top: 20px;
    font-family: Balgin Bold;
    text-decoration: none; 
    border: 2px solid #0a6b8d;
}


.job-apply-button a:hover {
    padding: 15px; 
    background: white;
    color: #0a6b8d; ;
    border-radius: 5px;
    margin-top: 20px;
    font-family: Balgin Bold;
    text-decoration: none; 
    border: 1px solid #0a6b8d
}




</style>

