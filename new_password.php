<?php
include("conexion.php");
$conexion = mysqli_connect($servidor, $usuario, $password, $bbdd);

if (!$conexion) {
    die("Error de conexión a la base de datos: " . mysqli_connect_error());
}

mysqli_query($conexion, "SET NAMES 'UTF8'");
$message = "";
$message_type = "alert-danger"; //mensaje por default de que es erroneo 
$token_valido = false; 
$token_from_url = ''; 

if(isset($_GET['token'])){
    $token_from_url = $_GET['token']; 

    $stmt = mysqli_prepare($conexion, "SELECT id_usuario, fecha_expiracion FROM password_resets WHERE token = ?;");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $token_from_url);
        mysqli_stmt_execute($stmt);
        $resultado = mysqli_stmt_get_result($stmt);

        if(mysqli_num_rows($resultado) == 1){
            $fila = mysqli_fetch_array($resultado);
            $id_usuario_from_token_table = $fila['id_usuario']; 
            $fecha_expiration = strtotime($fila['fecha_expiracion']);
            $fecha_actual = time();

            if($fecha_expiration < $fecha_actual){
                $message = "El enlace de reestablecimiento de contraseña ha expirado. Por favor, solicita uno nuevo.";
                $message_type = "alert-danger";
                $stmt_delete_expired = mysqli_prepare($conexion, "DELETE FROM password_resets WHERE token = ?");
                if ($stmt_delete_expired) {
                    mysqli_stmt_bind_param($stmt_delete_expired, "s", $token_from_url);
                    mysqli_stmt_execute($stmt_delete_expired);
                    mysqli_stmt_close($stmt_delete_expired);
                } else {
                    $message .= "Error interno al eliminar token expirado.";
                    error_log("Error al preparar DELETE de token expirado: " . mysqli_error($conexion));
                }
            } else {
                $token_valido = true;
            }
        } else {
            $message = 'El enlace de reestablecimiento de contraseña no es válido o ya ha sido utilizado.';
            $message_type = "alert-danger";
        }
        mysqli_stmt_close($stmt); 
    } else {
        $message = "Error interno de la base de datos al verificar el token.";
        $message_type = "alert-danger";
    }
} else {
    $message = "No se ha proporcionado un token válido para restablecer la contraseña.";
    $message_type = "alert-danger";
}

