<?php

class fotoCasaModel {
    private $apiKey = '';
    private $clientId = '';
    private $apiUrl = 'https://ws.fotocasa.es/mobile/api/v3.asmx/PropertyInsertNewAd';

    public function __construct($apiKey = '', $clientId = '') {
        $this->apiKey = $apiKey;
        $this->clientId = $clientId;
    }

    public function checkPropertyData($newProperty) {
        $errores = array();

        $campos_obligatorios = array(
            'external_id' => 'La referencia interna (external_id) no puede estar vacía.',
            'title' => 'El título no puede estar vacío.',
            'operation' => 'El tipo de operación tiene que ser sale/rent.',
            'property_type' => 'El tipo de propiedad es obligatorio.',
            'price' => 'El precio tiene que ser un número superior a cero.'
        );

        foreach ($campos_obligatorios as $field => $mensaje) {
            if (!isset($newProperty[$field])) {
                $errores[] = $mensaje;
            } else if (trim($newProperty[$field]) === '') {
                $errores[] = $mensaje;
            }
        }

        if (isset($newProperty['operation'])) {
            if ($newProperty['operation'] !== 'sale' && $newProperty['operation'] !== 'rent') {
                $errores[] = 'El campo "operation" tiene que ser estrictamente "sale" (venta) o "rent" (alquiler).';
            }
        }

        if (isset($newProperty['price'])) {
            if (!is_numeric($newProperty['price']) || $newProperty['price'] <= 0) {
                $errores[] = 'El precio tiene que ser un número entero mayor que cero.';
            }
        }

        if (isset($newProperty['rooms'])) {
            if (!is_numeric($newProperty['rooms']) || $newProperty['rooms'] < 0) {
                $errores[] = 'El número de habitaciones no es válido.';
            }
        }

        if (isset($newProperty['bathrooms'])) {
            if (!is_numeric($newProperty['bathrooms']) || $newProperty['bathrooms'] < 0) {
                $errores[] = 'El número de baños no es válido.';
            }
        }

        if (isset($newProperty['surface'])) {
            if (!is_numeric($newProperty['surface']) || $newProperty['surface'] <= 0) {
                $errores[] = 'La superficie tiene que ser un número superior a cero.';
            }
        }

        return $errores;
    }

    private function mapPropertyToFotocasa($newProperty) {
        $propertyTypeMap = array(
            'piso' => 1,
            'casa' => 2,
            'terreno' => 3,
            'garaje' => 4,
            'local' => 5,
            'oficina' => 6,
            'chalet' => 7,
        );

        $transactionTypeId = ($newProperty['operation'] === 'sale') ? '1' : '2';

        $description = isset($newProperty['description']) ? $newProperty['description'] : '';
        $features = isset($newProperty['features']) ? $newProperty['features'] : '';
        $surface = isset($newProperty['surface']) ? (int)$newProperty['surface'] : 0;
        $ground_surface = isset($newProperty['ground_surface']) ? (int)$newProperty['ground_surface'] : 0;
        $rooms = isset($newProperty['rooms']) ? (int)$newProperty['rooms'] : 0;
        $bathrooms = isset($newProperty['bathrooms']) ? (int)$newProperty['bathrooms'] : 0;
        $property_type = isset($newProperty['property_type']) ? $newProperty['property_type'] : 'piso';
        $property_subtype = isset($newProperty['property_subtype']) ? (int)$newProperty['property_subtype'] : 0;
        $zipcode = isset($newProperty['zipcode']) ? $newProperty['zipcode'] : '';
        $street_type_id = isset($newProperty['street_type_id']) ? (int)$newProperty['street_type_id'] : 1;
        $street = isset($newProperty['street']) ? $newProperty['street'] : '';
        $number = isset($newProperty['number']) ? $newProperty['number'] : '';
        $floor_id = isset($newProperty['floor_id']) ? (int)$newProperty['floor_id'] : 0;
        $portal_id = isset($newProperty['portal_id']) ? (int)$newProperty['portal_id'] : 0;
        $platform_id = isset($newProperty['platform_id']) ? (int)$newProperty['platform_id'] : 1;
        $latitude = isset($newProperty['latitude']) ? $newProperty['latitude'] : '';
        $longitude = isset($newProperty['longitude']) ? $newProperty['longitude'] : '';
        $energy_certificate = isset($newProperty['energy_certificate']) ? (int)$newProperty['energy_certificate'] : 0;
        $tourism_register = isset($newProperty['tourism_register']) ? $newProperty['tourism_register'] : '';
        $status_id = isset($newProperty['status_id']) ? (int)$newProperty['status_id'] : 1;
        $conservation_status = isset($newProperty['conservation_status']) ? (int)$newProperty['conservation_status'] : 0;
        $antiquity = isset($newProperty['antiquity']) ? (int)$newProperty['antiquity'] : 0;

        $propertyTypeId = isset($propertyTypeMap[$property_type]) ? $propertyTypeMap[$property_type] : 1;

        $params = array(
            'languageId' => 1,
            'sessionId' => $this->apiKey,
            'signature' => $this->clientId,
            'comments' => $description,
            'features' => $features,
            'surface' => $surface,
            'showSurface' => $surface > 0 ? 'true' : 'false',
            'groundSurface' => $ground_surface,
            'showGroundSurface' => $ground_surface > 0 ? 'true' : 'false',
            'rooms' => $rooms,
            'bathrooms' => $bathrooms,
            'propertyTypeId' => $propertyTypeId,
            'propertySubtypeId' => $property_subtype,
            'transactionTypeIds' => $transactionTypeId,
            'hideLocation' => 'false',
            'zipcode' => $zipcode,
            'streetTypeId' => $street_type_id,
            'street' => $street,
            'number' => $number,
            'floorId' => $floor_id,
            'portalId' => $portal_id,
            'depositRequired' => 'false',
            'less2MonthsDepositRequired' => 'false',
            'communityFeesIncluded' => 'false',
            'clientReferenceId' => $newProperty['external_id'],
            'platformId' => $platform_id,
            'latitude' => $latitude,
            'longitude' => $longitude,
            'energyCertificateId' => $energy_certificate,
            'tourismRegister' => $tourism_register,
            'statusId' => $status_id,
            'shouldSendToVibbo' => 'false',
            'requiresProfessionalServices' => 'false',
            'conservationStatusId' => $conservation_status,
            'antiquityId' => $antiquity,
        );

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
            $params['priceDaily'] = '';
            $params['priceWeekly'] = '';
            $params['priceMonthly'] = '';
            $params['rentPeriodicityIds'] = '1';
        }

