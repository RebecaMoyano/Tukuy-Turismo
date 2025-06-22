<?php
//comprueba las plazas, numero de personas, fecha, hora, datos de pago
session_start();
include("sesion.php");
include('conexion.php');
include('tours_data.php');

function getTourDataById($tourId, $tours_array){
    foreach ($tours_array as $tour) {
        if ($tour['id'] === $tourId) {
            return $tour;
        }
    }
    return null;
}

function obtenerPlazasOcupadasParaTourYFecha($conn, $tourId, $fechaReserva) {
    $plazasOcupadas = 0;
    $stmt = $conn->prepare("SELECT SUM(num_personas) AS total_personas FROM reservas WHERE tour_id = ? AND fecha_reserva = ? AND estado_reserva IN ('CONFIRMADA', 'PENDIENTE')");
    if ($stmt) {
        $stmt->bind_param("ss", $tourId, $fechaReserva);
        $stmt->execute();
        $stmt->bind_result($total_personas);
        $stmt->fetch();
        $stmt->close();
        return $total_personas ?? 0;
    } else {
        error_log("Error al preparar la consulta obtenerPlazasOcupadasParaTourYFecha en process_payment.php: " . $conn->error);
        return 0;
    }
}

function redirect_to_reserva_form_with_data(
    $tour_id, $num_personas, $fecha_reserva, $nombre, $email, $telefono, $precio_total_reserva_val, $message, $debug_message = ''
) {
    $url = "reserva_form.php?";
    $url .= "tour_id=" . urlencode($tour_id);
    $url .= "&num_personas=" . urlencode($num_personas);
    $url .= "&nombre=" . urlencode($nombre);
    $url .= "&email=" . urlencode($email);
    $url .= "&telefono=" . urlencode($telefono);
    $url .= "&precio_total_reserva=" . urlencode($precio_total_reserva_val); 
    $url .= "&message=" . urlencode($message);
    if ($debug_message) {
        $url .= "&debug_message=" . urlencode($debug_message);
    }
    header("Location: " . $url);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: index.php?message=invalid_request_method");
    exit();
}

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?message=necesita_login&redirect_to=" . urlencode("user-reservas.php"));
    exit();
}

$id_usuario = $_SESSION['id_usuario'];

$tour_id = htmlspecialchars($_POST['tour_id'] ?? '');
$num_personas = (int)($_POST['num_personas'] ?? 0);
$precio_total_reserva_val = (float)($_POST['precio_total'] ?? 0.0); 
$fecha_reserva = htmlspecialchars($_POST['fecha_reserva'] ?? '');
$fecha_limite_cancelacion_reembolso = htmlspecialchars($_POST['fecha_limite_cancelacion_reembolso'] ?? '0000-00-00');
$hora_reserva = htmlspecialchars($_POST['hora_reserva'] ?? date('H:i:s'));

$nombre_reserva = htmlspecialchars($_POST['nombre'] ?? '');
$email_reserva = htmlspecialchars($_POST['email'] ?? '');
$telefono_reserva = htmlspecialchars($_POST['telefono'] ?? '');

$payment_method = htmlspecialchars($_POST['selected_payment_method'] ?? '');
$numero_tarjeta = htmlspecialchars($_POST['numero_tarjeta'] ?? '');
$fecha_expiracion = htmlspecialchars($_POST['fecha_expiracion'] ?? '');
$cvv = htmlspecialchars($_POST['cvv'] ?? '');
$nombre_titular = htmlspecialchars($_POST['nombre_titular'] ?? '');

$id_reserva_existente_para_actualizar = $_POST['id_reserva'] ?? null;


if (empty($tour_id) || empty($fecha_reserva) || $num_personas <= 0 || !is_numeric($precio_total_reserva_val) || $precio_total_reserva_val <= 0 || empty($nombre_reserva) || empty($email_reserva) || empty($telefono_reserva)) {
    redirect_to_reserva_form_with_data(
        $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
        "datos_incompletos", "Datos esenciales faltantes."
    );
}

$current_tour_details = getTourDataById($tour_id, $tours);
if (!$current_tour_details) {
    header("Location: index.php?message=tour_not_found");
    exit();
}
$capacidad_tour = $current_tour_details['capacidad'] ?? 0;

$today = new DateTime();
$today->setTime(0,0,0);
try {
    $fechaTourDT = new DateTime($fecha_reserva);
    $fechaTourDT->setTime(0,0,0);
} catch (Exception $e) {
    redirect_to_reserva_form_with_data(
        $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
        "error_generico", "Fecha de tour inválida: " . $e->getMessage()
    );
}

