<?php
ob_start();
require_once __DIR__ . '/conexion.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$respuesta = ['status' => 'error', 'mensaje' => 'Acción no válida.'];

try {
    switch ($accion) {
        case 'obtener':
            $sql = "SELECT * FROM proveedores ORDER BY nombre ASC";
            $resultado = $conexion->query($sql);
            
            $proveedores = [];
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $proveedores[] = $fila;
                }
            }
            $respuesta = ['status' => 'exito', 'data' => $proveedores];
            break;

        case 'guardar':
            $nombre = $_POST['name'] ?? '';
            $contacto = $_POST['contact'] ?? '';
            $telefono = $_POST['phone'] ?? '';
            $email = $_POST['email'] ?? '';
            $especialidad = $_POST['specialty'] ?? '';

            if (empty($nombre)) throw new Exception('El nombre es obligatorio.');

            $stmt = $conexion->prepare("INSERT INTO proveedores (nombre, contacto, telefono, email, especialidad) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $nombre, $contacto, $telefono, $email, $especialidad);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Proveedor guardado.'];
            } else {
                throw new Exception('Error al guardar: ' . $stmt->error);
            }
            $stmt->close();
            break;

        case 'eliminar':
            $id = $_POST['id'] ?? '';
            if (empty($id)) throw new Exception('Falta ID.');

            $stmt = $conexion->prepare("DELETE FROM proveedores WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Proveedor eliminado.'];
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