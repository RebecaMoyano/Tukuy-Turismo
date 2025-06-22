<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <title>Sobre nosotros</title>
</head>
<body>
<?php 
include('includes/navigation.php');
?> 
<header class='contenedor-about'>
    <div class="d-flex justify-content-center align-items-center">
        <h2 class='text-center' style='font-family:Tinos'>QUÉ ES <span>TUKUY TOURS<span></h2>
    </div>
</header>
<div class='contenedor-vision container d-flex justify-content-center align-items-center'>
    <div class='row mb-3'>
        <div class='col-md-6 d-flex justify-content-center align-items-center'>
            <img src='assets/img/indigena-niña.png' alt='Niña indígena observando la naturaleza' class='img-fluid rounded' style='max-width: 350px; height: auto;'>
        </div>
        <div class='col-md-6 mt-4' >
            <h2 class='text-center' style='font-family:Tinos;'>NUESTRA <span>VISIÓN</span></h2>
            <p class='text-center'>
                En Tukuy Tours, visualizamos un futuro donde la exploración de la majestuosa Amazonía se realiza de manera intrínsecamente
                 ligada a su conservación y al respeto profundo por las comunidades que la habitan. Nuestra visión para la reserva de tours 
                 ecológicos va más allá de la simple transacción; aspiramos a ser el catalizador de experiencias transformadoras
                que inspiren una conexión duradera entre los viajeros y este invaluable ecosistema.
            </p>
        </div>
    </div>
</div>
<div class='contenedor-practicas container d-flex justify-content-center align-items-center'>
    <div class='row mb-3'>
        <div class='col-md-6 mt-4' >
            <h2 class='text-center'>NUESTRAS 
                <span style='color:green;'>PRÁCTICAS</span></h2>
            <p class='text-center'>
                En Tukuy Tours, el respeto por la Amazonía y sus habitantes guía cada una de nuestras acciones. Nos comprometemos 
                firmemente a implementar prácticas que minimizan nuestro impacto ambiental y contribuyen positivamente al bienestar 
                de las comunidades locales. Desde la planificación de nuestros tours hasta la ejecución de cada experiencia, 
                priorizamos la sostenibilidad, asegurando que nuestra pasión por la exploración se alinee con la preservación de 
                este invaluable ecosistema y el respeto por su gente. Elegir Tukuy Tours es elegir un viaje consciente, donde la aventura
                se encuentra con la responsabilidad.
            </p>
        </div>
        <div class='col-md-6 d-flex justify-content-center align-items-center'>
            <img src='assets/img/indigena-mujer.png' alt='Niña indígena observando la naturaleza' class='img-fluid rounded'>
        </div>
    </div>
</div>
<?php 
include('includes/footer.php') 
?>   
</body>
</html>

