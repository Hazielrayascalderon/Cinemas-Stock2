<?php
mysqli_report(MYSQLI_REPORT_OFF);

$servidor = "sql213.infinityfree.com";
$usuario = "if0_40344903";
$password = "Karlam890";
$base_de_datos = "if0_40344903_cinemas_wtc";

$conexion = new mysqli($servidor, $usuario, $password, $base_de_datos);

if ($conexion->connect_error) {
    enviarRespuestaJSON([
        'status' => 'error', 
        'mensaje' => 'Error de Conexión BD: ' . $conexion->connect_error
    ]);
}

$conexion->set_charset("utf8mb4");

if (!function_exists('enviarRespuestaJSON')) {
    function enviarRespuestaJSON($datos) {
        if (ob_get_length()) ob_clean(); 
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos);
        exit; 
    }
}
?>