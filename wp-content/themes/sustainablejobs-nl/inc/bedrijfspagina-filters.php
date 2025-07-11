<?php
if (!defined('ABSPATH')) exit;

/**
 * ✅ Shortcode: [bedrijfspagina_filter]
 * Toont filterformulier en lijst met pagina's die gekoppeld zijn aan job_company e.d.
 */

// Enqueue JS + CSS
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');

    wp_enqueue_script('bedrijfspagina-filters', get_stylesheet_directory_uri() . '/inc/bedrijfspagina-filters.js', ['jquery', 'select2-js'], null, true);
    wp_localize_script('bedrijfspagina-filters', 'bedrijf_filter_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
});

// Shortcode functie
function bedrijfspagina_filter_shortcode() {
    ob_start(); ?>



  <form class="company_filter-form" id="bedrijfspagina-filter-form">

     <div class="r" style="">
        <h1>Bekijk alle Duurzame Vacatures in ons Netwerk!</h1>
        <p>Of schrijf je in voor de <a href="https://sustainablejobs.nl/nieuwsbrief/" target="_blank" class="">vacature nieuwsbrief</a>!</p>
    </div>

    <div class="company_filter-search">
        <div class="company_filter-keywords">
            <input type="text" name="search_keywords" id="company_filter_keywords" placeholder="Bedrijfsnaam..." />
        </div>
    </div>

    <div class="company_filter-box">
        <?php
        $taxonomies = [
            'job_company'    => '🏢 Organisatie',
            'job_sector'     => '🌱 Sector',
            'certificering'  => '🏅 Certificering',
            'job_tag'        => '📌 Tags',
        ];
        foreach ($taxonomies as $taxonomy => $label) {
            $terms = get_terms(['taxonomy' => $taxonomy, 'hide_empty' => false]);
            if (!empty($terms)) {
                echo "<div class='company_filter-field company_filter-{$taxonomy}'>";
                echo "<select name='filter_{$taxonomy}[]' id='company_filter_{$taxonomy}' class='company_filter-select company_filter-{$taxonomy}' multiple='multiple' data-placeholder='{$label}'>";
                foreach ($terms as $term) {
                    echo "<option value='" . esc_attr($term->slug) . "'>" . esc_html($term->name) . "</option>";
                }
                echo "</select></div>";
            }
        }
        ?>
    </div>
</form>

<style>
    
</style>

<div id="bedrijf-resultaten"></div>

    <?php
    return ob_get_clean();
}
add_shortcode('bedrijfspagina_filter', 'bedrijfspagina_filter_shortcode');

// AJAX-handler
add_action('wp_ajax_filter_bedrijfspaginas', 'filter_bedrijfspaginas_ajax');
add_action('wp_ajax_nopriv_filter_bedrijfspaginas', 'filter_bedrijfspaginas_ajax');

function filter_bedrijfspaginas_ajax() {
    $search = sanitize_text_field($_POST['search_keywords'] ?? '');
    $tax_filters = ['job_company', 'job_sector', 'certificering', 'job_tag'];
    $tax_query = [];

    // Altijd filteren op pagina’s met een gekoppelde job_company
    $job_company_terms = get_terms([
        'taxonomy'   => 'job_company',
        'hide_empty' => false,
        'fields'     => 'slugs',
    ]);

    if (!empty($job_company_terms)) {
        $tax_query[] = [
            'taxonomy' => 'job_company',
            'field'    => 'slug',
            'terms'    => $job_company_terms,
            'operator' => 'IN',
        ];
    }

    // Voeg overige filters toe (indien aanwezig)
    foreach ($tax_filters as $tax) {
        if (!empty($_POST["filter_{$tax}"])) {
            $tax_query[] = [
                'taxonomy' => $tax,
                'field'    => 'slug',
                'terms'    => (array) $_POST["filter_{$tax}"],
            ];
        }
    }

    $args = [
        'post_type'      => 'page',
        'posts_per_page' => -1,
        's'              => $search,
    ];

    if (!empty($tax_query)) {
        $args['tax_query'] = [
            'relation' => 'AND',
            ...$tax_query
        ];
    }

    $query = new WP_Query($args);

    ob_start();
    if ($query->have_posts()) {
        echo "<ul class='bedrijf-list'>";
        while ($query->have_posts()) : $query->the_post();
            echo "<li><a href='" . get_permalink() . "'>" . get_the_title() . "</a></li>";
        endwhile;
        echo "</ul>";
    } else {
        echo "<p>Geen bedrijven gevonden.</p>";
    }
    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}


