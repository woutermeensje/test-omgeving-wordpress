<!DOCTYPE html>
<html>
<head>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="<?php echo get_stylesheet_directory_uri(); ?>/job_manager/custom-job-manager.css" rel="stylesheet">
</head>
<body class="body-class">
    <div class="main-container">
	


        <ul class="job-list">
            <li class="job-listing" <?php job_listing_class(); ?> data-longitude="<?php echo esc_attr($post->geolocation_long); ?>" data-latitude="<?php echo esc_attr($post->geolocation_lat); ?>">
                <?php if (has_post_thumbnail()) : ?>
                    <img src="<?php the_post_thumbnail_url('medium'); ?>" alt="<?php the_title_attribute(); ?>" class="rounded-image">
                <?php endif; ?>
                <div class="job-details-container">
                    <div class="job-info-container">
                        <h3 class="company-name-job-listing"><?php the_company_name(); ?></h3>
                        <h3 class="job-title">
                            <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
                        </h3>
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
</body>
</html>



<style>

	/* General Body Styling */


/* Main Container */
.main-container {
 margin-top: 20px; 
}

/* Job List */
ul.job-list {
    list-style: none;
    margin: 0;
    padding: 0;
}

/* Job Listing */
li.job-listing {
    display: flex;
    align-items: center;
    padding: 20px;
    margin-bottom: 20px;
    border: 1px solid #eaeaea;
    border-radius: 8px;
    background: #fff;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

li.job-listing:hover {
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}

/* Job Image */
.rounded-image {
    flex-shrink: 0;
    width: 80px;
    height: 80px;
    border-radius: 50%;
    object-fit: cover;
    border: 2px solid #eaeaea;
    margin-right: 20px;
}

/* Job Details Container */
.job-details-container {
    flex-grow: 1;
}

/* Job Info Container */
.job-info-container h3 {
    margin: 5px 0;
}

.company-name-job-listing {
    font-size: 14px;
    color: #555;
    font-weight: 500;
}

.job-title {
    font-size: 18px;
    font-weight: 600;
    color: #007bff;
    margin: 0;
}

.job-title a {
    text-decoration: none;
    color: #007bff;
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
    padding: 5px 10px;
    border-radius: 16px;
    display: inline-block;
}

/* Responsive Design */
@media only screen and (max-width: 768px) {
    li.job-listing {
        flex-direction: column;
        text-align: center;
    }

    .rounded-image {
        margin: 0 auto 10px auto;
    }

    .job-meta-container ul.meta {
        justify-content: center;
    }
}


.job-listing-image img {
    display: block;
    max-width: 100%;
    height: auto;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}


</style>