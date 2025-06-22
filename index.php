<?php 
include('sesion.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <title>Home</title>
</head>
<body>
    <?php 
    require_once('conexion.php');
    include('includes/navigation.php');
    ?>
    <header class="header-individual" style="background-position:center top; background-image:url(https://inaturalist-open-data.s3.amazonaws.com/photos/124587680/large.jpg);
    background-size:cover;">
          <h2 class="text-center">TUKUY <span style='color:#10A9E0;'>TOURS</span></h2>
    </header>
    <div class="container text-center grid-imagenes-fauna">
        <div class="row">
            <div class="col-12 col-md-4">
            <img src="https://s3.animalia.bio/animals/photos/full/original/scarlet-macaws-1.webp" class="img-fluid" alt="Guacamayo">
            </div>
            <div class="col-12 col-md-4">
            <img src="https://mxc.com.mx/wp-content/uploads/2024/08/mariposa.jpg" class="img-fluid" alt="Imagen 2">
            </div>
            <div class="col-12 col-md-4">
            <img src="https://www.elblogdelatabla.com/wp-content/uploads/2018/01/victoria-regia-amazonica-William-Sharp-2.jpg" class="img-fluid" alt="Imagen 3">
            </div>
        </div>
        <div class="row">
            <div class="col-12 col-md-4">
            <img src="https://www.colchesterzoologicalsociety.com/wp-content/uploads/2024/09/Green-Iguana.jpg" class="img-fluid" alt="Imagen 4">
            </div>
            <div class="col-12 col-md-4">
            <img src="https://media.airedesantafe.com.ar/p/cc95d94d50b141fa450aa5939d250a79/adjuntos/268/imagenes/003/675/0003675588/1200x675/smart/que-significa-que-un-colibri-visite-tu-casa.png" class="img-fluid" alt="Imagen 5">
            </div>
            <div class="col-12 col-md-4">
            <img src="https://cdn.download.ams.birds.cornell.edu/api/v1/asset/115347711/1200" class="img-fluid" alt="Imagen 6">
            </div>
        </div>
    </div>
    <div class="cont-offer">
      <div class="cont-offer-text">
        <h2 style='font-family:Tinos;'>¿POR QUÉ DEBERÍAS ELEGIRNOS?</h2>
        <div>
            <p class="cont-offer-descrip">Elige Tukuy Tours para una aventura amazónica auténtica y responsable. Experimenta la diferencia.</p>
        </div>
      </div>
      <div class="container-fluid row justify-content-center align-items-center">
        <div class="col-12 col-md-6 col-lg-3">
          <i class="fa-solid fa-phone"></i>
          <p class="title">Atención personalizada</p>
          <p class="subtitle">Guías locales expertos cercanos</p>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
          <i class="fa-solid fa-tree"></i>
          <p class="title">Turismo responsable</p>
          <p class="subtitle">Impacto mínimo, mayor apoyo local</p>
        </div>
        <div class="col-12 col-md-6 col-lg-3">
        <i class="fa-solid fa-user-tie"></i>
          <p class="title">Guías expertos</p>
          <p class="subtitle">Flora, fauna e historia en profundidad</p>
        </div>
        <div class="12 col-md-6 col-lg-3">
          <i class="fa-solid fa-fire"></i>
          <p class="title">Aventuras auténticas</p>
          <p class="subtitle">Conexión genuina con la naturaleza</p>
        </div>
      </div>
    </div>
    
    <?php 
    include('includes/catalog.php');
    include('includes/footer.php');
    ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>