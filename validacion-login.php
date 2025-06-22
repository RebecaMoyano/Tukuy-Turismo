<?php
session_start(); 

include('conexion.php');

$conn = new mysqli($servidor, $usuario, $password, $bbdd);

if ($conn->connect_error) {
    $redirect_to = isset($_POST['redirect_to']) ? $_POST['redirect_to'] : 'index.php'; // Obtener el redirect_to incluso si falla la DB
    header("Location: login.php?action=login&message=db_connection_error&redirect_to=" . urlencode($redirect_to));
    exit();
}

$conn->set_charset("utf8mb4");


if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $redirect_to = isset($_POST['redirect_to']) ? urldecode($_POST['redirect_to']) : 'index.php';

    if (isset($_POST['btn-login'])) {
        $email = $_POST['email'];
        $password = $_POST['pass'];

        $stmt = $conn->prepare("SELECT id_usuario, email, nombre, telefono,password FROM usuarios WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($fila = $resultado->fetch_assoc()) {
            if (password_verify($password, $fila['password'])) {
                $_SESSION['loggedin'] = true; 
                $_SESSION['id_usuario'] = $fila['id_usuario'];
                $_SESSION['usuario_email'] = $fila['email'];
                $_SESSION['nombre_usuario'] = $fila['nombre'];
                $_SESSION['telefono_usuario'] = $fila['telefono']; 
                header("Location: " . $redirect_to);
                exit();
            } else {
                header("Location: login.php?action=login&message=error_credenciales&redirect_to=" . urlencode($redirect_to));
                exit();
            }
        } else {
            header("Location: login.php?action=login&message=error_credenciales&redirect_to=" . urlencode($redirect_to));
            exit();
        }
    }
}
$conn->close();
?>