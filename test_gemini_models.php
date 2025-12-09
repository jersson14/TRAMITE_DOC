<?php
/**
 * Script para probar la API de Gemini y listar modelos disponibles
 */

// Tu API Key
$api_key = 'AIzaSyDEfGl34M__rnSKngDRbePSefjqm0TnmXc';

echo "<h2>Probando conexión con Google Gemini API</h2>";
echo "<hr>";

// 1. Listar modelos disponibles
echo "<h3>1. Listando modelos disponibles...</h3>";
$list_url = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $api_key;

$ch = curl_init($list_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "<p><strong>Código HTTP:</strong> $http_code</p>";

if ($http_code == 200) {
    $result = json_decode($response, true);
    
    if (isset($result['models'])) {
        echo "<p><strong>✅ Modelos disponibles:</strong></p>";
        echo "<ul>";
        
        foreach ($result['models'] as $model) {
            $model_name = $model['name'];
            $display_name = isset($model['displayName']) ? $model['displayName'] : 'N/A';
            $supported_methods = isset($model['supportedGenerationMethods']) 
                ? implode(', ', $model['supportedGenerationMethods']) 
                : 'N/A';
            
            echo "<li>";
            echo "<strong>$model_name</strong><br>";
            echo "Nombre: $display_name<br>";
            echo "Métodos soportados: $supported_methods";
            echo "</li>";
        }
        
        echo "</ul>";
    } else {
        echo "<p>❌ No se encontraron modelos en la respuesta</p>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
} else {
    echo "<p>❌ Error al listar modelos</p>";
    echo "<pre>" . htmlspecialchars($response) . "</pre>";
}

echo "<hr>";

// 2. Probar con diferentes nombres de modelo
echo "<h3>2. Probando diferentes nombres de modelo...</h3>";

$models_to_test = [
    'gemini-pro',
    'gemini-1.5-pro',
    'gemini-1.5-flash',
    'gemini-1.5-flash-latest',
    'gemini-1.5-pro-latest'
];

foreach ($models_to_test as $model_name) {
    echo "<h4>Probando: $model_name</h4>";
    
    $test_url = "https://generativelanguage.googleapis.com/v1beta/models/{$model_name}:generateContent?key=" . $api_key;
    
    $payload = [
        'contents' => [
            [
                'parts' => [
                    ['text' => 'Di "hola" en una palabra']
                ]
            ]
        ],
        'generationConfig' => [
            'maxOutputTokens' => 10
        ]
    ];
    
    $ch = curl_init($test_url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_TIMEOUT => 10
    ]);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($http_code == 200) {
        $result = json_decode($response, true);
        
        if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
            $text = $result['candidates'][0]['content']['parts'][0]['text'];
            echo "<p style='color: green;'>✅ <strong>FUNCIONA!</strong> Respuesta: $text</p>";
            echo "<p><strong>USA ESTE MODELO: $model_name</strong></p>";
        } else {
            echo "<p style='color: orange;'>⚠️ Respuesta recibida pero sin texto</p>";
            echo "<pre>" . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</pre>";
        }
    } else {
        $result = json_decode($response, true);
        $error_msg = isset($result['error']['message']) ? $result['error']['message'] : 'Error desconocido';
        echo "<p style='color: red;'>❌ Error (código $http_code): $error_msg</p>";
    }
    
    echo "<hr style='border: 1px dashed #ccc;'>";
}

echo "<hr>";
echo "<h3>3. Instrucciones</h3>";
echo "<p>Busca el modelo que tenga el ✅ <strong>FUNCIONA!</strong> y usa ese nombre en tu configuración.</p>";

?>

<style>
body { font-family: Arial, sans-serif; padding: 20px; }
pre { background: #f4f4f4; padding: 10px; border-radius: 5px; overflow-x: auto; }
li { margin: 10px 0; }
</style>