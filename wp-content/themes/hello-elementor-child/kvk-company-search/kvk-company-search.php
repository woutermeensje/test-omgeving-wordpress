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

        fetch(`https://api.kvk.nl/api/v1/companies?query=${query}`, {
            headers: {
                 'Authorization': 'Bearer l7e1f7d3c71df6420eb93cc7119b0dfc3e'
            }
        })
        .then(response => response.json())
        .then(data => {
            let resultsDiv = document.getElementById('kvk-results');
            resultsDiv.innerHTML = '';

            if (data && data.items && data.items.length > 0) {
                data.items.forEach(item => {
                    resultsDiv.innerHTML += `<p>${item.tradeName} - ${item.kvkNumber}</p>`;
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
