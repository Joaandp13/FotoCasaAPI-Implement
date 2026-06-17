<?php

class fotoCasaModel {
    private $apiKey = '';
    private $clientId = '';
    private $apiUrl = 'https://ws.fotocasa.es/mobile/api/v3.asmx/PropertyInsertNewAd';
    private $lastResponse = null;
    private $lastError = null;

    /**
     * Constructor - Inicializa con credenciales
     */
    public function __construct($apiKey = '', $clientId = '') {
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
    }

    /**
     * Valida los datos de la propiedad antes de insertar
     * @param array $newProperty Datos de la propiedad
     * @return array Array de errores (vacío si todo es correcto)
     */
    public function checkPropertyData(array $newProperty): array {
        $errores = [];

        $campos_obligatorios = [
            'external_id' => 'La referencia interna (external_id) no puede estar vacía.',
            'title' => 'El título no puede estar vacío.',
            'operation' => 'El tipo de operación tiene que ser sale/rent.',
            'property_type' => 'El tipo de propiedad es obligatorio.',
            'price' => 'El precio tiene que ser un número superior a cero.'
        ];

        // Validar campos vacíos
        foreach ($campos_obligatorios as $field => $mensaje) {
            if (!isset($newProperty[$field]) || trim($newProperty[$field] ?? '') === '') {
                $errores[] = $mensaje;
            }
        }

        // Validar que 'operation' sea sale o rent
        if (isset($newProperty['operation']) && !in_array($newProperty['operation'], ['sale', 'rent'])) {
            $errores[] = 'El campo "operation" tiene que ser estrictamente "sale" (venta) o "rent" (alquiler).';
        }

        // Validar que 'price' sea un número mayor a 0
        if (isset($newProperty['price']) && (!is_numeric($newProperty['price']) || $newProperty['price'] <= 0)) {
            $errores[] = 'El precio tiene que ser un número entero mayor que cero.';
        }

        // Validar campos adicionales si es necesario
        if (isset($newProperty['rooms']) && (!is_numeric($newProperty['rooms']) || $newProperty['rooms'] < 0)) {
            $errores[] = 'El número de habitaciones no es válido.';
        }

        if (isset($newProperty['bathrooms']) && (!is_numeric($newProperty['bathrooms']) || $newProperty['bathrooms'] < 0)) {
            $errores[] = 'El número de baños no es válido.';
        }

        if (isset($newProperty['surface']) && (!is_numeric($newProperty['surface']) || $newProperty['surface'] <= 0)) {
            $errores[] = 'La superficie tiene que ser un número superior a cero.';
        }

        return $errores;
    }

    /**
     * Mapea los datos del usuario a los parámetros de la API de Fotocasa
     * @param array $newProperty Datos de la propiedad
     * @return array Parámetros formateados para la API
     */
    private function mapPropertyToFotocasa(array $newProperty): array {
        // Mapear tipo de propiedad (necesitas ajustar estos IDs según tu catálogo)
        $propertyTypeMap = [
            'piso' => 1,
            'casa' => 2,
            'terreno' => 3,
            'garaje' => 4,
            'local' => 5,
            'oficina' => 6,
            'chalet' => 7,
        ];

        // Determinar tipo de transacción
        $transactionTypeId = ($newProperty['operation'] === 'sale') ? '1' : '2';

        $params = [
            'languageId' => 1, // 1 para español
            'sessionId' => $this->apiKey,
            'signature' => $this->clientId,
            'comments' => $newProperty['description'] ?? '',
            'features' => $newProperty['features'] ?? '',
            'surface' => (int)($newProperty['surface'] ?? 0),
            'showSurface' => isset($newProperty['surface']) ? 'true' : 'false',
            'groundSurface' => (int)($newProperty['ground_surface'] ?? 0),
            'showGroundSurface' => isset($newProperty['ground_surface']) ? 'true' : 'false',
            'rooms' => (int)($newProperty['rooms'] ?? 0),
            'bathrooms' => (int)($newProperty['bathrooms'] ?? 0),
            'propertyTypeId' => $propertyTypeMap[$newProperty['property_type']] ?? 1,
            'propertySubtypeId' => (int)($newProperty['property_subtype'] ?? 0),
            'transactionTypeIds' => $transactionTypeId,
            'hideLocation' => isset($newProperty['hide_location']) && $newProperty['hide_location'] ? 'true' : 'false',
            'zipcode' => $newProperty['zipcode'] ?? '',
            'streetTypeId' => (int)($newProperty['street_type_id'] ?? 1),
            'street' => $newProperty['street'] ?? '',
            'number' => $newProperty['number'] ?? '',
            'floorId' => (int)($newProperty['floor_id'] ?? 0),
            'portalId' => (int)($newProperty['portal_id'] ?? 0),
            'depositRequired' => isset($newProperty['deposit_required']) && $newProperty['deposit_required'] ? 'true' : 'false',
            'less2MonthsDepositRequired' => isset($newProperty['less_2months_deposit']) && $newProperty['less_2months_deposit'] ? 'true' : 'false',
            'communityFeesIncluded' => isset($newProperty['community_fees_included']) && $newProperty['community_fees_included'] ? 'true' : 'false',
            'clientReferenceId' => $newProperty['external_id'],
            'platformId' => (int)($newProperty['platform_id'] ?? 1),
            'latitude' => $newProperty['latitude'] ?? '',
            'longitude' => $newProperty['longitude'] ?? '',
            'energyCertificateId' => (int)($newProperty['energy_certificate'] ?? 0),
            'tourismRegister' => $newProperty['tourism_register'] ?? '',
            'statusId' => (int)($newProperty['status_id'] ?? 1),
            'shouldSendToVibbo' => isset($newProperty['send_to_vibbo']) && $newProperty['send_to_vibbo'] ? 'true' : 'false',
            'requiresProfessionalServices' => isset($newProperty['requires_professional']) && $newProperty['requires_professional'] ? 'true' : 'false',
            'conservationStatusId' => (int)($newProperty['conservation_status'] ?? 0),
            'antiquityId' => (int)($newProperty['antiquity'] ?? 0),
        ];

        // Manejo de precios según tipo de transacción
        if ($newProperty['operation'] === 'sale') {
            $params['price'] = (float)$newProperty['price'];
            $params['priceRent'] = '';
            $params['priceDaily'] = '';
            $params['priceWeekly'] = '';
            $params['priceMonthly'] = '';
            $params['rentPeriodicityIds'] = '';
        } else {
            $params['price'] = '';
            $params['priceRent'] = (float)$newProperty['price'];
            $params['priceDaily'] = (float)($newProperty['price_daily'] ?? '');
            $params['priceWeekly'] = (float)($newProperty['price_weekly'] ?? '');
            $params['priceMonthly'] = (float)($newProperty['price_monthly'] ?? '');
            $params['rentPeriodicityIds'] = $newProperty['rent_periodicity'] ?? '1'; // 1 = mensual
        }

        return $params;
    }