if ($fechaTourDT < $today) {
    redirect_to_reserva_form_with_data(
        $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
        "tour_ya_paso"
    );
}

$plazasOcupadas = obtenerPlazasOcupadasParaTourYFecha($conn, $tour_id, $fecha_reserva);
$plazasDisponibles = max(0, $capacidad_tour - $plazasOcupadas);

if ($num_personas > $plazasDisponibles) {
    redirect_to_reserva_form_with_data(
        $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
        "cap_max_excedida", "Plazas disponibles: " . $plazasDisponibles
    );
}

$estado_final_reserva = 'PENDIENTE';

$fecha_limite_pago_bbdd = null;

//si escogio el metodo de pago tarjeta, compruebo que los datos son correctos
if ($payment_method === 'card') {
    if (empty($numero_tarjeta) || empty($fecha_expiracion) || empty($cvv) || empty($nombre_titular)) {
        redirect_to_reserva_form_with_data(
            $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
            "datos_tarjeta_incompletos"
        );
    }
    if (!preg_match('/^[0-9]{3,4}$/', $cvv)) {
        redirect_to_reserva_form_with_data(
            $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
            "numero_cvv_erroneo"
        );
    }
    
    $pago_exitoso = true; 
//si no hubo ningun problema con los datos de la tarjeta, simulo un pago exitoso
    if ($pago_exitoso) {
        $estado_final_reserva = 'CONFIRMADA';
        $fecha_limite_pago_bbdd = '0000-00-00'; 
    } else {
        redirect_to_reserva_form_with_data(
            $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
            "payment_failed", "La pasarela de pago rechazó la transacción."
        );
    }
} else {
    redirect_to_reserva_form_with_data(
        $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
        "no_seleccion_metodo_pago"
    );
}

$conn->begin_transaction();

try {
    $id_reserva_a_operar = $id_reserva_existente_para_actualizar;
    $query_success = false;
    //si es una reserva pendiente, no hago el insert sino que actualizo 
    if ($id_reserva_a_operar) {
        $stmt = $conn->prepare("UPDATE reservas SET estado_reserva = ?, fecha_limite_pago = ?, fecha_actualizacion = NOW() WHERE id_reserva = ? AND id_usuario = ?");
        if ($stmt) {
            $stmt->bind_param("ssii",
                $estado_final_reserva,
                $fecha_limite_pago_bbdd,
                $id_reserva_a_operar,
                $id_usuario
            );
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $query_success = true;
            } else {
                error_log("Error al actualizar reserva (0 filas afectadas): ID " . $id_reserva_a_operar . " | Error: " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Error al preparar UPDATE de reserva: " . $conn->error);
        }
    } else {
        $stmt = $conn->prepare("INSERT INTO reservas (id_usuario, tour_id, fecha_reserva, hora_reserva, num_personas, precio_total_reserva, nombre_reserva, email_reserva, telefono_reserva, fecha_creacion, estado_reserva, fecha_limite_pago, fecha_limite_cancelacion_reembolso, fecha_actualizacion) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?, NOW())");
        if ($stmt) {
            $stmt->bind_param("issdsdssssss",
                $id_usuario,
                $tour_id,
                $fecha_reserva,
                $hora_reserva,
                $num_personas,
                $precio_total_reserva_val, 
                $nombre_reserva,
                $email_reserva,
                $telefono_reserva,
                $estado_final_reserva,
                $fecha_limite_pago_bbdd, 
                $fecha_limite_cancelacion_reembolso
            );
            $stmt->execute();
            if ($stmt->affected_rows > 0) {
                $query_success = true;
            } else {
                error_log("Error al insertar nueva reserva (0 filas afectadas): " . $stmt->error);
            }
            $stmt->close();
        } else {
            error_log("Error al preparar INSERT de reserva: " . $conn->error);
        }
    }
    //variable que maneja el exito de la insercion o de la actualizacion 
    if ($query_success){
        $conn->commit();
        header("Location: user-reservas.php?message=reserva_confirmada_exito");
        exit();
    } else {
        $conn->rollback();
        redirect_to_reserva_form_with_data( 
            $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
            "fallo_reserva", "Error al guardar/actualizar en DB."
        );
    }

} catch (Exception $e) {
    $conn->rollback();
    redirect_to_reserva_form_with_data( 
        $tour_id, $num_personas, $fecha_reserva, $nombre_reserva, $email_reserva, $telefono_reserva, $precio_total_reserva_val,
        "error_generico", $e->getMessage()
    );
}

$conn->close();
?>