<?php
include('tours_data.php'); 
include('conexion.php'); 

//para que el navegador interprete la respuesta como JSON
header('Content-Type: application/json'); 

$tourId = $_GET['tour_id'] ?? null;
$fechaReserva = $_GET['fecha_reserva'] ?? null;

if (!$tourId || !$fechaReserva) {
    echo json_encode(['error' => 'tour_id o fecha_reserva no especificados.']);
    exit();
}

// Obtener los detalles del tour
$current_tour_details = null;
if (isset($tours) && is_array($tours)) {
    foreach ($tours as $tour) {
        if ($tour['id'] === $tourId) {
            $current_tour_details = $tour;
            break;
        }
    }
}

if (!$current_tour_details) {
    echo json_encode(['error' => 'Tour no encontrado.']);
    exit();
}

$capacidad_total_para_fecha = $current_tour_details['capacidad'] ?? 0;
$fecha_salida_unica_definida = $current_tour_details['fecha_salida_unica'] ?? null;

//si la fecha de salida es distinta de la fecha unica del tour 
if ($fechaReserva !== $fecha_salida_unica_definida) {
    echo json_encode(['error' => 'La fecha de reserva solicitada no coincide con la fecha de salida única del tour.']);
    exit();
}

if ($capacidad_total_para_fecha === 0) {
    echo json_encode(['plazas_disponibles' => 0, 'message' => 'Capacidad cero definida para este tour.']);
    exit();
}

$plazasOcupadas = 0;
if ($conn) {
    // Incluir PENDIENTE en el cálculo de ocupación para la visualización en tiempo real
    $stmt = $conn->prepare("SELECT SUM(num_personas) AS total_personas FROM reservas WHERE tour_id = ? AND fecha_reserva = ? AND estado_reserva IN ('CONFIRMADA', 'PENDIENTE')");
    if ($stmt) {
        $stmt->bind_param("ss", $tourId, $fechaReserva);
        $stmt->execute();
        $stmt->bind_result($total_personas);
        $stmt->fetch();
        $stmt->close();
        $plazasOcupadas = $total_personas ?? 0;
    } else {
        echo json_encode(['error' => 'Error interno al consultar la base de datos.']);
        exit();
    }
} else {
    echo json_encode(['error' => 'Error de conexión a la base de datos.']);
    exit();
}

$plazasDisponibles = max(0, $capacidad_total_para_fecha - $plazasOcupadas);

echo json_encode(['plazas_disponibles' => $plazasDisponibles]);

// Cerrar la conexión a la base de datos
if ($conn) {
    $conn->close();
}
?>