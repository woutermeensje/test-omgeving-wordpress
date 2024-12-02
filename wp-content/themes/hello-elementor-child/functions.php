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
        wp_enqueue_style( 'chld_thm_cfg_child', trailingslashit( get_stylesheet_directory_uri() ) . 'style.css', array( 'hello-elementor', 'hello-elementor-theme-style', 'hello-elementor-header-footer' ) );
    }
endif;
add_action( 'wp_enqueue_scripts', 'child_theme_configurator_css', 10 );

if ( function_exists( 'wp_cache_clear_cache' ) ) {
    wp_cache_clear_cache();
}

function custom_job_manager_locate_templates( $template, $template_name, $template_path ) {
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
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css', [], '4.1.0' );
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js', ['jquery'], '4.1.0', true );
}
add_action( 'wp_enqueue_scripts', 'load_select2_assets' );


add_action('init', 'register_custom_job_taxonomies', 11);

function register_custom_job_taxonomies() {
    // Your taxonomy registration code here
}




function add_sector_support_to_jobs() {
    register_taxonomy_for_object_type( 'job_sector', 'job_listing' );
}
add_action( 'init', 'add_sector_support_to_jobs' );




add_action('init', 'register_job_sector');
function register_job_sector() {
    $args = [
        'label'  => esc_html__('Sectors', 'textdomain'),
        'labels' => [
            'menu_name'                  => esc_html__('Sectors', 'textdomain'),
            'all_items'                  => esc_html__('All Sectors', 'textdomain'),
            'edit_item'                  => esc_html__('Edit Sector', 'textdomain'),
            'view_item'                  => esc_html__('View Sector', 'textdomain'),
            'update_item'                => esc_html__('Update Sector', 'textdomain'),
            'add_new_item'               => esc_html__('Add New Sector', 'textdomain'),
            'new_item'                   => esc_html__('New Sector', 'textdomain'),
            'parent_item'                => esc_html__('Parent Sector', 'textdomain'),
            'parent_item_colon'          => esc_html__('Parent Sector:', 'textdomain'),
            'search_items'               => esc_html__('Search Sectors', 'textdomain'),
            'popular_items'              => esc_html__('Popular Sectors', 'textdomain'),
            'separate_items_with_commas' => esc_html__('Separate sectors with commas', 'textdomain'),
            'add_or_remove_items'        => esc_html__('Add or remove sectors', 'textdomain'),
            'choose_from_most_used'      => esc_html__('Choose from the most used sectors', 'textdomain'),
            'not_found'                  => esc_html__('No sectors found', 'textdomain'),
            'name'                       => esc_html__('Sectors', 'textdomain'),
            'singular_name'              => esc_html__('Sector', 'textdomain'),
        ],
        'public'               => true,
        'show_ui'              => true,
        'show_in_menu'         => true,
        'show_in_nav_menus'    => true,
        'show_tagcloud'        => true,
        'show_in_quick_edit'   => true,
        'show_admin_column'    => true, // Show in admin job listing columns
        'show_in_rest'         => true, // Enable REST API support
        'hierarchical'         => true, // Make it behave like categories
        'query_var'            => true,
        'sort'                 => false,
        'rewrite_no_front'     => false,
        'rewrite_hierarchical' => false,
        'rewrite'              => [ 'slug' => 'sector' ] // Custom rewrite slug
    ];
    register_taxonomy('job_sector', ['job_listing'], $args);
}


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

add_action( 'init', 'register_custom_job_taxonomies', 0 );

function child_theme_enqueue_styles() {
    wp_enqueue_style( 'poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', array(), null );
    wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style') );
}
add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_styles' );

function enqueue_select2_assets() {
    // Add Select2 CSS
    wp_enqueue_style( 'select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/css/select2.min.css', [], '4.1.0' );
    
    // Add Select2 JS
    wp_enqueue_script( 'select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0/dist/js/select2.min.js', ['jquery'], '4.1.0', true );
}
add_action( 'wp_enqueue_scripts', 'enqueue_select2_assets' );

add_action('init', 'register_custom_regio_taxonomy', 11);


function register_custom_regio_taxonomy() {
    $args = [
        'label'  => esc_html__( "Regio's", 'textdomain' ),
        'labels' => [
            'menu_name' => esc_html__( "Regio's", 'textdomain' ),
            'all_items' => esc_html__( "All Regio's", 'textdomain' ),
        ],
        'public' => true,
        'hierarchical' => true, // Makes it behave like categories
        'show_ui' => true,
        'show_admin_column' => true, // Adds a column in admin job listing table
        'rewrite' => [ 'slug' => 'regio' ], // Custom URL slug
    ];

    register_taxonomy('job_regio', ['job_listing'], $args);
}


