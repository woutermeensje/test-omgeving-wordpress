<?php
if (!defined('ABSPATH')) exit;

/**
 * ✅ ENQUEUE STYLES (with Elementor check)
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];
    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');
    wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css');
});

/**
 * ✅ WP JOB MANAGER: TEMPLATE OVERRIDES
 */
add_filter('job_manager_locate_template', function ($template, $template_name) {
    $custom_templates = [
        'content-job_listing.php',
        'content-single-job_listing.php',
        'job-filters.php',
        'job-filter-job-types.php',
        'job-listings-start.php',
        'job-listings-end.php',
        'job-submit.php',
        'functions.php',
    ];
    $custom_path = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
    return (in_array($template_name, $custom_templates) && file_exists($custom_path)) ? $custom_path : $template;
}, 10, 2);

/**
 * ✅ REGISTER CUSTOM TAXONOMIES
 */
add_action('init', function () {
    register_taxonomy('job_company', 'job_listing', ['label' => 'Organisaties', 'hierarchical' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => ['slug' => 'organisatie'],]);
    register_taxonomy('job_tag', 'job_listing', ['label' => 'Tags', 'hierarchical' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => ['slug' => 'tag'],]);
    register_taxonomy('job_sector', 'job_listing', ['label' => 'Sectors', 'hierarchical' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => ['slug' => 'sector'],]);
    register_taxonomy('certificering', 'job_listing', ['label' => 'Certificeringen', 'hierarchical' => true, 'show_ui' => true, 'show_admin_column' => true, 'show_in_rest' => true, 'rewrite' => ['slug' => 'certificering'],]);
});

/**
 * ✅ Shortcode filters: Handles initial page load AND prepares data for JavaScript
 */
add_filter('job_manager_get_listings_shortcode_args', function($atts){
    global $sj_job_shortcode_atts;
    $sj_job_shortcode_atts = $atts;

    $custom_filters = [
        'job_company'       => 'job_company',
        'job_tag'           => 'job_tag',
        'job_sector'        => 'job_sector',
        'certificering'     => 'certificering',
        'job_listing_type'  => 'job_listing_type',
    ];

    $tax_query = [];
    $filters_to_pass_to_js = [];

    foreach ($custom_filters as $attr => $taxonomy) {
        if (!empty($atts[$attr])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_title', explode(',', $atts[$attr])),
                'operator' => 'IN',
            ];
            
            $form_input_name = 'filter_' . $taxonomy;
            $filters_to_pass_to_js[$form_input_name] = sanitize_title(explode(',', $atts[$attr])[0]);
        }
    }

    if (!empty($tax_query)) {
        $atts['tax_query'] = $tax_query;
    }
    
    if ( ! empty( $filters_to_pass_to_js ) ) {
        wp_enqueue_script(
            'sjn-wpjm-shortcode-fix', 
            get_stylesheet_directory_uri() . '/js/wpjm-shortcode-fix.js', 
            [ 'jquery', 'job-manager-ajax-filters' ],
            '2.0.0', // New version to guarantee no caching
            true
        );
        
        wp_localize_script(
            'sjn-wpjm-shortcode-fix', 
            'sjn_shortcode_filters',
            $filters_to_pass_to_js 
        );
    }

    return $atts;
}, 10, 1);

/**
 * ✅ Combine AJAX filterdata + shortcode tax_query
 */
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    $custom_taxonomies = [
        'filter_job_tag'       => 'job_tag',
        'filter_job_sector'    => 'job_sector',
        'filter_job_company'   => 'job_company',
        'filter_job_types'     => 'job_listing_type',
        'filter_certificering' => 'certificering',
    ];
    
    foreach ($custom_taxonomies as $filter_key => $taxonomy) {
        if (!empty($_POST[$filter_key])) {
            $terms = (array) $_POST[$filter_key];
            $terms = array_map('sanitize_title', $terms);
            $query_args['tax_query'][] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => $terms,
                'operator' => 'IN',
            ];
        }
    }
    
    return $query_args;
}, 10, 2);
