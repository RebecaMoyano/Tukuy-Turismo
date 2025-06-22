<?php
include("conexion.php");
include("sesion.php");

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (!isset($_SESSION['id_usuario'])){
    header("Location: login.php?message=necesita_login");
    exit();
}
$id_usuario_session = $_SESSION['id_usuario'];

$tour_id = filter_var($_GET['tour_id'] ?? null);
$id_usuario_url = filter_var($_GET['id_usuario'] ?? null, FILTER_VALIDATE_INT);

$nombre_reserva = htmlspecialchars($_GET['nombre'] ?? '');
$telefono_reserva = htmlspecialchars($_GET['telefono'] ?? '');
$fecha_reserva = htmlspecialchars($_GET['fecha_reserva'] ?? ''); 
$fecha_limite_cancelacion_reembolso = htmlspecialchars($_GET['fecha_limite_cancelacion_reembolso'] ?? ''); 
$hora_reserva = htmlspecialchars($_GET['hora_reserva'] ?? ''); 
$fecha_limite_pago = htmlspecialchars($_GET['fecha_limite_pago'] ?? ''); 

$email_reserva = filter_var($_GET['email'] ?? null, FILTER_VALIDATE_EMAIL);

$num_personas = filter_var($_GET['num_personas'] ?? null, FILTER_VALIDATE_INT);

$precio_total = filter_var($_GET['precio_total_reserva'] ?? null, FILTER_VALIDATE_FLOAT);

$estado_reserva = htmlspecialchars($_GET['estado_reserva'] ?? ''); 
$payment_method = htmlspecialchars($_GET['payment_method'] ?? ''); 

if (empty($payment_method)) {
    header("Location: index.php?message=Debe_seleccionar_un_metodo_de_pago_pagar_despues"); 
    exit();
}

if ($id_usuario_session !== $id_usuario_url) {
    header("Location: reserva_form.php?message=error_permisos_usuario_no_coincide");
    exit();
}

if (!$tour_id || !$id_usuario_url || !$email_reserva || !$fecha_reserva || !$num_personas || !$precio_total || !$estado_reserva) {
    header("Location: reserva_form.php?message=error_datos_faltantes_pagar_despues");
    exit();
}

try {

    $stmt = $conn->prepare("INSERT INTO reservas(id_usuario, tour_id, fecha_reserva, hora_reserva, num_personas, precio_total_reserva, 
    nombre_reserva, email_reserva, telefono_reserva, estado_reserva, fecha_limite_pago, fecha_limite_cancelacion_reembolso, metodo_pago)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

    if ($stmt === false) {
        error_log("Error al preparar la consulta INSERT en pagarDespues.php: " . $conn->error);
        header("Location: reserva_form.php?message=error_db_prepare");
        exit();
    }

    $bind_success = $stmt->bind_param(
        "isssidsssssss", 
        $id_usuario_url,
        $tour_id,
        $fecha_reserva,
        $hora_reserva,
        $num_personas,
        $precio_total,
        $nombre_reserva,
        $email_reserva,
        $telefono_reserva,
        $estado_reserva,
        $fecha_limite_pago,
        $fecha_limite_cancelacion_reembolso,
        $payment_method
    );

    if ($bind_success === false) {
        error_log("Error en bind_param en pagarDespues.php: " . $stmt->error);
        header("Location: reserva_form.php?message=error_db_bind");
        exit();
    }

    if ($stmt->execute()) {
        header("Location: reserva_form.php?message=reserva_exitosa_pago_pendiente&tour_id=".$tour_id);
        exit();
    } else {
        error_log("Error al ejecutar la consulta en pagarDespues.php: " . $stmt->error);
        header("Location: reserva_form.php?message=error_db_execute&tour_id=".$tour_id);
        exit();
    }

    $stmt->close();

} catch (mysqli_sql_exception $e) {
    error_log("Excepción de base de datos en pagarDespues.php: " . $e->getMessage());
    header("Location: reserva_form.php?message=error_db_exception");
    exit();
} finally {
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>