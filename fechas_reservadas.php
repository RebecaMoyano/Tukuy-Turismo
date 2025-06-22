<?php 
header('Content-Type: application/json');
session_start();
include("conexion.php");
include("tours_data.php");
$conexion = mysqli_connect($servidor, $usuario, $password, $bbdd);
if(!$conexion){
    echo json_encode(['error'=>'Error en la conexión a la base de datos'.mysqli_connect_error()]);
    exit();
}
mysqli_query($conexion, "SET NAMES 'UTF8'");
$tourId = $_GET['tour_id']?? null;
if(!$tourId){
    echo json_encode(['error'=>'No se proporcionó el ID']);
    mysqli_close($conexion);
    exit();
}
$tourId = mysqli_real_escape_string($conexion,$tourId);

$tourCapacidad = 0;
foreach($tours as $tour){
    if(isset($tour['id']) && $tour['id']==$tourId){
        $tourCapacidad = $tour['max-capacidad'] ?? 0;
        break; 
    }
}
if($tourCapacidad === 0){
    echo json_encode(['error'=>'Capacidad del tour no encontrada.']);
    mysqli_close($conexion);
    exit();
}
$sql = "SELECT fecha_reserva, SUM(num_personas) as total_personas_reservadas 
        FROM reservas 
        WHERE tour_id = '$tourId' 
        GROUP BY fecha_reserva"; 
$resultado = mysqli_query($conexion, $sql);
$fechasReservadas = [];
if ($resultado) {
    while ($fila = mysqli_fetch_assoc($resultado)) { 
        if ($fila['total_personas_reservadas'] >= $tourCapacidad){
            $fechasReservadas[] = $fila['fecha_reserva'];
        }
    }
} else {
    echo json_encode(['error' => 'Error al consultar reservas: ' . mysqli_error($conexion)]);
    mysqli_close($conexion);
    exit();
}
mysqli_close($conexion);
echo json_encode(['fechasReservadas'=>$fechasReservadas]);

?>