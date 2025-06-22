<?php
session_start();
include('conexion.php');
include('sesion.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?message=necesita_login&redirect_to=" . urlencode("user-reservas.php"));
    exit();
}

$id_usuario_session = $_SESSION['id_usuario'];
//filtra si es un numero entero válido 
$id_reserva = filter_var($_POST['id_reserva'] ?? null, FILTER_VALIDATE_INT);
$metodo_pago_existente = htmlspecialchars($_POST['metodo_pago_existente'] ?? ''); 

if (!$id_reserva) {
    header("Location: user-reservas.php?message=error_id_reserva_invalido");
    exit();
}

$conn = new mysqli($servidor, $usuario, $password, $bbdd);

if ($conn->connect_error) {
    header("Location: user-reservas.php?message=db_error");
    exit();
}
$conn->set_charset("utf8mb4");

try {
    $conn->begin_transaction();
    $estado_nuevo = 'CONFIRMADA';
    $fecha_actualizacion = (new DateTime())->format('Y-m-d H:i:s');
    //se actualiza el estado de la reserva a CONFIRMADA
    $stmt = $conn->prepare("UPDATE reservas SET estado_reserva = ?, metodo_pago = ?, fecha_actualizacion = ? WHERE id_reserva = ? AND id_usuario = ? AND estado_reserva IN ('PENDIENTE', 'PENDIENTE_CONFIRMACION_PAGO')");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de actualización: " . $conn->error);
    }

    $metodo_pago_a_usar = !empty($metodo_pago_existente) ? $metodo_pago_existente : 'Pago Directo o Por Confirmar'; 

    $stmt->bind_param(
        "sssii",
        $estado_nuevo,
        $metodo_pago_a_usar,
        $fecha_actualizacion,
        $id_reserva,
        $id_usuario_session 
    );
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $conn->commit();
        //se ejecuta si todo salio bien 
        header("Location: user-reservas.php?message=reserva_confirmada_exito");
        exit();
    } else {
        $conn->rollback();
        header("Location: user-reservas.php?message=reserva_no_actualizada_o_no_existe&id=" . $id_reserva);
        exit();
    }

} catch (Exception $e) {
    $conn->rollback();
    error_log("Excepción al confirmar reserva: " . $e->getMessage());
    header("Location: user-reservas.php?message=error_al_confirmar_reserva&id=" . $id_reserva);
    exit();
} finally {
    //siempre cierrto la conexion independientemente de si hubo error o no
    if (isset($conn) && $conn) {
        $conn->close();
    }
}
?>