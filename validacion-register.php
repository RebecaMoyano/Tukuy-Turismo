<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

include('conexion.php');

$conn = new mysqli($servidor, $usuario, $password, $bbdd);

if ($conn->connect_error) {
    $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php';
    header("Location: login.php?action=register&message=db_connection_error&redirect_to=" . urlencode($redirect_to));
    exit();
}

$conn->set_charset("utf8mb4");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php';

    if (isset($_POST['btn-register'])) {
        $reg_nombre = trim($_POST['reg_nombre']);
        $reg_apellido = trim($_POST['reg_apellido']);
        $reg_telefono = $_POST['reg_telefono'] ?? '';
        $reg_email = $_POST['reg_email'];
        $reg_pass = $_POST['reg_pass'];
        $reg_pass_confirm = $_POST['reg_pass_confirm'];

        if ($reg_pass !== $reg_pass_confirm) {
            header("Location: login.php?action=register&message=passwords_no_coinciden&redirect_to=" . urlencode($redirect_to));
            exit();
        }

        $stmt_check_email = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
        $stmt_check_email->bind_param("s", $reg_email);
        $stmt_check_email->execute();
        $result_check_email = $stmt_check_email->get_result();

        if ($result_check_email->num_rows > 0) {
            header("Location: login.php?action=register&message=email_existente&redirect_to=" . urlencode($redirect_to));
            exit();
        }

        if (strlen($reg_nombre) < 3 || strlen($reg_apellido) < 3 || strlen($reg_nombre) > 50 || strlen($reg_apellido) > 50 || empty($reg_nombre) || empty($reg_apellido)) {
            header("Location: login.php?action=register&message=nombre_apellido_invalido&redirect_to=" . urlencode($redirect_to));
            exit();
        }

        $hashed_password = password_hash($reg_pass, PASSWORD_DEFAULT);

        $stmt_insert_user = $conn->prepare("INSERT INTO usuarios (nombre, apellido, email, telefono, password) VALUES (?, ?, ?, ?, ?)");
        $stmt_insert_user->bind_param("sssss", $reg_nombre, $reg_apellido, $reg_email, $reg_telefono, $hashed_password);

        if ($stmt_insert_user->execute()) {
            header("Location: login.php?action=login&message=registro_exitoso&redirect_to=" . urlencode($redirect_to));
            exit();
        } else {
            header("Location: login.php?action=register&message=error_registro&redirect_to=" . urlencode($redirect_to));
            exit();
        }

        $stmt_check_email->close();
        $stmt_insert_user->close();
    }
}
$conn->close();
?>