<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

wp_enqueue_script( 'wp-job-manager-ajax-filters' );
?>

<style>
    
    
    .ai-search-wrapper {
        position: relative;
        margin-bottom: 10px;
    }

    .ai-search-wrapper input {
        width: 100%;
        padding: 15px 15px 15px 45px;
        font-size: 16px;
        border-radius: 6px;
        border: 1px solid #ccc;
    }

    .ai-search-wrapper .ai-search-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        font-size: 18px;
        color: #888;
    }

    .ai-search-suggestions {
        font-family: Poppins, sans-serif;
        font-size: 13px;
        color: #666;
        margin-top: 5px;
        margin-bottom: 20px;
    }

    .ai-search-suggestions span {
        background: #f1f1f1;
        border-radius: 4px;
        padding: 4px 8px;
        margin-right: 5px;
        display: inline-block;
    }
</style>

<form class="job_filters" style="margin-bottom: 20px;">
    <div class="ai-search-bar-wrapper">
        <div class="ai-search-wrapper">
            <span class="ai-search-icon">🔍</span>
            <input 
                type="text" 
                id="smart_search_input" 
                placeholder="Doorzoek alle vacatures als volgt: ‘duurzame marketingbaan in Amsterdam’"
            />
        </div>

        <div class="ai-search-suggestions">
            Voorbeelden:
            <span>parttime baan in Utrecht</span>
            <span>duurzame stage communicatie</span>
            <span>freelance installateur in Rotterdam</span>
        </div>
    </div>

    <!-- Verborgen velden -->
    <input type="hidden" name="search_keywords" id="search_keywords" value="">
    <input type="hidden" name="search_location" id="search_location" value="">
    <select name="search_sectors[]" id="search_sectors" style="display:none;" multiple="multiple">
        <?php
        $sectors = get_terms([
            'taxonomy'   => 'job_sector',
            'hide_empty' => false,
        ]);
        if ( ! empty( $sectors ) && ! is_wp_error( $sectors ) ) {
            foreach ( $sectors as $sector ) {
                echo '<option value="' . esc_attr( $sector->slug ) . '">' . esc_html( $sector->name ) . '</option>';
            }
        }
        ?>
    </select>
</form>

<div id="ai_summary_output" style="margin-bottom: 30px; display: none; padding: 15px; border-left: 4px solid #0a6b8d; background: #f0f8ff; font-family: Poppins, sans-serif; font-size: 15px; opacity: 0; transition: opacity 0.5s ease;"></div>

<button id="reset_search" style="display:none; padding: 10px 20px; background: #0a6b8d; color: white; border: none; border-radius: 6px; cursor: pointer; font-family: Poppins; font-size: 14px; margin-bottom: 30px;">
    🔁 Nieuwe zoekopdracht
</button>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const input = document.getElementById('smart_search_input');
    const form = document.querySelector('.job_filters');
    const summary = document.getElementById('ai_summary_output');
    const resetBtn = document.getElementById('reset_search');

    input.focus(); // Autofocus

    input.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();

            const userInput = input.value;

            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: new URLSearchParams({
                    action: 'process_smart_search',
                    query: userInput
                })
            })
            .then(res => res.json())
            .then(data => {
                console.log("AI response:", data);

                if (!data || typeof data !== 'object') {
                    console.error('Ongeldige AI response:', data);
                    return;
                }

                // Filters vullen
                if (data.keywords && document.getElementById('search_keywords')) {
                    document.getElementById('search_keywords').value = data.keywords;
                }

                if (data.location && document.getElementById('search_location')) {
                    document.getElementById('search_location').value = data.location;
                }

                if (Array.isArray(data.sectors) && document.getElementById('search_sectors')) {
                    const select = document.getElementById('search_sectors');
                    data.sectors.forEach(slug => {
                        const option = select.querySelector(`option[value="${slug}"]`);
                        if (option) option.selected = true;
                    });
                }

                // Samenvatting tonen
                let summaryText = '<strong>We filteren op:</strong><br>';

                if (data.keywords) {
                    summaryText += '🔎 Trefwoorden: <strong>' + data.keywords + '</strong><br>';
                }
                if (data.location) {
                    summaryText += '📍 Locatie: <strong>' + data.location + '</strong><br>';
                }
                if (data.sectors && data.sectors.length > 0) {
                    summaryText += '🏷️ Sectoren: <strong>' + data.sectors.join(', ') + '</strong>';
                }

                summary.innerHTML = summaryText;
                summary.style.display = 'block';
                setTimeout(() => summary.style.opacity = '1', 50);
                resetBtn.style.display = 'inline-block';

                form.dispatchEvent(new Event('submit'));
            })
            .catch(error => {
                console.error('Fout tijdens AI-verwerking:', error);
            });
        }
    });

    resetBtn.addEventListener('click', function() {
        input.value = '';
        document.getElementById('search_keywords').value = '';
        document.getElementById('search_location').value = '';
        document.getElementById('search_sectors').selectedIndex = -1;
        summary.style.opacity = '0';
        setTimeout(() => {
            summary.style.display = 'none';
            resetBtn.style.display = 'none';
        }, 400);

        form.dispatchEvent(new Event('submit'));
        input.focus(); // Focus opnieuw
    });
});
</script>
