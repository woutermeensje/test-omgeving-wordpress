<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * ✅ ENQUEUE STYLES (with Elementor check)
 */
add_action('wp_enqueue_scripts', function() {
    // Parent theme style
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');

    // Check of Elementor actief is en 'elementor-frontend' stijl is geregistreerd
    $dependencies = ['parent-style'];
    if ( did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered') ) {
        $dependencies[] = 'elementor-frontend';
    }

    // Child theme style (na parent en eventueel Elementor)
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', $dependencies, wp_get_theme()->get('Version'));

    // Google Fonts
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);
    wp_enqueue_style('custom-fonts', get_stylesheet_directory_uri() . '/fonts/fonts.css');

    // Gravity Forms custom styles
    wp_enqueue_style('child-gf-styles', get_stylesheet_directory_uri() . '/css/gravity-forms.css');
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
 * ✅ CUSTOM TAXONOMIES
 */
add_action('init', function() {
    // Organisaties
    register_taxonomy('job_company', 'job_listing', [
        'labels' => [
            'name' => __('Organisaties', 'textdomain'),
            'singular_name' => __('Organisatie', 'textdomain'),
            'menu_name' => __('Organisaties', 'textdomain'),
            'all_items' => __('Alle Organisaties', 'textdomain'),
            'add_new_item' => __('Nieuwe Organisatie toevoegen', 'textdomain'),
            'edit_item' => __('Bewerk Organisatie', 'textdomain'),
            'view_item' => __('Bekijk Organisatie', 'textdomain'),
            'search_items' => __('Zoek Organisaties', 'textdomain'),
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organisatie'],
    ]);

    // Sectors
    register_taxonomy('job_sector', 'job_listing', [
        'labels' => [
            'name' => __('Sectors', 'textdomain'),
            'singular_name' => __('Sector', 'textdomain'),
            'menu_name' => __('Sectors', 'textdomain'),
            'all_items' => __('All Sectors', 'textdomain'),
            'add_new_item' => __('Add New Sector', 'textdomain'),
            'edit_item' => __('Edit Sector', 'textdomain'),
            'view_item' => __('View Sector', 'textdomain'),
            'search_items' => __('Search Sectors', 'textdomain'),
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'sector'],
    ]);

    // Regions
    register_taxonomy('job_regio', 'job_listing', [
        'labels' => [
            'name' => __('Regions', 'textdomain'),
            'singular_name' => __('Region', 'textdomain'),
            'menu_name' => __('Regions', 'textdomain'),
            'all_items' => __('All Regions', 'textdomain'),
            'add_new_item' => __('Add New Region', 'textdomain'),
            'edit_item' => __('Edit Region', 'textdomain'),
            'view_item' => __('View Region', 'textdomain'),
            'search_items' => __('Search Regions', 'textdomain'),
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'regio'],
    ]);

    // Job names
    register_taxonomy('job_name', 'job_listing', [
        'labels' => [
            'name' => __('Job Names', 'textdomain'),
            'singular_name' => __('Job Name', 'textdomain'),
            'menu_name' => __('Job Names', 'textdomain'),
            'all_items' => __('All Job Names', 'textdomain'),
            'add_new_item' => __('Add New Job Name', 'textdomain'),
            'edit_item' => __('Edit Job Name', 'textdomain'),
            'view_item' => __('View Job Name', 'textdomain'),
            'search_items' => __('Search Job Names', 'textdomain'),
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'functie'],
        'meta_box_cb' => 'post_categories_meta_box',
    ]);

    // Salary ranges
    register_taxonomy('salary_range', 'job_listing', [
        'labels' => [
            'name' => __('Salary Ranges', 'textdomain'),
            'singular_name' => __('Salary Range', 'textdomain'),
            'menu_name' => __('Salary Ranges', 'textdomain'),
            'all_items' => __('All Salary Ranges', 'textdomain'),
            'add_new_item' => __('Add New Salary Range', 'textdomain'),
            'edit_item' => __('Edit Salary Range', 'textdomain'),
            'view_item' => __('View Salary Range', 'textdomain'),
            'search_items' => __('Search Salary Ranges', 'textdomain'),
        ],
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'salaris'],
    ]);

    // Vakgebied
    register_taxonomy('vakgebied', ['job_listing'], [
        'label' => __('Vakgebied', 'sustainablejobs'),
        'hierarchical' => true,
        'show_ui' => true,
        'show_in_rest' => true,
        'show_admin_column' => true,
        'rewrite' => ['slug' => 'vakgebied'],
    ]);
});

/**
 * ✅ COVER IMAGE EN COMPANY LOGO FIELD
 */
add_filter('job_manager_job_listing_data_fields', function($fields) {
    $fields['_cover_image'] = [
        'label' => __('Cover Image', 'job_manager'),
        'type'  => 'file',
    ];
    return $fields;
});
