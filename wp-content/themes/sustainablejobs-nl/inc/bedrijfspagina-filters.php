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
        <div class="filter-text">
            <h2>Doorzoek alle Duurzame Organisaties in ons netwerk</h2>
            <p>Of schrijf je in voor de <a href="https://sustainablejobs.nl/nieuwsbrief/" class="newsletter-link" target="_blank">vacature nieuwsbrief</a>!</p>
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

                    foreach ($sectors as $sector) {
                        echo "<div class='bedrijfs-sector'>{$sector}</div>";
                    }
                }
                if (!empty($certificeringen)) {
                     
                    foreach ($certificeringen as $certificering) {
                        echo "<div class='bedrijf-certificering'>{$certificering}</div>";
                    }
                }
                if (!empty($tags)) {
                    
                    foreach ($tags as $tag) {
                        echo "<div class='bedrijf-tags'>{$tag}</div>";
                    }
                }
                echo "</div>";
            echo "</a>";
        endwhile;
        echo "</div>";
    } else {
        echo "
        <div class='no-results'>
        <h2>Geen resultaten gevonden</h2>
        <p>
        Er zijn geen bedrijven gevonden die aan jouw voorwaarden voldoen. Het is tevens mogelijk om jouw organisatie in dit overzicht te krijgen, een premium bedrijfspagina aan te maken of jouw bedrijfspagina te bewerken.
        </p>
        <button class='bedrijf-reset-filter'>Bedrijfspagina aanmaken
        </button>
        </div>";
    }



    wp_reset_postdata();

    echo ob_get_clean();
    wp_die();
}


?>
<style>

/* ✅ Styling voor de bedrijfspagina filter */

form.company_filter-form {
    width: 90%;
    padding: 24px; 
    margin: 20px auto;
    background-color: white;
    border: 1px solid #0a6b8d;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.filter-text h2 {
    font-size: 28px;
    color: #333 !important;
    margin-bottom: 10px;
    font-family: 'Inter', sans-serif !important;
    font-weight: 700; 
}

a.newsletter-link {
    color: #0a6b8d;
    text-decoration: none;
    font-weight: 400;
    font-family: "Poppins", sans-serif;
}

a.newsletter-link:hover {
    color: var(--color-roze) !important;
    text-decoration: none; 
}

.company_filter-box {
    display: flex;
    flex-direction: row;
    flex-wrap: wrap;
    gap: 16px;
    margin: 20px 0;
}

.company_filter-box > .company_filter-field {
    flex: 1 1 22%;
    min-width: 160px;
}

input#company_filter_keywords {
    width: 100%;
    padding: 12px 14px 12px 12px;
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 0;
    background-color: white;
    color: #222;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

/* ✅ Select2 alleen binnen .company_filter-form */
/* ✅ Zorg dat Select2 de volledige breedte benut binnen bedrijfspagina filter */
.company_filter-form .select2-container {
    width: 100% !important;
}

/* 🎨 Vormgeving van de Select2 dropdown (single select) */
.company_filter-form .select2-container--default .select2-selection--single {
    border-radius: 50px;
    border: 1px solid #e3cfe2;
    background-color: #fcfbfa;
    padding: 8px 16px;
    height: auto;
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
    font-size: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: 0 10px 40px -5px #ddd inset;
    transition: all 0.2s ease;
}

/* 📋 Vormgeving van de dropdownlijst */
.company_filter-form .select2-container--default .select2-results > .select2-results__options {
    background-color: #fff;
    border: 1px solid #ccc;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.05);
    border-radius: 0px;
    max-height: 300px;
    overflow-y: auto;
    padding: 5px 0;
}

/* 📌 Placeholder */
.company_filter-form .select2-selection__placeholder {
    color: #000;
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
}

/* ✅ Geselecteerde waarde (rendered text) */
.company_filter-form .select2-selection__rendered {
    font-weight: 700;
    font-family: 'Poppins', sans-serif;
    padding-left: 2px;
}

