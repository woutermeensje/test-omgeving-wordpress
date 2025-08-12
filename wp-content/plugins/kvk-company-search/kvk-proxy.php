<?php
// Schakel foutmeldingen uit om HTML-uitvoer te vermijden
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
ini_set("log_errors", 1);
ini_set("error_log", __DIR__ . "/php-error.log"); 

// Headers instellen voor JSON-response
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json");

// Controleer of de handelsnaam-parameter is meegegeven
if (!isset($_GET['handelsnaam'])) {
    echo json_encode(['error' => 'No handelsnaam specified']);
    exit;
}

// Bouw de URL met de geëncodeerde handelsnaam
$handelsnaam = urlencode($_GET['handelsnaam']);
$api_url = "https://api.kvk.nl/api/v2/zoeken?plaats={$plaats}";



// Gebruik file_get_contents om de API-aanroep te maken
$response = file_get_contents($api_url, false, stream_context_create([
    "http" => [
    "header" => "apikey: l7e1f7d3c71df6420eb93cc7119b0dfc3e"
    ]
]));

// Controleer of er een response is ontvangen en stuur deze terug
if ($response === false) {
    $error = error_get_last();
    echo json_encode(['error' => 'Failed to fetch data from the KVK API', 'details' => $error['message']]);
} else {
    // Valideer en stuur de JSON-respons
    $json = json_decode($response);
    if (json_last_error() === JSON_ERROR_NONE) {
        echo $response;
    } else {
        echo json_encode(['error' => 'Invalid JSON received from KVK API']);
    }
}
