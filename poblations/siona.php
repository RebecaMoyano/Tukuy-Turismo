<?php
//cargar el archivo que tiene la informacion de todos los tours
if(file_exists('../tours_data.php')){
    include('../tours_data.php');
    //me aseguro de que el tour existe y es un array 
    if(!isset($tours) || !is_array($tours)){
        exit('la variable $tours no es un array o no está definida.');
    }
    //si hubo éxito 
    $toursData = []; 
    foreach($tours as $tour){
        if(isset($tour['id'])){
            $toursData[$tour['id']] = $tour;
        }
    }
} else{
    exit('Error: no se encontró el archivo de datos de los tours.');
}
//establecer el tour actual 
$tourId = 'siona';
//obtener informacion del tour actual 
$tourActual = $toursData[$tourId] ?? null; 
if(!$tourActual){
    exit('Error: Información del tour no encontrada.');
}
//codigo comun a todos los tours, obtengo los tours relacionados de c/uno
include('../includes/tours_relacionados.php');
//para las cartillas 
$toursPorCara = 3; 
$toursAgrupados = array_chunk($toursRelacionadosData, $toursPorCara); 
// Iniciar sesión si no está iniciada (necesario para la verificación de login en JS)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $tourActual['id'].': Detalles del Tour'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../css/styles.css">
</head>
<body>
<?php include('../includes/navigation.php') ?>
    <header class='header-individual' style='background-image:url(../assets/img/eco-img2.jpg)'>
        <h2>SIONA</h2>
    </header>
    <div class='cont-pueblo'>
        <div class='fondo'>
            <?php include('../includes/botones-detalles.php')?>
            <div class='contenido d-flex justify-content-center align-items-center'>
                <div class="tab-content w-100" id="tourInfoTabsContent">
                    <div class="tab-pane fade show active" id="descripcion-panel" role="tabpanel" aria-labelledby="descripcion-tab-btn">
                        <div id='info-resumen'>
                            <h1 style='font-family:Tinos;'><?php echo $tourActual['nombre'] ?? '';?></h1>
                            <div>
                                <p id='text'><?php echo $tourActual['descripcion'] ?? '';?></p>
                            </div>
                            <section class="promocion-destacada">
                                <div class="container-fluid no-padding"> 
                                    <div class="row g-0"> 
                                        <div class="col-lg-3 col-md-12 promo-early-booking">
                                            <div class="overlay-early-booking">
                                                <div class="content-early-booking d-block">
                                                    <h4 class="tagline-top">DETALLES DEL TOUR</h4>
                                                    <i class="fa-solid fa-leaf" style="color: #299474;"></i>
                                                    <p class="description-early">
                                                        <?php echo $tourActual['resumen']; ?>
                                                    </p>
                                                    <p class="price-from">POR SOLO</p>
                                                    <h3 class="price-value"><?php echo $tourActual['precio']?>€ c/p</h3>
                                                    <div class="form-group d-block mb-3">
                                                        <p>Elija el número de viajeros:</p>
                                                        <div class='d-block botones-aumento-personas'>
                                                            <p class='cantidad-personas'>Viajeros: <span id='cantidad_viajeros'>0</span></p><button class='btn-incrementar m-3 p-1' id='btn_sumar'>+</button><button class='btn-restar m-3 p-1' id='btn_restar'>-</button>
                                                        </div>
                                                        <span id="precio_unitario_tour_oculto" data-precio="<?php echo htmlspecialchars($tourActual['precio']); ?>" style="display:none;"></span>
                                                    </div>
                                                    <a href="#" class='btn-early-booking' id="btn_reservar">RESERVAR</a>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-lg-8 col-md-12 promo-gallery-grid">
                                            <div class="row g-0"> 
                                                <div class="col-md-6 col-sm-6 gallery-item">
                                                    <a href="enlace-hotel-1.php" class="gallery-card">
                                                        <img src="../assets/img/siona-images/img1.jpg" alt="TUI Hotel Sardigame" class='img-fluid'>
                                                    </a>
                                                </div>
                                                <div class="col-md-6 col-sm-6 gallery-item">
                                                    <a href="enlace-hotel-2.php" class="gallery-card">
                                                        <img src="../assets/img/siona-images/img2.webp" alt="TUI Hotel Palm Garden" class='img-fluid'>
                                                    </a>
                                                </div>
                                                <div class="col-md-6 col-sm-6 gallery-item">
                                                    <a href="enlace-hotel-3.php" class="gallery-card">
                                                        <img src="../assets/img/siona-images/img3.jpeg" alt="TUI Hotel Palm Beach" class='img-fluid'>
                                                    </a>
                                                </div>
                                                <div class="col-md-6 col-sm-6 gallery-item">
                                                    <a href="enlace-hotel-4.php" class="gallery-card">
                                                        <img src="../assets/img/siona-images/img4.jpeg" alt="TUI Hotel Sesa Oasis" class='img-fluid'>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </section>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="detalles-panel" role="tabpanel" aria-labelledby="detalles-tab-btn">
                        <section class="detalles-tour-section py-5">
                            <div class="container">
                                <div class="row align-items-center">
                                    <div class="col-12 col-md-6 text-center text-md-start">
                                        <p class="section-subtitle">DETALLES</p>
                                        <h2 class="section-title mb-4">TODO LO QUE NECESITAS SABER</h2>
                                        <p class="section-description mb-4"><?php echo $tourActual['resumen'] ?? '';?></p>
                                    </div>
                                    <div class="col-12 col-md-6 d-flex justify-content-center justify-content-md-end mt-4 mt-md-0">
                                        <div class="image-container">
                                            <img src="../assets/img/landscape.jpg" class="img-fluid rounded" alt="paisaje siona">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </section>
                        <div class='container incluye'>
                            <h2>CANCELACIÓN</h2>
                            <ul class='list-unstyled'>
                                <li><i class="fa-solid fa-leaf"></i> ¡GRATIS! Cancela gratis hasta 15 días antes de la actividad. Si cancelas con menos tiempo, llegas tarde o 
                                    no te presentas, no se ofrecerá ningún REEMBOLSO.
                                </li>
                            </ul>
                            <h2>REEMBOLSO</h2>
                            <ul class='list-unstyled'>
                                <li><i class="fa-solid fa-leaf"></i> Cancela con 15 días de anticipación a la fecha de tu tour y te devolveremos un 50% del importe.</li>
                            </ul>
                            <h2>QUÉ INCLUYE EL TOUR SIONA</h2>
                            <ul class='list-unstyled tour-includes-list'> <li>
                                <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Estancia en el centro comercial Siona de <?php $tourActual['duracion'] ?? '';?> en alojamientos
                            ecológicos dentro de la comunidad Siona, diseñados para el bienestar y la conexión.</li>
                            <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Gastronomía autóctona: Régimen de pensión completa con alimentos orgánicos cultivados por la comunidad,
                                destacando la yuca y el pescado de río.
                            </li>
                            <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Experiencias de aprendizaje y sanación: 
                                <ul class='list-unstyled sublist'>
                                    <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Encuentros con los taitas (chamanes): Sesiones de diálogo y aprendizaje sobre la cosmovisón Siona y 
                                        el uso de plantas maestras (opcional y  con previo acuerdo).
                                    </li>
                                    <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Recorridos botánicos: Caminatas guiadas por la selva para identificar y aprender sobre las propiedades
                                        medicinales de las plantas.
                                    </li>
                                    <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Preparación de la yuca: Participación en el proceso de elaboración de la chicha de la yuca, bebidas
                                        tradicional Siona.
                                    </li>
                                    <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Observación de aves y fauna: Expediciones tempranas en canoa para avistar la rica biodiversidad
                                        del río Putumayo.
                                    </li>
                                    <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Pintura facial Siona: Aprende los diseños tradicionales y sus significados, aplicándolos con tintes 
                                        naturales.
                                    </li>
                                </ul>
                            </li>
                            <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Transporte fluvial: traslado en lancha desde Puerto Asís o punto de encuentro cercana hasta la comunidad
                                de Siona.
                            </li>
                            <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Guías especializados: Acompañamiento de guías Siona y un facilitador cultural, garantizando una experiencia
                                auténtica y respetuosa.
                            </li>
                            <li><i class="fa-solid fa-leaf" style="color: #27aa78;"></i> Fondo para la preservación cultural: Una parte de tu inversión apoya directamente las iniciativas de preservación
                                de la lengua las tradiciones Siona. 
                            </li>
                            </ul>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="consejos-panel" role="tabpanel" aria-labelledby="consejos-tab-btn">
                        <section class="consejos-seccion"> 
                            <?php include('../includes/consejos.php') ?>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </div> 
    <div class='another-tours'>
        <h2>Otros tours que podrían interesarte</h2>
            <div id="carouselRelatedTours" class="carousel slide" data-bs-ride="carousel" data-bs-interval="3000">
                <div class="carousel-inner">
                    <?php if (!empty($toursAgrupados)): ?>
                        <?php $first = true; ?>
                        <?php foreach ($toursAgrupados as $grupo): ?>
                            <div class="carousel-item <?php if ($first) echo 'active'; ?>">
                                <div class='row row-cols-1 row-cols-md-3 g-4 justify-content-center'>
                                    <?php foreach($grupo as $relatedTour): ?>
                                        <div class="col" style='width:25%;'>
                                            <div class='card h-100 shadow-sm'>
                                                <a href="<?php echo $relatedTour['archivo']; ?>">
                                                    <img src="../assets/img/<?php echo htmlspecialchars($relatedTour['imagen_principal']);?>" class="card-img-top" alt="<?php echo $relatedTour['nombre']; ?>">
                                                </a>
                                                <div class='card-body d-flex flex-column'>
                                                    <h5 class='card-title'><?php echo htmlspecialchars($relatedTour['nombre'] ?? $relatedTour['nombre']); ?></h5>
                                                    <p class='card-text flex-grow-1'>
                                                            <?php 
                                                            $description = $relatedTour['descripcion'] ?? $relatedTour['descripcion'] ?? ''; 
                                                            echo htmlspecialchars(substr($description, 0, 100)); 
                                                            if (strlen($description) > 100) {
                                                                echo '...';
                                                            }
                                                            ?>
                                                    </p>
                                                    <div class='botones d-flex justify-content-around align-items-center'>
                                                        <p><?php echo $relatedTour['duracion']; ?></p>
                                                        <h4><?php echo $relatedTour['precio']; ?></h4>
                                                        <a href="<?php echo $relatedTour['archivo']; ?>"><i class="fa-solid fa-calculator"></i></a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php $first = false; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="carousel-item active"> 
                            <p class="text-center w-100">No hay otros tours relacionados.</p>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if (!empty($toursAgrupados) && count($toursAgrupados) > 1):  ?>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselRelatedTours" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselRelatedTours" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                <?php endif; ?>
            </div>
    </div>
    <?php include('../includes/footer.php') ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const tourIdFromPHP = <?php echo json_encode($tourActual['id'] ?? ''); ?>;
        const tourNameFromPHP = <?php echo json_encode($tourActual['name'] ?? $tourActual['nombre'] ?? ''); ?>; // Usar 'nombre' si 'name' no está
        const isUserLoggedInFromPHP = <?php echo isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario']) ? 'true' : 'false'; ?>;
    </script>
    <script src="../js/script.js"></script>  
</body>
</html> 