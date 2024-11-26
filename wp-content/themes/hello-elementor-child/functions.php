<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

// BEGIN ENQUEUE PARENT ACTION
// AUTO GENERATED - Do not modify or remove comment markers above or below:

if ( !function_exists( 'chld_thm_cfg_locale_css' ) ):
    function chld_thm_cfg_locale_css( $uri ){
        if ( empty( $uri ) && is_rtl() && file_exists( get_template_directory() . '/rtl.css' ) )
            $uri = get_template_directory_uri() . '/rtl.css';
        return $uri;
    }
endif;
add_filter( 'locale_stylesheet_uri', 'chld_thm_cfg_locale_css' );
         
if ( !function_exists( 'child_theme_configurator_css' ) ):
    function child_theme_configurator_css() {
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor','hello-elementor','hello-elementor-theme-style','hello-elementor-header-footer' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

if ( function_exists( 'wp_cache_clear_cache' ) ) {
    wp_cache_clear_cache();
}

function custom_job_manager_locate_templates( $template, $template_name, $template_path ) {
    // Array of templates you want to override
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

    // Check if the template is in our list of custom templates
    if ( in_array( $template_name, $custom_templates ) ) {
        $custom_template = get_stylesheet_directory() . '/wp-job-manager/' . $template_name;
        if ( file_exists( $custom_template ) ) {
            return $custom_template;
        }
    }

    return $template;
}
add_filter( 'job_manager_locate_template', 'custom_job_manager_locate_templates', 10, 3 );


function load_select2_assets() {
    // Enqueue Select2 CSS
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css', [], '4.1.0' );
    // Enqueue Select2 JS
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', ['jquery'], '4.1.0', true );
}
add_action( 'wp_enqueue_scripts', 'load_select2_assets' );


function register_custom_job_taxonomies() {
    // Register "Sectors" Taxonomy
    $sector_labels = [
        'name'              => _x( 'Sectors', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Sector', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Sectors', 'textdomain' ),
        'all_items'         => __( 'All Sectors', 'textdomain' ),
        'edit_item'         => __( 'Edit Sector', 'textdomain' ),
        'update_item'       => __( 'Update Sector', 'textdomain' ),
        'add_new_item'      => __( 'Add New Sector', 'textdomain' ),
        'new_item_name'     => __( 'New Sector Name', 'textdomain' ),
        'menu_name'         => __( 'Sectors', 'textdomain' ),
    ];

    $sector_args = [
        'hierarchical'      => true, // Hierarchical like categories
        'labels'            => $sector_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'sector' ],
        'meta_box_cb'       => 'post_tags_meta_box', // Enables the meta box for selection/creation
    ];

    register_taxonomy( 'job_sector', 'job_listing', $sector_args );

    // Register "Companies" Taxonomy
    $company_labels = [
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

    $company_args = [
        'hierarchical'      => false,
        'labels'            => $company_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'company' ],
        'meta_box_cb'       => 'post_tags_meta_box',
    ];

    register_taxonomy( 'job_company', 'job_listing', $company_args );

    // Register "Countries" Taxonomy
    $country_labels = [
        'name'              => _x( 'Countries', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Country', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Countries', 'textdomain' ),
        'all_items'         => __( 'All Countries', 'textdomain' ),
        'edit_item'         => __( 'Edit Country', 'textdomain' ),
        'update_item'       => __( 'Update Country', 'textdomain' ),
        'add_new_item'      => __( 'Add New Country', 'textdomain' ),
        'new_item_name'     => __( 'New Country Name', 'textdomain' ),
        'menu_name'         => __( 'Countries', 'textdomain' ),
    ];

    $country_args = [
        'hierarchical'      => true,
        'labels'            => $country_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'country' ],
        'meta_box_cb'       => 'post_tags_meta_box',
    ];

    register_taxonomy( 'job_country', 'job_listing', $country_args );

    // Register "Certifications" Taxonomy
    $certification_labels = [
        'name'              => _x( 'Certifications', 'taxonomy general name', 'textdomain' ),
        'singular_name'     => _x( 'Certification', 'taxonomy singular name', 'textdomain' ),
        'search_items'      => __( 'Search Certifications', 'textdomain' ),
        'all_items'         => __( 'All Certifications', 'textdomain' ),
        'edit_item'         => __( 'Edit Certification', 'textdomain' ),
        'update_item'       => __( 'Update Certification', 'textdomain' ),
        'add_new_item'      => __( 'Add New Certification', 'textdomain' ),
        'new_item_name'     => __( 'New Certification Name', 'textdomain' ),
        'menu_name'         => __( 'Certifications', 'textdomain' ),
    ];

    $certification_args = [
        'hierarchical'      => false,
        'labels'            => $certification_labels,
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'certification' ],
        'meta_box_cb'       => 'post_tags_meta_box',
    ];

    register_taxonomy( 'job_certification', 'job_listing', $certification_args );
}
add_action( 'init', 'register_custom_job_taxonomies', 0 );

add_shortcode('kvk_search_by_city', function($atts) {
    // Haal de waarde van 'plaats' op uit de GET-parameters
    $plaatsnaam = isset($_GET['plaats']) ? sanitize_text_field($_GET['plaats']) : '';

    // Toon een melding als er geen plaatsnaam is ingevoerd
    if (empty($plaatsnaam)) {
        return "Voer een plaatsnaam in om te zoeken naar bedrijven.";
    }

    // Roep de WPGetAPI shortcode aan met de dynamische plaatsnaam
    return do_shortcode("[wpgetapi_endpoint api_id='kvk_zoeken_v2' endpoint_id='kvk_zoeken_v2' plaats='{$plaatsnaam}']");
});


