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
    <title>Contacto</title>
    <style>
        h3, p{
            padding-top:1.2em;
            text-align:center;
            color:white;
        }
        header{
            background-image:url('../assets/img/about-us-header.jpeg');
            background-size: cover;
            background-position:center center;
            background-repeat:no-repeat;
            justify-content: center; 
            align-items: center;
        }
        header div{
            height:100%;
        }
        header h2{
            z-index:1000;
            color:white;
            font-size:75px;
            font-family:'Bebas Neue';
            letter-spacing:2px;
            text-shadow: 3px 4px 2px #006FDF;
        }
        .secciones{
            background-image:url('assets/img/fondo-hojas.webp');
            height:40vh;
            width: 100%;
            background-size: cover;
            background-position:center center;
            opacity:.8;
            background-repeat:no-repeat;
        }
        .bloques{
            padding:30px;
        }
        .bloques div div{
            margin-left:20px;
        }
        .bloques div div{
            background-color:#639A4F;
            border-radius:10px;
        }
        .contact-info-text h2, p{
            color:black;
        }
        .btn{
            color:white;
        }
        .mt-4 i{
            color:#4A805C;
        }
        iframe{
            width: 90%;
        }
        #title{
            font-size:4em;
            font-family:'Tinos';
        }
    </style>
</head>
<body>
<?php 
include('includes/navigation.php');
?>
    <header style="height:40vh;">
        <p id='title' style='color:white; font-weight:bold;'>CONTÁCTANOS</p>
    </header>
    <section class="contact-section mt-5">
        <div class="container">
            <div class="row g-4 justify-content-center align-items-center"> 
                <div class="col-md-6">
                    <div class="contact-form-container">
                        <h2 class="mb-4 text-center" style='font-family:Tinos'>ENVÍANOS UN MENSAJE</h2>
                        <form action="process_contact_form.php" method="POST">
                            <div class="mb-3">
                                <label for="name" class="form-label">Nombre Completo:</label>
                                <input type="text" class="form-control" id="name" name="name" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Correo Electrónico:</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="phone" class="form-label">Teléfono (Opcional):</label>
                                <input type="tel" class="form-control" id="phone" name="phone">
                            </div>
                            <div class="mb-3">
                                <label for="subject" class="form-label">Asunto:</label>
                                <input type="text" class="form-control" id="subject" name="subject" required>
                            </div>
                            <div class="mb-3">
                                <label for="message" class="form-label">Tu Mensaje:</label>
                                <textarea class="form-control" id="message" name="message" rows="5" required></textarea>
                            </div>
                            <div class="d-grid"> 
                                <button type="submit" class="btn btn-lg" style='background-color:#46AA68; color:white;'>ENVIAR</button>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="contact-info-text">
                        <p>¿Tienes preguntas sobre nuestros destinos, necesitas un tour personalizado o simplemente quieres charlar sobre las maravillas de nuestro mundo?</p>
                        <p>Nuestro equipo de expertos está aquí para escucharte y ayudarte a planificar la experiencia perfecta.</p>
                        <p>No dudes en ponerte en contacto. ¡Tu viaje de ensueño te espera!</p>
                        <div class="mt-4">
                            <p class="mb-1"><i class="fas fa-envelope"></i> tukuy_tours@gmail.com</p>
                            <p><i class="fas fa-phone"></i> +XX XXX XXX XXX</p>
                            <p><i class="fas fa-map-marker-alt"></i> Mi dirección, Madrid</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <section class='d-flex justify-content-center align-items-center mb-5' style='margin-top:4em;'>
        <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3989.8787920150917!2d-78.47167628526315!3d-0.18731779999999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x91d59bc6a9d7b41b%3A0x7d2f8d3d9d3d9d3d!2sKayap%C3%B3+Eco+Lodge!5e0!3m2!1ses!2sec!4v1678888888888!5m2!1ses!2sec" 
        height="300" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
    </section>
    <?php include('includes/footer.php')?>
</body>
</html>