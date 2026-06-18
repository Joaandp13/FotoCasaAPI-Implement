<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?php

include_once('../config/config.inc.php'); //cargando archivo de configuracion


$uM = load_model('usuario');

$uM->control_sesion($ruta_inicio, ADMIN);

$eM = load_model('ventas');
$hM = load_model('html');
$fM = load_model('form');
$iM = load_model('inputs');

//=====NUEVO=====
$fcM = load_model('fotocasa');

$fotocasa = new fotoCasaModel('', '');
//====NUEVO END===

// $cM = load_model('crm');



//campos formulario

$i = 0;
$array_null = array();
$id_cataleg = 0;
$nombre = '';
$descripcion = '';
$id_cliente = '';
$categoria = '';
$email = '';
$direccion = '';
$ogha='';
$cp = '';
$hab=0;
$banys=0;

$provincia_id = '';
$pais = '';
$web = '';
$tipo_establecimiento = '';
$telefono = '';
$id_usuario = $_SESSION['id_usuario'];

$descripcio = '';
$descripcioes = '';
$descripcio_en = '';
$localitat='';
$ordre = '';
$zona = '';
$tipus = '';
$estat = '';
$adreca = '';
$m2 = 0;
$preu = 0;
$venut = 0;
$visible = 0;
$certificat_foto = '';
$certificat_text = '';
$localitzacio='';
$cp='';
$ogia='';
$destacat=0;
$ordre=100;

$arr_err = array();
$verif = true;
$cont=0;
$cuentafotos=0;
$modif=false;
$destacat=0;
$servicios=array();
$consum = 0;
$emissions=0;
$lletra_consum = '';
$lletra_emissions='';

$servicios_pre = ['obranueva' => 'Obra Nova', 'parking' => 'Parking', 'ascensor' => 'Ascensor', 'piscina' => 'Piscina', 'jardin' => 'Jardí', 'terraza' => 'Terrassa'];


//NUEVO (FOTOCASA)
$enviar_fotocasa = 0;
$msg_fotocasa = '';


//POST__________________________________________________________________________

