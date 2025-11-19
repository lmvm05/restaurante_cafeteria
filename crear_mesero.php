<?php
include 'db_connection.php';
$mensaje = "";

// Lógica para insertar un nuevo mesero (Se asume que la conexión está abierta por db_connection.php)
// NOTA: Si db_connection.php NO define $conn, el código original fallaría. 
// Para que funcione con tu db_connection.php simple (que solo abre $conn), necesitas usar $conn sin el new mysqli.
// Ajustaré el PHP para usar la conexión global $conn (asumiendo que está abierta).

$conn = $conn ?? null; // Usar la conexión global si existe (si no, esto es un error)
if ($conn) {
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $puesto = $_POST['puesto'];

        // Usando prepared statement por seguridad
        // NOTA: Tu tabla de personal es 'meseros', lo cual puede ser limitado.
        $stmt = $conn->prepare("INSERT INTO meseros (nombre, apellido, puesto) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $nombre, $apellido, $puesto);

        if ($stmt->execute()) {
            $mensaje = "✅ Nuevo personal **" . htmlspecialchars($nombre) . "** agregado con éxito.";
        } else {
            $mensaje = "❌ Error: " . $stmt->error;
        }

        $stmt->close();
    }
    // NOTA: Si db_connection.php no cierra la conexión, debemos cerrarla aquí:
    // $conn->close();
} else {
    // Esto debería evitar errores si la conexión no se inicializó
    $mensaje = "❌ Error de conexión a la base de datos. Verifica db_connection.php.";
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Personal</title>
    <style>
        /* Paleta de Colores (consistencia con el dashboard) */
        :root {
            --color-cafe-oscuro: #4E342E;
            --color-crema-fondo: #FDF7F0;
            --color-destacado-fuerte: #D4AC0D;
            --color-sombra: rgba(0, 0, 0, 0.1);
            --color-error: #dc3545;
            --color-success: #28a745;
        }

        body { 
            font-family: 'Arial', sans-serif; 
            background-color: var(--color-crema-fondo); 
            color: var(--color-cafe-oscuro); 
            margin: 0; 
            padding: 20px; 
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 500px; /* Formulario centrado y más compacto */
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 15px var(--color-sombra);
        }

        h2 { 
            color: var(--color-cafe-oscuro); 
            border-bottom: 2px solid var(--color-destacado-fuerte); 
            padding-bottom: 10px; 
            margin-bottom: 25px;
            text-align: center;
        }

        /* ESTILOS DEL FORMULARIO */
        form label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--color-cafe-oscuro);
        }

        form input[type="text"], 
        form select {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        form input[type="text"]:focus, 
        form select:focus {
            border-color: var(--color-destacado-fuerte);
            outline: none;
        }
        
        /* Ajuste específico para el select */
        form select {
            appearance: none; /* Elimina estilos nativos en algunos navegadores */
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="292.4" height="292.4"><path fill="%234E342E" d="M287 69.4a17.6 17.6 0 0 0-13.4-6.2H18.8c-5.8 0-11.1 2.4-13.4 6.2s-2.1 9.4 0 14l127.3 127.3c2.2 2.2 5.1 3.4 8.2 3.4s6-.9 8.2-3.4L287 83.4c2.2-4.6 2.4-9.8 0-14z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 10px auto;
        }

        input[type="submit"] {
            background-color: var(--color-destacado-fuerte);
            color: white;
            padding: 12px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #A3820B; /* Tono más oscuro al pasar el ratón */
        }

        /* MENSAJES DE ESTADO */
        .mensaje-info {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }

        .mensaje-success {
            background-color: #d4edda;
            color: var(--color-success);
        }

        .mensaje-error {
            background-color: #f8d7da;
            color: var(--color-error);
        }

        /* ENLACE VOLVER */
        .volver-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .volver-link a {
            color: var(--color-cafe-oscuro);
            text-decoration: none;
            border: 1px solid #ccc;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .volver-link a:hover {
            background-color: var(--color-cafe-oscuro);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>🧑‍💼 Gestión de Personal</h2>
        <hr>

        <?php if ($mensaje && $conn): // Muestra el mensaje solo si hay conexión y mensaje
            $clase_mensaje = (strpos($mensaje, '✅') !== false) ? 'mensaje-success' : 'mensaje-error';
        ?>
            <p class="mensaje-info <?php echo $clase_mensaje; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        <?php if (!$conn): // Muestra error de conexión si aplica ?>
             <p class="mensaje-info mensaje-error">Error al intentar conectar la base de datos.</p>
        <?php endif; ?>

        <form method="POST" action="crear_mesero.php">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="apellido">Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>

            <label for="puesto">Puesto:</label>
            <select id="puesto" name="puesto" required>
                <option value="" disabled selected>-- Seleccionar Puesto --</option>
                <option value="Mesero">Mesero</option>
                <option value="Barista">Barista</option>
                <option value="Cajero">Cajero</option>
                <option value="Gerente">Gerente</option>
            </select>

            <input type="submit" value="Guardar Personal">
        </form>
        
        <div class="volver-link">
            <a href="toma_pedido.php">← Volver al Panel de Control</a>
        </div>
    </div>
</body>
</html>