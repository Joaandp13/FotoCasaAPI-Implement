<?php 
function mapear_tipo_property($tipus) {
            $map = [
                'Casa' => 'casa',
                'Pis-Apartament' => 'piso',
                'Terreny' => 'terreno',
                'Local' => 'local',
                'Altres' => 'piso'
            ];
            return isset($map[$tipus]) ? $map[$tipus] : 'piso';  
        }

        
        function mapear_scale($lletra) {
            $map = array('A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7);
            return isset($map[$lletra]) ? $map[$lletra] : 7;
        }

        function mapear_estado($estat) {
            $map = array('Nou' => 3, 'Reformat' => 6, 'Segona ma' => 2);
            return isset($map[$estat]) ? $map[$estat] : 2;
        }
        function enviarPropiedadAFotocasa($fotocasa, $eM, $id_cataleg, $tipus, $preu, $descripcio, $m2, $hab, $banys,
                                    $estat, $lletra_consum, $lletra_emissions, $consum, $emissions,
                                    $adreca, $localitat, $cp) {

                $adreca_fc    = html_entity_decode($adreca, ENT_QUOTES, 'UTF-8');
                $localitat_fc = html_entity_decode($localitat, ENT_QUOTES, 'UTF-8');

                $coords = $fotocasa->getCoordinates($adreca_fc, $localitat_fc, $cp);

                // Fotos reales de la ficha, sacadas de imagenes_cataleg
                $fotos_urls = [];
                $base_fotos = 'https://www.finquessauleda.com/fotos/';
                $rimg = $eM->get_imagenes_by_cataleg($id_cataleg);
                if ($rimg) {
                    while ($fimg = $rimg->fetch_assoc()) {
                        if (!empty($fimg['img'])) {
                            $fotos_urls[] = $base_fotos . rawurlencode($fimg['img']);
                        }
                    }
                }

                $external_id = 'VENTA-' . $id_cataleg;

                $property_fotocasa = array(
                    'external_id' => $external_id,
                    'agency_reference' => $external_id,
                    'title' => $tipus . ' en ' . $localitat_fc,
                    'property_type' => $tipus,
                    'operation' => 'sale',
                    'price' => (float)$preu,
                    'surface' => (float)$m2,
                    'rooms' => (int)$hab,
                    'bathrooms' => (int)$banys,
                    'description' => strip_tags(html_entity_decode($descripcio, ENT_QUOTES, 'UTF-8')),
                    'conservation_status' => mapear_estado($estat),
                    'consumption_scale' => mapear_scale($lletra_consum),
                    'emissions_scale' => mapear_scale($lletra_emissions),
                    'consumption_value' => (int)$consum,
                    'emissions_value' => (int)$emissions,
                    'street' => $adreca_fc,
                    'zipcode' => $cp,
                    'latitude' => $coords ? $coords['latitude'] : 43.3397409074,
                    'longitude' => $coords ? $coords['longitude'] : -3.21288689804,
                    'images' => $fotos_urls,
                    'email' => 'sauleda@finquessauleda.com',
                    'phone' => '937600916'
                );

                // Decide insert vs update
                if ($fotocasa->propertyExists($external_id)) {
                    $result = $fotocasa->updateProperty($property_fotocasa);
                } else {
                    $result = $fotocasa->insertProperty($property_fotocasa);
                }

                if ($result['success']) {
                    return '<div class="alert alert-success">✅ ' . $result['message'] . '</div>';
                } else {
                    return '<div class="alert alert-warning">⚠️ ' . $result['message'] . '</div>';
                }
            }