        return $params;
    }

    private function makeRequest($params, $method = 'POST') {
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
            return array(
                'success' => false,
                'message' => 'Error en la conexión: ' . $curlError,
                'http_code' => $httpCode
            );
        }

        if ($httpCode !== 200) {
            return array(
                'success' => false,
                'message' => 'Error HTTP ' . $httpCode,
                'response' => $response,
                'http_code' => $httpCode
            );
        }

        return $this->parseXmlResponse($response);
    }

    private function parseXmlResponse($xmlResponse) {
        $xml = simplexml_load_string($xmlResponse);

        if ($xml === false) {
            return array(
                'success' => false,
                'message' => 'Respuesta XML inválida',
                'raw_response' => $xmlResponse
            );
        }

        $isPayAd = null;

        if (isset($xml->IsPayAd)) {
            $isPayAd = (string)$xml->IsPayAd;
        } elseif (isset($xml->ObjPropertyInsertResponse)) {
            if (isset($xml->ObjPropertyInsertResponse->IsPayAd)) {
                $isPayAd = (string)$xml->ObjPropertyInsertResponse->IsPayAd;
            }
        } elseif (isset($xml->PropertyInsertNewAdResult)) {
            if (isset($xml->PropertyInsertNewAdResult->IsPayAd)) {
                $isPayAd = (string)$xml->PropertyInsertNewAdResult->IsPayAd;
            }
        }

        if ($isPayAd !== null) {
            $isPay = (strtolower($isPayAd) === 'true' || $isPayAd === '1');
            return array(
                'success' => true,
                'message' => 'Propiedad insertada correctamente en Fotocasa.',
                'is_pay_ad' => $isPay,
                'raw_response' => $xmlResponse
            );
        } else {
            return array(
                'success' => false,
                'message' => 'Respuesta incompleta de Fotocasa',
                'raw_response' => $xmlResponse
            );
        }
    }

    public function insertProperty($newProperty, $method = 'POST') {
        $errores = $this->checkPropertyData($newProperty);

        if (!empty($errores)) {
            return array(
                'success' => false,
                'message' => 'Errores en la validación: ' . implode(' ', $errores),
                'errors' => $errores,
                'property_id' => null
            );
        }

        $params = $this->mapPropertyToFotocasa($newProperty);
        $response = $this->makeRequest($params, strtoupper($method));

        if (isset($response['success']) && $response['success']) {
            $response['property_id'] = $newProperty['external_id'];
        }

        return $response;
    }

}

?>
