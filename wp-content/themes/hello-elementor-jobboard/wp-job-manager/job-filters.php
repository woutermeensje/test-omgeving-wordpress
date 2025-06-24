<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );

do_action( 'job_manager_job_filters_before', $atts );
?>

<form class="job_filters">

<!-- Title above the search fields -->
<div class="filter-header" style="padding: 0 20px 10px 20px;">
      <h2>
            Explore All Open Sustainability Jobs!
      </h2>
    <p>
        Or <a href="https://sustainablejobs.com/sustainability-jobs-newsletter/" target="_blank" rel="noopener noreferrer">sign up for the job newsletter! 📩</a>
    </p>
 </div>
    <div class="search-basic">
        <div class="search_keywords">
            <input 
                type="text" 
                name="search_keywords" 
                id="search_keywords" 
                placeholder="Job title, sector, or topic.." 
                value="<?php echo esc_attr( $keywords ); ?>" 
            />
        </div>

        <div class="search_location">
            <input 
                type="text" 
                name="search_location" 
                id="search_location" 
                placeholder="City or place" 
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
    margin-top: 15px;
}

.filter-header h2 {
    font-family: Balgin Bold;
    font-size: 25px;
    color: var(--color-text);
    margin-bottom: 10px;
    display: inline;
    background: linear-gradient(transparent 60%, var(--color-tertiary) 60%);
    font-weight: bold;
    border-radius: 2px;
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
        flex-basis: 1000%;
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
    box-shadow: 0 2px 8px rgba(10, 107, 141, 0.25); /* Primaire kleur met transparantie */
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
