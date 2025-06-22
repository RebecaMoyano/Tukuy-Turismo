<?php
include("conexion.php");
include("sesion.php");
include('tours_data.php'); 

if (!isset($_SESSION['id_usuario'])){
    header("Location:login.php?message=login_required");
    exit();
}

$id_usuario_session = $_SESSION['id_usuario'];

$tour_id = $_POST['tour_id'] ?? null;
$id_usuario = $_POST['id_usuario'] ?? null;
$nombre = $_POST['nombre'] ?? null;
$email = $_POST['email'] ?? null;
$telefono = $_POST['telefono'] ?? null;
$fecha_reserva = $_POST['fecha_reserva'] ?? null; 
$num_personas = $_POST['num_personas'] ?? null;
$precio_total = $_POST['precio_total_reserva'] ?? null;
$id_reserva_existente = $_POST['id_reserva_existente'] ?? null; 

$num_personas = (int)$num_personas;
$precio_total = (float)$precio_total;

if($num_personas < 1){
    header("Location:reserva_form.php?message=cantidad_viajeros_invalida&tour_id=".urlencode($tour_id).
        "&nombre=".urlencode($nombre)."&email=".urlencode($email)."&telefono=".urlencode($telefono).
        "&num_personas=".urlencode($num_personas)."&precio_total_reserva=".urlencode($precio_total));
    exit();
}

if (empty($tour_id) || empty($id_usuario) || empty($nombre) || empty($email) || empty($telefono) || empty($fecha_reserva) || !is_numeric($precio_total) || $precio_total <= 0){
    header("Location:reserva_form.php?message=datos_incompletos&tour_id=".urlencode($tour_id).
        "&nombre=".urlencode($nombre)."&email=".urlencode($email)."&telefono=".urlencode($telefono).
        "&num_personas=".urlencode($num_personas)."&precio_total_reserva=".urlencode($precio_total));
    exit();
}

$current_tour_details = null;
foreach ($tours as $tour_item) {
    if ($tour_item['id'] === $tour_id) {
        $current_tour_details = $tour_item;
        break;
    }
}

if (!$current_tour_details) {
    header("Location: index.php?message=error_tour_no_encontrado");
    exit();
}

$fecha_salida_unica_tour = $current_tour_details['fecha_salida_unica'] ?? null;
$capacidad_tour = $current_tour_details['capacidad'] ?? 0;


if ($fecha_reserva !== $fecha_salida_unica_tour) {
    header("Location: reserva_form.php?tour_id=" . urlencode($tour_id) . "&message=error_generico&debug_message=fecha_no_coincide");
    exit();
}

$today = new DateTime();
$today->setTime(0,0,0); 
try {
    $fechaTourDT = new DateTime($fecha_salida_unica_tour);
    $fechaTourDT->setTime(0,0,0); 
} catch (Exception $e) {
    header("Location: reserva_form.php?tour_id=" . urlencode($tour_id) . "&message=error_generico&debug_message=fecha_invalida_tour");
    exit();
}


if ($fechaTourDT < $today) {
    header("Location: reserva_form.php?tour_id=" . urlencode($tour_id) . "&message=tour_ya_paso");
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
        error_log("Error al preparar la consulta: " . $conn->error);
    }
    return $plazasOcupadas;
}

//una vez que ya tengo las plazas ocupadas, resto con la capacidad del tour
$plazasOcupadas = obtenerPlazasOcupadasParaTourYFecha($conn, $tour_id, $fecha_salida_unica_tour);
$plazasDisponibles = max(0, $capacidad_tour - $plazasOcupadas);

if ($num_personas > $plazasDisponibles) {
    header("Location: reserva_form.php?tour_id=" . urlencode($tour_id) . "&message=cap_max_excedida&plazas_disponibles=" . $plazasDisponibles);
    exit();
}