if (isset($_POST['id_cataleg'])) {


    $id_cataleg = filter_var($_POST['id_cataleg'], FILTER_SANITIZE_NUMBER_INT);
    $descripcio = htmlentities($_POST['descripcio'], ENT_QUOTES, "UTF-8");
    $descripcio_en = htmlentities($_POST['descripcio_en'], ENT_QUOTES, "UTF-8");
    $descripcioes = htmlentities($_POST['descripcioes'], ENT_QUOTES, "UTF-8");
    $cp = filter_var($_POST['cp'], FILTER_SANITIZE_STRING);
    $localitat = htmlentities($_POST['localitat'], ENT_QUOTES, "UTF-8");
    $tipus = $_POST['tipus'];
    $ordre = filter_var($_POST['ordre'], FILTER_SANITIZE_NUMBER_INT);
    $zona = $_POST['zona'];
    $estat = $_POST['estat'];
    $adreca = htmlentities($_POST['adreca'], ENT_QUOTES, "UTF-8");
    $certificat_text = $_POST['certificat_text'];
    $m2 = filter_var($_POST['m2'], FILTER_SANITIZE_NUMBER_INT);
    $venut = filter_var($_POST['venut'], FILTER_SANITIZE_NUMBER_INT);
    $visible = filter_var($_POST['visible'], FILTER_SANITIZE_NUMBER_INT);
    $hab = filter_var($_POST['hab'], FILTER_SANITIZE_NUMBER_INT);
    $banys = filter_var($_POST['banys'], FILTER_SANITIZE_NUMBER_INT);
    $destacat = filter_var($_POST['destacat'], FILTER_SANITIZE_NUMBER_INT);
    $consum = filter_var($_POST['consum'], FILTER_SANITIZE_NUMBER_INT);
    $emissions=filter_var($_POST['emissions'], FILTER_SANITIZE_NUMBER_INT);
    $preu = $_POST['preu'];
    $servicios = $_POST['servicios'];
    $lletra_consum = filter_var($_POST['lletra_consum'], FILTER_SANITIZE_STRING);
    $lletra_emissions=filter_var($_POST['lletra_emissions'], FILTER_SANITIZE_STRING);
    $enviar_fotocasa = isset($_POST['enviar_fotocasa']) ? 1 : 0;
    $localitzacio='';

    $verif=true;

    //control de errores ---------------------------------------------------- */

    if ($verif == true) {
        //id_usuario


        if ($id_cataleg > 0) { //UPDATE

            //para el campo nombre_usuario se le pasa email_usuario (el email es usado para el login)
            $ruu = $eM->update_cataleg($id_cataleg, $tipus,$preu,$descripcio,$venut,$m2,$estat,$zona,$localitat,$descripcioes,$adreca,$ordre,$descripcio_en,$localitzacio,$certificat_text,$visible,$destacat,$cp,$hab,$banys,$servicios,$consum,$emissions,$lletra_consum,$lletra_emissions);
            if ($ruu) {
                //==========NUEVO===========
                    if ($enviar_fotocasa == 1) {
                        $property_fotocasa = [
                            'external_id' => 'CASA-' . $id_cataleg,
                            'title' => $tipus . ' en ' . $localitat,
                            'operation' => 'sale',
                            'property_type' => mapear_tipo_property($tipus),
                            'price' => (int)$preu,
                            'street' => $adreca,
                            'number' => '',
                            'zipcode' => $cp,
                            'surface' => $m2,
                            'rooms' => $hab,
                            'bathrooms' => $banys,
                            'description' => strip_tags($descripcio),
                            'features' => implode(', ', $servicios),
                            'conservation_status' => mapear_estado($estat),
                            'user_id' => $_SESSION['id_usuario'],
                        ];
                        
                        $result_fotocasa = $fotocasa->insertProperty($property_fotocasa, 'POST');
                        
                        if ($result_fotocasa['success']) {
                            $msg_fotocasa = '<div class="alert alert-success">Publicado en Fotocasa</div>';
                        } else {
                            $msg_fotocasa = '<div class="alert alert-warning">No publicado: ' . $result_fotocasa['message'] . '</div>';
                        }
                    }
                    //=======NUEVO END======
                header('Location: '.$ruta_inicio.'admin/cataleg.php'); exit();
            } else $str_errores = '<div class="error_alert">Error actualizando cataleg</div>';
        } else { //NUEVO
            
            $rau = $eM->add_cataleg($tipus,$preu,$descripcio,$venut,$m2,$estat,$zona,$localitat,$descripcioes,$adreca,$ordre,$descripcio_en,$localitzacio,$certificat_text,$visible,$destacat,$cp,$hab,$banys,$servicios,$consum,$emissions,$lletra_consum,$lletra_emissions);
            if ($rau) { 
                $modif=true;

                //=========NUEVO========
                        if ($enviar_fotocasa == 1) {
                            $ultimo_id = $eM->get_last_cataleg_id();
                            
                            $property_fotocasa = [
                                'external_id' => 'CASA-' . $ultimo_id,
                                'title' => $tipus . ' en ' . $localitat,
                                'operation' => 'sale',
                                'property_type' => mapear_tipo_property($tipus),
                                'price' => (int)$preu,
                                'street' => $adreca,
                                'number' => '',
                                'zipcode' => $cp,
                                'surface' => $m2,
                                'rooms' => $hab,
                                'bathrooms' => $banys,
                                'description' => strip_tags($descripcio),
                                'features' => implode(', ', $servicios),
                                'conservation_status' => mapear_estado($estat),
                                'user_id' => $_SESSION['id_usuario'],
                            ];
                            
                            $result_fotocasa = $fotocasa->insertProperty($property_fotocasa, 'POST');
                            
                            if ($result_fotocasa['success']) {
                                $msg_fotocasa = '<div class="alert alert-success">✅ Publicado en Fotocasa</div>';
                            } else {
                                $msg_fotocasa = '<div class="alert alert-warning">⚠️ No publicado</div>';
                            }
                        }
                    //======NUEVO END=====
                header('Location: '.$ruta_inicio.'admin/cataleg.php'); exit();
            } else $str_errores = '<div class="error_alert">Error añadiendo cataleg</div>';

            

        }

        

    }

    //MySQL ----------------------------------------------------------------- */

}