    /**
     * Realiza una llamada HTTP a la API (GET o POST)
     * @param array $params Parámetros a enviar
     * @param string $method GET o POST
     * @return array Respuesta de la API
     */
    private function makeRequest(array $params, $method = 'POST'): array {
        try {
            $ch = curl_init();

            if ($method === 'GET') {
                $queryString = http_build_query($params);
                curl_setopt($ch, CURLOPT_URL, $this->apiUrl . '?' . $queryString);
                curl_setopt($ch, CURLOPT_HTTPGET, true);
            } else {
                curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-FotoCasa-Client/1.0');

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);

            curl_close($ch);

            if ($curlError) {
                return [
                    'success' => false,
                    'message' => 'Error en la conexión: ' . $curlError,
                    'http_code' => $httpCode
                ];
            }

            if ($httpCode !== 200) {
                return [
                    'success' => false,
                    'message' => 'Error HTTP ' . $httpCode,
                    'response' => $response,
                    'http_code' => $httpCode
                ];
            }

            // Parsear la respuesta XML
            return $this->parseXmlResponse($response);

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Parsea la respuesta XML de Fotocasa
     * @param string $xmlResponse Respuesta XML
     * @return array Datos parseados
     */
    private function parseXmlResponse($xmlResponse): array {
        try {
            $xml = simplexml_load_string($xmlResponse);

            if ($xml === false) {
                return [
                    'success' => false,
                    'message' => 'Respuesta XML inválida',
                    'raw_response' => $xmlResponse
                ];
            }

            // Buscar la respuesta en la estructura XML
            // La respuesta puede estar en ObjPropertyInsertResponse
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
                    'message' => 'Propiedad insertada correctamente en Fotocasa.',
                    'is_pay_ad' => strtolower($isPayAd) === 'true' || $isPayAd === '1',
                    'raw_response' => $xmlResponse
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'Respuesta incompleta de Fotocasa',
                    'raw_response' => $xmlResponse
                ];
            }

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al procesar respuesta: ' . $e->getMessage(),
                'raw_response' => $xmlResponse
            ];
        }
    }

    /**
     * Inserta una nueva propiedad en Fotocasa usando POST
     * @param array $newProperty Datos de la propiedad
     * @param string $method GET o POST (default: POST)
     * @return array ['success' => bool, 'message' => string, 'property_id' => string|null]
     */
    public function insertProperty(array $newProperty, $method = 'POST'): array {
        // Primero validar los datos
        $errores = $this->checkPropertyData($newProperty);

        if (!empty($errores)) {
            return [
                'success' => false,
                'message' => 'Errores en la validación: ' . implode(' ', $errores),
                'errors' => $errores,
                'property_id' => null
            ];
        }

        try {
            // Mapear los datos al formato de Fotocasa
            $params = $this->mapPropertyToFotocasa($newProperty);

            // Hacer la llamada HTTP
            $response = $this->makeRequest($params, strtoupper($method));

            // Añadir el ID de referencia a la respuesta
            if (isset($response['success']) && $response['success']) {
                $response['property_id'] = $newProperty['external_id'];
            }

            return $response;

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Error al insertar la propiedad: ' . $e->getMessage(),
                'property_id' => null
            ];
        }
    }

    /**
     * Obtiene la última respuesta (para debugging)
     * @return string
     */
    public function getLastResponse(): string {
        return $this->lastResponse ?? '';
    }

    /**
     * Obtiene el último error (para debugging)
     * @return string
     */
    public function getLastError(): string {
        return $this->lastError ?? '';
    }
}

?>