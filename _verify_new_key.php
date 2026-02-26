<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'config/gemini_config.php';

function test_api($version, $model, $key) {
    echo "Probando Version: $version | Modelo: $model -> ";
    $url = "https://generativelanguage.googleapis.com/$version/models/$model:generateContent?key=$key";
    
    $payload = [
        'contents' => [['parts' => [['text' => 'Hola']]]]
    ];
    
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => true
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        echo "¡ÉXITO! (200 OK)\n";
        return true;
    } else {
        echo "FALLO ($http_code)\n";
        return false;
    }
}

$key = GEMINI_API_KEY;
echo "Nueva Key (primeros 5): " . substr($key, 0, 5) . "...\n\n";

$combinations = [
    ['v1', 'gemini-1.5-flash'],
    ['v1beta', 'gemini-1.5-flash'],
    ['v1', 'gemini-pro'],
    ['v1beta', 'gemini-pro']
];

$found = false;
foreach ($combinations as $c) {
    if (test_api($c[0], $c[1], $key)) {
        $found = true;
        echo "\nRESULTADO: Usar $c[0] con $c[1]\n";
        break;
    }
}

if (!$found) echo "\nNinguna combinación estándar funcionó. Revisa la consola de Google Cloud para ver si la API de Generative Language está habilitada.";
?>
