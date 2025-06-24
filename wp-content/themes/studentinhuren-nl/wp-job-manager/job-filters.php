<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<form class="job_filters">
    <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>Bekijk hieronder werk dat bij jou past!</h2>
        <p>
            Of ontvang <a href="https://student-inhuren.nl/updates-ontvangen/" class="unstyled-newsletter-link" target="_blank" rel="noopener">nieuwe updates</a> en projecten van Studentinhuren.nl in de e-mail!
        </p>
    </div>

    <div class="search-basic">
        <div class="search_keywords">
            <input type="text" name="search_keywords" id="search_keywords" placeholder="Functienaam, sector of onderwerp.." value="<?php echo esc_attr( $keywords ); ?>" />
        </div>
        <div class="search_location">
            <input type="text" name="search_location" id="search_location" placeholder="Stad of plaats" value="<?php echo esc_attr( $location ); ?>" />
        </div>
    </div>

  
</form>

<?php do_action( 'job_manager_job_filters_after', $atts ); ?>



<style>
/* Container blijft 100% breed */
.job_filters {
    width: 90%;
    padding: 20px 0;
    margin: 20px auto;
    background-color: var(--color-bg);
    border: 1px solid var(--color-primary);
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);
}

.filter-header {
    padding: 0 20px 10px 20px;
}

.filter-header p {
    font-family: Poppins;
    font-size: 15px;
    color: var(--color-text);
    margin: 10px 0;
}

.filter-header h2 {
    font-family: Balgin Bold;
    font-size: 25px;
    color: var(--color-text);
    margin-bottom: 15px;
    display: inline;
    background: linear-gradient(transparent 60%, var(--color-tertiary) 60%);
    font-weight: bold;
    border-radius: 2px;
}

body .filter-header a.unstyled-newsletter-link {
    color: var(--color-primary);
    text-decoration: none;
    font-weight: 400;
    font-family: "Poppins", sans-serif;
}

body .filter-header a.unstyled-newsletter-link:hover {
    color: var(--color-tertiary) !important;
    text-decoration: none;
}

/* Flexbox voor de twee velden */
.search-basic {
    display: flex;
    justify-content: left;
    gap: 20px;
    padding: 0 20px;
}

.search_location,
.search_keywords {
    flex-basis: 50%;
    max-width: 50%;
    display: flex;
    justify-content: center;
    align-items: center;
    position: relative;
}

@media (max-width: 768px) {
    .search-basic {
        flex-direction: column;
    }

    .search-basic > div {
        max-width: 100%;
        flex-basis: 100%;
    }
}

/* Inputvelden gestyled met schaduw en icon ruimte */
.search-basic input[type="text"] {
    width: 100%;
    padding: 12px 14px 12px 38px;
    font-size: 16px;
    border: 1px solid var(--color-border-light);
    border-radius: 0;
    background-color: var(--color-bg);
    color: var(--color-text);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-basic input[type="text"]::placeholder {
    color: var(--color-text-muted);
}

/* Focus state */
.search-basic input[type="text"]:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

/* Vergrootglas icoon */
.search_keywords::before {
    content: '🔍';
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: var(--color-primary);
    pointer-events: none;
}

/* Locatie icoon */
.search_location::before {
    content: '📍';
    position: absolute;
    left: 10px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 16px;
    color: var(--color-primary);
    pointer-events: none;
}
</style>
