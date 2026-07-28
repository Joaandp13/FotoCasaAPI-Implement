<?php
/**
 * Fotocasa Pro API Integration
 * Documentation: https://www.fotocasa.pro/u/api-documentation
 * Requires: API Key (get from Fotocasa Pro > Your profile > Content import)
 */

class fotoCasaModel {
    private $apiKey = '';
    private $apiUrl = 'https://imports.gw.fotocasa.pro';
    
    public function __construct($apiKey = '') {
        $this->apiKey = $apiKey;
    }
    
    /**
     * Mapea tipos de propiedad de tu CMS a IDs de Fotocasa
     */
    public function mapTypeId($tipus) {
        $map = array(
            'Casa' => 2,              // House
            'Pis-Apartament' => 1,    // Flat
            'Terreny' => 6,           // Land
            'Local' => 3,             // Commercial store
            'Altres' => 1             // Default: Flat
        );
        return isset($map[$tipus]) ? $map[$tipus] : 1;
    }
    
    /**
     * Mapea subtipos según el TypeId
     */
   public function mapSubTypeId($conservation_status, $typeId) {
        // Mapear número a etiqueta
        $statusMap = array(
            1 => 'Nou',
            2 => 'Reformat', 
            3 => 'Segona ma',
            6 => 'Reformat'
        );
        
        $statusLabel = isset($statusMap[$conservation_status]) ? $statusMap[$conservation_status] : 'Segona ma';
        
        $subtypes = array(
            1 => array(  // Flat
                'Nou' => 9,
                'Reformat' => 9,
                'Segona ma' => 9
            ),
            2 => array(  // House
                'Nou' => 13,
                'Reformat' => 13,
                'Segona ma' => 13
            ),
            6 => array(  // Land
                'Nou' => 56,
                'Reformat' => 56,
                'Segona ma' => 56
            ),
            3 => array(  // Commercial
                'Nou' => 49,
                'Reformat' => 49,
                'Segona ma' => 49
            )
        );
        
        if (isset($subtypes[$typeId][$statusLabel])) {
            return $subtypes[$typeId][$statusLabel];
        }
        return 9;
    }
    
    /**
     * Mapea estado de conservación a FeatureId 249
     * 1=Good, 2=Pretty good, 3=Almost new, 4=Needs renovation, 6=Renovated
     */
    private function mapConservationStatus($estat) {
        $map = array(
            'Nou' => 3,           // Almost new
            'Reformat' => 6,      // Renovated
            'Segona ma' => 2      // Pretty good
        );
        return isset($map[$estat]) ? $map[$estat] : 2;
    }
    
    /**
     * Mapea eficiencia energética
     * 1=A, 2=B, 3=C, 4=D, 5=E, 6=F, 7=G
     */
    private function mapEnergyScale($lletra) {
        $map = array(
            'A' => 1,
            'B' => 2,
            'C' => 3,
            'D' => 4,
            'E' => 5,
            'F' => 6,
            'G' => 7
        );
        return isset($map[$lletra]) ? $map[$lletra] : 7;
    }
    
