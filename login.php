<?php
// Iniciar sesión para guardar el estado del usuario
session_start();

// Carga la conexión. **IMPORTANTE:** Este archivo debe crear la variable $conn
// que se usará en el código siguiente, sin encapsularla en una función.
require 'db_connection.php'; 

$mensaje = ''; 

// 1. Procesar el formulario POST al hacer clic en Iniciar Sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Obtener y sanear los datos
    $usuario = trim($_POST['usuario'] ?? '');
    $password = $_POST['password'] ?? '';

    // En lugar de llamar a conectarDB(), usaremos la variable $conn
    // que se creó cuando se hizo el 'require' de db_connection.php.
    // Usaremos $conn en lugar de $db.
    $db = $conn;

    // 2. Buscar al empleado por nombre de usuario (Sentencia Preparada para seguridad)
    $sql = "SELECT id_empleado, nombre, rol, password_hash FROM empleados WHERE usuario = ?";
    $stmt = $db->prepare($sql);
    
    // Verificar si la preparación falló
    if ($stmt === false) {
        // Muestra el error de la base de datos si falla la preparación
        $mensaje = "Error interno del sistema al preparar la consulta: " . $db->error;
    } else {
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $empleado = $resultado->fetch_assoc();
            
            // 3. Verificar la contraseña usando password_verify()
            if (password_verify($password, $empleado['password_hash'])) {
                
                // Contraseña correcta: Iniciar sesión y guardar datos clave
                $_SESSION['usuario_id'] = $empleado['id_empleado'];
                $_SESSION['usuario_nombre'] = $empleado['nombre'];
                $_SESSION['usuario_rol'] = $empleado['rol']; 

                // 4. LÓGICA DE REDIRECCIÓN BASADA EN EL ROL
                if ($empleado['rol'] === 'Administrador') {
                    // Redirige al dashboard de administración
                    header('Location: toma_pedido.php'); 
                } else {
                    // Redirige al módulo de toma de pedido (o cualquier otra interfaz de empleado)
                    header('Location: toma_pedido.php'); 
                }
                exit; // Detiene la ejecución después de la redirección
            } else {
                // Contraseña incorrecta
                $mensaje = "Error: Contraseña o usuario incorrectos.";
            }
        } else {
            // Usuario no encontrado
            $mensaje = "Error: Contraseña o usuario incorrectos.";
        }

        $stmt->close();
    }
    // Cerrar la conexión (utilizando la variable $conn que creó db_connection.php)
    $db->close();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso de Empleados</title>
    <style>
        body { font-family: Arial, sans-serif; display: flex; justify-content: center; align-items: center; height: 100vh; background-color: #f4f4f4; }
        .login-container { background: white; padding: 25px; border-radius: 8px; box-shadow: 0 0 10px rgba(0, 0, 0, 0.1); }
        h1 { text-align: center; color: #333; }
        input[type="text"], input[type="password"] { width: 100%; padding: 10px; margin: 8px 0; display: inline-block; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        button { background-color: #5cb85c; color: white; padding: 14px 20px; margin: 8px 0; border: none; border-radius: 4px; cursor: pointer; width: 100%; }
        button:hover { background-color: #4cae4c; }
        .mensaje-error { color: red; text-align: center; }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>☕ Acceso de Personal</h1>

        <?php if ($mensaje): ?>
            <p class="mensaje-error"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="usuario">Usuario:</label>
            <input type="text" id="usuario" name="usuario" required><br>
            
            <label for="password">Contraseña:</label>
            <input type="password" id="password" name="password" required><br>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
    </div>
</body>
</html>