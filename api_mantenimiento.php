<?php
ob_start();
require_once __DIR__ . '/conexion.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$respuesta = ['status' => 'error', 'mensaje' => 'Acción no válida.'];

try {
    switch ($accion) {
        case 'obtener':
            $sql = "SELECT * FROM reportes_mantenimiento ORDER BY fecha DESC";
            $resultado = $conexion->query($sql);
            
            $reportes = [];
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $reportes[] = $fila;
                }
            }
            $respuesta = ['status' => 'exito', 'data' => $reportes];
            break;

        case 'guardar':
            $area = $_POST['area'] ?? '';
            $urgencia = $_POST['urgency'] ?? '';
            $descripcion = $_POST['description'] ?? '';
            $fecha = $_POST['date'] ?? '';

            if (empty($area) || empty($descripcion)) {
                throw new Exception('Datos incompletos.');
            }

            $stmt = $conexion->prepare("INSERT INTO reportes_mantenimiento (area, urgencia, descripcion, fecha) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $area, $urgencia, $descripcion, $fecha);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Reporte creado.'];
            } else {
                throw new Exception('Error SQL: ' . $stmt->error);
            }
            $stmt->close();
            break;
    }
} catch (Exception $e) {
    $respuesta = ['status' => 'error', 'mensaje' => $e->getMessage()];
}

if (isset($conexion)) $conexion->close();
enviarRespuestaJSON($respuesta);
?>