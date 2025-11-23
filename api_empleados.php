<?php
ob_start();
require_once __DIR__ . '/conexion.php';

$accion = $_POST['accion'] ?? '';
$respuesta = ['status' => 'error', 'mensaje' => 'Acción no válida.'];

try {
    switch ($accion) {
        case 'obtener':
            $sql = "SELECT * FROM empleados ORDER BY nombre ASC";
            $resultado = $conexion->query($sql);

            $empleados = [];
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $empleados[] = $fila;
                }
            }
            $respuesta = ['status' => 'exito', 'data' => $empleados];
            break;

        case 'guardar':
            $nombre = $_POST['nombre'] ?? '';
            $puesto = $_POST['puesto'] ?? '';
            $horas = $_POST['horas'] ?? 0;

            if (empty($nombre) || empty($puesto)) {
                throw new Exception('Faltan datos: Nombre y Puesto son obligatorios.');
            }

            $stmt = $conexion->prepare("INSERT INTO empleados (nombre, puesto, horas) VALUES (?, ?, ?)");
            if (!$stmt) throw new Exception("Error preparando consulta: " . $conexion->error);

            $stmt->bind_param("ssd", $nombre, $puesto, $horas);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Empleado guardado correctamente.'];
            } else {
                throw new Exception('Error al guardar en BD: ' . $stmt->error);
            }
            $stmt->close();
            break;

        case 'actualizar':
            $id = $_POST['id'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $puesto = $_POST['puesto'] ?? '';
            $horas = $_POST['horas'] ?? 0;

            if (empty($id)) throw new Exception('No se recibió el ID para actualizar.');

            $stmt = $conexion->prepare("UPDATE empleados SET nombre = ?, puesto = ?, horas = ? WHERE id = ?");
            if (!$stmt) throw new Exception("Error preparando consulta: " . $conexion->error);

            $stmt->bind_param("ssdi", $nombre, $puesto, $horas, $id);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Empleado actualizado correctamente.'];
            } else {
                throw new Exception('Error al actualizar: ' . $stmt->error);
            }
            $stmt->close();
            break;

        case 'eliminar':
            $id = $_POST['id'] ?? '';
            if (empty($id)) throw new Exception('Falta el ID para eliminar.');

            $stmt = $conexion->prepare("DELETE FROM empleados WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Empleado eliminado.'];
            } else {
                throw new Exception('Error al eliminar: ' . $stmt->error);
            }
            $stmt->close();
            break;
            
        default:
             throw new Exception('Acción desconocida: ' . $accion);
    }

} catch (Exception $e) {
    $respuesta = ['status' => 'error', 'mensaje' => $e->getMessage()];
}

if (isset($conexion) && $conexion instanceof mysqli) {
    $conexion->close();
}

enviarRespuestaJSON($respuesta);
?>