    /**
     * Construye el array de PropertyFeature
     */
    private function buildPropertyFeatures($property) {
        $features = array();
        
        // FeatureId 1: Surface (m2)
        if (!empty($property['surface'])) {
            $features[] = array(
                'FeatureId' => 1,
                'DecimalValue' => (float)$property['surface']
            );
        }
        
        // FeatureId 2: Title
        if (!empty($property['title'])) {
            $features[] = array(
                'FeatureId' => 2,
                'TextValue' => substr($property['title'], 0, 130)
            );
        }
        
        // FeatureId 3: Description
        if (!empty($property['description'])) {
            $features[] = array(
                'FeatureId' => 3,
                'TextValue' => substr($property['description'], 0, 5000)
            );
        }
        
        // FeatureId 11: Rooms
        if (!empty($property['rooms'])) {
            $features[] = array(
                'FeatureId' => 11,
                'DecimalValue' => (int)$property['rooms']
            );
        }
        
        // FeatureId 12: Bathrooms
        if (!empty($property['bathrooms'])) {
            $features[] = array(
                'FeatureId' => 12,
                'DecimalValue' => (int)$property['bathrooms']
            );
        }
        
        // FeatureId 249: Conservation status
        if (!empty($property['conservation_status'])) {
            $features[] = array(
                'FeatureId' => 249,
                'DecimalValue' => $property['conservation_status']
            );
        }
        
        // FeatureId 323: Consumption efficiency scale
        if (!empty($property['consumption_scale'])) {
            $features[] = array(
                'FeatureId' => 323,
                'DecimalValue' => $property['consumption_scale']
            );
        }
        
        // FeatureId 324: Emissions efficiency scale
        if (!empty($property['emissions_scale'])) {
            $features[] = array(
                'FeatureId' => 324,
                'DecimalValue' => $property['emissions_scale']
            );
        }
        
        // FeatureId 325: Consumption efficiency value
        if (!empty($property['consumption_value'])) {
            $features[] = array(
                'FeatureId' => 325,
                'DecimalValue' => (int)$property['consumption_value']
            );
        }
        
        // FeatureId 326: Emissions efficiency value
        if (!empty($property['emissions_value'])) {
            $features[] = array(
                'FeatureId' => 326,
                'DecimalValue' => (int)$property['emissions_value']
            );
        }
        
        return $features;
    }
    
    /**
     * Construye PropertyAddress
     */
    private function buildPropertyAddress($property) {
        $address = array(
            array(
                'x' => (float)$property['longitude'],
                'y' => (float)$property['latitude'],
                'VisibilityModeId' => 2  // Street level visibility
            )
        );
        
        if (!empty($property['zipcode'])) {
            $address[0]['ZipCode'] = $property['zipcode'];
        }
        if (!empty($property['street'])) {
            $address[0]['Street'] = $property['street'];
        }
        if (!empty($property['number'])) {
            $address[0]['Number'] = $property['number'];
        }
        
        return $address;
    }
    
    /**
     * Construye PropertyContactInfo
     */
    private function buildContactInfo($property) {
        $contacts = array();
        
        if (!empty($property['email'])) {
            $contacts[] = array(
                'TypeId' => 1,  // Email
                'Value' => $property['email']
            );
        }
        
        if (!empty($property['phone'])) {
            $contacts[] = array(
                'TypeId' => 2,  // Phone
                'Value' => $property['phone']
            );
        }
        
        // Default contact if none provided
        if (empty($contacts)) {
            $contacts[] = array(
                'TypeId' => 1,
                'Value' => 'sauleda@finquessauleda.com'
            );
        }
        
        return $contacts;
    }
    
    /**
     * Construye PropertyTransaction
     */
    private function buildPropertyTransaction($property) {
        $transactionType = $property['operation'] === 'rent' ? 3 : 1;
        
        $transaction = array(
            array(
                'TransactionTypeId' => $transactionType,
                'Price' => (float)$property['price'],
                'ShowPrice' => true
            )
        );
        
        // Si es alquiler, indica si es temporal o largo plazo
        if ($transactionType === 3) {
            $transaction[0]['RentalType'] = !empty($property['rental_type']) ? $property['rental_type'] : 'LONG_TERM';
        }
        
        return $transaction;
    }
    
