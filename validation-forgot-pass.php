<?php
if (isset($_POST['btn-send'])) {
    $email = $_POST['email'];
    $message = "";
    include('conexion.php');
    $conexion = mysqli_connect($servidor, $usuario, $password, $bbdd);
    mysqli_query($conexion, "SET NAMES 'UTF8'"); 

    if (mysqli_select_db($conexion, $bbdd)) {
        $stmt_user = mysqli_prepare($conexion, "SELECT nombre, id_usuario FROM usuarios WHERE email = ?");
        if ($stmt_user) {
            mysqli_stmt_bind_param($stmt_user, "s", $email); 
            mysqli_stmt_execute($stmt_user);
            $resultado = mysqli_stmt_get_result($stmt_user);

            if (mysqli_num_rows($resultado) == 1){
                $fila = mysqli_fetch_array($resultado);
                $nombre_usuario = $fila['nombre'];
                $id_usuario = $fila['id_usuario'];

                $token = bin2hex(random_bytes(32));
                $expiration_date = date("Y-m-d H:i:s", time() + (60 * 60)); 

                $stmt_insert_token = mysqli_prepare($conexion, "INSERT INTO password_resets (id_usuario, token, fecha_expiracion, email) VALUES (?, ?, ?, ?)");
                if ($stmt_insert_token) {
                    mysqli_stmt_bind_param($stmt_insert_token, "isss", $id_usuario, $token, $expiration_date, $email);

                    if (mysqli_stmt_execute($stmt_insert_token)) {
                        $link_replace = "<a href='http://localhost/new_password.php?token=" . $token . "'>Haz clic aquí para restablecer tu contraseña</a>";
                        $asunto = "Restablecer Contraseña - Tukuy Tours"; 
                        $destino = $email; 
                        $cuerpo = '
                        <html>
                            <head>
                                <title>Restablecer Contraseña</title>
                            </head>
                            <body>
                                <h1>Hola ' . htmlspecialchars($nombre_usuario) . ',</h1>
                                <p>Hemos recibido una solicitud para restablecer la contraseña de tu cuenta en Tukuy Tours.</p>
                                <p>Para restablecer tu contraseña, haz clic en el siguiente enlace:</p>
                                <p>' . $link_replace . '</p>
                                <p>Este enlace expirará en 1 hora por motivos de seguridad.</p>
                                <p>Si no solicitaste este restablecimiento, por favor ignora este correo.</p>
                                <p>Saludos cordiales,<br>El equipo de Tukuy Tours</p>
                            </body>
                        </html>
                        ';
                        $headers = "MIME-Version: 1.0\r\n";
                        $headers .= "Content-type: text/html; charset=utf-8\r\n";
                        $headers .= "From: Tukuy Tours <noreply@tukuystours.com>\r\n"; 
                        $headers .= "Return-path: noreply@tukuystours.com\r\n"; 

                        if (mail($destino, $asunto, $cuerpo, $headers)) {
                            $message = "Se ha enviado un correo electrónico con las instrucciones para restablecer tu contraseña.";
                        } else {
                            $message = "Hubo un error al enviar el correo de restablecimiento. Por favor, inténtalo de nuevo más tarde.";
                            error_log("Error al enviar correo: " . error_get_last()['message']); 
                        }
                    } else {
                        $message = "Error al guardar el token de restablecimiento. Por favor, inténtalo de nuevo más tarde.";
                        error_log("Error de BD al insertar token: " . mysqli_stmt_error($stmt_insert_token));
                    }
                    mysqli_stmt_close($stmt_insert_token);
                } else {
                     $message = "Error interno de la base de datos al preparar la inserción del token.";
                     error_log("Error al preparar INSERT: " . mysqli_error($conexion));
                }
            } else {
                $message = 'No se encontró ninguna cuenta asociada a este correo.'; 
            }
            mysqli_stmt_close($stmt_user);
        } else {
            $message = "Error interno de la base de datos al verificar el correo.";
            error_log("Error al preparar SELECT (usuarios): " . mysqli_error($conexion));
        }
    } else {
        $message = "Error en la conexión a la base de datos.";
    }

    mysqli_close($conexion);

    header("Location: forgot-password.php?message=" . urlencode($message));
    exit(); 
}
?>