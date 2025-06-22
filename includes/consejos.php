<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        .imagen-contenedor img{
            box-shadow:0;
        }
        .col-12{
            padding:60px;
        }
        .col-md-7{
            width: 700px;
        }
        .col-md-5{
            width: 300px;
        }
        .row{
            margin:0;
            padding:0;    
        }
    </style>
</head>
<body>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class="col-12 col-md-7"> 
            <div class="content-block p-4"> 
                <h3 class="mb-3">DOCUMENTOS Y VISAS</h3>
                <?php
                    if (isset($tourActual['consejos']['documentos_y_visas']) && is_array($tourActual['consejos']['documentos_y_visas'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['documentos_y_visas'] as $item) {
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre documentos y visas para este tour.</p>';
                    }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
            <div class="image-contenedor">
                <img src="<?php echo $tourActual['images']['documentos_y_visas'][0]; ?>" class="img-fluid" 
                alt="Imagen de pasaporte">
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class='col-12 col-md-5 d-flex justify-content-center align-items-center'>
            <div class='image-contenedor'>
                <img src='<?php echo $tourActual['images']['finanzas_y_moneda'][0];?>' class='img-fluid' alt=''>
            </div>
        </div>
        <div class='col-12 col-md-7'>
            <div class='content-block p-4 p-md-5'>
                <h3 class="mb-3">FINANZAS Y MONEDA</h3>
                <?php
                    if (isset($tourActual['consejos']['finanzas_y_moneda']) && is_array($tourActual['consejos']['finanzas_y_moneda'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['finanzas_y_moneda'] as $item){
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre finanzas y moneda.</p>';
                    }
                ?>
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class="col-12 col-md-7"> 
            <div class="content-block p-4 p-md-5"> 
                <h3 class="mb-3">LENGUAJE</h3>
                <?php
                    if (isset($tourActual['consejos']['lenguaje']) && is_array($tourActual['consejos']['lenguaje'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['documentos_y_visas'] as $item) {
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre documentos y visas para este tour.</p>';
                    }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
            <div class="image-contenedor">
                <img src="<?php echo $tourActual['images']['lenguaje'][0]; ?>" class="img-fluid justify-content-center
                align-items-center" 
                alt="Imagen de pasaporte">
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class='col-12 col-md-5 d-flex justify-content-center align-items-center'>
            <div class='image-contenedor'>
                <img src='<?php echo $tourActual['images']['cultura_y_costumbres'][0];?>' class='img-fluid' alt=''>
            </div>
        </div>
        <div class='col-12 col-md-7'>
            <div class='content-block p-4 p-md-5'>
                <h3 class="mb-3">CULTURA Y COSTUMBRE</h3>
                <?php
                    if (isset($tourActual['consejos']['finanzas_y_moneda']) && is_array($tourActual['consejos']['cultura_y_costumbres'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['finanzas_y_moneda'] as $item){
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre finanzas y moneda.</p>';
                    }
                ?>
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class="col-12 col-md-7"> 
            <div class="content-block p-4 p-md-5"> 
                <h3 class="mb-3">RELIGIÓN</h3>
                <?php
                    if (isset($tourActual['consejos']['religion']) && is_array($tourActual['consejos']['religion'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['documentos_y_visas'] as $item) {
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre documentos y visas para este tour.</p>';
                    }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
            <div class="image-contenedor">
                <img src="<?php echo $tourActual['images']['religion'][0]; ?>" class="img-fluid" 
                alt="Imagen de pasaporte">
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class='col-12 col-md-5 d-flex justify-content-center align-items-center'>
            <div class='image-contenedor'>
                <img src='<?php echo $tourActual['images']['electricidad'][0];?>' class='img-fluid' alt=''>
            </div>
        </div>
        <div class='col-12 col-md-7'>
            <div class='content-block p-4 p-md-5'>
                <h3 class="mb-3">ELECTRICIDAD</h3>
                <?php
                    if (isset($tourActual['consejos']['electricidad']) && is_array($tourActual['consejos']['electricidad'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['finanzas_y_moneda'] as $item){
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre finanzas y moneda.</p>';
                    }
                ?>
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class="col-12 col-md-7"> 
            <div class="content-block p-4 p-md-5"> 
                <h3 class="mb-3">INTERNET</h3>
                <?php
                    if (isset($tourActual['consejos']['internet']) && is_array($tourActual['consejos']['internet'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['documentos_y_visas'] as $item) {
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre documentos y visas para este tour.</p>';
                    }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
            <div class="image-contenedor">
                <img src="<?php echo $tourActual['images']['internet'][0]; ?>" class="img-fluid" 
                alt="Imagen de pasaporte">
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class='col-12 col-md-5 d-flex justify-content-center align-items-center'>
            <div class='image-contenedor'>
                <img src='<?php echo $tourActual['images']['seguro_de_viaje'][0];?>' class='img-fluid' alt=''>
            </div>
        </div>
        <div class='col-12 col-md-7'>
            <div class='content-block p-4 p-md-5'>
                <h3 class="mb-3">SEGURO DE VIAJE</h3>
                <?php
                    if (isset($tourActual['consejos']['seguro_de_viaje']) && is_array($tourActual['consejos']['seguro_de_viaje'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['finanzas_y_moneda'] as $item){
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre finanzas y moneda.</p>';
                    }
                ?>
            </div>
        </div>
    </div>
    <div class='row justify-content-center align-items-center row-consejos w-100'>
        <div class="col-12 col-md-7"> 
            <div class="content-block p-4 p-md-5"> 
                <h3 class="mb-3">CLIMA</h3>
                <?php
                    if (isset($tourActual['consejos']['documentos_y_visas']) && is_array($tourActual['consejos']['clima_y_alimentacion'])) {
                        echo '<ul class="list-unstyled required-docs-list">';
                        foreach ($tourActual['consejos']['documentos_y_visas'] as $item) {
                            $text = htmlspecialchars($item, ENT_QUOTES, 'UTF-8');
                            $icon = "fa-solid fa-circle-check";
                            $color = "#27aa78";
                            echo '<li>';
                            echo '<i class="' . $icon . '" style="color: ' . $color . '; margin-right: 10px;"></i> ';
                            echo $text;
                            echo '</li>';
                        }
                        echo '</ul>';
                    } else {
                        echo '<p>No hay información detallada sobre documentos y visas para este tour.</p>';
                    }
                ?>
            </div>
        </div>
        <div class="col-12 col-md-5 d-flex justify-content-center align-items-center">
            <div class="image-contenedor">
                <img src="<?php echo $tourActual['images']['clima_y_alimentacion'][0]; ?>" class="img-fluid" 
                alt="Imagen de pasaporte">
            </div>
        </div>
    </div>
    <script src='../js/script.js'></script>
</body>
</html>