add_action('init', 'register_custom_job_name_taxonomy', 11);

function register_custom_job_name_taxonomy() {
    $args = [
        'label'  => esc_html__('Job Names', 'textdomain'),
        'labels' => [
            'menu_name'     => esc_html__('Job Names', 'textdomain'),
            'all_items'     => esc_html__('All Job Names', 'textdomain'),
            'edit_item'     => esc_html__('Edit Job Name', 'textdomain'),
            'view_item'     => esc_html__('View Job Name', 'textdomain'),
            'update_item'   => esc_html__('Update Job Name', 'textdomain'),
            'add_new_item'  => esc_html__('Add New Job Name', 'textdomain'),
            'new_item_name' => esc_html__('New Job Name', 'textdomain'),
            'search_items'  => esc_html__('Search Job Names', 'textdomain'),
        ],
        'public'            => true,
        'hierarchical'      => false, // Behaves like tags
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'job-name' ],
        'show_in_rest'      => true, // Enables REST API support
        'meta_box_cb'       => 'post_tags_meta_box', // Enables term selection via dropdown
    ];

    register_taxonomy('job_name', ['job_listing'], $args);
}




add_action('init', 'register_salary_range_taxonomy', 11);

function register_salary_range_taxonomy() {
    $args = [
        'label'  => esc_html__('Salary Ranges', 'textdomain'),
        'labels' => [
            'menu_name'     => esc_html__('Salary Ranges', 'textdomain'),
            'all_items'     => esc_html__('All Salary Ranges', 'textdomain'),
            'edit_item'     => esc_html__('Edit Salary Range', 'textdomain'),
            'view_item'     => esc_html__('View Salary Range', 'textdomain'),
            'update_item'   => esc_html__('Update Salary Range', 'textdomain'),
            'add_new_item'  => esc_html__('Add New Salary Range', 'textdomain'),
            'new_item_name' => esc_html__('New Salary Range', 'textdomain'),
            'search_items'  => esc_html__('Search Salary Ranges', 'textdomain'),
        ],
        'public'            => true,
        'hierarchical'      => false, // Behaves like tags
        'show_ui'           => true,
        'show_admin_column' => true,
        'query_var'         => true,
        'rewrite'           => [ 'slug' => 'salary-range' ],
        'show_in_rest'      => true, // Enables REST API support
    ];

    register_taxonomy('salary_range', ['job_listing'], $args);
}


add_action('init', function() {
    $ranges = [
        '€2.000 - €3.000',
        '€3.000 - €4.000',
        '€4.000 - €5.000',
        '€5.000 - €6.000',
        '€6.000 - €8.000',
        '€8.000 - €12.000',
        '€5.000+',
        'Op basis van CAO inschaling',
        'Vrijwilligersvergoeding',
        'Stage vergoeding'
    ];

    foreach ( $ranges as $range ) {
        if ( ! term_exists( $range, 'salary_range' ) ) {
            wp_insert_term( $range, 'salary_range' );
        }
    }
});


function my_child_theme_enqueue_styles() {
    wp_enqueue_style('parent-style', get_template_directory_uri() . '/style.css');
    wp_enqueue_style('child-style', get_stylesheet_directory_uri() . '/style.css', array('parent-style'));
}
add_action('wp_enqueue_scripts', 'my_child_theme_enqueue_styles');


// Add custom field for job listing image
function add_job_listing_image_field($fields) {
    $fields['_job_listing_image'] = array(
        'label'       => __('Job Listing Image', 'wp-job-manager'),
        'type'        => 'file', // Allows image upload
        'description' => __('Upload an image for this job listing.', 'wp-job-manager'),
        'priority'    => 2, // Determines order of the field
    );

    return $fields;
}
add_filter('job_manager_job_listing_data_fields', 'add_job_listing_image_field');


// Save the custom job listing image field
function save_job_listing_image($post_id, $post) {
    if (isset($_FILES['_job_listing_image']) && !empty($_FILES['_job_listing_image']['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        $uploaded = media_handle_upload('_job_listing_image', $post_id);

        if (!is_wp_error($uploaded)) {
            update_post_meta($post_id, '_job_listing_image', $uploaded);
        }
    }
}
add_action('job_manager_save_job_listing', 'save_job_listing_image', 10, 2);


