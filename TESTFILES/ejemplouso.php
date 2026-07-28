<?php

// Incluir la clase
require_once 'fotoCasaModel.php';

// ========================
// EJEMPLO DE USO
// ========================

// Instanciar la clase con tus credenciales
$fotocasa = new fotoCasaModel(
    apiKey: 'tu_session_id_aqui',
    clientId: 'tu_signature_aqui'
);

// Array con los datos de la propiedad a insertar
$newProperty = [
    // Campos obligatorios
    'external_id' => 'PROP-2024-001',           // Tu referencia interna
    'title' => 'Piso en el Eixample, Barcelona', // Título del anuncio
    'operation' => 'sale',                       // 'sale' o 'rent'
    'property_type' => 'piso',                   // Tipo de propiedad
    'price' => 350000,                           // Precio (en compraventa)
    
    // Campos de ubicación
    'street' => 'Paseo de Gracia',
    'number' => '123',
    'zipcode' => '08008',
    'latitude' => '41.3947',
    'longitude' => '2.1656',
    
    // Características físicas
    'surface' => 120,                 // m²
    'rooms' => 3,                     // Habitaciones
    'bathrooms' => 2,                 // Baños
    'description' => 'Piso reformado con vistas a la ciudad. Luz natural, cocina equipada, baño con ducha y bañera.',
    'features' => 'Ascensor, Calefacción, Aire acondicionado',
    
    // Campos opcionales
    'conservation_status' => 2,       // 1: Nuevo, 2: Excelente, 3: Bueno, etc.
    'antiquity' => 5,                 // Años de antigüedad
    'energy_certificate' => 3,        // ID del certificado energético
    'user_id' => 12345,              // ID del usuario en Fotocasa
];

// Ejemplo de alquiler
$rentalProperty = [
    'external_id' => 'PROP-2024-002',
    'title' => 'Apartamento amueblado Barcelona',
    'operation' => 'rent',                    // Alquiler
    'property_type' => 'piso',
    'price' => 1200,                          // Precio mensual del alquiler
    'street' => 'Carrer de Còrsega',
    'number' => '456',
    'zipcode' => '08036',
    'latitude' => '41.3984',
    'longitude' => '2.1658',
    'surface' => 85,
    'rooms' => 2,
    'bathrooms' => 1,
    'description' => 'Apartamento completamente amueblado. Ideal para profesionales.',
    'user_id' => 12345,
];

// ========================
// OPCIÓN 1: INSERTAR CON POST (RECOMENDADO)
// ========================

echo "=== INSERTANDO PROPIEDAD CON POST (COMPRAVENTA) ===\n";
$result = $fotocasa->insertProperty($newProperty, 'POST');

if ($result['success']) {
    echo "✅ Éxito: " . $result['message'] . "\n";
    echo "ID de referencia: " . $result['property_id'] . "\n";
    echo "Es anuncio de pago: " . ($result['is_pay_ad'] ? 'Sí' : 'No') . "\n";
} else {
    echo "❌ Error: " . $result['message'] . "\n";
    if (isset($result['errors'])) {
        echo "Detalles de validación:\n";
        foreach ($result['errors'] as $error) {
            echo "  - $error\n";
        }
    }
    if (isset($result['raw_response'])) {
        echo "Respuesta API:\n";
        echo $result['raw_response'] . "\n";
    }
}

echo "\n---\n\n";

// ========================
// OPCIÓN 2: INSERTAR CON GET
// ========================

echo "=== INSERTANDO PROPIEDAD CON GET (ALQUILER) ===\n";
$result2 = $fotocasa->insertProperty($rentalProperty, 'GET');

if ($result2['success']) {
    echo "✅ Éxito: " . $result2['message'] . "\n";
    echo "ID de referencia: " . $result2['property_id'] . "\n";
} else {
    echo "❌ Error: " . $result2['message'] . "\n";
}

echo "\n---\n\n";

// ========================
// VALIDACIÓN SIN INSERTAR
// ========================

echo "=== VALIDANDO DATOS (SIN INSERTAR) ===\n";

// Ejemplo con datos inválidos
$invalidProperty = [
    'external_id' => '',  // Vacío
    'title' => 'Test',
    'operation' => 'venta', // Incorrecto (debe ser sale o rent)
    'property_type' => 'piso',
    'price' => -500, // Negativo
];

$errores = $fotocasa->checkPropertyData($invalidProperty);

if (empty($errores)) {
    echo "✅ Datos válidos\n";
} else {
    echo "❌ Errores encontrados:\n";
    foreach ($errores as $error) {
        echo "  - $error\n";
    }
}

echo "\n---\n\n";

// ========================
// EXAMPLE 3: MASIVA (MÚLTIPLES PROPIEDADES)
// ========================

echo "=== INSERTANDO MÚLTIPLES PROPIEDADES ===\n";

$propiedades = [
    [
        'external_id' => 'PROP-2024-100',
        'title' => 'Piso 1',
        'operation' => 'sale',
        'property_type' => 'piso',
        'price' => 250000,
        'street' => 'Carrer A',
        'number' => '10',
        'zipcode' => '08001',
        'surface' => 80,
        'rooms' => 2,
        'bathrooms' => 1,
        'description' => 'Piso reformado',
        'user_id' => 12345,
    ],
    [
        'external_id' => 'PROP-2024-101',
        'title' => 'Piso 2',
        'operation' => 'rent',
        'property_type' => 'piso',
        'price' => 1000,
        'street' => 'Carrer B',
        'number' => '20',
        'zipcode' => '08002',
        'surface' => 90,
        'rooms' => 3,
        'bathrooms' => 2,
        'description' => 'Piso moderno',
        'user_id' => 12345,
    ],
];

$exitosas = 0;
$fallidas = 0;

foreach ($propiedades as $propiedad) {
    $result = $fotocasa->insertProperty($propiedad, 'POST');
    
    if ($result['success']) {
        echo "✅ " . $propiedad['external_id'] . " - Insertada correctamente\n";
        $exitosas++;
    } else {
        echo "❌ " . $propiedad['external_id'] . " - Error: " . $result['message'] . "\n";
        $fallidas++;
    }
}

echo "\nResumen: $exitosas exitosas, $fallidas fallidas\n";

?>