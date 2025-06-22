<?php
session_start();
include('conexion.php');
include('tours_data.php'); 

$id_reserva = $_GET['id_reserva'] ?? null; // identificador de la reserva
$reserva_details = null;
$tour_nombre = '';

// Si no se recibe un ID de reserva, redirigir
if (!$id_reserva) {
    header("Location: index.php?message=no_reserva_id_for_payment");
    exit();
}

// Verificar que el usuario esté logueado, es crucial para la seguridad
if (!isset($_SESSION['id_usuario'])){
    header("Location: login.php?message=necesita_login&redirect_to=" . urlencode("payment_method.php?id_reserva=" . $id_reserva));
    exit();
}

// Conectar a la base de datos
$conn = new mysqli($servidor, $usuario, $password, $bbdd);

if ($conn->connect_error) {
    error_log("Error de conexión a la base de datos en payment_method.php: " . $conn->connect_error);
    header("Location: index.php?message=db_error");
    exit();
}
$conn->set_charset("utf8mb4");

try {
    //selecciono la reserva para ese usuario en concreto 
    $stmt = $conn->prepare("SELECT * FROM reservas WHERE id_reserva = ? AND id_usuario = ?");
    if (!$stmt) {
        throw new Exception("Error al preparar la consulta de reserva: " . $conn->error);
    }
    $stmt->bind_param("ii", $id_reserva, $_SESSION['id_usuario']);
    $stmt->execute();
    $result = $stmt->get_result();
    $reserva_details = $result->fetch_assoc();
    $stmt->close();

    if (!$reserva_details){
        header("Location: user-reservas.php?message=reserva_no_encontrada");
        exit();
    }

    $current_date = (new DateTime())->format('Y-m-d');
    $fecha_limite_pago = $reserva_details['fecha_limite_pago'];
    $estado_reserva = $reserva_details['estado_reserva'];

    if ($estado_reserva === 'CONFIRMADA') {
        header("Location: user-reservas.php?message=reserva_ya_confirmada&id_reserva=" . $id_reserva);
        exit();
    } elseif ($estado_reserva === 'EXPIRADA' || $current_date > $fecha_limite_pago) {
        if ($estado_reserva === 'PENDIENTE') {
            $stmt_expire = $conn->prepare("UPDATE reservas SET estado_reserva = 'EXPIRADA' WHERE id_reserva = ?");
            $stmt_expire->bind_param("i", $id_reserva);
            $stmt_expire->execute();
            $stmt_expire->close();
        }
        header("Location: user-reservas.php?message=reserva_expirada&id_reserva=" . $id_reserva);
        exit();
    } elseif ($estado_reserva !== 'PENDIENTE') {
        header("Location: user-reservas.php?message=estado_no_valido_para_pago&id_reserva=" . $id_reserva);
        exit();
    }

    $tour_data = $tours[$reserva_details['tour_id']] ?? null;
    $tour_nombre = $tour_data['nombre'] ?? 'Tour Desconocido';

} catch (Exception $e) {
    header("Location: user-reservas.php?message=error_generico");
    exit();
} finally {
    //indepenientemente de si se ha producido un error o no, cierro la conexión
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
    <title>Método de Pago - Confirmar Reserva</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <style>
        .payment-option {
            cursor: pointer;
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            border-radius: 5px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.3s ease;
        }
        .payment-option:hover {
            background-color: #f8f9fa;
        }
        .payment-option.selected {
            border-color: #007bff;
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
        }
        .payment-option img {
            max-height: 30px;
        }
        .payment-details {
            border: 1px solid #eee;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            background-color: #fdfdfd;
        }
        .summary-card {
            background-color: #e9ecef;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
        }
        .summary-card h4 {
            color: #343a40;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <?php include("includes/header.php"); ?>

    <main class="container my-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <h2 class="mb-4 text-center">Confirmar Pago de Reserva</h2>

                <?php if (isset($_GET['message'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?php
                            if ($_GET['message'] === 'pago_fallido') {
                                echo "El pago no pudo ser procesado. Por favor, inténtalo de nuevo.";
                            }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="summary-card">
                    <h4>Detalles de la Reserva</h4>
                    <p><strong>Tour:</strong> <?php echo htmlspecialchars($tour_nombre); ?></p>
                    <p><strong>Fecha de Reserva:</strong> <?php echo htmlspecialchars($reserva_details['fecha_reserva']); ?></p>
                    <p><strong>Hora:</strong> <?php echo htmlspecialchars(substr($reserva_details['hora_reserva'], 0, 5)); ?></p>
                    <p><strong>Personas:</strong> <?php echo htmlspecialchars($reserva_details['num_personas']); ?></p>
                    <hr>
                    <h5 class="text-end">Total a Pagar: <span class="text-primary">$<?php echo number_format($reserva_details['precio_total'], 2); ?></span></h5>
                </div>

                <div class="card shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title mb-3">Selecciona tu método de Pago</h5>

                        <form action="process_payment.php" method="POST" id="paymentForm">
                            <input type="hidden" name="id_reserva" value="<?php echo htmlspecialchars($id_reserva); ?>">
                            
                            <input type="hidden" name="payment_method" id="selectedPaymentMethodInput">

                            <div class="mb-3">
                                <div class="payment-option" data-method="card">
                                    <span><i class="fas fa-credit-card me-2"></i> Tarjeta de Crédito/Débito</span>
                                    <img src="img/cards.png" alt="Tarjetas">
                                </div>
                            </div>

                            <div id="card-details" class="payment-details d-none">
                                <h6 class="mb-3">Datos de la Tarjeta</h6>
                                <div class="mb-3">
                                    <label for="card_number" class="form-label">Número de Tarjeta</label>
                                    <input type="text" class="form-control" id="card_number" name="card_number" placeholder="XXXX XXXX XXXX XXXX" disabled>
                                </div>
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="expiry_date" class="form-label">Fecha de Caducidad (MM/AA)</label>
                                        <input type="text" class="form-control" id="expiry_date" name="expiry_date" placeholder="MM/AA" disabled>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="cvv" class="form-label">CVV</label>
                                        <input type="text" class="form-control" id="cvv" name="cvv" placeholder="XXX" disabled>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary mt-4 w-100" id="payButton" disabled>Continuar al Pago</button>
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
            //opciones pago 
            const paymentOptions = document.querySelectorAll('.payment-option');
            //campos 
            const paymentDetailsDivs = document.querySelectorAll('.payment-details');
            //metodo seleccionado
            const selectedPaymentMethodInput = document.getElementById('selectedPaymentMethodInput');
            //boton 
            const payButton = document.getElementById('payButton');

            paymentOptions.forEach(option => {
                option.addEventListener('click', function() {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    //habilito las opciones que son necsarias paa el metodo de pago seleccionado 
                    //deshabilito  las que no voy a necesitar 
                    paymentDetailsDivs.forEach(div => {
                        div.classList.add('d-none');
                        div.querySelectorAll('input').forEach(input => input.disabled = true);
                        div.querySelectorAll('input').forEach(input => input.removeAttribute('required')); 
                    });

                    this.classList.add('selected');
                    const selectedMethod = this.dataset.method;
                    selectedPaymentMethodInput.value = selectedMethod; 

                    const currentDetailsDiv = document.getElementById(selectedMethod + '-details');
                    if (currentDetailsDiv) {
                        currentDetailsDiv.classList.remove('d-none');
                        currentDetailsDiv.querySelectorAll('input').forEach(input => input.disabled = false);
                        if (selectedMethod === 'card') {
                            currentDetailsDiv.querySelectorAll('input').forEach(input => input.setAttribute('required', 'required'));
                        }
                    }
                    payButton.disabled = false;
                });
            });
            //si el metodo de pago fue seleccionado se permite enviar el formulario
            document.getElementById("reservationForm").addEventListener("submit", function(e) {
                const selectedPayment = document.querySelector('input[name="payment_method"]:checked');
                //si no, se previene que lo envie 
                if (!selectedPayment) {
                    e.preventDefault();
                    alert("Por favor selecciona un método de pago antes de continuar.");
                }
            });

        });
    </script>
</body>
</html>