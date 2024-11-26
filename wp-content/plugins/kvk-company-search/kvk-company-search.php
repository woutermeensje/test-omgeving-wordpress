<?php
/*
Plugin Name: KVK Company Search
Description: Plugin voor het zoeken naar bedrijven in de KVK-database.
Version: 1.0
Author: Jouw Naam
*/

// Basis beveiliging om directe toegang tot het bestand te blokkeren.
if (!defined('ABSPATH')) {
    exit;
}

function kvk_company_search_form() {
    ob_start();
    ?>
    <form id="kvk-search-form">
        <input type="text" id="kvk-search-query" placeholder="Zoek op bedrijfsnaam of KVK-nummer">
        <button type="submit">Zoeken</button>
    </form>
    <div id="kvk-results"></div>

    <script>
    document.getElementById('kvk-search-form').addEventListener('submit', function(event) {
        event.preventDefault();
        let query = document.getElementById('kvk-search-query').value;

        // Zorg dat de URL naar kvk-proxy.php correct is ingesteld
        fetch(`<?php echo plugin_dir_url(__FILE__); ?>kvk-proxy.php?handelsnaam=${query}`)
        .then(response => response.json())
        .then(data => {
            let resultsDiv = document.getElementById('kvk-results');
            resultsDiv.innerHTML = '';

            if (data && data.resultaten && data.resultaten.length > 0) {
                data.resultaten.forEach(item => {
                    resultsDiv.innerHTML += `<p>${item.naam} - ${item.kvkNummer}</p>`;
                });
            } else {
                resultsDiv.innerHTML = '<p>Geen resultaten gevonden</p>';
            }
        })
        .catch(error => console.error('Error:', error));
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('kvk_company_search', 'kvk_company_search_form');