if (isset($_GET['eliminar_habitacion'])) {
    $eliminar_habitacion = htmlspecialchars(base64_decode($_GET['eliminar_habitacion']));
    $id_cataleg = htmlspecialchars(base64_decode($_GET['id_cataleg']));
    $tipo_habitacion = htmlspecialchars(base64_decode($_GET['tipo_habitacion']));
    $rdai = $habM->delete_habitacion($id_cataleg, $eliminar_habitacion, $tipo_habitacion);
    if ($rdai) {
        $str_info = $hM->get_alert_success('Habitación eliminada del establecimiento correctamente');  
    } else $str_errores = $hM->get_alert_danger('Error eliminando habitación del establecimiento');
}

if (isset($_GET['id_cataleg'])) {

    $modif=true;
    $id_cataleg = base64_decode($_GET['id_cataleg']);
    $rgu = $eM->get_catalegg($id_cataleg);
    if ($rgu) {
    
        while ($fgu = $rgu->fetch_assoc()) {
                $descripcio = html_entity_decode($fgu['descripcio']);
                $descripcioes = html_entity_decode($fgu['descripcioesp']);
                $descripcio_en = html_entity_decode($fgu['descripcio_en']);
                $localitat=html_entity_decode($fgu['localitat']);

                $cp=$fgu['cp'];
                $ordre = $fgu['ordre'];
                $zona = $fgu['zona'];
                $tipus = $fgu['tipus'];
                $estat = $fgu['estat'];
                $adreca = html_entity_decode($fgu['adreca']);
                $m2 = $fgu['m2'];
                $preu = $fgu['preu'];
                $venut = $fgu['venut'];
                $visible=$fgu['visible'];
                $certificat_text = $fgu['certificat_text'];
                $certificat_foto= $fgu['certificat_foto'];
                $hab = $fgu['hab'];
                $banys = $fgu['banys'];
                $destacat=$fgu['destacat'];
                $consum = $fgu['consum_energia'];
                $emissions=$fgu['emissions'];
                $lletra_consum = $fgu['consum_energia_lletra'];
                $lletra_emissions=$fgu['emissions_lletra'];
                $rgs = $eM->get_serveis_cataleg($id_cataleg);
                if($fgs = $rgs->fetch_assoc()){
                    $servicios = array_keys($fgs, 1);
                } else {
                    $eM->add_servicios_cataleg($id_cataleg,$servicios);
                    $rgs = $eM->get_serveis_cataleg($id_cataleg);
                    if($fgs = $rgs->fetch_assoc()){
                        $servicios = array_keys($fgs, 1);
                    }
                }
        }

        $rguu = $eM->get_imagenes_by_cataleg($id_cataleg);

        if ($rguu) {
        
            while ($fguu = $rguu->fetch_assoc()) {
                

                /*$ogia .= '<tr>';
                $ogia .=     '<td align="center"><img class="img-fluid img-thumbnail" src="../fotos/'.$fguu['img'].'" width="100" /></td>';
                
                $ogia .=     '<td align="center">';
                $ogia .=         '<a href="'.$ruta_inicio.'admin/asignar-img-cataleg.php?id_cataleg='.base64_encode($id_cataleg).'&eliminar_imagen='.base64_encode($fguu['id']).'">';
                $ogia .=             '<button type="button" class="btn btn-outline-danger">Eliminar</button>';
                $ogia .=         '</a>';
                $ogia .=     '</td>';
                $ogia .= '</tr>';*/

                $ogia .= '<div class="galeria-cms col-6 col-md-4 col-lg-3">
                            <a href="../fotos/'.$fguu['img'].'" class="box glightbox">
                                <img src="../fotos/'.$fguu['img'].'" alt="image" class="image img-fluid">
                                <div class="overlay"></div>
                                <div class="controls"><i class="bi bi-eye"></i></div>
                            </a>
                            <a class="delete" href="'.$ruta_inicio.'admin/asignar-img-cataleg.php?id_cataleg='.base64_encode($id_cataleg).'&eliminar_imagen='.base64_encode($fguu['id']).'"><i class="bi bi-x-lg"></i></a>
                        </div>';

            }
        }
  
    } else $str_errores = $str_errores = '<div class="error_alert">Error cargando establecimiento</div>';

}


