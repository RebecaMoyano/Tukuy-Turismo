<?php
$servidor = "localhost"; 
$usuario = "root";       
$password = "";        
$bbdd = "tukuy_db";      

$conn = mysqli_connect($servidor, $usuario, $password, $bbdd);
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>