/* 🔽 Pijltje rechts */
.company_filter-form .select2-selection__arrow b {
    border-color: #111 transparent transparent transparent !important;
    border-width: 6px 5px 0 5px !important;
}

/* 🟢 Focus-staat */
.company_filter-form .select2-container--default .select2-selection--single:focus {
    border-color: #0a6b8d;
    box-shadow: 0 0 0 2px rgba(10, 107, 141, 0.2);
    outline: none;
}

/* 🟡 Vormgeving multiple select */
.company_filter-form .select2-container--default .select2-selection--multiple {
    border-radius: 50px;
    border: 1px solid #ccc;
    min-height: 44px;
    padding: 6px;
}

/* 🔷 Tag-elementen in multiple select */
.company_filter-form .select2-container--default .select2-selection--multiple .select2-selection__choice {
    background-color: #0a6b8d;
    color: white;
    border: none;
    border-radius: 16px;
    padding: 2px 10px;
    margin: 2px;
    font-size: 13px;
}

/* 🔍 Inputveld in multiple select */
.company_filter-form .select2-search__field {
    font-family: 'Poppins', sans-serif;
    font-size: 14px;
}


/* 📱 Responsiveness */
@media (max-width: 1024px) {
    .filter-box > div {
        flex: 1 1 calc(50% - 16px);
    }
}

@media (max-width: 600px) {
    .filter-box {
        flex-direction: column;
    }

    .filter-box > div {
        flex: 1 1 100%;
        min-width: 100%;
    }
}

/* Bedrijven Output */
.bedrijf-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
    width: 90%; 
    max-width: 1800px; 
    margin: 0 auto;
}

.bedrijf-item {
    background: var(--color-bg);
    padding: 20px;
    border: 1px solid var(--color-border);
    border-radius: 4px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.bedrijf-item:hover {
    border: 1px solid var(--color-primary);
}

.bedrijf-title {
    font-family: 'Balgin Bold', sans-serif;
    font-size: 20px;
    color: var(--color-primary);
    margin-bottom: 10px;
}

.bedrijf-button {
    display: inline-block;
    background-color: var(--color-primary);
    color: white;
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: background-color 0.3s ease;
}

.bedrijf-taxonomies {
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    margin-top: 10px;
    margin-bottom: 10px;
}

.bedrijf-certificering {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-tertiary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
    margin-right: 5px;
}

.bedrijf-tags {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: var(--color-tertiary);
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
    margin-right: 5px;
}

.bedrijf-sector,
.bedrijf-certificering,
.bedrijf-tags {
    margin-bottom: 10px;
}

/* Bedrijfspagina filters */

.no-results {
    width: 90% !important; 
    margin: 0 auto; 
    background: white; 
    border: 1px solid var(--color-primary); 
    padding: 24px;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.no-results h2 {
    font-family: Balgin Bold; 
    font-size: 20px; 
}

.bedrijf-reset-filter {
    background-color: var(--color-tertiary);
    border: 2px solid var(--color-primary) !important;
    border-radius: 0px; 
    color: var(--color-primary);
    font-family: Balgin Bold; 
    padding: 12px 16px; 
    margin-top: 24px !important; 
}

.bedrijf-reset-filter:hover {
    background-color: var(--color-primary);
    color: white; 
}

.bedrijfs-sector {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: var(--color-primary);
    border: 1px solid var(--color-primary);
    background-color: #b9d1b3 !important;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
    margin-right: 5px;
    display: inline-block;
    margin-bottom: 5px;
}

.bedrijf-certificering {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: #b9d1b3 !important;
    border: 1px solid var(--color-primary);
    background-color: var(--color-primary) !important;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px; 
    text-decoration: none;
    display: inline-block;
    margin-bottom: 5px;
}

.bedrijf-tag {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px; 
    color: #92E9AB !important;
    border: 1px solid var(--color-primary) !important;
    background-color: #0A6B8D !important;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer; 
    margin-right: 5px;
    display: inline-block;
    margin-bottom: 5px;
}

.bedrijf-taxonomies {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 10px;
}

</style>
