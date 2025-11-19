<?php
// Incluye tu archivo de conexión
require 'db_connection.php'; 

// --- 1. DATOS DEL ADMINISTRADOR ---
$nombre = 'Admin Principal';
$usuario = 'admin'; // El nombre de usuario que usarás para iniciar sesión
$password_plana = 'admin123'; // !!! CAMBIA ESTO por tu contraseña segura y fácil de recordar
$rol = 'Administrador';

// --- 2. GENERAR HASH SEGURO ---
// password_hash() es la función obligatoria que hace compatible la contraseña con password_verify()
$password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

$db = conectarDB();

// --- 3. VERIFICACIÓN Y REGISTRO ---

// Primero, verificamos si el usuario 'admin' ya existe para evitar duplicados
$check_sql = "SELECT id_empleado FROM empleados WHERE usuario = ?";
$stmt_check = $db->prepare($check_sql);
$stmt_check->bind_param("s", $usuario);
$stmt_check->execute();
$stmt_check->store_result();

if ($stmt_check->num_rows > 0) {
    echo "❌ Error: El usuario '$usuario' ya existe en la base de datos. No se realizaron cambios.";
    $stmt_check->close();
    $db->close();
    exit;
}
$stmt_check->close();


// Si no existe, procedemos a insertar
$sql = "INSERT INTO empleados (nombre, rol, usuario, password_hash) VALUES (?, ?, ?, ?)";
$stmt = $db->prepare($sql);

if ($stmt === false) {
    die("Error al preparar la consulta de inserción: " . $db->error);
}

// Vinculamos los parámetros: (s=string, s=string, s=string, s=string)
$stmt->bind_param("ssss", $nombre, $rol, $usuario, $password_hash);

if ($stmt->execute()) {
    echo "✅ **REGISTRO EXITOSO**";
    echo "<h3>Usuario: $usuario</h3>";
    echo "<h3>Contraseña: (usa la contraseña plana que definiste: $password_plana)</h3>";
    echo "<p>Rol: $rol</p>";
    echo "<p>El hash cifrado es: $password_hash</p>";
} else {
    echo "❌ Error al crear el usuario: " . $stmt->error;
}

$stmt->close();
$db->close();
?>