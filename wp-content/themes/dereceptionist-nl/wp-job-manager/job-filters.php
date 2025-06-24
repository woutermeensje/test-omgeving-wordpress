<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<body>
    

<form class="job_filters">

   <!-- Titel boven de zoekvelden -->
   <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>
            Vacatures & Opdrachten voor Receptionisten!
        </h2>
        <p>
            Of schrijf je in voor de <a href="https://dereceptionist.nl/vacature-nieuwsbrief/" target="_blank" class="unstyled-newsletter-link">vacature nieuwsbrief</a>
! 
        </p>
    </div>
    <div class="search-basic">
        <div class="search_keywords">
            <input 
                type="text" 
                name="search_keywords" 
                id="search_keywords" 
                placeholder="Functienaam, sector of onderwerp.." 
                value="<?php echo esc_attr( $keywords ); ?>" 
            />
        </div>

        <div class="search_location">
            <input 
                type="text" 
                name="search_location" 
                id="search_location" 
                placeholder="Stad of plaats" 
                value="<?php echo esc_attr( $location ); ?>" 
            />
        </div>
    
    </div>

</form>

</body>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('.job_filters');
    const keywordInput = document.getElementById('search_keywords');
    const locationInput = document.getElementById('search_location');

    [keywordInput, locationInput].forEach(input => {
        input.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.dispatchEvent(new Event('submit', { bubbles: true }));
            }
        });
    });
});
</script>



<?php do_action( 'job_manager_job_filters_after', $atts ); ?>
<noscript>
    <?php esc_html_e( 'Your browser does not support JavaScript, or it is disabled. JavaScript must be enabled in order to view listings.', 'wp-job-manager' ); ?>
</noscript>

<script>
jQuery(document).ready(function($) {
    $('#search_sectors').select2({
        placeholder: 'Sectoren',
        allowClear: true
    });

    $('#select-category').select2({
        placeholder: 'Type baan',
        allowClear: true
    });

    $('#search_regios').select2({
        placeholder: 'Select a regio',
        allowClear: true
    });

    $('#search_job_names').select2({
        placeholder: 'Beroep',
        allowClear: true
    });
});
</script>

<style>
.job_filters {
    width: 90%;
    padding: 20px 0;
    margin: 0 auto;
    margin-top: 20px;
    margin-bottom: 20px;
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
    margin-bottom: 10px;
    margin-top: 10px;
}

body .filter-header h2 {
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
    color: var(--color-roze) !important;
    text-decoration: none;
}

.job-tags-meta {
    padding: 0 20px 10px 20px;
    display: flex;
    margin-top: 24px;
    flex-wrap: wrap;
    gap: 10px;
}

.job-tags-meta p {
    font-family: Poppins, sans-serif;
    font-weight: 700;
    font-size: 12px;
    border-radius: 5px;
    padding: 5px 10px;
    cursor: pointer;
    margin-right: 5px;
    display: inline-block;
    transition: all 0.2s ease;
}

.tag-one {
    color: var(--color-primary);
    background-color: var(--color-groen);
    border: 1px solid var(--color-primary);
}

.tag-one:hover {
    color: var(--color-bg);
    background-color: var(--color-primary);
    border-color: var(--color-groen);
}

.tag-two {
    color: var(--color-primary);
    background-color: var(--color-roze);
    border: 1px solid var(--color-primary);
}

.tag-two:hover {
    color: var(--color-bg);
    background-color: var(--color-primary);
    border-color: var(--color-roze);
}

.tag-three {
    color: var(--color-primary);
    background-color: var(--color-lichtgroen);
    border: 1px solid var(--color-primary);
}

.tag-three:hover {
    color: var(--color-bg);
    background-color: var(--color-primary);
    border-color: var(--color-lichtgroen);
}

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
        flex-basis: 1000%;
    }
}

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

.search-basic input[type="text"]:focus {
    outline: none;
    border-color: var(--color-primary);
    box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25);
}

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
