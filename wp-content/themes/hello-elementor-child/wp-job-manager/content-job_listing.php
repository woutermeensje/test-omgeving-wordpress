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
                        
                        <div class="rounded-image">
                            <div class="logo-wrapper">
                                <?php the_company_logo(); ?>
                            </div>
                        </div>


                        <div class="job-details-container">
                        <div class="vacature-content">

                        <div class="job-date"> 
                                    <p><?php echo date_i18n('l j F', strtotime(get_the_date()), true); ?></p>
                                </div>
                                <h2 class="job-title">
                                    <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
                                </h2>

                                <div class="job_text">
                                    <p><?php echo wp_trim_words(get_the_excerpt(), 30, '...'); ?></p>
                                </div>

                            <div class="vacature-meta"> 
                            <div class="job-meta-container">
                           
                                        <?php do_action('job_listing_meta_start'); ?>
                                        <?php do_action('job_listing_meta_end'); ?>
                                    
                                        <p class="company-name-job-listing"><?php the_company_name(); ?></p>

                                        <p class="job-type">
                                            <?php if (get_option('job_manager_enable_types')) : ?>
                                                <?php $types = wpjm_get_the_job_types(); ?>
                                                <?php if (!empty($types)) : foreach ($types as $type) : ?>
                                                    <?php echo esc_html($type->name); ?>
                                                <?php endforeach; endif; ?>
                                            <?php endif; ?>
                                        </p>

                                        <p class="job-location"><?php the_job_location(true); ?></p>
                                    </div>
                                </div>
                            </div>
                     
                    </li>
                </ul>

                
           

        </div>
        
    </div>

                
    <div class="mobile-job-listings">
        <h2 class="job-title">
            <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
        </h2>
        <div class="job_text-mobile">
            <p><?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?></p>
        </div>
        <p class="company-name-mobile"><?php the_company_name(); ?></p>
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
    margin: 0 auto; /* Center the div */
    padding: 0;
    width: 90%;
}

/* Job Listing */
li.job-listing {
    display: flex;
    align-items: center;
    padding: 0px;
    border: 1px solid #0a6b8d;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    border-radius: 0px;
    background: #fff;
    transition: box-shadow 0.3s ease, transform 0.3s ease;
    margin-top: 30px;
    height: 325px; 
    position: relative; 

}

li.job-listing:hover {
    box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
    transform: translateY(-5px);
}


.rounded-image {
    position: absolute; /* Keeps overlap positioning */
    width: 10%; /* Width of the container */
    height: auto; /* Keeps the aspect ratio */
    left: 45%; /* Centers horizontally */
    top: 50%; /* Centers vertically */
    transform: translateY(-50%); /* Centers vertically using transform */
    z-index: 1; /* Keeps the correct stacking order */
    margin: auto; /* No change here */
    border-radius: 5px; /* Rounded corners for the container */
    overflow: hidden; /* Ensures the logo doesn't overflow the container */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow effect */

}

.logo-wrapper img {
    width: 100%; /* Make the image fill the container width */
    height: 100%; /* Make the image fill the container height */
    object-fit: cover; /* Ensures the image covers the container while maintaining its aspect ratio */
    display: block; /* Prevents inline spacing issues */
    background: #0a6b8d; 
    padding: 2px; 
    border-radius: 6px; 
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
}

.company-name-job-listing {
    font-family: Poppins; 
	font-size: 13px !important; 
	color: #333 !important;
	font-weight: 200 !important; 
}

.job-title {
    font-size: 45px;
    font-weight: 600;
    color: #007bff;
    margin: 0;
    font-family: Balgin Bold; 
}

.job-title a {
    color: #333333;
    font-family: Balgin Bold; 
    font-size: 25px;
    text-decoration: none;
}

p {
    font-size: 14px;
    color: #333;
    font-family: Poppins;
    margin-top: 10px;
    margin-bottom: 10px; 
}

.job-title a:hover {
    color: #0a6b8d; 
}

.vacature-meta {
    display: flex; /* Enable Flexbox layout */
    align-items: center; /* Center items vertically */
}

.vacature-content {
	margin-left: 75px !important; 
    margin-right: 25px; 
}

.job-location {
    margin: auto; 
    font-size: 13px !important; 
    color: #333  !important; 
    font-family: Poppins  !important; 
    font-weight: 200  !important; 
}

/* Job Meta Container */
.job-meta-container {
    display: flex; /* Enable Flexbox layout */
    justify-content: space-between; /* Distribute items with equal space between */
    gap: 10px; /* Optional: Add consistent spacing between items */
    align-items: center; 
}

.job-meta-container p {
    font-family: Poppins; 
	font-size: 13px !important; 
	color: #333 !important;
	font-weight: 200 !important; 
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
    font-size: 15px;
    color: #0a6b8d;
    font-family: Poppins; 
    font-weight: 500; 
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

       /* CSS to hide and style the mobile job listings */
       .mobile-job-listings {
            display: none; /* Hide by default */
            background-color: white; /* Light background color */
            border-radius: 5px;
            margin: 25px; 
            padding: 15px; /* Add some padding */
            border: 1px solid #0a6b8d;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .mobile-job-listings:hover {
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.1);
            transform: translateY(-5px);
        }

        .mobile-job-listings h2.job-title {
            font-size: 15px;
            margin-bottom: 10px;
        }


        .mobile-job-listings .job_text-mobile {
            font-size: 14px;
            color: #555;
        }

        .company-name-mobile {
            font-size: 15px;
            font-family: Poppins;
            font-weight: 700;
            color: #0a6b8d;
        }

    

/* Responsive Design */
@media only screen and (max-width: 768px) {
    

    .main-container {
        display: none; 
    }
    .jobs-list-container {
        width: 100%; /* Default to full width */
        max-width: 600px; /* Set the maximum width */
        min-width: 320px; /* Set the minimum width */
        margin: 0 auto; /* Center it if the width is less than 100% */
        padding: 10px; /* Optional: Add some padding */
    }

    .jobs-list:hover {
        
    }
    
    
    .content-container {
        flex-direction: column;
    }

    .li.job-listing {
        margin-top: 0px !important;  
        padding-top: 0px !important;
        padding-bottom: 0px !important; 
        
    }

    .job-details-container {
      
    }
   

    .vacature-content {
     

    }
    
    .job-meta-container ul.meta {
     
    }

    .job-meta-container ul.meta li.job-type {
        font-size: 12px;
    }

    div.job-details-container {
       
    }

    h2.job-title {
        font-size: 20px;
        font-family: Poppins !important;
    }

    a {
        font-size: 20px !important;
        font-family: Balgin Bold !important; 
        font-weight: 500;
        color: #0a6b8d !important; 
    }

    .rounded-image {
        display: none !important; /* Hides the logo on screens smaller than 768px */
    }

    .job-cover-image {
        display: none !important;;
    }

    .jobs-list-container {
      
    }

    a.google_map_link {
        display: none !important; 
    }

    p {
    }

    .mobile-job-listings {
        display: block; 
    }

    .mobile-job-listings h2.job-title a {
        font-family: Poppins !important; 
        font-size: 16px !important;
        color: #333333 !important; 
        font-weight: 700; 
}

.mobile-job-listings h2.job-title a:hover {
        font-family: Poppins !important; 
}

   
}




</style>