    /**
     * Construye el payload JSON para POST/PUT
     */
    private function buildPropertyPayload($property) {
        $typeId = $this->mapTypeId($property['property_type']);
        $subTypeId = $this->mapSubTypeId($property['conservation_status'], $typeId);
        
        $payload = array(
            'ExternalId' => (string)$property['external_id'],
            'AgencyReference' => substr($property['agency_reference'], 0, 50),
            'TypeId' => $typeId,
            'SubTypeId' => $subTypeId,
            'ContactTypeId' => 3,  // Specific contact
            'PropertyAddress' => $this->buildPropertyAddress($property),
            'PropertyFeature' => $this->buildPropertyFeatures($property),
            'PropertyContactInfo' => $this->buildContactInfo($property),
            'PropertyTransaction' => $this->buildPropertyTransaction($property)
        );
        
        // PropertyDocument (imágenes) - opcional
        if (!empty($property['images'])) {
            $docs = array();
            $sortId = 1;
            foreach ($property['images'] as $image) {
                $docs[] = array(
                    'TypeId' => 1,  // Image
                    'Url' => $image,
                    'SortingId' => $sortId++
                );
            }
            $payload['PropertyDocument'] = $docs;
        }
        
        // PropertyPublications - opcional
        if (!empty($property['publications'])) {
            $pubs = array();
            foreach ($property['publications'] as $pubId) {
                $pubs[] = array(
                    'PublicationId' => $pubId
                );
            }
            $payload['PropertyPublications'] = $pubs;
        }
        
        return $payload;
    }
    
