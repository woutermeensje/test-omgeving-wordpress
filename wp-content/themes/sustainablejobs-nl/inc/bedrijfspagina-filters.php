<style>
    .company_filter-form {
        width: 90%;
        margin: 30px auto;
        background: #fff;
        padding: 20px;
        border: 1px solid #0a6b8d;
        box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
    }

    .company_filter-form .filter-text-h1 h1 {
        font-size: 24px;
        font-family: 'Inter', sans-serif;
        margin-bottom: 10px;
        background: linear-gradient(transparent 60%, #E0D0E1 60%);
        font-weight: bold;
        display: inline-block;
    }

    .company_filter-form .filter-text-h1 p {
        font-size: 15px;
        font-family: 'Poppins', sans-serif;
    }

    .company_filter-box {
        display: flex;
        flex-wrap: wrap;
        gap: 16px;
        margin-top: 20px;
    }

    .company_filter-field {
        flex: 1 1 calc(25% - 16px);
        min-width: 160px;
    }

    .company_filter-select {
        width: 100%;
    }

    /* Stijl select2 container single & multiple */
    .select2-container--default .select2-selection--single,
    .select2-container--default .select2-selection--multiple {
        border-radius: 50px !important;
        border: 1px solid #e3cfe2 !important;
        background-color: #fff !important;
        padding: 8px 16px !important;
        min-height: 44px !important;
        font-family: 'Poppins', sans-serif !important;
        font-weight: 700 !important;
        font-size: 16px !important;
        box-shadow: 0 10px 40px -5px #ddd inset !important;
        display: flex !important;
        align-items: center !important;
        position: relative;
    }

    /* Placeholder tekst stijl */
    .select2-selection__placeholder {
        color: #000 !important;
        font-weight: 700 !important;
        font-family: 'Poppins', sans-serif !important;
    }

    /* Geselecteerde tekst */
    .select2-selection__rendered {
        font-weight: 700 !important;
        font-family: 'Poppins', sans-serif !important;
        padding-left: 2px !important;
        line-height: 1.4 !important;
        display: flex;
        align-items: center;
    }

    /* Pijltje rechterkant */
    .select2-selection__arrow {
        position: absolute;
        top: 50%;
        right: 16px;
        transform: translateY(-50%);
        height: 100%;
        display: flex;
        align-items: center;
        pointer-events: none;
    }

    .select2-selection__arrow b {
        border-color: #111 transparent transparent transparent !important;
        border-style: solid;
        border-width: 6px 5px 0 5px !important;
        height: 0;
        width: 0;
        display: inline-block;
    }

 .bedrijf-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* precies 3 kolommen */
    gap: 24px;
    margin: 40px auto;
    padding: 0 20px;
    max-width: 1400px;
    width: 95%; 
}

.bedrijf-item {
    background: #ffffff;
    border-radius: 16px;
    border: 1px solid #e0e0e0;
    box-shadow: 0 40px 10px -5px rgba(0, 0, 0, 0.15);
    padding: 24px;
    transition: all 0.3s ease;
    text-decoration: none;
    color: inherit;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}


.bedrijf-item:hover {
    border-color: var(--color-primary);
    box-shadow: 0 6px 24px rgba(10, 107, 141, 0.12);
}

.bedrijf-title {
    font-family: 'Balgin Bold', sans-serif;
    font-size: 20px;
    color: var(--color-primary);
    margin-bottom: 10px;
}

.bedrijf-taxonomies {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    margin-bottom: 16px;
}

.bedrijf-taxonomies span {
    font-size: 12px;
    font-weight: 600;
    padding: 6px 12px;
    border-radius: 50px;
    background-color: #f0f8f8;
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    font-family: 'Poppins', sans-serif;
    white-space: nowrap;
}

.bedrijf-button {
    align-self: flex-start;
    padding: 10px 18px;
    background-color: var(--color-primary);
    color: #fff;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 600;
    font-size: 14px;
    transition: background-color 0.2s ease;
    font-family: 'Poppins', sans-serif;
}

.bedrijf-button:hover {
    background-color: #065a73;
}


    @media (max-width: 768px) {
        .company_filter-box {
            flex-direction: column;
        }

        .company_filter-field {
            width: 100%;
        }

        .bedrijf-item {
            flex: 1 1 100%;
        }
    }
</style>


<?php
if (!defined('ABSPATH')) exit;

/**
 * ✅ Shortcode: [bedrijfspagina_filter]
 * Toont filterformulier en lijst met pagina's die gekoppeld zijn aan job_company e.d.
 */

// Enqueue scripts en styles
add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('select2-css', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css');
    wp_enqueue_script('select2-js', 'https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js', ['jquery'], null, true);

    wp_enqueue_script(
        'bedrijfspagina-filters',
        get_stylesheet_directory_uri() . '/inc/bedrijfspagina-filters.js',
        ['jquery', 'select2-js'],
        null,
        true
    );

    wp_localize_script('bedrijfspagina-filters', 'bedrijf_filter_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
    ]);
});


// Shortcode functie
function bedrijfspagina_filter_shortcode() {
    ob_start(); ?>

    <form class="company_filter-form" id="bedrijfspagina-filter-form">
        <div class="filter-text-h1">
            <h1>Doorzoek alle Duurzame Organisaties in ons netwerk</h1>
            <p>Of schrijf je in voor de <a href="https://sustainablejobs.nl/nieuwsbrief/" target="_blank">vacature nieuwsbrief</a>!</p>
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
        echo "<div class='bedrijf-grid'>";
        while ($query->have_posts()) : $query->the_post();
            $title = get_the_title();
            $permalink = get_permalink();

            $sectors = wp_get_post_terms(get_the_ID(), 'job_sector', ['fields' => 'names']);
            $certificeringen = wp_get_post_terms(get_the_ID(), 'certificering', ['fields' => 'names']);
            $tags = wp_get_post_terms(get_the_ID(), 'job_tag', ['fields' => 'names']);

            echo "<a href='{$permalink}' class='bedrijf-item'>";
                echo "<h3 class='bedrijf-title'>{$title}</h3>";

                echo "<div class='bedrijf-taxonomies'>";
                if (!empty($sectors)) {
                    echo "<span class='bedrijf-sector'>" . implode(', ', $sectors) . "</span><br />";
                }
                if (!empty($certificeringen)) {
                    echo "<span class='bedrijf-certificering'>" . implode(', ', $certificeringen) . "</span><br />";
                }
                if (!empty($tags)) {
                    echo "<span class='bedrijf-tags'>" . implode(', ', $tags) . "</span>";
                }
                echo "</div>";
            echo "</a>";
        endwhile;
        echo "</div>";
    } else {
        echo "<p>Geen bedrijven gevonden.</p>";
    }



    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}



