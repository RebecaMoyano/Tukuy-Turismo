<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/styles.css">
    <title>Forgot password</title>
    <style>
       .forgot-pass{
        height:100;
       }
       .bloque-img, .bloque-form{
        width: 50%;
       }
       .bloque-img img{
        opacity:0.9;
       }
       .bloque-form{
        display:flex;
        justify-content:center;
        align-items:center;
       }
       .descrip{
        font-size:1em;
       }
       .bloque-form .title{
        font-size:2em;
        font-weight:600;
       }
       .btn-volver{
        background-color: #0052A5;
       }
       .btn-enviar{
        background-color: #157F17;
       }
       .btn-enviar, .btn-volver{
        color:white;
        padding:8px;
        border-radius:5px;
        margin-left:5px;
        width: 20%;
        text-align:center;
        text-decoration:none;
        border:none;
        margin-top:1em;
       }
         @media (max-width: 768px) {
          .bloque-img, .bloque-form{
                width: 100%;
                height: 100vh;
          }
          .bloque-img{
                display:none;
          }
          .bloque-form{
                padding:0px;
                background-color:#E0EEE0;
          }
         }
    </style>
</head>
<body>
<?php
include('includes/navigation.php');
echo "<div class='container-fluid w-100 p-0'>";
    echo "<div class='container-fluid forgot-pass d-flex flex-row justify-content-center align-items-center'>";
        echo "<div class='bloque-img'>
            <img src='assets/img/forgot-pass-img.jpg' alt='forgot-password-img' class='img-fluid'>
            </div>";
        echo "<div class='bloque-form'>
            <form class='form-login' action='validation-forgot-pass.php' method='POST'>
                <p class='title'>Recuperar contraseña</p>
                <p class='descrip'>¿Has olvidado tu contraseña? ¡No te preocupes! Ingresa tu dirección de correo electrónico a continuación y te enviaremos
                 un enlace para que puedas restablecerla y volver a acceder a tu cuenta de Tukuy Tours.</p>
                <div class='mb-3'>
                    <label class='form-label'> Correo: </label>";
                    echo "<input type='email' name='email' class='form-control' placeholder='Ingrese su correo electrónico:' required>";
                    echo "<div id='emailHelp' class='form-text'>Nunca compartiremos tu información con nadie más.</div>";
                    if (isset($_GET['message'])){
                        $message = htmlspecialchars($_GET['message']);
                        echo "<div class='container mt-3'><div class='alert alert-warning' role='alert'>" . $message . "</div></div>";
                    }
                    echo "<div class='botones d-flex justify-content-center align-items-center mt-2'>
                        <input type='submit' name='btn-send' class='btn-enviar'>
                        <a href='login.php?action=login' class='btn-volver'>Volver</a>
                    </div>";
                echo "</div>";
            echo "</form>";
        echo "</div>";
    echo "</div>";
echo "</div>";
?>
</body>
</html>