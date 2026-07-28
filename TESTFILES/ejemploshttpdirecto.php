<?php

/**
 * EJEMPLOS DE LLAMADAS DIRECTAS HTTP POST Y GET A FOTOCASA
 * Sin usar la clase fotoCasaModel
 */

// ========================
// EJEMPLO 1: HTTP POST CON cURL
// ========================

function insertar_fotocasa_post($sessionId, $signature) {
    $url = 'https://ws.fotocasa.es/mobile/api/v3.asmx/PropertyInsertNewAd';
    
    // Parámetros de la propiedad
    $datos = [
        'languageId' => 1,
        'sessionId' => $sessionId,
        'signature' => $signature,
        'propertyTypeId' => 1,  // piso
        'transactionTypeIds' => '1', // compraventa
        'price' => 250000,
        'surface' => 100,
        'showSurface' => 'true',
        'rooms' => 3,
        'bathrooms' => 2,
        'street' => 'Paseo de Gracia',
        'number' => '123',
        'zipcode' => '08008',
        'clientReferenceId' => 'PROP-2024-001',
        'comments' => 'Piso reformado en Barcelona',
        'latitude' => '41.3947',
        'longitude' => '2.1656',
        'userId' => 12345,
        'platformId' => 1,
        'statusId' => 1,
    ];
    
    // Inicializar cURL
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Ejecutar
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Procesar respuesta
    echo "HTTP Code: " . $httpCode . "\n";
    if ($error) {
        echo "Error: " . $error . "\n";
    } else {
        echo "Respuesta:\n";
        var_dump($response);
    }
}

// ========================
// EJEMPLO 2: HTTP GET CON cURL
// ========================

function insertar_fotocasa_get($sessionId, $signature) {
    $url = 'https://ws.fotocasa.es/mobile/api/v3.asmx/PropertyInsertNewAd';
    
    // Parámetros de la propiedad
    $datos = [
        'languageId' => 1,
        'sessionId' => $sessionId,
        'signature' => $signature,
        'propertyTypeId' => 2,  // casa
        'transactionTypeIds' => '2', // alquiler
        'priceRent' => 1200,
        'surface' => 120,
        'showSurface' => 'true',
        'rooms' => 4,
        'bathrooms' => 2,
        'street' => 'Carrer de Còrsega',
        'number' => '456',
        'zipcode' => '08036',
        'clientReferenceId' => 'PROP-2024-002',
        'comments' => 'Casa para alquilar',
        'latitude' => '41.3984',
        'longitude' => '2.1658',
        'userId' => 12345,
        'platformId' => 1,
    ];
    
    // Construir URL con parámetros
    $urlCompleta = $url . '?' . http_build_query($datos);
    
    // Inicializar cURL
    $ch = curl_init();
    
    curl_setopt($ch, CURLOPT_URL, $urlCompleta);
    curl_setopt($ch, CURLOPT_HTTPGET, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    
    // Ejecutar
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    
    curl_close($ch);
    
    // Procesar respuesta
    echo "HTTP Code: " . $httpCode . "\n";
    if ($error) {
        echo "Error: " . $error . "\n";
    } else {
        echo "Respuesta:\n";
        var_dump($response);
    }
}

// ========================
// EJEMPLO 3: Parsear respuesta XML
// ========================

function procesar_respuesta_fotocasa($xmlResponse) {
    try {
        $xml = simplexml_load_string($xmlResponse);
        
        if ($xml === false) {
            return ['error' => 'XML inválido'];
        }
        
        // Intentar encontrar IsPayAd en diferentes ubicaciones
        $isPayAd = null;
        
        if (isset($xml->IsPayAd)) {
            $isPayAd = (string)$xml->IsPayAd;
        } elseif (isset($xml->{'ObjPropertyInsertResponse'}->IsPayAd)) {
            $isPayAd = (string)$xml->{'ObjPropertyInsertResponse'}->IsPayAd;
        } elseif (isset($xml->{'PropertyInsertNewAdResult'}->IsPayAd)) {
            $isPayAd = (string)$xml->{'PropertyInsertNewAdResult'}->IsPayAd;
        }
        
        if ($isPayAd !== null) {
            return [
                'success' => true,
                'is_pay_ad' => strtolower($isPayAd) === 'true',
                'raw_xml' => $xmlResponse
            ];
        } else {
            return [
                'success' => false,
                'error' => 'IsPayAd no encontrado en respuesta'
            ];
        }
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// ========================
// EJEMPLO 4: Usando file_get_contents (sin cURL)
// ========================

function insertar_fotocasa_stream($sessionId, $signature) {
    $url = 'https://ws.fotocasa.es/mobile/api/v3.asmx/PropertyInsertNewAd';
    
    $datos = [
        'languageId' => 1,
        'sessionId' => $sessionId,
        'signature' => $signature,
        'propertyTypeId' => 1,
        'transactionTypeIds' => '1',
        'price' => 350000,
        'surface' => 100,
        'rooms' => 3,
        'bathrooms' => 1,
        'street' => 'Paseo de Gracia',
        'number' => '789',
        'zipcode' => '08007',
        'clientReferenceId' => 'PROP-2024-003',
        'userId' => 12345,
    ];
    
    $options = [
        'http' => [
            'method' => 'POST',
            'header' => 'Content-Type: application/x-www-form-urlencoded',
            'content' => http_build_query($datos),
            'timeout' => 30
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false,
        ]
    ];
    
    $context = stream_context_create($options);
    
    try {
        $response = file_get_contents($url, false, $context);
        echo "Respuesta:\n";
        var_dump($response);
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }
}

// ========================
// EJEMPLO 5: Con GuzzleHttp (si tienes composer)
// ========================

/*
require 'vendor/autoload.php';

use GuzzleHttp\Client;

function insertar_fotocasa_guzzle($sessionId, $signature) {
    $client = new Client();
    
    $response = $client->post('https://ws.fotocasa.es/mobile/api/v3.asmx/PropertyInsertNewAd', [
        'form_params' => [
            'languageId' => 1,
            'sessionId' => $sessionId,
            'signature' => $signature,
            'propertyTypeId' => 1,
            'transactionTypeIds' => '1',
            'price' => 250000,
            'surface' => 100,
            'rooms' => 3,
            'bathrooms' => 2,
            'street' => 'Paseo de Gracia',
            'number' => '123',
            'zipcode' => '08008',
            'clientReferenceId' => 'PROP-2024-001',
            'userId' => 12345,
        ],
        'verify' => false,
        'timeout' => 30,
    ]);
    
    $body = $response->getBody();
    echo "Respuesta: " . $body;
}
*/

// ========================
// USO DE LOS EJEMPLOS
// ========================

// Descomenta la función que quieras usar:

/*
insertar_fotocasa_post('tu_sessionId', 'tu_signature');
*/

/*
insertar_fotocasa_get('tu_sessionId', 'tu_signature');
*/

/*
$xmlResponse = '<ObjPropertyInsertResponse><IsPayAd>true</IsPayAd></ObjPropertyInsertResponse>';
$resultado = procesar_respuesta_fotocasa($xmlResponse);
var_dump($resultado);
*/

/*
insertar_fotocasa_stream('tu_sessionId', 'tu_signature');
*/

?>