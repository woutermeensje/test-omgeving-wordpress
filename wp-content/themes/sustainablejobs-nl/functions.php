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

    if (!empty($args['tax_query'])) {
        if (empty($query_args['tax_query'])) {
            $query_args['tax_query'] = [];
        }
        $query_args['tax_query'] = array_merge($query_args['tax_query'], $args['tax_query']);
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



function import_filtered_arcadis_jobs_to_wpjm() {
    $skills = ['Renewable Energy', 'Sustainability'];
    $max_per_skill = 30;
    $inserted = 0;

    foreach ($skills as $skill) {
        $start = 0;

        do {
            $skill_param = urlencode($skill);
            $url = "https://jobs.arcadis.com/api/apply/v2/jobs?domain=arcadis.com&location=netherlands&skill=$skill_param&num=10&start=$start";
            $response = wp_remote_get($url);

            if (is_wp_error($response)) break;

            $data = json_decode(wp_remote_retrieve_body($response), true);
            if (empty($data['positions'])) break;

            foreach ($data['positions'] as $job) {
                $title       = $job['name'];
                $external_id = $job['id'];
                $location    = $job['location'] ?? 'Netherlands';
                $job_url     = $job['canonicalPositionUrl'] ?? '';
                $job_type    = $job['work_location_option'] ?? '';

                // Skip als al bestaat
                $existing = get_posts([
                    'post_type'   => 'job_listing',
                    'meta_query'  => [[
                        'key'     => '_arcadis_id',
                        'value'   => (string) $external_id,
                        'compare' => '='
                    ]],
                    'post_status' => 'any',
                    'numberposts' => 1,
                ]);
                if ($existing) continue;

                // Voeg toe als concept
                $post_id = wp_insert_post([
                    'post_title'   => wp_strip_all_tags($title),
                    'post_content' => '', // Nog geen beschrijving
                    'post_type'    => 'job_listing',
                    'post_status'  => 'draft',
                ]);

                if ($post_id && !is_wp_error($post_id)) {
                    update_post_meta($post_id, '_job_location', $location);
                    update_post_meta($post_id, '_application', $job_url);
                    update_post_meta($post_id, '_arcadis_id', (string) $external_id);
                    update_post_meta($post_id, '_job_posted', date('Y-m-d'));

                    // Taxonomieën koppelen
                    wp_set_object_terms($post_id, ['Engineering', 'Duurzaamheid', 'Energietransitie'], 'job_sector');
                    wp_set_object_terms($post_id, 'Arcadis Nederland', 'job_company');
                    wp_set_object_terms($post_id, 'uitgelichte werkgever', 'job_tag');

                    // Job type (optioneel, op basis van work_location_option)
                    if ($job_type === 'onsite') {
                        wp_set_object_terms($post_id, 'full-time', 'job_type');
                    } elseif ($job_type === 'hybrid') {
                        wp_set_object_terms($post_id, 'part-time', 'job_type');
                    } elseif ($job_type === 'remote_local') {
                        wp_set_object_terms($post_id, 'remote', 'job_type');
                    }
                }

                $inserted++;
            }

            $start += 10;
            if ($inserted >= $max_per_skill) break;

        } while (count($data['positions']) === 10);
    }

    return "$inserted duurzame vacatures geïmporteerd als concept.";
}


add_action('admin_menu', function() {
    add_menu_page(
        'Import Duurzame Arcadis Jobs',
        'Import Arcadis',
        'manage_options',
        'import-arcadis',
        function() {
            echo '<div class="wrap"><h1>Duurzame Arcadis Vacatures Importeren</h1>';
            echo '<p>' . import_filtered_arcadis_jobs_to_wpjm() . '</p>';
            echo '</div>';
        }
    );
});
