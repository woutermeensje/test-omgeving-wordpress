<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ ENQUEUE STYLES
 */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', ['parent-style'], wp_get_theme()->get('Version'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
});

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');
});


/**
 * ✅ LOAD SELECT2
 */
add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css', [], '4.1.0');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true);
});

/**
 * ✅ WP JOB MANAGER: CUSTOM TEMPLATE OVERRIDES
 */
add_filter('job_manager_locate_template', function($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing-company.php',
        'content-single-job_listing-meta.php',
        'content-single-job_listing.php',
        'content-widget-job_listing.php',
        'functions.php',
        'job-filter-job-types.php',
        'job-filters.php',
        'job-listings-end.php',
        'job-listings-start.php',
        'job-preview.php',
        'job-submit.php',
    ];
    $custom_template_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
    return (in_array($template_name, $custom_templates) && file_exists($custom_template_path)) ? $custom_template_path : $template;
}, 10, 2);

/**
 * ✅ CUSTOM TAXONOMIES (SECTORS, COMPANIES, COUNTRIES, ETC.)
 * Zie je originele code voor complete registratie (je had dit goed)
 */
add_action('init', 'register_custom_job_taxonomies');
function register_custom_job_taxonomies() {
    // Plaats hier je `register_taxonomy()` aanroepen zoals job_sector, job_country, etc.
    // Deze staan al goed in je originele code.
}

/**
 * ✅ COVER IMAGE EN COMPANY LOGO FIELD
 */
add_filter('job_manager_job_listing_data_fields', function($fields) {
    $fields['_cover_image'] = [
        'label' => __('Cover Image', 'job_manager'),
        'type'  => 'file',
    ];
    $fields['_company_logo'] = [
        'label' => __('Company Logo', 'job_manager'),
        'type'  => 'file',
        'description' => __('Upload the company logo.', 'job_manager'),
    ];
    return $fields;
});

/**
 * ✅ COVER IMAGE SAVE & FIX
 */
add_action('job_manager_save_job_listing', function($post_id) {
    if (!empty($_FILES['_cover_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = media_handle_upload('_cover_image', $post_id);
        if (!is_wp_error($uploaded)) {
            update_post_meta($post_id, '_cover_image', $uploaded);
        }
    }
}, 10, 1);

add_action('init', function() {
    // Fix cover image meta: URL → attachment ID
    $query = new WP_Query(['post_type' => 'job_listing', 'posts_per_page' => -1]);
    while ($query->have_posts()) {
        $query->the_post();
        $cover_url = get_post_meta(get_the_ID(), '_cover_image', true);
        if (filter_var($cover_url, FILTER_VALIDATE_URL)) {
            $attachment_id = attachment_url_to_postid($cover_url);
            if ($attachment_id) update_post_meta(get_the_ID(), '_cover_image', $attachment_id);
        }
    }
    wp_reset_postdata();
});

/**
 * ✅ CONTACTFORMULIER BIJ VACATURE
 */
add_action('init', function() {
    if (isset($_POST['submit_question'])) {
        $first_name = sanitize_text_field($_POST['first_name']);
        $email      = sanitize_email($_POST['email']);
        $message    = sanitize_textarea_field($_POST['message']);
        $job_id     = intval($_POST['job_id']);
        $to         = get_post_meta($job_id, '_application', true);

        if ($to) {
            wp_mail($to, 'Vraag over vacature #' . $job_id, "Naam: $first_name\nE-mail: $email\n\nVraag:\n$message", [
                'Content-Type: text/plain; charset=UTF-8',
                'From: ' . $email
            ]);
            add_action('wp_footer', fn() => print '<script>alert("Uw vraag is succesvol verstuurd!");</script>');
        } else {
            add_action('wp_footer', fn() => print '<script>alert("Geen contactpersoon beschikbaar.");</script>');
        }
    }
});



function shortcode_company_jobs_simple($atts) {
    $atts = shortcode_atts([
        'slug' => ''
    ], $atts, 'company_jobs_simple');

    if (empty($atts['slug'])) {
        return '<p>⚠️ Geen bedrijf opgegeven.</p>';
    }

    $jobs = new WP_Query([
        'post_type' => 'job_listing',
        'posts_per_page' => 10,
        'tax_query' => [[
            'taxonomy' => 'job_company',
            'field'    => 'slug',
            'terms'    => $atts['slug']
        ]]
    ]);

    ob_start();

    if ($jobs->have_posts()) {
        echo '<div class="simple-job-listings">';
        while ($jobs->have_posts()) {
            $jobs->the_post();
            $location = get_the_job_location();
            echo '<div class="simple-job">';
            echo '<h3><a href="' . get_permalink() . '">' . get_the_title() . '</a></h3>';
            if ($location) echo '<p class="simple-location">' . esc_html($location) . '</p>';
            echo '<p class="simple-excerpt">' . wp_trim_words(get_the_excerpt(), 20, '...') . '</p>';
            echo '</div>';
        }
        echo '</div>';
    } else {
        echo '<p>Er zijn momenteel geen vacatures van dit bedrijf.</p>';
    }

    wp_reset_postdata();
    return ob_get_clean();
}
add_shortcode('company_jobs_simple', 'shortcode_company_jobs_simple');


/**
 * ✅ AJAX FILTER SUPPORT (WP Job Manager)
 * Bevat jouw smart search en filtering logica. Deze kun je hieronder gewoon herhalen uit je huidige functions.php.
 * Denk aan: handle_custom_job_filters(), handle_smart_search(), validate_terms() etc.
 */

 add_action('init', 'register_job_company_taxonomy');

 function register_job_company_taxonomy() {
     $labels = [
         'name'              => _x( 'Companies', 'taxonomy general name', 'textdomain' ),
         'singular_name'     => _x( 'Company', 'taxonomy singular name', 'textdomain' ),
         'search_items'      => __( 'Search Companies', 'textdomain' ),
         'all_items'         => __( 'All Companies', 'textdomain' ),
         'edit_item'         => __( 'Edit Company', 'textdomain' ),
         'update_item'       => __( 'Update Company', 'textdomain' ),
         'add_new_item'      => __( 'Add New Company', 'textdomain' ),
         'new_item_name'     => __( 'New Company Name', 'textdomain' ),
         'menu_name'         => __( 'Companies', 'textdomain' ),
     ];
 
     $args = [
         'hierarchical'      => false,
         'labels'            => $labels,
         'show_ui'           => true,
         'show_admin_column' => true,
         'query_var'         => true,
         'rewrite'           => [ 'slug' => 'company' ],
         'meta_box_cb'       => 'post_tags_meta_box',
         'show_in_rest'      => true, // voor blokeditor en API
     ];
 
     register_taxonomy( 'job_company', 'job_listing', $args );
 }

 