    /**
     * Hace request a la API (POST o PUT)
     */
    private function makeRequest($method, $endpoint, $payload) {
        $url = $this->apiUrl . '/' . $endpoint;
        
        $ch = curl_init($url);
        
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Api-Key: ' . $this->apiKey
            ),
            CURLOPT_POSTFIELDS => json_encode($payload)
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            return array(
                'success' => false,
                'message' => 'cURL error: ' . $curlError,
                'http_code' => 0,
                'raw_response' => ''
            );
        }
        
        return array(
            'success' => in_array($httpCode, array(200, 201)),
            'http_code' => $httpCode,
            'raw_response' => $response,
            'parsed' => json_decode($response, true)
        );
    }
    
    /**
     * Inserta una propiedad nueva en Fotocasa
     */
    public function insertProperty($property) {
        // Validar datos mínimos
        if (empty($property['external_id']) || empty($property['title']) || 
            empty($property['price']) || empty($property['latitude']) || 
            empty($property['longitude'])) {
            return array(
                'success' => false,
                'message' => 'Faltan datos requeridos (external_id, title, price, latitude, longitude)',
                'errors' => array('Validación fallida')
            );
        }
        
        // Valores por defecto
        if (empty($property['agency_reference'])) {
            $property['agency_reference'] = 'REF-' . $property['external_id'];
        }
        
        // Construir payload
        $payload = $this->buildPropertyPayload($property);
        
        // Hacer request
        $result = $this->makeRequest('POST', 'api/property', $payload);
        
        // Procesar respuesta
        return array(
            'success' => $result['success'],
            'message' => isset($result['parsed']['Message']) ? $result['parsed']['Message'] : 'Sin respuesta',
            'code' => isset($result['parsed']['Code']) ? $result['parsed']['Code'] : '',
            'http_code' => $result['http_code'],
            'external_id' => isset($result['parsed']['Message']) ? $property['external_id'] : null,
            'raw_response' => $result['raw_response']
        );
    }
    
    /**
     * Actualiza una propiedad existente en Fotocasa
     */
    public function updateProperty($property) {
        // Validar external_id (requerido para actualizar)
        if (empty($property['external_id'])) {
            return array(
                'success' => false,
                'message' => 'ExternalId es requerido para actualizar',
                'errors' => array('ExternalId faltante')
            );
        }
        
        // Valores por defecto
        if (empty($property['agency_reference'])) {
            $property['agency_reference'] = 'REF-' . $property['external_id'];
        }
        
        // Construir payload
        $payload = $this->buildPropertyPayload($property);
        
        // Hacer request
        $result = $this->makeRequest('PUT', 'api/property', $payload);
        
        // Procesar respuesta
        return array(
            'success' => $result['success'],
            'message' => isset($result['parsed']['Message']) ? $result['parsed']['Message'] : 'Sin respuesta',
            'code' => isset($result['parsed']['Code']) ? $result['parsed']['Code'] : '',
            'http_code' => $result['http_code'],
            'external_id' => $property['external_id'],
            'raw_response' => $result['raw_response']
        );
    }
    
    /**
     * Elimina una propiedad de Fotocasa
     */
    public function deleteProperty($externalId) {
        if (empty($externalId)) {
            return array(
                'success' => false,
                'message' => 'ExternalId es requerido'
            );
        }
        
        // Codificar en base64
        $encodedId = base64_encode($externalId);
        
        $ch = curl_init($this->apiUrl . '/api/v2/property/' . $encodedId);
        
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => array(
                'Api-Key: ' . $this->apiKey
            )
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $parsed = json_decode($response, true);
        
        return array(
            'success' => in_array($httpCode, array(200, 201)),
            'message' => isset($parsed['Message']) ? $parsed['Message'] : 'Sin respuesta',
            'code' => isset($parsed['Code']) ? $parsed['Code'] : '',
            'http_code' => $httpCode
        );
    }
    
    /**
     * Obtiene todas las propiedades publicadas
     */
    public function getPublishedProperties() {
        $ch = curl_init($this->apiUrl . '/api/publication/property');
        
        curl_setopt_array($ch, array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => array(
                'Api-Key: ' . $this->apiKey
            )
        ));
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $properties = json_decode($response, true);
        
        return array(
            'success' => $httpCode === 200,
            'properties' => is_array($properties) ? $properties : array(),
            'count' => is_array($properties) ? count($properties) : 0
        );
    }

    /**
 * Comprueba si una propiedad ya existe en Fotocasa por su ExternalId
 */
    public function propertyExists($externalId) {
        $result = $this->getPublishedProperties();

        if (!$result['success'] || empty($result['properties'])) {
            return false;
        }

        foreach ($result['properties'] as $prop) {
            // El nombre exacto del campo puede variar según la respuesta real de la API;
            // cubrimos las variantes más probables
            $existingId = $prop['ExternalId'] ?? $prop['externalId'] ?? $prop['external_id'] ?? null;
            if ($existingId !== null && (string)$existingId === (string)$externalId) {
                return true;
            }
        }

        return false;
    }

    /**
     * Geocodifica una dirección usando Nominatim (OpenStreetMap), con reintentos
     * en cascada cuando la calle no se reconoce (p.ej. nombres de urbanizaciones
     * que no existen como "calle" real en OSM):
     *   1) calle + ciudad + CP
     *   2) ciudad + CP (sin calle)
     *   3) solo ciudad
     */
    public function getCoordinates($street, $city, $postalcode) {
        $coords = $this->tryGeocode($street, $city, $postalcode);
        if ($coords) return $coords;

        // Fallback 1: sin la calle, solo ciudad + CP
        $coords = $this->tryGeocode('', $city, $postalcode);
        if ($coords) return $coords;

        // Fallback 2: solo ciudad
        $coords = $this->tryGeocode('', $city, '');
        return $coords;
    }

    /**
     * Hace la petición real a Nominatim con los parámetros dados.
     * Los parámetros vacíos se omiten de la query.
     */
    private function tryGeocode($street, $city, $postalcode) {
        $params = http_build_query(array_filter([
            'street' => $street,
            'city' => $city,
            'postalcode' => $postalcode,
            'country' => 'Spain',
            'format' => 'json',
            'limit' => 1
        ]));

        $url = "https://nominatim.openstreetmap.org/search?" . $params;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "User-Agent: FinquesSauleda/1.0 (sauleda@finquessauleda.com)"
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            return false;
        }

        $json = json_decode($response, true);

        if (!empty($json) && isset($json[0]['lat']) && isset($json[0]['lon'])) {
            return array(
                'latitude' => (float)$json[0]['lat'],
                'longitude' => (float)$json[0]['lon']
            );
        }

        return false;
    }
}
?>