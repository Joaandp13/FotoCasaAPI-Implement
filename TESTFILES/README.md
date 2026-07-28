# Integración API Fotocasa con POST/GET - Guía Completa

## 📋 Requisitos Previos

1. **PHP 7.4 o superior**
2. **Extensión cURL habilitada**
   - Verifica que tengas activada: `php -m | grep curl`
   - Para instalar en Linux: `apt-get install php-curl`

3. **Credenciales de Fotocasa**
   - `sessionId` - Tu ID de sesión/API Key
   - `signature` - Tu firma/Client ID
   - Estas credenciales las proporciona Fotocasa en tu panel de desarrollador

## ⚙️ Configuración

### Paso 1: Obtener credenciales
- Accede a https://www.fotocasa.es/
- Ve a tu panel de desarrollador
- Obtén tu `sessionId` y `signature`

### Paso 2: Incluir la clase
```php
require_once 'fotoCasaModel.php';
```

### Paso 3: Instanciar y configurar
```php
$fotocasa = new fotoCasaModel(
    apiKey: 'tu_session_id_aqui',
    clientId: 'tu_signature_aqui'
);
```

### Paso 4: Preparar datos de la propiedad
```php
$property = [
    'external_id' => 'PROP-001',     // Identificador único tuyo
    'title' => 'Piso en Barcelona',
    'operation' => 'sale',            // 'sale' o 'rent'
    'property_type' => 'piso',
    'price' => 350000,
    // ... más campos
];
```

## 🌐 Métodos de Envío

### POST (Recomendado)
```php
$result = $fotocasa->insertProperty($property, 'POST');
```

### GET
```php
$result = $fotocasa->insertProperty($property, 'GET');
```

**Diferencia:**
- **POST**: Más seguro, sin límites de parámetros
- **GET**: URL visible, límite de caracteres (~2KB)

## 🔍 Mapeo de Tipos de Propiedad

```
'piso' => 1
'casa' => 2
'terreno' => 3
'garaje' => 4
'local' => 5
'oficina' => 6
'chalet' => 7
```

Si usas otros tipos, ajusta el array `$propertyTypeMap` en `mapPropertyToFotocasa()`.

## 🏷️ Mapeo de Estados de Conservación

```
1 = Nuevo
2 = Excelente
3 = Bueno
4 = Aceptable
5 = Necesita reforma
```

## 💰 Diferencias entre Compraventa y Alquiler

### Para COMPRAVENTA (sale)
```php
'operation' => 'sale',
'price' => 350000,      // Precio de compra
```

### Para ALQUILER (rent)
```php
'operation' => 'rent',
'price' => 1200,        // Precio mensual
// Opcionalmente:
'price_daily' => 50,
'price_weekly' => 300,
'price_monthly' => 1200,
```

## ✅ Campos Obligatorios

- `external_id` - Tu referencia interna (única)
- `title` - Título del anuncio
- `operation` - 'sale' o 'rent'
- `property_type` - Tipo de propiedad
- `price` - Precio (número > 0)

## 📍 Todos los Campos Disponibles

### Información Básica
```php
'external_id' => 'PROP-001',           // ID único del usuario
'title' => 'Piso en Barcelona',        // Título del anuncio
'description' => 'Descripción...',     // Descripción detallada
'features' => 'Ascensor, Calefacción', // Características destacadas
```

### Ubicación
```php
'street' => 'Paseo de Gracia',
'number' => '123',
'zipcode' => '08008',
'latitude' => '41.3947',
'longitude' => '2.1656',
'hide_location' => false,              // Ocultar ubicación exacta
```

### Características Físicas
```php
'surface' => 120,              // m² útiles
'ground_surface' => 200,       // m² de parcela (si aplica)
'rooms' => 3,                  // Habitaciones
'bathrooms' => 2,              // Baños
'conservation_status' => 2,    // Estado de conservación (1-5)
'antiquity' => 5,              // Años de antigüedad
```

### Precios
```php
// Para compraventa
'price' => 350000,

// Para alquiler
'price' => 1200,               // Precio por defecto
'price_daily' => 50,
'price_weekly' => 300,
'price_monthly' => 1200,
'rent_periodicity' => '1',     // 1 = mensual
```

### Edificio/Comunidad
```php
'floor_id' => 2,               // Piso (0=planta baja)
'portal_id' => 1,              // Portal número
'street_type_id' => 1,         // Tipo de vía
'deposit_required' => true,    // Requiere depósito
'less_2months_deposit' => false, // Depósito < 2 meses
'community_fees_included' => false, // Gastos incluidos
```

### Datos Adicionales
```php
'energy_certificate' => 3,     // ID del certificado energético
'tourism_register' => '',      // Registro de turismo
'status_id' => 1,              // Estado del anuncio
'user_id' => 12345,            // Tu ID usuario en Fotocasa
'platform_id' => 1,            // ID de plataforma
'send_to_vibbo' => false,      // Enviar a Vibbo
'requires_professional' => false, // Requiere servicios profesionales
```

## 📊 Respuesta de la API

