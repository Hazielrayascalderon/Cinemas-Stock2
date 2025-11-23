<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$servidor = "sql213.infinityfree.com"; 
$usuario = "if0_40344903";
$password = "Karlam890";
$base_de_datos = "if0_40344903_cinemas_wtc";

echo "<h1>Diagnóstico de Conexión</h1>";
echo "<p>Intentando conectar a: <strong>$servidor</strong> con usuario <strong>$usuario</strong>...</p>";

$mysqli = new mysqli($servidor, $usuario, $password, $base_de_datos);

if ($mysqli->connect_error) {
    die("<h2 style='color:red'>FALLÓ LA CONEXIÓN: " . $mysqli->connect_error . "</h2><p>Revisa el nombre del servidor ('MySQL Host Name') en tu panel de InfinityFree.</p>");
}

echo "<h2 style='color:green'>¡CONEXIÓN EXITOSA!</h2>";

$sql = "SELECT * FROM usuarios WHERE username = 'admin'";
$resultado = $mysqli->query($sql);

if ($resultado) {
    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        echo "<h3 style='color:blue'>Usuario 'admin' ENCONTRADO.</h3>";
        echo "<ul>";
        echo "<li>Contraseña en BD: <strong>" . $fila['password'] . "</strong></li>";
        echo "<li>Rol: <strong>" . $fila['role'] . "</strong></li>";
        echo "</ul>";
        echo "<p>Si aquí dice '1234', el login DEBE funcionar.</p>";
    } else {
        echo "<h3 style='color:orange'>La tabla existe, pero el usuario 'admin' NO existe.</h3>";
        echo "<p>Ejecuta el INSERT en phpMyAdmin.</p>";
    }
} else {
    echo "<h3 style='color:red'>Error en la tabla: " . $mysqli->error . "</h3>";
    echo "<p>Seguramente la tabla 'usuarios' no se creó. Ejecuta el script SQL completo en phpMyAdmin.</p>";
}

$mysqli->close();
?>