include_once('inc/cabecera.inc.php'); //cargando cabecera 
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

    function mapear_estado($estat) {
        $map = [
            'Nou' => 1,
            'Reformat' => 2,
            'Segona ma' => 3
        ];
        return isset($map[$estat]) ? $map[$estat] : 3;  
    }

?>

<script type="text/javascript">
</script>

<body class="snippet-body">
    <?php include_once('inc/admin-menu.php'); ?>
    <?php include_once('../inc/modal_ps.php'); ?>

    <div class="content">
        <div class="layout">
            <div class="layout-table">
                <div id="alertas">
                    <?php if (isset($str_info)) echo $str_info; ?>
                    <?php if (isset($str_errores)) echo '<div class="alert alert-danger" role="alert">'.$str_errores.'</div>'; ?>
                </div>

                <div class="layout-table-item my-3">
                    <div class="layout-table-header p-3 mx-3 mb-3">
                        <h4 class="mb-0">Fitxa de Venda</h4>
                        <!--<div class="layout-table-content">
                            <a href="<?php echo $ruta_inicio;?>admin/alquileres.php?editar_alquiler=true"><button class="btn bg-primary text-light">Volver</button><i class="bi bi-arrow-return-left"></i></a>
                        </div>-->
                    </div>

                    <div class="layout-table-content">
                        <form action="nuevo-cataleg.php" method="post">
                            <div class="row">
                                <div class="col-md-6">

                                    <?php echo $fM->get_input_hidden('id_cataleg', $id_cataleg);?>

                                    <label>Localitat</label>
                                    <?php echo $eM->get_combo_array_poblaciones($localitat,"localitat",'',"form-control");?>
                                    <label>Codi Postal</label>
                                    <?php echo $iM->get_input_text("cp", $cp,"form-control", '', '', 'Campo requerido', 1); ?>

                                
                                    <label>Zona</label>

                                    <select id="zona" class="form-control" name="zona" value="<?php echo $zona;?>"  >
                                        <option value="Centre Poble" <?php if ($zona =='Centre Poble') echo 'selected="selected"';?>>Centre Poble</option>
                                        <option value="Els Garrofers" <?php if ($zona=='Els Garrofers') echo 'selected="selected"';?>>Els Garrofers</option> 
                                        <option value="L´Hotel" <?php if ($zona=='L´Hotel') echo 'selected="selected"';?>>L´Hotel</option> 
                                        <option value="La Marina" <?php if ($zona=='La Marina') echo 'selected="selected"';?>>La Marina</option> 
                                        <option value="Sant Pol 2000" <?php if ($zona=='Sant Pol 2000') echo 'selected="selected"';?>>Sant Pol 2000</option> 
                                        <option value="Urbapol" <?php if ($zona=='Urbapol') echo 'selected="selected"';?>>Urbapol</option> 
                                        <option value="Jardins St. Pol - El Farell" <?php if ($zona=='Jardins St. Pol - El Farell') echo 'selected="selected"';?>>Jardins St. Pol - El Farell</option>
                                        <option value="Can Pou - Rajoleria" <?php if ($zona=='Can Pou - Rajoleria') echo 'selected="selected"';?>>Can Pou - Rajoleria</option>
                                        <option value="Vistamar" <?php if ($zona=='Vistamar') echo 'selected="selected"';?>>Vistamar</option> 
                                    </select>

                                    <label>Adreça</label>

                                    <?php echo $iM->get_input_text("adreca", $adreca,"form-control", '', '', 'Campo requerido', 1); ?>

                                    <label>Tipus</label>

                                    <select id="tipus" class="form-control" name="tipus" value="<?php echo $tipus;?>"  >
                                        <option value="Casa" <?php if ($tipus =='Casa') echo 'selected="selected"';?>>Casa</option>
                                        <option value="Pis-Apartament" <?php if ($tipus =='Pis-Apartament') echo 'selected="selected"';?>>Pis-Apartament</option>
                                        <option value="Terreny" <?php if ($tipus =='Terreny') echo 'selected="selected"';?>>Terreny</option>
                                        <option value="Local" <?php if ($tipus =='Local') echo 'selected="selected"';?>>Local</option>
                                        <option value="Altres" <?php if ($tipus =='Altres') echo 'selected="selected"';?>>Altres</option>
                                    </select> 

                                    <label>Estat</label>

                                    <select id="estat" class="form-control" name="estat" value="<?php echo $estat;?>"  >
                                        <option value="Nou" <?php if ($estat =='Nou') echo 'selected="selected"';?>>Nou</option>
                                        <option value="Reformat" <?php if ($estat =='Reformat') echo 'selected="selected"';?>>Reformat</option>
                                        <option value="Segona ma" <?php if ($estat =='Segona ma') echo 'selected="selected"';?>>Segona ma;</option>
                                    </select>

                                    
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="mt-3">Certificat energètic</label><br>
                                                <img class="img-fluid img-thumbnail" src="../fotos/certificacio-energetica.png" width="100%" />
                                            </div>
                                            <!--<div class="form-group">
                                                <label class="mt-3">Certificat energètic</label><br>
                                                <img class="img-fluid img-thumbnail" src="../fotos/<?php echo $certificat_foto;?>" width="100%" />
                                            </div>-->
                                            
                                            <div class="form-group">
                                                <label class="mt-3">Descripció Certificat energètic</label><br>
                                                <?php echo $iM->get_input_text("certificat_text", $certificat_text,"form-control", '', '', 'Campo requerido', 1); ?>
                                            </div>

                                            <div class="form-group">
                                                <label class="mt-3">Consum energètic (Kwh/m<sup>2</sup> any)</label><br>
                                                <?php echo $iM->get_input_number("consum", $consum, 'form-control', '', '', 'Campo requerido', 0, false, 'int');?>
                                            </div>
                                            <div class="form-group">
                                                <label class="mt-3">Lletra Consum energètic</label><br>
                                                <?php echo $iM->get_input_text("lletra_consum", $lletra_consum,"form-control", '', '', 'Campo requerido', 1); ?>
                                            </div>
                                                        
                                            <div class="form-group">
                                                <label class="mt-3">Emissions (kgCO<sup>2</sup>/m<sup>2</sup> any)</label><br>
                                                    <?php echo $iM->get_input_number("emissions", $emissions, 'form-control', '', '', 'Campo requerido', 0, false, 'int');?>
                                            </div>
                                                        
                                            <div class="form-group">
                                                <label class="mt-3">Lletra Emissions</label><br>
                                                <?php echo $iM->get_input_text("lletra_emissions", $lletra_emissions,"form-control", '', '', 'Campo requerido', 1); ?>
                                            </div>
                                                        
                                        </div>

                                        <div class="col-md-6">    
                                            

                                            <div class="container">

                                                <label class="mt-3" for="servicios">Serveis</label><br>
                                                <div class="row">
                                                    <div class="col-sm">
                                                        <?php foreach($servicios_pre as $key => $servicio): ?>
                                                            <?php $i++ ?>
                                                            <label><input <?php if(in_array($key, $servicios)) {echo 'checked';} ?> name="servicios[]" type="checkbox" value="<?php echo $key ?>"> <?php echo $servicio ?></label><br>
                                                            <?php if($i === 10): ?>
                                                                </div>
                                                                <div class="col-sm">
                                                            <?php endif; ?>
                                                        <?php endforeach; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            
                                        </div>
                                    </div>
                                </div>
                            
                                <div class="col-md-6">
                                    <div class="textarea-idioma">
                                        <div class="menu">
                                            <ul class="">
                                                <li class="">
                                                    <a id="descripcio" class="selectorIdioma active" href="javascript:;">Català</a>
                                                </li>
                                                <li class="">
                                                    <a id="descripcioes" class="selectorIdioma" href="javascript:;">Castellà</a>
                                                </li>
                                                <li class="">
                                                    <a id="descripcio_en" class="selectorIdioma" href="javascript:;">Anglés</a>
                                                </li>
                                                
                                            </ul>
                                        </div> 
                                        <div class="col-12 textarea-content">
                                            <div id="descripcio" class="form-group textarea-container active">
                                                <?php //echo $iM->get_input_textarea("descripcio", $descripcio   ,"form-control", 'Descripció Catalá', '', '',false,false,true,5); ?>
                                                <?php echo $iM->get_input_textarea_admin("descripcio", $descripcio   ,"form-control editor-base", 'Descripció Català', '', '',false,false,true,5); ?>
                                            </div>
                                            <div id="descripcioes" class="form-group textarea-container">
                                                <?php //echo $iM->get_input_textarea("descripcioes", $descripcioes   ,"form-control", 'Descripció Castellá', '', '',false,false,true,5); ?>
                                                <?php echo $iM->get_input_textarea_admin("descripcioes", $descripcioes   ,"form-control editor-base", 'Descripció Castellà', '', '',false,false,true,5); ?>
                                            </div>
                                            <div id="descripcio_en" class="form-group textarea-container">
                                                <?php //echo $iM->get_input_textarea("descripcio_en", $descripcio_en   ,"form-control", 'Descripció Anglés', '', '',false,false,true,5); ?>
                                                <?php echo $iM->get_input_textarea_admin("descripcio_en", $descripcio_en   ,"form-control editor-base", 'Descripció Anglés', '', '',false,false,true,5); ?>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <label class="mt-3">Metres Quadrats</label>

                                            <?php echo $iM->get_input_number('m2', $m2, 'form-control', '', '', 'Campo requerido', 0, false, 'int');?>

                                            <label class="mt-3">Preu</label>
                                            <?php echo $iM->get_input_number('preu', $preu, 'form-control', '', '', 'Campo requerido',0, false, 'price');?>
                                            <label class="mt-3">Habitacions</label>

                                            <?php echo $iM->get_input_number('hab', $hab, 'form-control', '', '', 'Campo requerido', 0, false, 'int');?>
                                            <label class="mt-3">Banys</label>

                                            <?php echo $iM->get_input_number('banys', $banys, 'form-control', '', '', 'Campo requerido', 0, false, 'int');?>

                                            
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label class="mt-3">Ordre</label>
                                                <?php echo $iM->get_input_number('ordre', $ordre, 'form-control', '', '', 'Campo requerido', 0, false, 'int');?>
                                            </div>
                                        
                                            <div class="form-group">
                                                <label class="mt-3">Venut</label>
                                                <select id="venut" class="form-control" name="venut" value="<?php echo $venut;?>"  >
                                                    <?php echo $venut;?>
                                                    <option value="1" <?php if ($venut =='1') echo 'selected="selected"';?>>SI</option>
                                                    <option value="0" <?php if ($venut=='0') echo 'selected="selected"';?>>NO</option>
                                                </select>
                                            </div>
                                        
                                            <div class="form-group">
                                                <label class="mt-3">Visible</label>
                                                <select id="visible" class="form-control" name="visible" value="<?php echo $visible;?>"  >
                                                    <?php echo $visible;?>
                                                    <option value="1" <?php if ($visible =='1') echo 'selected="selected"';?>>SI</option>
                                                    <option value="0" <?php if ($visible=='0') echo 'selected="selected"';?>>NO</option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label class="mt-3">Destacat</label>
                                                <select id="destacat" class="form-control" name="destacat" value="<?php echo $destacat;?>"  >
                                                    <?php echo $visible;?>
                                                    <option value="1" <?php if ($destacat =='1') echo 'selected="selected"';?>>SI</option>
                                                    <option value="0" <?php if ($destacat=='0') echo 'selected="selected"';?>>NO</option>
                                                </select>
                                            </div>
                                            
                                            
                                        </div>
                                    </div>
                                    <br>
                                    <div class="form-group">
                                        <?php echo '<iframe width="100%" height="270" style="border:0" loading="lazy" allowfullscreen src="https://www.google.com/maps/embed/v1/place?key='.MAPS_KEY.' &q='.$adreca.','.$cp.'">
                                            </iframe>';
                                        ?>
                                    </div>
                                    <br>
                                                                
                                    
                                </div>
                            </div>
                            <?php //NUEVO?>
                            <div class="alert alert-info">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" 
                                        id="enviar_fotocasa" name="enviar_fotocasa" value="1">
                                    <label class="form-check-label" for="enviar_fotocasa">
                                        <strong>Publicar en Fotocasa.es</strong>
                                    </label>
                                </div>
                            </div>
 
                            <?php if (!empty($msg_fotocasa)) echo $msg_fotocasa; ?>
                            <?php //NUEVO_END?>
                            <div class="d-flex justify-content-end">
                                <button class="btn bg-primary text-light">Acceptar</button>
                            </div>
                        </form>
                        
                    </div>
                </div>
                            
                <?php if ($modif) {?>

                    <?php //include_once ('view_imagenes.php');?>
                    <div id="imagenes" name="imagenes"></div>
                <?php }?>
            </div>
        </div>
    </div>
