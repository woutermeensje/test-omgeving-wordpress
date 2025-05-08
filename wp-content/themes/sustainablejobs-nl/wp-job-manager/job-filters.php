<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<form class="job_filters">

   <!-- Titel boven de zoekvelden -->
   <div class="filter-header" style="padding: 0 20px 10px 20px;">
        <h2>
            Doorzoek de Openstaande Vacatures!
        </h2>
        <p>
            Of schrijf je in voor de <a href="https://sustainablejobs.nl/nieuwsbrief/" target="_blank" class="unstyled-newsletter-link">vacature nieuwsbrief</a>
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
/* Container blijft 100% breed */
.job_filters {
    width: 90%;
    padding: 20px 0;
    margin: 0 auto;
    margin-top: 20px;
    margin-bottom: 20px;
    background-color: white;
    border: 1px solid #0a6b8d;
    box-shadow: 0 10px 40px -5px rgba(0, 0, 0, 0.15);

}

.filter-header {
    padding: 0 20px 10px 20px;
}

.filter-header p {
    font-family: Poppins; 
    font-size: 15px;
    color: #333333;
    margin-bottom: 10px;
}

.filter-header h2 {
    font-family: Balgin Bold; 
    font-size: 25px;
    color: #333333;
    margin-bottom: 10px;
}

/* Linkstijl buiten Elementor */
.filter-header a.unstyled-newsletter-link {
    color: #0a6b8d !important;
    text-decoration: underline;
    font-weight: 400;
    font-family: Poppins, sans-serif;

}

.filter-header a.unstyled-newsletter-link:hover {
    color: var(--color-roze) !important;
    text-decoration: none; 
}


/* Flexbox voor de twee velden */
.search-basic {
    display: flex; 
    justify-content: left;
    gap: 20px;
    padding: 0 20px;
}

/* Beide velden naast elkaar */
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
        align-items: center;
    }

    .search-basic > div {
        max-width: 90%;
        flex-basis: 90%;
    }
}

/* Inputvelden gestyled met schaduw en icon ruimte */
.search-basic input[type="text"] {
    width: 100%;
    padding: 12px 14px 12px 38px; /* ruimte voor icoon links */
    font-size: 16px;
    border: 1px solid #ccc;
    border-radius: 0;
    background-color: white;
    color: #222;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}

.search-basic input[type="text"]::placeholder {
    color: #777;
}

/* Focus state */
.search-basic input[type="text"]:focus {
    outline: none;
    border-color: #0a6b8d;
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
    color: #0a6b8d;
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
    color: #0a6b8d;
    pointer-events: none;
}
</style>
