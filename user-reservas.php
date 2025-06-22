<?php
session_start();
include('conexion.php');
include('tours_data.php');

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php?message=necesita_login&redirect_to=" . urlencode("user-reservas.php"));
    exit();
}

$id_usuario = $_SESSION['id_usuario'];
$reservas_usuario = [];
$message = $_GET['message'] ?? '';

$conn = new mysqli($servidor, $usuario, $password, $bbdd);

if ($conn->connect_error) {
    error_log("Error de conexión a la base de datos en user-reservas.php: " . $conn->connect_error);
    header("Location: index.php?message=db_error");
    exit();
}
$conn->set_charset("utf8mb4");

try {
    // Actualizar reservas expiradas
    $current_date = (new DateTime())->format('Y-m-d');
    $stmt_update = $conn->prepare("
        UPDATE reservas
        SET estado_reserva = 'EXPIRADA'
        WHERE id_usuario = ?
        AND estado_reserva = 'PENDIENTE'
        AND fecha_limite_pago < ?
    ");
    if (!$stmt_update) {
        throw new Exception("Error al preparar la consulta de actualización de reservas: " . $conn->error);
    }
    $stmt_update->bind_param("is", $id_usuario, $current_date);
    $stmt_update->execute();
    $stmt_update->close();

    $stmt = $conn->prepare("
        SELECT
            id_reserva, tour_id, fecha_reserva, num_personas,
            precio_total_reserva, nombre_reserva, email_reserva,
            telefono_reserva, fecha_creacion, estado_reserva,
            fecha_limite_pago, fecha_limite_cancelacion_reembolso
        FROM reservas
        WHERE id_usuario = ?
        ORDER BY fecha_reserva DESC, fecha_creacion DESC
    ");

    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de reservas: " . $conn->error);
    }

    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $tour_nombre = "Tour Desconocido";
            foreach ($tours as $tour_item){
                //si existe el tour id y coincide con el de la reserva
                if (isset($tour_item['id']) && $tour_item['id'] === $row['tour_id']) {
                    $tour_nombre = $tour_item['nombre'] ?? $tour_item['name'] ?? "Tour sin nombre";
                    break;
                }
            }
            $row['nombre_tour'] = $tour_nombre;
            //variable que usaré para cuando el usuario no tiene reservas hechas 
            $reservas_usuario[] = $row;
        }
    }
    $stmt->close();

} catch (Exception $e) {
    error_log("Error en user-reservas.php: " . $e->getMessage());
    header("Location: index.php?message=error_fetching_reservas&debug_message=" . urlencode($e->getMessage()));
    exit();
} finally {
    if ($conn) {
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Reservas - Tukuy</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .estado-badge {
            font-size: 0.8em;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            font-weight:bold;
        }
        .estado-PENDIENTE { background-color: #ffc107; color: #343a40; }
        .estado-CONFIRMADA { background-color: #28a745; color: #fff; }
        .estado-CANCELADA { background-color: #dc3545; color: #fff; }
        .estado-PENDIENTE_CONFIRMACION_PAGO { background-color: #17a2b8; color: #fff; }
        .estado-EXPIRADA { background-color: #6c757d; color: #fff; }
    </style>
</head>
<body>
    <?php include("includes/navigation.php"); ?>

    <main class="container-fluid my-5 min-vh-100">
        <div class="row justify-content-center">
            <div class="col-md-11">
                <h1 class="mb-4 text-center">Mis Reservas</h1>
                <?php
                if ($message) {
                    $alert_class = 'alert-info';
                    $alert_icon = 'fas fa-info-circle';
                    $alert_text = '';

                    switch ($message) {
                        case 'reserva_cancelada':
                            $alert_class = 'alert-success';
                            $alert_icon = 'fas fa-check-circle';
                            $alert_text = '¡La reserva ha sido cancelada exitosamente!';
                            break;
                        case 'error_cancelacion':
                            $alert_class = 'alert-danger';
                            $alert_icon = 'fas fa-exclamation-triangle';
                            $alert_text = 'Hubo un error al intentar cancelar la reserva.';
                            if (isset($_GET['debug_message'])) {
                                $alert_text .= '<br><small>Detalles: ' . htmlspecialchars($_GET['debug_message']) . '</small>';
                            }
                            break;
                        case 'no_reserva_id_for_cancel':
                            $alert_class = 'alert-warning';
                            $alert_icon = 'fas fa-exclamation-circle';
                            $alert_text = 'No se proporcionó un ID de reserva para cancelar.';
                            break;
                        case 'reserva_confirmada_exito':
                            $alert_class = 'alert-success';
                            $alert_icon = 'fas fa-credit-card'; 
                            $alert_text = '¡Su reserva ahora esta confirmada! Gracias por confiar en nosotros.';
                            break;
                        case 'reserva_invalida_pago':
                            $alert_class = 'alert-danger';
                            $alert_icon = 'fas fa-exclamation-triangle';
                            $alert_text = 'La reserva no es válida para el pago o no existe.';
                            break;
                        case 'id_reserva_invalido':
                            $alert_class = 'alert-danger';
                            $alert_icon = 'fas fa-exclamation-triangle';
                            $alert_text = 'No existe un ID para esta reserva.';
                            break;
                    }
                    echo '<div class="alert ' . $alert_class . ' alert-dismissible fade show" role="alert">';
                    echo '<i class="' . $alert_icon . ' me-2"></i>' . $alert_text;
                    echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                    echo '</div>';
                }
                ?>
                
                <?php if (empty($reservas_usuario)): //aqui muestro si el usuario no tiene reservas  ?>
                    <div class="alert alert-warning text-center" role="alert">
                        <i class="fas fa-exclamation-circle"></i> No tienes reservas activas en este momento.
                        <p class="mt-2"><a href="index.php" class="alert-link">¡Explora nuestros tours y haz tu primera reserva!</a></p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle text-center">
                            <thead class="table-light">
                            <?php $contador = 1; ?>
                                <tr>
                                    <th>#</th>
                                    <th>Tour</th>
                                    <th>Día</th>
                                    <th>Personas</th>
                                    <th>Precio Total</th>
                                    <th>Creación</th>
                                    <th>Estado</th>
                                    <th>Fecha Límite Pago</th> 
                                    <th>Reembolso Hasta</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reservas_usuario as $reserva): ?>
                                <tr>
                                    <td><?php echo ($contador++); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['nombre_tour']); ?></td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($reserva['fecha_reserva']))); ?></td>
                                    <td><?php echo htmlspecialchars($reserva['num_personas']); ?></td>
                                    <td class="text-success"><?php echo number_format($reserva['precio_total_reserva'], 2, ',', '.'); ?> €</td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y H:i', strtotime($reserva['fecha_creacion']))); ?></td>
                                    <td>
                                        <span class="estado-badge estado-<?php echo htmlspecialchars($reserva['estado_reserva']); ?>">
                                            <?php echo htmlspecialchars(str_replace('_', ' ', $reserva['estado_reserva'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars(date('d/m/Y', strtotime($reserva['fecha_limite_pago']))); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars(date('d/m/Y', strtotime($reserva['fecha_limite_cancelacion_reembolso']))); ?></td>
                                <!--si el estado badge lo tengo como PENDIENTE entonces aparece este bloque -->
                                    <td class="d-flex flex-column gap-2">
                                        <?php if ($reserva['estado_reserva'] === 'PENDIENTE'): ?> 
                                            <form action='confirm_pending_reservation.php' method="POST">
                                                <input type="hidden" name="id_reserva" value="<?php echo htmlspecialchars($reserva['id_reserva'] ?? ''); ?>">
                                                <input type="hidden" name="tour_id" value="<?php echo htmlspecialchars($reserva['tour_id'] ?? ''); ?>">
                                                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($reserva['id_usuario']??'');?>"></input>
                                                <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($reserva['nombre']??'');?>"></input>
                                                <input type="hidden" name="email" value="<?php echo htmlspecialchars($reserva['email']??'');?>"></input>
                                                <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($reserva['telefono']??'');?>"></input>
                                                <input type="hidden" name="fecha_reserva" value="<?php echo htmlspecialchars($reserva['fecha_reserva']??''); ?>">
                                                <input type="hidden" name="num_personas" value="<?php echo htmlspecialchars($reserva['num_personas']??''); ?>">
                                                <input type="hidden" name="precio_total_reserva" value="<?php echo htmlspecialchars($reserva['precio_total_reserva']??''); ?>">

                                                <button type="submit" class="btn btn-sm btn-primary">
                                                    <i class="fas fa-credit-card"></i> Pagar
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="cancel_reservation.php" method="POST" onsubmit="return confirm('¿Estás seguro de cancelar esta reserva?');">
                                        <!--si cancela o expira la reserva, desabilito el boton-->    
                                        <input type="hidden" name="id_reserva" value="<?php echo htmlspecialchars($reserva['id_reserva']); ?>">
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                <?php if (in_array($reserva['estado_reserva'], ['CANCELADA', 'EXPIRADA'])) echo 'disabled'; ?>>
                                                <i class="fas fa-times-circle"></i> Cancelar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include("includes/footer.php"); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>