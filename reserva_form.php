<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
//para formatear fechas en español 
setlocale(LC_TIME, 'es_ES.UTF-8', 'es_ES', 'es');

include('tours_data.php'); 
include('conexion.php');

//devuelve true o false 
function usuarioTieneReservaActivaGeneral($conn, $idUsuario) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM reservas WHERE id_usuario = ? AND estado_reserva = 'CONFIRMADA'");
    if ($stmt) {
        $stmt->bind_param("i", $idUsuario);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        return $count > 0; 
    } else {
        error_log("Error al preparar la consulta usuarioTieneReservaActivaGeneral: " . $conn->error);
        return false;
    }
}

function getTourData($conn, $tourId){
    global $tours; 
    foreach ($tours as $tour) {
        if ($tour['id'] === $tourId) {
            return $tour;
        }
    }
    return null;
}

$tourId = $_GET['tour_id'] ?? null;
$current_tour_details = null;
$usuario_logueado_id = $_SESSION['id_usuario'] ?? null;
$usuario_logueado_nombre = $_SESSION['nombre_usuario'] ?? '';
$usuario_logueado_email = $_SESSION['usuario_email'] ?? '';
$usuario_logueado_telefono = $_SESSION['telefono_usuario'] ?? '';
$usuario_tiene_reserva_activa_general = false;

if ($usuario_logueado_id !== null) {
    $usuario_tiene_reserva_activa_general = usuarioTieneReservaActivaGeneral($conn, $usuario_logueado_id);
}

$id_reserva_existente = $_GET['id_reserva'] ?? null;
$reserva_a_cargar = null;

if ($id_reserva_existente && $usuario_logueado_id) {
    if ($conn->connect_error) {
        error_log("Error de conexión al cargar reserva en reserva_form.php: " . $conn->connect_error);
        header("Location: user-reservas.php?message=db_error_load_reserva");
        exit();
    } else {
        $stmt_carga = $conn->prepare("SELECT * FROM reservas WHERE id_reserva = ? AND id_usuario = ?");
        if ($stmt_carga) {
            $stmt_carga->bind_param("ii", $id_reserva_existente, $usuario_logueado_id);
            $stmt_carga->execute();
            $result_carga = $stmt_carga->get_result();
            if ($result_carga->num_rows > 0) {
                $reserva_a_cargar = $result_carga->fetch_assoc();
                if (!$tourId) {
                    $tourId = $reserva_a_cargar['tour_id'];
                }
            } else {
                header("Location: user-reservas.php?message=reserva_invalida_pago");
                exit();
            }
            $stmt_carga->close();
        } else {
            error_log("Error al preparar la consulta de carga de reserva: " . $conn->error);
            header("Location: user-reservas.php?message=db_error_prepare_load");
            exit();
        }
    }
}

if (!$tourId){
    header("Location: index.php?message=no_tour_specified");
    exit();
}

$current_tour_details = getTourData($conn, $tourId);

if (!$current_tour_details) {
    header("Location: index.php?message=tour_not_found");
    exit();
}

$fecha_salida_unica_tour = $current_tour_details['fecha_salida_unica'] ?? null;
$capacidad_tour = $current_tour_details['capacidad'] ?? 0;

if (!$fecha_salida_unica_tour){
    error_log("Tour " . $tourId . " no tiene una 'fecha_salida_unica' definida en tours_data.php");
    header("Location: index.php?message=tour_no_fecha_unica");
    exit();
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
        $plazasOcupadas = $total_personas ?? 0;
    } else {
        error_log("Error al preparar la consulta obtenerPlazasOcupadasParaTourYFecha: " . $conn->error);
    }
    return $plazasOcupadas;
}

$plazasOcupadasParaUnicaFecha = obtenerPlazasOcupadasParaTourYFecha($conn, $tourId, $fecha_salida_unica_tour);
$plazasDisponiblesParaUnicaFecha = max(0, $capacidad_tour - $plazasOcupadasParaUnicaFecha);

