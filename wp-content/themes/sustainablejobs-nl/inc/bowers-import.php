<?php
// ✅ Bowers XML Job Feed Importer
add_action('bowers_weekly_job_import', 'import_bowers_jobs');

function import_bowers_jobs() {
    $url = 'https://bowers.nl/xml-jobs-feed/';
    $response = wp_remote_get($url);

    if (is_wp_error($response)) return;

    $xml = simplexml_load_string(wp_remote_retrieve_body($response));
    if (!$xml) return;

    foreach ($xml->job as $job) {
        $external_id = (string) $job->id;

        // Skip als vacature al bestaat
        $existing = get_posts([
            'post_type' => 'job_listing',
            'meta_key' => '_bowers_job_id',
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
            update_post_meta($post_id, '_bowers_job_id', $external_id);
            update_post_meta($post_id, '_job_location', (string) $job->location);
            update_post_meta($post_id, '_application', (string) $job->link);
            update_post_meta($post_id, '_company_name', 'Bowers');

            // Voeg taxonomieën toe
            wp_set_object_terms($post_id, 'Bowers', 'job_company');
            wp_set_object_terms($post_id, 'uitgelichte werkgever', 'job_tag');
            wp_set_object_terms($post_id, ['bouw', 'techniek'], 'job_sector');

            $type = strtolower((string) $job->type);
            if (in_array($type, ['fulltime', 'parttime', 'freelance'])) {
                wp_set_object_terms($post_id, $type, 'job_type');
            }
        }
    }
}

// ✅ Cronjob plannen
if (!wp_next_scheduled('bowers_weekly_job_import')) {
    wp_schedule_event(time(), 'weekly', 'bowers_weekly_job_import');
}