$fecha_limite_cancelacion_reembolso_obj = clone $fechaTourDT;
$fecha_limite_cancelacion_reembolso_obj->modify('-15 days'); //modfico la fecha 
$fecha_limite_cancelacion_reembolso_bbdd = $fecha_limite_cancelacion_reembolso_obj->format('Y-m-d'); //formateo para 
//que se acepten fechas en la base de datos

$estado_reserva_confirmada = "CONFIRMADA"; 
$estado_reserva_pendiente = "PENDIENTE";
$fecha_limite_pago = (new DateTime())->modify('+2 days'); 
$fecha_limite_pago_bbdd = $fecha_limite_pago->format('Y-m-d');
$hora_reserva =  date('H:i:s');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seleccionar Método de Pago</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <style>
        .payment-option {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            background-color: #f9f9f9;
        }
        .payment-option:hover {
            background-color: #e9e9e9;
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.2);
        }
        .payment-option.selected {
            border-color: #007bff;
            box-shadow: 0 0 10px rgba(0, 123, 255, 0.3);
            background-color: #e0f7fa;
        }
        .payment-option .icon {
            font-size: 2em;
            margin-right: 15px;
            color: #007bff;
        }
        .payment-details {
            border: 1px dashed #ced4da;
            padding: 15px;
            border-radius: 8px;
            background-color: #f8f9fa;
        }
        .summary-box {
            background-color: #e9f7ef;
            border: 1px solid #c3e6cb;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
        }
    </style>