</body>
<script>
    var id_cataleg="<?php echo $id_cataleg;?>";
    $("#imagenes").load('view_imagenes.php?id_cataleg='+id_cataleg);
</script>


</html>
<!--<script>
    var id_cataleg="<?php echo $id_catgaleg;?>";
    var lightbox = GLightbox();
    lightbox.on('open', (target) => {
        console.log('lightbox opened');
    });
    $("#imagenes").load('view-imagenes.php?id_alquiler='+id_cataleg);
</script>
<script>
    var options = {
        swapThreshold: 0.90,
        animation: 250,
        easing: "cubic-bezier(0.45, 0, 0.55, 1)",
        filter: ".exclude",
        onMove: event => {
         return !event.related.classList.contains('exclude');
        },
    };
    var pos_old='';
    var pos_new='';

    events = [
    'onChoose',
    'onStart',
    'onEnd',
    'onAdd',
    'onUpdate',
    'onSort',
    'onRemove',
    'onChange',
    'onUnchoose'
    ].forEach(function (name) {
    options[name] = function (evt) {
        pos_old=evt.oldIndex;
        pos_new=evt.newIndex;
    };
    
    });
    

    // With options:
    $('#glbdrag').sortable(options);
</script>
<script>
    function thisFileUpload() {
        document.getElementById("glbfile").click();
        
    };

    $(".delete").click(function() {
        var imagen = $(this).attr("data-target"); 
        $(this).closest("#galery"+imagen).remove();
        var id_cataleg="<?php echo $id_cataleg;?>";
        var parametros = {
          "eliminar" : 1,  
          "id_cataleg" : id_cataleg,
          "imagen" : imagen
        };

        $.ajax({
                data:  parametros, //datos que se envian a traves de ajax
                url:   'actualiza_imagenes.php', //archivo que recibe la peticion
                type:  'post', //método de envio
                beforeSend: function () {
                        $("#resultado").html("Procesando, espere por favor...");
                },
                success:  function (response) { 
                //una vez que el archivo recibe el request lo procesa y lo devuelve
                        $("#resultado").html(response);
                        //alert(response);
                        $("#imagenes").load('view-imagenes.php?id_cataleg='+id_cataleg);
                }
        });
    
    });
</script>
<script>
    tinymce.init({
        selector: 'textarea.editor-base'
    });
</script>-->
</html>