if(isset($_POST['btn-reset-password']) && isset($_GET['token'])){
    $token_from_post = $_GET['token']; 
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if(empty($new_password)){
        $message = "Por favor, ingresa tu nueva contraseña.";
        $message_type = "alert-danger";
    } elseif(empty($confirm_password)){
         $message = "Por favor, confirma tu nueva contraseña.";
         $message_type = "alert-danger";
    } elseif($new_password !== $confirm_password){ 
        $message = "Las contraseñas no coinciden.";
        $message_type = "alert-danger";
    } elseif(strlen($new_password) < 8){
        $message = "La contraseña debe tener al menos 8 caracteres.";
        $message_type = "alert-danger";
    } elseif (!preg_match('/^(?=(?:.*\d){3})(?=(?:.*[A-Za-z]){5}).*$/', $new_password)) {
        $message = "La contraseña debe contener al menos 8 caracteres, incluyendo 3 números y 5 letras (mayúsculas o minúsculas).";
        $message_type = "alert-danger";
    } else {
        $stmt_check_token_post = mysqli_prepare($conexion, "SELECT id_usuario FROM password_resets WHERE token = ? AND fecha_expiracion > NOW()");
        if ($stmt_check_token_post) {
            mysqli_stmt_bind_param($stmt_check_token_post, "s", $token_from_post);
            mysqli_stmt_execute($stmt_check_token_post);
            $resultado_check = mysqli_stmt_get_result($stmt_check_token_post);

            if (mysqli_num_rows($resultado_check) == 1) {
                $fila_check = mysqli_fetch_assoc($resultado_check);
                $id_usuario_to_update = $fila_check['id_usuario']; 

                $password_hasheada = password_hash($new_password, PASSWORD_DEFAULT);

                $stmt_update = mysqli_prepare($conexion, "UPDATE usuarios SET password = ? WHERE id_usuario = ?");
                if ($stmt_update) {
                    mysqli_stmt_bind_param($stmt_update, "si", $password_hasheada, $id_usuario_to_update); 
                    if (mysqli_stmt_execute($stmt_update)){
                        $stmt_delete_token = mysqli_prepare($conexion, "DELETE FROM password_resets WHERE token = ?");
                        if ($stmt_delete_token) {
                            mysqli_stmt_bind_param($stmt_delete_token, "s", $token_from_post);
                            mysqli_stmt_execute($stmt_delete_token);
                            mysqli_stmt_close($stmt_delete_token);
                        } else {
                            error_log("Error al eliminar token después de restablecimiento: " . mysqli_error($conexion));
                        }
                        $message = "<p style='color:green;'>Tu contraseña ha sido restablecida con éxito. Ahora puedes iniciar sesión con tu nueva contraseña.</p>";
                        $message_type = "alert-success";
                        $token_valido = false;
                    } else {
                        $message = "Error al actualizar la contraseña. Por favor, inténtalo de nuevo más tarde.";
                        $message_type = "alert-danger";
                        error_log("Error de BD al actualizar contraseña: " . mysqli_stmt_error($stmt_update));
                    }
                    mysqli_stmt_close($stmt_update);
                } else {
                    $message = "Error interno de la base de datos al preparar la actualización de contraseña.";
                    $message_type = "alert-danger";
                    error_log("Error al preparar UPDATE: " . mysqli_error($conexion));
                }
            } else {
                $message = "El token de restablecimiento no es válido o ha expirado. Por favor, solicita uno nuevo.";
                $message_type = "alert-danger";
            }
            mysqli_stmt_close($stmt_check_token_post);
        } else {
            $message = "Error interno de la base de datos al verificar el token (POST).";
            $message_type = "alert-danger";
            error_log("Error al preparar SELECT (POST): " . mysqli_error($conexion));
        }
    }
}

mysqli_close($conexion);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="css/styles.css">
    <title>Restablecer Contraseña</title>
</head>
<body style="background-color:#D9F3DE;">
    <?php include("includes/navigation.php"); ?>
    <div class='container mt-4' style="height:84vh;">
        <h2>Restablecer Contraseña</h2>
        <p class='text-start'>Por favor, escriba su nueva contraseña</p>
        <div>
            <div class='alert <?php echo $message_type;?>' role='alert'><?php echo $message;?></div>
        </div>
        <?php if($token_valido): ?>
            <form method="POST" class="mt-3" action='new_password.php?token=<?php echo htmlspecialchars($token_from_url); ?>'>
                <div class='mb-3'>
                    <label for="new_password" class="form-label">Nueva contraseña:</label>
                    <input type='password' name='new_password' class='form-control' id='new_password' placeholder='Ingrese su contraseña:'
                        pattern='^(?=(?:.*\d){3})(?=(?:.*[A-Za-z]){5}).*$' title='La contraseña debe contener al menos
                        8 caracteres, incluyendo 3 números y 5 letras (mayúsculas o minúsculas).' required>
                </div>
                <div class='mb-3'>
                    <label for="confirm_password" class="form-label">Confirmar contraseña:</label>
                    <input type='password' name='confirm_password' class='form-control' id='confirm_password' placeholder='Confirme su contraseña:' required>
                </div>
                <input type="submit" class="btn btn-primary" name="btn-reset-password" value="Restablecer Contraseña">
            </form>
        <?php endif; ?>
        <p class='mt-3 btn btn-secondary' style=""><a href='login.php?action=login' 
        style="color:white; text-decoration:none;">Volver a iniciar sesión</a></p>
    </div>
    <?php include("includes/footer.php"); ?>
</body>
</html>