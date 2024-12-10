<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo get_stylesheet_directory_uri(); ?>/job_manager/custom-job-manager.css" rel="stylesheet">
</head>
<body class="body-class">
    <div class="main-container">
            <!-- Job Listings Section -->
            <div class="jobs-list-container">
                <ul class="job-list">
                    <li class="job-listing" <?php job_listing_class(); ?> data-longitude="<?php echo esc_attr($post->geolocation_long); ?>" data-latitude="<?php echo esc_attr($post->geolocation_lat); ?>">
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


                        

                       

                        <div class="job-details-container">
                            <div class="logo">
                                <!-- Thumbnail Image -->
                                <?php if (has_post_thumbnail()) : ?>
                                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>" class="rounded-image">
                               <?php endif; ?>
                            </div>
                        
                            <div class="job-info-container-one">        
                                
                          
                            <div class="job-info-container">
                                <h3 class="company-name-job-listing"><?php the_company_name(); ?></h3>
                                <h2 class="job-title">
                                    <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
                                </h2>
                                <p class="job-location"><?php the_job_location(true); ?></p>
                            </div>
                            <div class="job-meta-container">
                                <?php do_action('job_listing_meta_start'); ?>
                                <?php do_action('job_listing_meta_end'); ?>
                                <?php the_job_publish_date(); ?>
                                <ul class="meta">
                                    <?php if (get_option('job_manager_enable_types')) : ?>
                                        <?php $types = wpjm_get_the_job_types(); ?>
                                        <?php if (!empty($types)) : foreach ($types as $type) : ?>
                                            <li class="job-type <?php echo esc_attr(sanitize_title($type->slug)); ?>">
                                                <?php echo esc_html($type->name); ?>
                                            </li>
                                        <?php endforeach; endif; ?>
                                    <?php endif; ?>
                                </ul>
                            </div>
                           
                        </div>
                    </li>
                </ul>
           

        </div>
    </div>
</body>
</html>



<style>
/* General Styling */
body {
    font-family: 'Poppins', sans-serif;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9;
    color: #333;
}

/* Main Container */
.main-container {
    display: flex;
    justify-content: center;
    
}


/* Job Listings Section */
.jobs-list-container {
  margin: 0px; 
  padding: 0px; 
}



/* Job List */
ul.job-list {
    list-style: none;
    margin: 0;
    padding: 0;
    width: 100%;
    
}

/* Job Listing */
li.job-listing {
    display: flex;
    align-items: center;
    padding: 0px;
    border: 2px solid #0a6b8d;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    border-radius: 5px;
    background: #fff;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    margin-top: 30px;
    height: 300px; 
}

li.job-listing:hover {
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}

.rounded-image {
    margin-left: -50%;
    align-self: center;
}

/* Job Image (Thumbnail) */
.rounded-image {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eaeaea;
    margin-right: 20px;
}

/* Job Cover Image */
.job-cover-image {
    width: 100%;
    max-height: 100%;
    overflow: hidden;
    
}

.job-cover-image img.cover-image {
    height: auto;
    object-fit: cover;
}

/* Job Details Container */
.job-details-container {
    width: 100%;
    display: flex; 
}

/* Job Info Container */
.job-info-container h2 {
    margin: 5px 0;
    font-weight: bold;
    color: #333333;
}

.company-name-job-listing {
    font-size: 14px;
    color: #555;
    font-weight: 500;
}

.job-title {
    font-size: 45px;
    font-weight: 600;
    color: #007bff;
    margin: 0;
    font-family: Balgin Bold; 
}

.job-title a {
    text-decoration: none;
    color: #007bff;
}

h2.job-title {
    font-size: 24px;
    font-weight: 600;
    margin: 0;
    font-family: Balgin Bold;
    color: black; 
}

.job-title a:hover {
    text-decoration: underline;
}

.job-location {
    font-size: 14px;
    color: #777;
}

/* Job Meta Container */
.job-meta-container {
    margin-top: 15px;
}

ul.meta {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

ul.meta li.job-type {
    font-size: 12px;
    color: #fff;
    background-color: #007bff;
    padding: 0px 0px;
    border-radius: 16px;
    display: inline-block;
}

/* Sidebar Section */
.side-bar-jobs {
    flex: 1; /* 25% width */
    background: rgba(10, 107, 141, 0.8);
    padding: 00px;
    border-radius: 8px;
    color: #fff;
    box-shadow: 0 10px 30px -10px rgba(0, 0, 0, 0.2);
}

.side-bar-jobs h3 {
    font-size: 20px;
    font-weight: bold;
    margin-bottom: 15px;
}

.side-bar-jobs ul {
    list-style: none;
    margin: 0;
    padding: 0;
}

.side-bar-jobs ul li {
    margin-bottom: 10px;
    font-size: 16px;
}

/* Responsive Design */
@media only screen and (max-width: 768px) {
    .content-container {
        flex-direction: column;
    }

    li.job-listing {
        flex-direction: column;
        text-align: center;
    }

    .rounded-image {
        margin: 0 auto 10px auto;
    }

    .logo {
        border: 2px solid #0a6b8d;
    }
    .job-meta-container ul.meta {
        justify-content: center;
    }

    .side-bar-jobs {
        margin-top: 20px;
    }
}
</style>