```php
[
    'success' => true|false,
    'message' => 'Descripción del resultado',
    'is_pay_ad' => true|false,    // Si es anuncio de pago
    'property_id' => 'PROP-001',   // Tu external_id
    'errors' => [...],             // Errores de validación
    'raw_response' => '...',       // Respuesta XML original
]
```

## 🎯 Ejemplos de Uso

### Ejemplo 1: Insertar una compraventa
```php
<?php
require_once 'fotoCasaModel.php';

$fotocasa = new fotoCasaModel('sessionId', 'signature');

$property = [
    'external_id' => 'PROP-2024-001',
    'title' => 'Piso reformado Barcelona Centro',
    'operation' => 'sale',
    'property_type' => 'piso',
    'price' => 250000,
    'street' => 'Carrer de Còrsega',
    'number' => '123',
    'zipcode' => '08036',
    'surface' => 100,
    'rooms' => 3,
    'bathrooms' => 1,
    'description' => 'Piso completamente reformado...',
    'user_id' => 12345
];

$result = $fotocasa->insertProperty($property, 'POST');

if ($result['success']) {
    echo "✅ Propiedad insertada: " . $result['property_id'];
} else {
    echo "❌ Error: " . $result['message'];
}
?>
```

### Ejemplo 2: Insertar un alquiler
```php
$property = [
    'external_id' => 'PROP-2024-002',
    'title' => 'Apartamento amueblado',
    'operation' => 'rent',
    'property_type' => 'piso',
    'price' => 1200,
    'street' => 'Paseo de Gracia',
    'number' => '456',
    'zipcode' => '08007',
    'surface' => 85,
    'rooms' => 2,
    'bathrooms' => 1,
    'description' => 'Completamente amueblado...',
    'user_id' => 12345,
];

$result = $fotocasa->insertProperty($property, 'POST');
```

### Ejemplo 3: Validar datos sin insertar
```php
$errores = $fotocasa->checkPropertyData($property);

if (empty($errores)) {
    echo "✅ Datos válidos";
    // Proceder con la inserción
} else {
    echo "❌ Errores encontrados:";
    foreach ($errores as $error) {
        echo "  - $error\n";
    }
}
```

### Ejemplo 4: Insertar múltiples propiedades
```php
$propiedades = [
    ['external_id' => 'PROP-001', ...],
    ['external_id' => 'PROP-002', ...],
    ['external_id' => 'PROP-003', ...],
];

foreach ($propiedades as $propiedad) {
    $result = $fotocasa->insertProperty($propiedad, 'POST');
    
    if ($result['success']) {
        echo "✅ " . $propiedad['external_id'] . " insertada\n";
    } else {
        echo "❌ " . $propiedad['external_id'] . " ERROR\n";
    }
}
```

## 🐛 Troubleshooting

### Error: "Connection timeout"
- Verifica conexión a internet
- Comprueba que la URL de la API sea accesible: https://ws.fotocasa.es/mobile/api/v3.asmx
- Aumenta el timeout en `makeRequest()`: cambiar `CURLOPT_TIMEOUT` a 60

### Error: "Invalid sessionId or signature"
- Verifica que tus credenciales sean correctas
- Comprueba que la sesión no haya expirado
- Asegúrate de usar credenciales de API, no de usuario web

### Error: "Property already exists"
- El `external_id` ya existe en Fotocasa
- Usa un `external_id` único: `'PROP-' . date('YmdHis') . uniqid()`

### Error: "Field X is required"
- Revisa que todos los campos obligatorios estén presentes
- Verifica que no haya campos vacíos en los requeridos
- Comprueba los tipos de datos (int, string, float)

### cURL no funciona
```bash
# Ubuntu/Debian
sudo apt-get install php-curl
sudo service apache2 restart

# CentOS/RHEL
sudo yum install php-curl
sudo service httpd restart
```

## 📝 Mejoras Sugeridas

1. **Logging**: Implementa logging de todas las inserciones
2. **Reintentos**: Reintentos automáticos en caso de fallo
3. **Batch processing**: Procesar múltiples propiedades con control
4. **Caché**: Guardar en caché los catálogos de tipos
5. **Webhooks**: Notificaciones de éxito/error

## 🔗 Recursos

- Documentación oficial: https://ws.fotocasa.es/mobile/api/v3.asmx
- Panel de desarrollador: https://www.fotocasa.es/
- cURL en PHP: https://www.php.net/manual/en/book.curl.php

## 📝 Notas Importantes

1. **Límites de la API**
   - Verifica con Fotocasa los límites de inserciones por hora/día
   - Implementa delays entre inserciones masivas

2. **Campos y valores válidos**
   - Algunos campos esperan valores específicos (IDs)
   - Consulta con Fotocasa el catálogo de valores válidos

3. **Actualizaciones**
   - Esta implementación usa `PropertyInsertNewAd` (inserción)
   - Para actualizar, necesitarías usar `PropertyUpdateAd`
   - Contacta a Fotocasa para acceder a ese método

4. **Imágenes**
   - Esta implementación no incluye inserción de imágenes
   - Fotocasa tiene métodos separados para ello
   - Requiere otra llamada a la API