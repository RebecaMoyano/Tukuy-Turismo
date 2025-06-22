<?php
session_start();
include('conexion.php'); 

// 1. Verificar que se ha enviado por POST
if ($_SERVER['REQUEST_METHOD'] !== "POST") {
    header("Location: user-reservas.php?message=invalid_access");
    exit();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?message=necesita_login&redirect_to=" . urlencode("user-reservas.php"));
    exit();
}

// 3. Obtener el ID de la reserva a cancelar
$id_reserva = $_POST['id_reserva'] ?? null;
$id_usuario_sesion = $_SESSION['id_usuario'];

if (!$id_reserva) {
    header("Location: user-reservas.php?message=no_reserva_id_for_cancel");
    exit();
}

$conn = new mysqli($servidor, $usuario, $password, $bbdd);

if ($conn->connect_error) {
    error_log("Error de conexión a la base de datos en cancel_reservation.php: " . $conn->connect_error);
    header("Location: user-reservas.php?message=db_error");
    exit();
}
$conn->set_charset("utf8mb4");

$conn->begin_transaction(); // Iniciar una transacción

try {
    // 4. Verificar la reserva: debe existir, pertenecer al usuario y no estar ya CANCELADA o EXPIRADA
    $stmtCheck = $conn->prepare("SELECT estado_reserva FROM reservas WHERE id_reserva = ? AND id_usuario = ? AND estado_reserva NOT IN ('CANCELADA', 'EXPIRADA') FOR UPDATE");
    
    if (!$stmtCheck) {
        throw new Exception("Error al preparar la verificación de reserva: " . $conn->error);
    }
    $stmtCheck->bind_param("ii", $id_reserva, $id_usuario_sesion);
    $stmtCheck->execute();
    $resultCheck = $stmtCheck->get_result();

    if ($resultCheck->num_rows === 0) {
        // La reserva no existe, no pertenece al usuario 
        throw new Exception("reserva_no_encontrada_o_no_cancelable");
    }
    $reserva_info = $resultCheck->fetch_assoc();
    $current_estado = $reserva_info['estado_reserva'];
    $stmtCheck->close();

    // 5. este mensaje lo pongo en la url cuando cancelo la reserva 
    $cancellation_message_param = "reserva_cancelada"; 

    // 6. Actualizo el estado de la reserva a 'CANCELADA'
    $stmtUpdate = $conn->prepare("UPDATE reservas SET estado_reserva = 'CANCELADA', fecha_actualizacion = NOW() WHERE id_reserva = ?");
    
    if (!$stmtUpdate) {
        throw new Exception("Error al preparar la actualización a CANCELADA: " . $conn->error);
    }
    $stmtUpdate->bind_param("i", $id_reserva);

    if (!$stmtUpdate->execute()) {
        throw new Exception("Error al ejecutar la actualización a CANCELADA: " . $stmtUpdate->error);
    }
    $stmtUpdate->close();

    $conn->commit(); // Confirmar la transacción

    header("Location: user-reservas.php?message=" . $cancellation_message_param);
    exit();

} catch (Exception $e) {
    $conn->rollback(); 
    error_log("Error al cancelar reserva (catch): " . $e->getMessage());
    //no se pudo cancelar la reserva, hubo un error 
    $error_message_param = "error_cancelacion";
    if ($e->getMessage() === "reserva_no_encontrada_o_no_cancelable") {
        $error_message_param = "reserva_no_cancelable";
    }

    // Redirigir con mensaje de error para la reserva no cancelable 
    header("Location: user-reservas.php?message=" . $error_message_param . "&debug_message=" . urlencode($e->getMessage()));
    exit();
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>