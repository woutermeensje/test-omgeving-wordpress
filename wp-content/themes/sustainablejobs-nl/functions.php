<?php
if (!defined('ABSPATH')) exit;



/**
 * ✅ ENQUEUE STYLES (with Elementor check + cache busting)
 */
add_action('wp_enqueue_scripts', function () {
    $dependencies = ['parent-style'];

    if (did_action('elementor/loaded') && wp_style_is('elementor-frontend', 'registered')) {
        $dependencies[] = 'elementor-frontend';
    }

    // Parent theme CSS
    wp_enqueue_style(
        'parent-style',
        get_template_directory_uri() . '/style.css',
        [],
        filemtime(get_template_directory() . '/style.css')
    );

    // Child theme main CSS met automatische versie
    wp_enqueue_style(
        'child-style',
        get_stylesheet_directory_uri() . '/style.css',
        $dependencies,
        filemtime(get_stylesheet_directory() . '/style.css')
    );

    // Google Fonts
    wp_enqueue_style('poppins-font', 'https://fonts.googleapis.com/css2?family=Poppins:wght@100;200;300;400;500;600;700;800;900&display=swap', [], null);
    wp_enqueue_style('inter-font', 'https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap', [], null);

    // Custom fonts
    wp_enqueue_style(
        'custom-fonts',
        get_stylesheet_directory_uri() . '/fonts/fonts.css',
        [],
        filemtime(get_stylesheet_directory() . '/fonts/fonts.css')
    );

    // Gravity Forms styling
    wp_enqueue_style(
        'child-gf-styles',
        get_stylesheet_directory_uri() . '/css/gravity-forms.css',
        [],
        filemtime(get_stylesheet_directory() . '/css/gravity-forms.css')
    );
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
    register_taxonomy('job_company', 'job_listing', [
        'label' => 'Organisaties',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'organisatie'],
    ]);

    register_taxonomy('job_tag', 'job_listing', [
        'label' => 'Tags',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'tag'],
    ]);

    register_taxonomy('job_sector', 'job_listing', [
        'label' => 'Sectors',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'sector'],
    ]);

    register_taxonomy('certificering', 'job_listing', [
        'label' => 'Certificeringen',
        'hierarchical' => true,
        'show_ui' => true,
        'show_admin_column' => true,
        'show_in_rest' => true,
        'rewrite' => ['slug' => 'certificering'],
    ]);
});

/**
 * ✅ Shortcode filters: [jobs job_company="bowers" job_sector="klimaatadaptatie"]
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

    foreach ($custom_filters as $attr => $taxonomy) {
        if (!empty($atts[$attr])) {
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field'    => 'slug',
                'terms'    => array_map('sanitize_title', explode(',', $atts[$attr])),
                'operator' => 'IN',
            ];
        }
    }

    if (!empty($tax_query)) {
        $atts['tax_query'] = $tax_query;
    }

    return $atts;
}, 10, 1);

/**
 * ✅ Combine AJAX filterdata + shortcode tax_query
 */
add_filter('get_job_listings_query_args', function ($query_args, $args) {
    global $sj_job_shortcode_atts;

    if (isset($_POST['form_data'])) {
        parse_str($_POST['form_data'], $parsed);
        foreach ($parsed as $key => $value) {
            $_POST[$key] = $value;
        }
        error_log('🧩 Parsed form_data: ' . print_r($parsed, true));
    }

    error_log('🔍 WPJM POST filterdata: ' . print_r($_POST, true));

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

    if (!empty($sj_job_shortcode_atts) && empty($_POST['form_data'])) {
        foreach ($custom_taxonomies as $filter_key => $taxonomy) {
            $key = str_replace('filter_', '', $filter_key);
            if (!empty($sj_job_shortcode_atts[$key])) {
                $terms = explode(',', sanitize_text_field($sj_job_shortcode_atts[$key]));
                $query_args['tax_query'][] = [
                    'taxonomy' => $taxonomy,
                    'field'    => 'slug',
                    'terms'    => $terms,
                    'operator' => 'IN',
                ];
            }
        }
    }

    if (!empty($query_args['tax_query'])) {
        error_log('📦 TAX_QUERY in get_job_listings_query_args: ' . print_r($query_args['tax_query'], true));
    } else {
        error_log('📭 Geen tax_query aanwezig in get_job_listings_query_args');
    }

    return $query_args;
}, 10, 2);

/**
 * ✅ Debug WP_Query inhoud
 */
add_action('pre_get_posts', function($query) {
    if (!is_admin() && $query->is_main_query() && isset($query->query_vars['post_type']) && $query->query_vars['post_type'] === 'job_listing') {
        error_log('👉 WP_Query tax_query: ' . print_r($query->query_vars['tax_query'], true));
    }
});

// Add custom default attributes to the jobs shortcode
add_filter('job_manager_output_jobs_defaults', function($defaults) {
    $defaults['job_company'] = '';
    $defaults['job_tag'] = '';
    $defaults['job_sector'] = '';
    $defaults['certificering'] = '';
    $defaults['job_listing_type'] = '';
    
    return $defaults;
});



// ✅ Include custom import scripts
// require_once get_stylesheet_directory() . '/inc/bowers-import.php';
// require_once get_stylesheet_directory() . '/inc/arcadis-import.php';
// require_once get_stylesheet_directory() . '/inc/jackling-import.php';



// ✅ Jackling XML Job Feed Importer
add_action('jackling_weekly_job_import', 'import_jackling_jobs');

function import_jackling_jobs() {
    $url = 'https://jackling.nl/xml-jobs-feed/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) return;

    $xml = simplexml_load_string(wp_remote_retrieve_body($response));
    if (!$xml) return;

    foreach ($xml->job as $job) {
        $external_id = (string) $job->id;

        // Skip als vacature al bestaat
        $existing = get_posts([
            'post_type' => 'job_listing',
            'meta_key' => '_jackling_job_id',
            'meta_value' => $external_id,
            'posts_per_page' => 1,
            'post_status' => ['draft', 'publish']
        ]);
        if ($existing) continue;

        $post_id = wp_insert_post([
            'post_title'   => wp_strip_all_tags((string) $job->title),
            'post_content' => wp_kses_post((string) $job->description),
            'post_type'    => 'job_listing',
            'post_status'  => 'draft',
        ]);

        if ($post_id && !is_wp_error($post_id)) {
            update_post_meta($post_id, '_jackling_job_id', $external_id);
            update_post_meta($post_id, '_job_location', (string) $job->location);
            update_post_meta($post_id, '_application', (string) $job->link);
            update_post_meta($post_id, '_company_name', 'Jackling');

            // Voeg taxonomieën toe
            wp_set_object_terms($post_id, 'Jackling', 'job_company');
            wp_set_object_terms($post_id, 'uitgelichte werkgever', 'job_tag');
            wp_set_object_terms($post_id, ['techniek', 'bouw'], 'job_sector');

            $type = strtolower((string) $job->type);
            if (in_array($type, ['fulltime', 'parttime', 'freelance'])) {
                wp_set_object_terms($post_id, $type, 'job_type');
            }
        }
    }
}

// ✅ Cronjob activeren als die nog niet bestaat
if (!wp_next_scheduled('jackling_weekly_job_import')) {
    wp_schedule_event(time(), 'weekly', 'jackling_weekly_job_import');
}