$current_num_personas = $_POST['num_personas'] ?? $_GET['num_personas'] ?? null;
if($current_num_personas === null){
    $current_num_personas = $_POST['cantidad'] ?? $_GET['cantidad'] ?? null;
}
if ($current_num_personas === null || $current_num_personas < 1) { 
    $current_num_personas = 1;
}
$current_precioTotal = $_POST['precio_total_reserva'] ?? $_GET['precio_total_reserva'] ?? '';
$current_nombre = $_POST['nombre'] ?? $_GET['nombre'] ?? $usuario_logueado_nombre;
$current_email = $_POST['email'] ?? $_GET['email'] ?? $usuario_logueado_email;
$current_telefono = $_POST['telefono'] ?? $_GET['telefono'] ?? $usuario_logueado_telefono;

$message = $_GET['message'] ?? '';
$display_text = '';

switch ($message) {
    case 'datos_incompletos':
        $display_text = '<div class="alert alert-danger" role="alert">Datos incompletos. Para confirmar su reserva por favor, rellene  todos los campos.</div>';
        break;
    case 'formato_email_invalido':
        $display_text = '<div class="alert alert-danger" role="alert">El formato del email es inválido. Por favor, introduce un email válido.</div>';
        break;
    case 'error_generico':
        $display_text = '<div class="alert alert-danger" role="alert">Hubo un error al procesar tu reserva. Intenta de nuevo más tarde.' . (isset($_GET['debug_message']) ? ' (Debug: ' . htmlspecialchars($_GET['debug_message']) . ')' : '') . '</div>';
        break;
    case 'not_logged_in':
        $display_text = '<div class="alert alert-warning" role="alert">Por favor, inicia sesión para realizar una reserva.</div>';
        break;
    case 'tour_cerrado_fecha': 
        $fecha_cierre = $_GET['fecha_cierre'] ?? 'desconocida';
        $display_text = '<div class="alert alert-danger" role="alert">Lo sentimos, la fecha seleccionada para este tour está cerrada para nuevas reservas. La fecha límite para reservar era el ' . htmlspecialchars($fecha_cierre) . '. Por favor, elige otra fecha.</div>';
        break;
    case 'cap_max_excedida':
        $plazas_disponibles_msg = htmlspecialchars($_GET['plazas_disponibles'] ?? 'N/A');
        $display_text = '<div class="alert alert-warning" role="alert">El número de personas excede la capacidad disponible para este tour. Plazas disponibles: ' . $plazas_disponibles_msg . '.</div>';
        break;
    case 'reserva_exitosa_pago_pendiente':
        $display_text = '<div class="alert alert-warning" role="alert">Ha guardado una reserva con estado PENDIENTE. Por favor, complete el pago en el apartado de Mis Reservas para confirmar su plaza.</div>';
        break;
    case 'payment_canceled':
        $display_text = '<div class="alert alert-info" role="alert">Has cancelado la operación de pago. Tu reserva no ha sido confirmada.</div>';
        break;
    case 'payment_failed':
        $display_text = '<div class="alert alert-danger" role="alert">El pago fue rechazado. Por favor, asegúrese de haber introducido valores correctos.</div>';
        break;
    case 'error_tour_no_encontrado':
        $display_text = '<div class="alert alert-danger" role="alert">Error: El tour especificado no fue encontrado.</div>';
        break;
    case 'reserva_exitosa':
        $display_text = '<div class="alert alert-success" role="alert">¡Reserva creada con exito!</div>';
        break;
    case 'fallo_reserva':
        $display_text = '<div class="alert alert-danger" role="alert">Ha habido un error al hacer la reserva. Intentelo de nuevo.</div>';
        break;
    case 'cantidad_viajeros_invalida':
        $display_text = '<div class="alert alert-danger" role="alert">No ha sido posible procesar su reserva, el número de viajeros debe ser mayor a 0.</div>';
        break;
    case 'debes_seleccionar_metodo_pago':
        $display_text = '<div class="alert alert-danger" role="alert">Debe seleccionar el método de pago y rellenar los campos primero.</div>';
        break;
    case 'user_has_active_reservation':
        $display_text = '<div class="alert alert-info" role="alert">Ya tienes una reserva confirmada activa. No puedes hacer más reservas en este momento.</div>';
        break;
    case 'tour_ya_paso':
        $display_text = '<div class="alert alert-danger" role="alert">Lo sentimos, la fecha de este tour ya ha pasado.</div>';
        break;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reservar Tour: <?php echo htmlspecialchars($current_tour_details['nombre']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tinos:ital,wght@0,400;0,700;1,400;1,700&display=swap" rel="stylesheet">
    <style>
        header {
            height: 40vh;
            background-image: url('../assets/img/header-reservas.jpeg');
            background-size: cover;
            background-position: center 20%;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .main-content {
            padding: 40px 0;
            min-height: 500px;
        }
        .card{
            border: none;
            border-radius: 10px;
            width: 90%;
        }
        .card-reserva{
            background-color:white;
        }
        #reembolso_info {
            padding: 10px;
            border-radius: 5px;
            margin-top: 10px;
            background-color: #f0f8ff;
            border: 1px solid #cce5ff;
        }
        .form-control:read-only {
            background-color: #e9ecef;
            opacity: 1;
        }
    </style>
</head>
<body>
    <?php
    include('includes/navigation.php');
    ?>
    <header class='d-flex justify-content-center align-items-center'>
        <h2 style='font-family:Tinos; font-size:3em; font-weight:bold;'>RESERVA TU PRÓXIMA AVENTURA</h2>
    </header>

    <div class="container main-content">
        <div class="card-reserva p-4 shadow-sm">
            <?php echo $display_text;  ?>

            <h3 class="mb-4 text-center">Reservando: <?php echo htmlspecialchars($current_tour_details['nombre']); ?></h3>

            <p><strong>Descripción:</strong> <?php echo htmlspecialchars($current_tour_details['descripcion'] ?? 'No disponible.'); ?></p>
            <p><strong>Duración:</strong> <?php echo htmlspecialchars($current_tour_details['duracion'] ?? 'No disponible.'); ?></p>
            <p><strong>Precio por Persona:</strong><?php echo htmlspecialchars($current_tour_details['precio'] ?? '0.00'); ?> €</p>

            <p><strong>Fecha de Salida del Tour:</strong>
                <?php
                $formatted_fecha_salida = '';
                //fecha de salida formateada para que se muestre bnito 
                try {
                    $dt_salida = new DateTime($fecha_salida_unica_tour);
                    //convierto el Date a fecha en string 
                    $formatted_fecha_salida = strftime('%d de %B de %Y', $dt_salida->getTimestamp());
                } catch (Exception $e) {
                    $formatted_fecha_salida = 'Fecha no válida';
                    error_log("Error formateando fecha de salida única: " . $e->getMessage());
                }
                echo htmlspecialchars($formatted_fecha_salida);
                ?>
            </p>
            <p class="text-muted small">Esta es la fecha fija para este tour. No es posible seleccionar una fecha diferente.</p>

            <hr>
            <form action="process_reservation.php" method="POST" id="reservationForm">
                <input type="hidden" name="tour_id" value="<?php echo htmlspecialchars($tourId); ?>">
                <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($usuario_logueado_id); ?>">
                <input type="hidden" name="fecha_reserva" value="<?php echo htmlspecialchars($fecha_salida_unica_tour); ?>">

                <?php if ($reserva_a_cargar): ?>
                    <input type="hidden" name="id_reserva_existente" value="<?php echo htmlspecialchars($reserva_a_cargar['id_reserva']); ?>">
                <?php endif; ?>

                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre Completo:</label>
                    <input type="text" name="nombre" id="nombre" class="form-control"
                           value="<?php echo htmlspecialchars($current_nombre); ?>" required
                           <?php echo (!empty($usuario_logueado_nombre) ? 'readonly' : ''); ?>>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email:</label>
                    <input type="email" name="email" id="email" class="form-control"
                           value="<?php echo htmlspecialchars($current_email); ?>" required
                           <?php echo (!empty($usuario_logueado_email) ? 'readonly' : ''); ?>>
                </div>
                <div class="mb-3">
                    <label for="telefono" class="form-label">Teléfono:</label>
                    <input type="tel" name="telefono" id="telefono" class="form-control" placeholder="Ej: 123-456-7890"
                           value="<?php echo htmlspecialchars($current_telefono); ?>" required>
                </div>

                <div class="mb-3">
                    <label for="fecha_display" class="form-label">Fecha del Tour:</label>
                    <input type="text" id="fecha_display" class="form-control" readonly
                           value="<?php echo htmlspecialchars($formatted_fecha_salida); ?>">
                    <div id="reembolso_info" class="mt-2 p-2 rounded"></div>
                </div>

                <div class="mb-3">
                    <label for="num_personas" class="form-label">Número de Personas:</label>
                    <input type="number" name="num_personas" id="num_personas" class="form-control" min="1"
                           value="<?php echo htmlspecialchars($current_num_personas); ?>" required
                           <?php echo ($reserva_a_cargar ? 'readonly':''); ?>>
                    <small class="form-text text-muted" id="plazasDisponiblesMsg"></small>
                </div>

                <div class="mb-3">
                    <label for="precio_total_reserva" class="form-label">Precio Total:</label>
                    <input type="text" name="precio_total_reserva" id="precio_total_reserva" class="form-control" readonly
                           value="<?php echo htmlspecialchars($current_precioTotal); ?>">
                    <input type="hidden" id="precio_unitario_tour_js" value="<?php echo htmlspecialchars($current_tour_details['precio'] ?? '0.00'); ?>">
                </div>
                <button type="button" class="btn btn-primary btn-lg w-100" id="submitButton">
                     <?php echo ($reserva_a_cargar) ? 'Continuar al Pago' : 'Confirmar Reserva y Pagar'; ?>
                </button>
            </form>
        </div>
    </div>

    <?php
    include('includes/footer.php');
    ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const numPersonasInput = document.getElementById('num_personas');
        const precioUnitarioTourJS = document.getElementById('precio_unitario_tour_js');
        const precioTotalElement = document.getElementById('precio_total_reserva');
        const submitButton = document.getElementById('submitButton');
        const reembolsoInfoDiv = document.getElementById('reembolso_info');
        const plazasDisponiblesMsg = document.getElementById('plazasDisponiblesMsg');
        const email = document.getElementById('email');

        //variables para usar en las validaciones 
        const tourId = '<?php echo htmlspecialchars($tourId); ?>';
        const fechaSalidaUnicaTour = '<?php echo htmlspecialchars($fecha_salida_unica_tour); ?>';
        const capacidadTour = <?php echo json_encode($capacidad_tour); ?>;
        const plazasDisponiblesParaUnicaFecha = <?php echo json_encode($plazasDisponiblesParaUnicaFecha); ?>;

        const usuarioTieneReservaActivaGeneral = <?php echo json_encode($usuario_tiene_reserva_activa_general); ?>;
        const usuarioLogueadoId = <?php echo json_encode($usuario_logueado_id); ?>;
        const reservaACargar = <?php echo json_encode((bool)$reserva_a_cargar); ?>;

        // Establecer el número inicial de personas a 1 si no está pre-rellenado o es inválido
        if (!numPersonasInput.value || parseInt(numPersonasInput.value) <= 0) {
            numPersonasInput.value = 1;
        }

        // Función auxiliar para formatear fechas de manera consistente
        function formatDateToLocale(dateObj){
            return dateObj.toLocaleDateString('es-ES', { day: '2-digit', month: 'long', year: 'numeric' });
        }

        // Calcula y actualiza el campo del precio total
        function calcularPrecioTotal() {
            const precioPorPersona = parseFloat(precioUnitarioTourJS.value);
            const numPersonas = parseInt(numPersonasInput.value);

            if (!isNaN(precioPorPersona) && !isNaN(numPersonas) && numPersonas > 0) {
                precioTotalElement.value = (precioPorPersona * numPersonas).toFixed(2);
            } else {
                precioTotalElement.value = '0.00';
            }
        }

        // Función para actualizar todos los mensajes y el estado del botón
        function validateFormAndSetButtonState() {
            let canSubmit = true; // Asumir verdadero inicialmente

            const currentNumPersonas = parseInt(numPersonasInput.value);
            const fechaTourObj = new Date(fechaSalidaUnicaTour + 'T00:00:00'); // Convertir a objeto Date
            fechaTourObj.setHours(0,0,0,0);

            const today = new Date();
            today.setHours(0, 0, 0, 0);

            console.log("fechaSalidaUnicaTour:", fechaSalidaUnicaTour);
            console.log("numPersonas:", currentNumPersonas);
            console.log("plazasDisponiblesParaUnicaFecha:", plazasDisponiblesParaUnicaFecha);

            // --- 1. Verificar reserva confirmada existente (máxima prioridad) ---
            if (usuarioLogueadoId && usuarioTieneReservaActivaGeneral && !reservaACargar) {
                reembolsoInfoDiv.textContent = '¡Atención! Ya tienes una reserva confirmada activa. No puedes hacer más reservas en este momento.';
                reembolsoInfoDiv.className = 'mt-2 p-2 rounded alert alert-info';
                canSubmit = false;
                submitButton.textContent = 'Ya tienes una reserva activa';
                submitButton.classList.remove('btn-primary');
                submitButton.classList.add('btn-secondary');

                document.querySelectorAll('#reservationForm input:not([type="hidden"]), #reservationForm select, #reservationForm textarea').forEach(element => {
                    element.setAttribute('readonly', 'readonly');
                    element.setAttribute('disabled', 'disabled');
                });
                submitButton.disabled = true;

                // Redirigir con mensaje si no se ha hecho ya
                const messageParam = new URLSearchParams(window.location.search).get('message');
                if (messageParam !== 'user_has_active_reservation') {
                     window.location.href = window.location.pathname + '?tour_id=' + encodeURIComponent(tourId) + '&message=user_has_active_reservation';
                }
                return;
            } else if (reservaACargar) {
                reembolsoInfoDiv.textContent = 'Procesando pago para reserva existente.';
                reembolsoInfoDiv.className = 'mt-2 p-2 rounded alert alert-info';
                submitButton.textContent = 'Continuar al Pago';
            } else { // Si no hay reserva activa o es una nueva reserva
                reembolsoInfoDiv.className = 'mt-2 p-2 rounded';
                reembolsoInfoDiv.style.backgroundColor = '#f0f8ff';
                reembolsoInfoDiv.style.borderColor = '#cce5ff';
                let mensajeReembolso = '';

                // --- 2. Verificación de fecha del tour (si ya pasó) ---
                if (fechaTourObj < today) {
                    mensajeReembolso = '¡Lo sentimos! Este tour ya ha salido.';
                    reembolsoInfoDiv.classList.add('alert', 'alert-danger');
                    reembolsoInfoDiv.style.backgroundColor = '#f8d7da';
                    reembolsoInfoDiv.style.borderColor = '#f5c6cb';
                    canSubmit = false;
                    plazasDisponiblesMsg.textContent = 'Este tour ya no está disponible para reservas.';
                    plazasDisponiblesMsg.style.color = 'red';
                } else {
                    // --- 3. Verificación de plazas y mensaje de reembolso para fechas futuras ---
                    if (plazasDisponiblesParaUnicaFecha <= 0) {
                        mensajeReembolso = '¡Lo sentimos! Ya no quedan plazas disponibles para este tour.';
                        reembolsoInfoDiv.classList.add('alert', 'alert-danger');
                        reembolsoInfoDiv.style.backgroundColor = '#f8d7da';
                        reembolsoInfoDiv.style.borderColor = '#f5c6cb';
                        canSubmit = false;
                        plazasDisponiblesMsg.textContent = 'Tour completo. No hay plazas disponibles.';
                        plazasDisponiblesMsg.style.color = 'red';
                    } else if (currentNumPersonas > plazasDisponiblesParaUnicaFecha) {
                        mensajeReembolso = 'El número de personas excede la capacidad disponible. Solo quedan ' + plazasDisponiblesParaUnicaFecha + ' plazas.';
                        reembolsoInfoDiv.classList.add('alert', 'alert-warning');
                        reembolsoInfoDiv.style.backgroundColor = '#fff3cd';
                        reembolsoInfoDiv.style.borderColor = '#ffeeba';
                        canSubmit = false;
                        plazasDisponiblesMsg.textContent = 'Solo quedan ' + plazasDisponiblesParaUnicaFecha + ' plazas disponibles.';
                        plazasDisponiblesMsg.style.color = 'orange';
                    } else {
                        // Aún hay plazas
                        const diffTime = fechaTourObj.getTime() - today.getTime();
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

                        if (diffDays >= 15) {
                            mensajeReembolso = 'Tienes opción a reembolso por cancelación si cancelas con al menos 15 días de antelación a la fecha de salida oficial.';
                            reembolsoInfoDiv.classList.add('alert', 'alert-success');
                            reembolsoInfoDiv.style.backgroundColor = '#d4edda';
                            reembolsoInfoDiv.style.borderColor = '#c3e6cb';
                        } else {
                            mensajeReembolso = 'No hay opción a reembolso por cancelaciones con menos de 15 días de antelación a la fecha de salida oficial.';
                            reembolsoInfoDiv.classList.add('alert', 'alert-danger');
                            reembolsoInfoDiv.style.backgroundColor = '#f8d7da';
                            reembolsoInfoDiv.style.borderColor = '#f5c6cb';
                        }
                        //se guarda en la variable que se definió arriba 
                        plazasDisponiblesMsg.innerHTML = `¡Genial! Aún quedan <strong>${plazasDisponiblesParaUnicaFecha} plazas disponibles</strong> para este tour.`;
                        plazasDisponiblesMsg.style.color = 'green';
                    }
                }
                reembolsoInfoDiv.textContent = mensajeReembolso;

                // Restablecer el texto del botón si no es una reserva cargada
                if (!reservaACargar) {
                    submitButton.textContent = 'Confirmar Reserva y Pagar';
                    submitButton.classList.remove('btn-secondary');
                    submitButton.classList.add('btn-primary');
                }
            }

            // Aplicación del estado final del botón
            submitButton.disabled = !canSubmit;
        }

        // EVENTOS
        numPersonasInput.addEventListener('input', function() {
            calcularPrecioTotal();
            validateFormAndSetButtonState();
        });

        // Llamadas iniciales para configurar el estado del formulario
        calcularPrecioTotal();
        validateFormAndSetButtonState();

        submitButton.addEventListener('click', function(event) {
            event.preventDefault();
            // Volver a ejecutar la validación inmediatamente antes del envío
            validateFormAndSetButtonState();
            if (submitButton.disabled) {
                return;
            }

            if (reservaACargar) {
                document.getElementById('reservationForm').submit(); 
                return;
            }
            // Validación final del número de personas y disponibilidad
            const currentNumPersonas = parseInt(numPersonasInput.value);
            const url = `/get-tour-availability.php?tour_id=${tourId}&fecha_reserva=${fechaSalidaUnicaTour}`;

            fetch(url)
            .then(response => {
                if (!response.ok) {
                    console.error("DEBUG JS: Error de respuesta HTTP:", response.status, response.statusText);
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                //devuelvo al cliente la respuesta en formato JSON 
                return response.json();
            })
            //lo recorro 
            .then(data => {
                if (data.error) {
                    alert("Error al obtener disponibilidad del tour: " + data.error);
                } else {
                    if (data.plazas_disponibles >= currentNumPersonas) {
                        document.getElementById('reservationForm').submit();
                    } else {
                        alert("Se ha producido un cambio en la disponibilidad. No hay suficientes plazas disponibles para la fecha seleccionada. Plazas disponibles: " + data.plazas_disponibles + ". Por favor, reduce el número de personas o elige otra fecha.");
                        // Vuelve a validar para actualizar mensajes y estado del botón.
                        validateFormAndSetButtonState();
                    }
                }
            })
            .catch(error => {
                alert("Error de red o comunicación al obtener disponibilidad del tour.");
                console.error("Error:", error);
            });
        });
    });
</script>
</body>
</html>
<?php
// Cerrar la conexión a la base de datos al final
if ($conn) {
    $conn->close();
}
?>