<?php
ob_start();
require_once __DIR__ . '/conexion.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$respuesta = ['status' => 'error', 'mensaje' => 'Acción no válida.'];

try {
    switch ($accion) {
        case 'obtener':
            $sql = "SELECT * FROM citas ORDER BY fecha ASC, hora ASC";
            $resultado = $conexion->query($sql);
            
            $citas = [];
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $fila['id_proveedor'] = (int)$fila['id_proveedor'];
                    $citas[] = $fila;
                }
            }
            $respuesta = ['status' => 'exito', 'data' => $citas];
            break;

        case 'guardar':
            $id_proveedor = $_POST['supplierId'] ?? null;
            $fecha = $_POST['date'] ?? null;
            $hora = $_POST['time'] ?? null;
            $tipo = $_POST['type'] ?? null;
            $descripcion = $_POST['description'] ?? ''; 
            $estado = $_POST['status'] ?? 'programada';

            if (empty($id_proveedor) || empty($fecha) || empty($hora)) {
                throw new Exception('Faltan datos obligatorios.');
            }

            
            $id_proveedor = (int)$id_proveedor;

            $stmt = $conexion->prepare("INSERT INTO citas (id_proveedor, fecha, hora, tipo, descripcion, estado) VALUES (?, ?, ?, ?, ?, ?)");
            if (!$stmt) throw new Exception("Error SQL: " . $conexion->error);

            $stmt->bind_param("isssss", $id_proveedor, $fecha, $hora, $tipo, $descripcion, $estado);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'id' => $conexion->insert_id, 'mensaje' => 'Cita agendada correctamente.'];
            } else {
                throw new Exception('Error al guardar: ' . $stmt->error);
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