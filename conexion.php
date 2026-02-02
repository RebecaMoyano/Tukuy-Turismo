<?php
$servidor = "sql306.infinityfree.com"; 
$usuario = ""; //contraseña vacía por seguridad       
$password = "EduxsVUdXr6";        
$bbdd = "if0_41057389_tukuy_db";      

$conn = mysqli_connect($servidor, $usuario, $password, $bbdd);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>