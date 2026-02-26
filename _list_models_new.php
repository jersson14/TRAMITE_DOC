<?php
header('Content-Type: text/plain; charset=utf-8');
require_once 'config/gemini_config.php';

$key = GEMINI_API_KEY;
$url = "https://generativelanguage.googleapis.com/v1beta/models?key=$key";

echo "=== LISTANDO MODELOS DISPONIBLES (NUEVA KEY) ===\n";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Status: $http_code\n";
echo "Respuesta:\n" . $response . "\n";
?>
