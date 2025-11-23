<?php
ob_start();
require_once __DIR__ . '/conexion.php';

$accion = $_POST['accion'] ?? $_GET['accion'] ?? '';
$respuesta = ['status' => 'error', 'mensaje' => 'Acción no válida.'];

try {
    switch ($accion) {
        case 'obtener':
            $sql = "SELECT id, username, role, nombre FROM usuarios";
            $resultado = $conexion->query($sql);
            
            $usuarios = [];
            if ($resultado) {
                while ($fila = $resultado->fetch_assoc()) {
                    $usuarios[$fila['username']] = [
                        'id' => $fila['id'],
                        'role' => $fila['role'],
                        'name' => $fila['nombre']
                    ];
                }
            }
            $respuesta = ['status' => 'exito', 'data' => $usuarios];
            break;

        case 'cambiar_pass':
            $username = $_POST['username'] ?? '';
            $new_password = $_POST['new_password'] ?? '';

            if (empty($username) || empty($new_password)) {
                throw new Exception('Faltan datos.');
            }

            $stmt = $conexion->prepare("UPDATE usuarios SET password = ? WHERE username = ?");
            $stmt->bind_param("ss", $new_password, $username);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $respuesta = ['status' => 'exito', 'mensaje' => 'Contraseña actualizada.'];
                } else {
                    $respuesta = ['status' => 'error', 'mensaje' => 'Usuario no encontrado o contraseña igual a la anterior.'];
                }
            } else {
                throw new Exception('Error SQL: ' . $stmt->error);
            }
            $stmt->close();
            break;
            
        case 'login':
            $user = $_POST['username'] ?? '';
            $pass = $_POST['password'] ?? '';
            
            $stmt = $conexion->prepare("SELECT * FROM usuarios WHERE username = ? AND password = ?");
            $stmt->bind_param("ss", $user, $pass);
            $stmt->execute();
            $res = $stmt->get_result();
            
            if ($res->num_rows > 0) {
                $datos = $res->fetch_assoc();
                $respuesta = [
                    'status' => 'exito', 
                    'mensaje' => 'Login correcto', 
                    'username' => $datos['username'],
                    'role' => $datos['role'], 
                    'nombre' => $datos['nombre']
                ];
            } else {
                $respuesta = ['status' => 'error', 'mensaje' => 'Credenciales incorrectas'];
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