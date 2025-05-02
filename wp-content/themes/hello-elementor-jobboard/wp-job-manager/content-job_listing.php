<li class="job-listing-simple" <?php job_listing_class(); ?>>
    <div class="job-logo">
        <?php the_company_logo(); ?>
    </div>
    <div class="job-details">
        <h2 class="job-title">
            <a href="<?php the_job_permalink(); ?>"><?php wpjm_the_job_title(); ?></a>
        </h2>
        <div class="job-meta">
            <span class="company-name"><?php the_company_name(); ?></span> |
            <span class="job-location"><?php the_job_location(); ?></span> |
            <span class="job-type">
                <?php if (get_option('job_manager_enable_types')) : ?>
                    <?php $types = wpjm_get_the_job_types(); ?>
                    <?php if (!empty($types)) : foreach ($types as $type) : ?>
                        <?php echo esc_html($type->name); ?>
                    <?php endforeach; endif; ?>
                <?php endif; ?>
            </span>
        </div>
        <div class="job-description">
            <?php echo wp_trim_words(get_the_excerpt(), 25, '...'); ?>
        </div>
    </div>
</li>


<style>
    .job-listing-simple {
    display: flex;
    align-items: flex-start;
    gap: 20px;
    padding: 20px;
    width: 90%;
    max-width: 1200px;
    margin: 0 auto 20px auto;
    border: 1px solid #0a6b8d; /* originele kleur */
    background-color: #fff;
    border-radius: 0px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15); /* originele shadow */
    transition: box-shadow 0.3s ease, transform 0.3s ease;
}

.job-listing-simple:hover {
    box-shadow: 0 6px 15px rgba(0,0,0,0.1); /* hover effect zoals origineel */
    transform: translateY(-5px);
}

.job-logo img {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border: 1px solid #0a6b8d; /* originele randkleur */
    border-radius: 5px;
    background: #0a6b8d;

}

.job-details {
    flex: 1;
}

.job-title {
    font-size: 20px;
    font-weight: 600;
    margin: 0 0 8px;
    color: #333333; /* consistent met randkleur */
}

.job-title a {
    color: #333333;
    text-decoration: none;
}

.job-title a:hover {
    text-decoration: underline;
}

.job-meta {
    font-size: 14px;
    color: #333;
    margin-bottom: 10px;
    font-weight: 300;
}

.company-name {
    font-weight: 500;
}

.job-description {
    font-size: 14px;
    color: #333;
    font-family: Poppins; 
    font-weight: 300;
    line-height: 1.5;

}

/* Mobiel */
@media only screen and (max-width: 768px) {
    .job-listing-simple {
        flex-direction: column;
        align-items: flex-start;
    }

    .job-logo img {
        width: 60px;
        height: 60px;
    }

    .job-title {
        font-size: 18px;
    }
}

</style>