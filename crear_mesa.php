<?php
include 'db_connection.php';
$mensaje = "";

// CREATE: Lógica para insertar una nueva mesa
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $numero_mesa = (int)$_POST['numero_mesa'];
    $capacidad = (int)$_POST['capacidad'];
    $estado = $_POST['estado'];
    $area = $_POST['area'] ?? 'General'; 

    $conn = new mysqli($servername, $username, $password, $dbname); // Asume que db_connection.php define estas variables
    if ($conn->connect_error) { die("Conexión fallida: " . $conn->connect_error); }
    $conn->set_charset("utf8");

    // Usando prepared statement por seguridad
    $stmt = $conn->prepare("INSERT INTO mesas (numero_mesa, capacidad, estado, area) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiss", $numero_mesa, $capacidad, $estado, $area);

    if ($stmt->execute()) {
        $mensaje = "✅ Mesa #" . htmlspecialchars($numero_mesa) . " agregada con éxito.";
    } else {
        if ($conn->errno == 1062) { 
             $mensaje = "❌ Error: El número de mesa " . htmlspecialchars($numero_mesa) . " ya existe.";
        } else {
             $mensaje = "❌ Error: " . $stmt->error;
        }
    }

    $stmt->close();
    $conn->close();
}
// NOTA: Si usas el db_connection.php anterior, asegúrate de quitar la línea de $conn->close() 
// y usar la variable $conn directamente. Aquí asumo una reconexión para fines del ejemplo.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Mesa</title>
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

        form input[type="number"], 
        form input[type="text"] {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        form input[type="number"]:focus, 
        form input[type="text"]:focus {
            border-color: var(--color-destacado-fuerte);
            outline: none;
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
        <h2>➕ Gestión de Mesas</h2>
        <hr>

        <?php if ($mensaje): 
            $clase_mensaje = (strpos($mensaje, '✅') !== false) ? 'mensaje-success' : 'mensaje-error';
        ?>
            <p class="mensaje-info <?php echo $clase_mensaje; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form method="POST" action="crear_mesa.php">
            <label for="numero_mesa">Número de Mesa:</label>
            <input type="number" id="numero_mesa" name="numero_mesa" min="1" required>

            <label for="capacidad">Capacidad (Personas):</label>
            <input type="number" id="capacidad" name="capacidad" min="1" required>

            <label for="area">Área:</label>
            <input type="text" id="area" name="area" value="General" required>

            <input type="hidden" name="estado" value="Disponible">

            <input type="submit" value="Guardar Mesa">
        </form>
        
        <div class="volver-link">
            <a href="toma_pedido.php">← Volver al Panel de Control</a>
        </div>
    </div>
</body>
</html>