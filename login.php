<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login-Register</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        .snd-cont{
            width: 100%;
            height:100%;
        }
        .bloque-img, .bloque-form{
            padding-top:20px;
            width: 50%;
            height: 100%;
        }
        .bloque-form{
            background-color: #D3E3D0;
            display: flex; 
            flex-direction: column; 
            align-items: center; 
            padding:50px;
            height:100vh;
        }
        .cont-message {
            width: 100%; 
            margin-bottom: 15px; 
        }
        .bloque-img{
            background-image: url('assets/img/login-imagen.jpg');
            background-size: cover;
            height:105vh;
        }
        .row{
            text-align: center;
        }
        .btn-volver{
            background-color:#0052A5;
        }
        .btn-volver, .btn-enviar{
            color: white;
            border: none;
            padding: 10px 13px;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            display: inline-block;
            font-size: 16px;
            margin: 4px 2px;
            cursor: pointer;
            width: 20%;
        }
        .btn-enviar{
            background-color: #157F17;
        }
        .fa-brands{
            font-size: 30px;
            margin: 10px;
        }
        footer{
            margin-top:0;
        }
        @media (max-width: 576px){
            .bloque-img{
                display:none;
                background-image: url('assets/img/login-imagen.jpg');
                background-size: cover;
            }
            .bloque-form{
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <?php

    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }

    if (isset($_SESSION['id_usuario']) && !empty($_SESSION['id_usuario'])){
        $redirect_to_on_login = isset($_GET['redirect_to']) ? htmlspecialchars($_GET['redirect_to']) : 'index.php';        
        $final_redirect_url = $redirect_to_on_login;
        if (isset($_GET['tour_id']) && !empty($_GET['tour_id'])) {
            $final_redirect_url .= (strpos($final_redirect_url, '?') === false ? '?' : '&') . 'tour_id=' . urlencode($_GET['tour_id']);
        }
        if (isset($_GET['fecha_reserva']) && !empty($_GET['fecha_reserva'])) {
            $final_redirect_url .= '&fecha_reserva=' . urlencode($_GET['fecha_reserva']);
        }
        if (isset($_GET['hora_reserva']) && !empty($_GET['hora_reserva'])) {
            $final_redirect_url .= '&hora_reserva=' . urlencode($_GET['hora_reserva']);
        }
        if (isset($_GET['num_personas']) && !empty($_GET['num_personas'])) {
            $final_redirect_url .= '&num_personas=' . urlencode($_GET['num_personas']);
        }
        header("Location: " . $final_redirect_url);
        exit();
    }

    $message_from_url = $_GET['message'] ?? '';
    $display_text = ''; 

    $action = isset($_GET['action']) ? $_GET['action'] : 'login';

    $redirect_to = isset($_GET['redirect_to']) ? $_GET['redirect_to'] : 'index.php';

    // Manejo de mensajes
    if (!empty($message_from_url)) {
        switch ($message_from_url) {
            case 'login_required':
            case 'necesita_login':
                $display_text = 'Debes iniciar sesión para hacer la reserva.';
                break;
            case 'sesion_expirada':
                $display_text = 'Tu sesión ha expirado. Por favor, inicia sesión nuevamente.';
                break;
            case 'sesion_maxima_alcanzada':
                $display_text = 'Has alcanzado el tiempo máximo de sesión. Por favor, inicia sesión nuevamente.';
                break;
            case 'error_credenciales':
                $display_text = 'Credenciales incorrectas. Por favor, verifica tu email y contraseña.';
                break;
            case 'registro_exitoso':
                $display_text = '¡Registro exitoso! Por favor, inicia sesión.';
                $action = 'login'; 
                break;
            case 'error_registro':
                $display_text = 'Error al registrar el usuario. Por favor, intenta de nuevo.';
                $action = 'register'; 
                break;
            case 'db_connection_error':
                $display_text = 'No se pudo conectar con la base de datos. Por favor, inténtalo más tarde.';
                break;
            case 'internal_error':
                $display_text = 'Ocurrió un error interno. Por favor, inténtalo de nuevo.';
                break;
            case 'email_existente':
                $display_text = 'Este email ya está registrado. Por favor, usa otro o inicia sesión.';
                $action = 'register'; 
                break;
            case 'passwords_no_coinciden':
                $display_text = 'Las contraseñas no coinciden. Por favor, inténtalo de nuevo.';
                $action = 'register';
                break;
            default: 
                $display_text = '';
                break;
        }
    }
 
    ?>

    <?php include "includes/navigation.php"; ?>

    <div class="container-fluid w-100 p-0">
        <div class="snd-cont d-flex flex-wrap">
            <div class="bloque-img"></div>
            <div class="bloque-form">
                <?php if ($action == "login") { ?>
                    <form class="form-login" action="validacion-login.php" method="POST">
                        <h1 style='font-family:Tinos;' class='mb-3'>INICIAR SESIÓN</h1>
                        <p>¡Bienvenido/a, explorador/a! Inicia sesión para <strong>reservar</strong> tu próxima aventura inolvidable por la Amazonía. ¡Tus experiencias únicas te esperan!</p>

                        <div class="mb-3">
                            <label for="exampleInputEmail1" class="form-label">Correo electrónico:</label>
                            <input type="email" name="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="Ingrese su correo electrónico:">
                            <div id="emailHelp" class="form-text">No compartiremos tus datos con nadie más.</div>
                        </div>
                        <div class="mb-3">
                            <label for="exampleInputPassword1" class="form-label">Contraseña:</label>
                            <input type="password" name="pass" class="form-control" id="exampleInputPassword1" placeholder="Ingrese su contraseña:"
                            pattern="^(?=(?:.*\d){3})(?=(?:.*[A-Za-z]){5}).*$" title="Formato permitido: La contraseña debe contener al menos
                            8 caracteres, incluyendo 3 números y 5 letras (mayúsculas o minúsculas)" maxlength="8" minlength="8" required>
                        </div>
                        <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>">

                        <div class="container">
                            <div class="row">
                                <div class="col mb-3">
                                    <p>¿No tienes cuenta? <a href='login.php?action=register&redirect_to=<?php echo urlencode($redirect_to); ?>'>Crea una aquí</a></p>
                                    <a href='/forgot-password.php'>Recuperar contraseña</a>
                                </div>
                            </div>
                        </div>
                        <?php
                            if (!empty($display_text)) {
                                echo '<div class="cont-message mt-2"><p class="alert alert-danger" role="alert">'. htmlspecialchars($display_text) .'</p></div>';
                            }
                        ?>
                        <div class="container d-flex justify-content-center">
                            <input type="submit" name="btn-login" class="btn-enviar m-2" value="Enviar">
                            <a href="index.php" class="m-2 btn-volver">Volver</a>
                        </div>
                    </form>
                <?php } else {  ?>
                    <form class="form-login register" action="validacion-register.php" method="POST">
                        <h1 style='font-family:Tinos;' class='mb-3'>CREAR CUENTA NUEVA</h1>
                        <p>¡Únete a la aventura! Regístrate y desbloquea un mundo de experiencias inexploradas en la Amazonía.</p>
                        <div class='d-flex'>
                            <div class="mb-3" style='width:45%; margin-right:25px;'>
                                <label>Nombre:</label>
                                <input type="text" name="reg_nombre" class="form-control" placeholder="Ingrese su nombre:" required>
                            </div>
                            <div class="mb-3" style='width:50%;'>
                                <label>Apellido:</label>
                                <input type="text" name="reg_apellido" class="form-control" placeholder="Ingrese su apellido:" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Teléfono:</label>
                            <input type="text" name="reg_telefono" class="form-control" placeholder="Ingrese un número de teléfono:" pattern="[0-9]{9}"
                                    title="Longitud permitida: 9 caracteres. Solo caracteres numéricos.">
                        </div>
                        <div class="mb-3">
                            <label for="reg_email" class="form-label">Correo:</label>
                            <input type="email" name="reg_email" class="form-control" id="reg_email" aria-describedby="emailHelp" placeholder="Ingrese su correo electrónico:">
                        </div>
                        <div class="mb-3">
                            <label for="reg_pass" class="form-label">Contraseña:</label>
                            <input type="password" name="reg_pass" class="form-control" id="reg_pass" placeholder="Ingrese su contraseña:"
                            pattern="^(?=(?:.*\d){3})(?=(?:.*[A-Za-z]){5}).*$" title="Formato permitido: La contraseña debe contener al menos
                            8 caracteres, incluyendo 3 números y 5 letras (mayúsculas o minúsculas)" maxlength="8" minlength="8" required>
                        </div>
                        <div class="mb-3">
                            <label for="reg_pass_confirm" class="form-label">Confirmar Contraseña:</label>
                            <input type="password" name="reg_pass_confirm" class="form-control" id="reg_pass_confirm" placeholder="Confirme su contraseña:" required>
                        </div>

                        <input type="hidden" name="redirect_to" value="<?php echo htmlspecialchars($redirect_to); ?>">
                        <?php
                            if (!empty($display_text)) {
                                echo '<div class="cont-message"><p class="alert alert-danger" role="alert">'. htmlspecialchars($display_text) .'</p></div>';
                            }
                        ?>
                        <div class="container d-flex justify-content-center">
                            <input type="submit" name="btn-register" class="btn-enviar m-2" value="Registrar">
                            <a href="index.php" class="m-2 btn-volver">Volver</a>
                        </div>
                        <div class='d-flex justify-content-center align-items-center'>
                            <p>¿Ya tienes cuenta? <a href='login.php?action=login&redirect_to=<?php echo urlencode($redirect_to); ?>'>Inicia sesión aquí</a></p>
                        </div>
                    </form>
                <?php } ?>
            </div> 
        </div> 
    </div> <?php include "includes/footer.php"; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
</body>
</html>