</head>
<body>
    <?php include("includes/navigation.php"); ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-9">
                <div class="card shadow-lg" style="max-width:900px;">
                    <div class="card-header bg-primary text-white text-center">
                        <h2 class="mb-0"><i class="fas fa-credit-card"></i> Selecciona tu método de Pago</h2>
                    </div>
                    <div class="card-body p-4">
                       <?php if (isset($_GET['message']) && $_GET['message'] === 'error_pago'): ?>
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle"></i> Error al procesar el pago. Por favor, intenta de nuevo.
                                <?php if (isset($_GET['debug_message'])): ?>
                                    <br><small><?php echo htmlspecialchars($_GET['debug_message']); ?></small>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['message']) && $_GET['message'] === 'datos_tarjeta_incompletos'): ?>
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle"></i> Por favor, completa todos los datos de la tarjeta.
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['message']) && $_GET['message'] === 'numero_cvv_erroneo'): ?>
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle"></i> El CVV es inválido. Debe ser numérico (3 o 4 dígitos).
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['message']) && $_GET['message'] === 'no_seleccion_metodo_pago'): ?>
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle"></i> Por favor, selecciona un método de pago.
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['message']) && $_GET['message'] === 'datos_incompletos'): ?>
                            <div class="alert alert-warning text-center">
                                <i class="fas fa-exclamation-triangle"></i> Faltan datos esenciales de la reserva.
                            </div>
                        <?php endif; ?>
                        <?php if (isset($_GET['message']) && $_GET['message'] === 'fallo_reserva'): ?>
                            <div class="alert alert-danger text-center">
                                <i class="fas fa-exclamation-triangle"></i> Hubo un error al intentar guardar tu reserva. Por favor, inténtalo de nuevo.
                            </div>
                        <?php endif; ?>

                        <div class="summary-box text-center mb-4">
                            <h4>Resumen de tu Reserva</h4>
                            <p class='mb-1'>A nombre de: <strong><?php echo htmlspecialchars($nombre); ?></strong></p>
                            <p class="mb-1">Tour: <strong><?php echo htmlspecialchars($current_tour_details['nombre'] ?? $tour_id); ?></strong></p>
                            <p class="mb-1">Fecha del Tour: <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($fecha_salida_unica_tour))); ?></strong></p>
                            <p class="mb-1">Número de Personas: <strong><?php echo htmlspecialchars($num_personas); ?></strong></p>
                            <!--decimales, separados decimales, separados miles-->
                            <h3 class="mt-3">Total a Pagar: <span class="text-success"><?php echo number_format($precio_total, 2, ',', '.'); ?></span> € </h3>
                            <p class="mb-1 small text-muted">Fecha Límite para Cancelación con Reembolso: <strong><?php echo htmlspecialchars(date('d/m/Y', strtotime($fecha_limite_cancelacion_reembolso_bbdd))); ?></strong></p>
                        </div>

                        <form action="process_payment.php" method="POST" id="paymentForm">
                            <?php $fecha_limite_pago_confirmada = "0000-00-00";?> 
                            <input type="hidden" name="tour_id" value="<?php echo htmlspecialchars($tour_id); ?>">
                            <input type="hidden" name="id_usuario" value="<?php echo htmlspecialchars($id_usuario); ?>">
                            <input type="hidden" name="nombre" value="<?php echo htmlspecialchars($nombre); ?>">
                            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                            <input type="hidden" name="telefono" value="<?php echo htmlspecialchars($telefono); ?>">
                            <input type="hidden" name="fecha_reserva" value="<?php echo htmlspecialchars($fecha_salida_unica_tour); ?>">
                            <input type="hidden" name="fecha_salida_tour_real" value="<?php echo htmlspecialchars($fecha_salida_unica_tour); ?>">
                            <input type="hidden" name="num_personas" value="<?php echo htmlspecialchars($num_personas); ?>">
                            <input type="hidden" name="precio_total" value="<?php echo htmlspecialchars($precio_total); ?>">
                            <input type="hidden" name="estado_reserva_initial" value="<?php echo htmlspecialchars($estado_reserva_confirmada); ?>">
                            <input type="hidden" name="fecha_limite_pago" value="<?php echo htmlspecialchars($fecha_limite_pago_confirmada); ?>">
                            <input type="hidden" name="fecha_limite_cancelacion_reembolso" value="<?php echo htmlspecialchars($fecha_limite_cancelacion_reembolso_bbdd); ?>">
                            <input type="hidden" name="hora_reserva" value="<?php echo htmlspecialchars($hora_reserva); ?>">

                            <?php if ($id_reserva_existente): ?>
                            <input type="hidden" name="id_reserva" value="<?php echo htmlspecialchars($id_reserva_existente); ?>">
                            <?php endif; ?>

                            <div class="payment-option d-flex align-items-center mb-3" data-method="card">
                                <i class="icon fas fa-credit-card"></i>
                                <div>
                                    <h6 class="mb-0">Tarjeta de Crédito/Débito</h6>
                                    <small class="text-muted">Paga con Visa, Mastercard, American Express.</small>
                                </div>
                            </div>
                            <div id="card-details" class="payment-details d-none mb-4">
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" id="card_number" name="numero_tarjeta" placeholder="XXXX XXXX XXXX XXXX" required disabled
                                    pattern="[0-9]{16}" title="El número de tarjeta debe tener 16 dígitos numéricos">
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_date" class="form-label">Fecha de Caducidad (MM/AA)</label>
                                        <input type="text" class="form-control" id="expiry_date" name="fecha_expiracion" placeholder="MM/AA" required disabled
                                        title="Formato: MM/AA por ejmplo: 12/28" maxlength="5">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="XXX" required disabled pattern="[0-9]{3,4}" title="El cvv
                                        debe ser de 3 o 4 dígitos numéricos" maxlength="4">
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="card_name" class="form-label">Nombre del Titular de la Tarjeta</label>
                                    <input type="text" class="form-control" id="card_name" name="nombre_titular" required disabled pattern="[A-Za-zñÑáéíóúÁÉÍÓÚ\s]+" title="El nombre solo debe contener
                                    letras y espacios" maxlength="50">
                                </div>
                            </div>

                            <input type="hidden" name="selected_payment_method" id="selected_payment_method" required>
                            <button type="submit" class="btn btn-success w-100" id="payButton">CONFIRMAR PAGO</button> 
                            <a class='btn btn-primary mt-2 w-100' id='pagarDespuesLink' href='pagarDespues.php?tour_id=<?php echo urlencode($tour_id); ?>&id_usuario=<?php echo urlencode($id_usuario); ?>&nombre=<?php echo urlencode($nombre); ?>&email=<?php echo urlencode($email); ?>&telefono=<?php echo urlencode($telefono); ?>&fecha_reserva=<?php echo urlencode($fecha_salida_unica_tour); ?>&num_personas=<?php echo urlencode($num_personas); ?>&precio_total_reserva=<?php echo urlencode($precio_total); ?>&fecha_salida_tour_real=<?php echo urlencode($fecha_salida_unica_tour); ?>&fecha_limite_cancelacion_reembolso=<?php echo urlencode($fecha_limite_cancelacion_reembolso_bbdd); ?>&hora_reserva=<?php echo urlencode($hora_reserva);?>&estado_reserva=<?php echo urlencode($estado_reserva_pendiente);?>&fecha_limite_pago=<?php echo urlencode($fecha_limite_pago_bbdd);?>'>
                            PAGAR DESPUES
                            </a>
                            <a class='btn btn-secondary mt-2 w-100' href='reserva_form.php?tour_id=<?php echo urlencode($tour_id);?>&message=payment_canceled'>VOLVER</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include("includes/footer.php"); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
        const paymentOptions = document.querySelectorAll('.payment-option');
        const selectedPaymentMethodInput = document.getElementById('selected_payment_method'); 

        paymentOptions.forEach(option => {
            option.addEventListener('click', function() {
                paymentOptions.forEach(div => {
                    div.classList.remove('selected');
                    const detailsDivId = div.dataset.method + '-details';
                    const detailsDiv = document.getElementById(detailsDivId);
                    if (detailsDiv) {
                        detailsDiv.classList.add('d-none');
                        detailsDiv.querySelectorAll('input').forEach(input => input.disabled = true);
                        detailsDiv.querySelectorAll('input').forEach(input => input.removeAttribute('required'));
                    }
                });

                this.classList.add('selected');
                const selectedMethod = this.dataset.method;
                selectedPaymentMethodInput.value = selectedMethod; 

                const currentDetailsDiv = document.getElementById(selectedMethod + '-details');
                if (currentDetailsDiv) {
                    currentDetailsDiv.classList.remove('d-none');
                    currentDetailsDiv.querySelectorAll('input').forEach(input => {
                        input.disabled = false;
                        if (selectedMethod === 'card') {
                            input.setAttribute('required', 'required');
                        }
                    });
                }
            });
        });

        //nombre titular de la tarjeta
        const cardNameInput = document.getElementById('card_name');
        if (cardNameInput) {
            cardNameInput.pattern = "[A-Za-zñÑáéíóúÁÉÍÓÚ\\s]+";
            cardNameInput.title = "El nombre solo debe contener letras y espacios.";
        }
        //recojo el 'link' 
        const pagarDespuesLink = document.getElementById('pagarDespuesLink');

        if (pagarDespuesLink) {
            pagarDespuesLink.addEventListener('click', function(event) {
                //detengo la accion por defecto del enlace 
                event.preventDefault(); 
                //recojo el valor del input hidden que contiene el metodo de pago seleccionado
                const selectedMethod = selectedPaymentMethodInput.value; 
                //actuo sobre el link de pagarDespues y limpio por si hubo otro metodo de pago 
                let currentHref = this.href.split('&payment_method=')[0]; 
                
                if (selectedMethod) {
                    //lo vuelvo a construir y rellenar 
                    this.href = currentHref + '&payment_method=' + encodeURIComponent(selectedMethod);
                    window.location.href = this.href; 
                } else {
                    window.location.href = 'index.php?message=Debe_seleccionar_un_metodo_de_pago'; 
                }
            });
        }
    });
    </script>
</body>
</html>