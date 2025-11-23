<?php
ob_start();
require_once __DIR__ . '/conexion.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$respuesta = ['status' => 'error', 'mensaje' => 'Acción no válida.'];

try {
    switch ($accion) {
        case 'obtener':
            
            $sql = "SELECT * FROM inventario ORDER BY area ASC, nombre ASC";
            $resultado = $conexion->query($sql);

            $inventario = [];
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                   
                    $fila['stock_actual'] = (int)$fila['stock_actual'];
                    $fila['stock_minimo'] = (int)$fila['stock_minimo'];
                    $inventario[] = $fila;
                }
            }
            $respuesta = ['status' => 'exito', 'data' => $inventario];
            break;

        case 'guardar':
            $area = $_POST['area'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            $stock_actual = (int)($_POST['stock_actual'] ?? 0);
            $stock_minimo = (int)($_POST['stock_minimo'] ?? 0);
            $unidad = $_POST['unidad'] ?? 'unidades';

            if (empty($area) || empty($nombre)) {
                throw new Exception('Nombre y Área son obligatorios.');
            }

            $stmt = $conexion->prepare("INSERT INTO inventario (area, nombre, categoria, stock_actual, stock_minimo, unidad) VALUES (?, ?, ?, ?, ?, ?)");
            // "sssiis" = String, String, String, Int, Int, String
            $stmt->bind_param("sssiis", $area, $nombre, $categoria, $stock_actual, $stock_minimo, $unidad);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'id' => $conexion->insert_id, 'mensaje' => 'Producto agregado al inventario.'];
            } else {
                throw new Exception('Error al guardar: ' . $stmt->error);
            }
            $stmt->close();
            break;

        case 'actualizar':
            $id = $_POST['id'] ?? '';
            $nombre = $_POST['nombre'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            $stock_actual = (int)($_POST['stock_actual'] ?? 0);
            $stock_minimo = (int)($_POST['stock_minimo'] ?? 0);
            $unidad = $_POST['unidad'] ?? 'unidades';

            if (empty($id)) throw new Exception('Falta el ID para actualizar.');

            $stmt = $conexion->prepare("UPDATE inventario SET nombre=?, categoria=?, stock_actual=?, stock_minimo=?, unidad=? WHERE id=?");
            $stmt->bind_param("ssiisi", $nombre, $categoria, $stock_actual, $stock_minimo, $unidad, $id);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Producto actualizado.'];
            } else {
                throw new Exception('Error al actualizar: ' . $stmt->error);
            }
            $stmt->close();
            break;

        case 'eliminar':
            $id = $_POST['id'] ?? '';
            if (empty($id)) throw new Exception('Falta el ID para eliminar.');

            $stmt = $conexion->prepare("DELETE FROM inventario WHERE id = ?");
            $stmt->bind_param("i", $id);

            if ($stmt->execute()) {
                $respuesta = ['status' => 'exito', 'mensaje' => 'Producto eliminado.'];
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