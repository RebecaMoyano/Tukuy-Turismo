<?php
if(session_status() == PHP_SESSION_NONE){
    session_start();
}
$tiempo_inactividad = 1800; //30min 
$tiempo_maximo_sesion = 8*3600; //8 horas 
//cuando el usuario inicia por primera vez  
if (isset($_SESSION['id_usuario'])){
    if (!isset($_SESSION['tiempo_inicio'])){
        //tiempo inicio es igual a tiempo actual
        $_SESSION['tiempo_inicio'] = time();
        //si pasó eltiempo maximo de sesion 
    } elseif ((time() - $_SESSION['tiempo_inicio']) > $tiempo_maximo_sesion){
        session_unset();
        session_destroy();
        header("Location: login.php?action=login&message=sesion_maxima_alcanzada");
        exit();
    }
    //si la sesion se ha expirado por inactividad
    if (isset($_SESSION['ultimo_acceso'])) {
        $tiempo_transcurrido = time() - $_SESSION['ultimo_acceso'];
        if ($tiempo_transcurrido > $tiempo_inactividad) {
            session_unset();
            session_destroy();
            header("Location: login.php?action=login&message=sesion_expirada");
            exit();
        }
    }
    $_SESSION['ultimo_acceso'] = time();

} else {
    //se eliminan las variables de sesion 
    unset($_SESSION['ultimo_acceso']);
    unset($_SESSION['tiempo_inicio']);
}
?>