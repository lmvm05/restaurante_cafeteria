<?php
include 'db_connection.php';
$mensaje = "";

// Asumo que db_connection.php define y abre $conn
$conn = $conn ?? null;

if ($conn) {
    // Función para verificar solapamiento de reservas (asumiendo 1 hora de duración)
    function check_availability($conn, $mesa_id, $fecha_hora) {
        $start_time = $fecha_hora;

        // Búsqueda de reservas que se solapen. Usamos el campo 'mesa_id' de tu tabla.
        $check_sql = $conn->prepare("
            SELECT reservacion_id FROM reservaciones 
            WHERE mesa_id = ? 
            AND estado IN ('Pendiente', 'Confirmada')
            -- Verifica si la nueva reserva (asumiendo 1 hora) se solapa con reservas existentes (asumiendo 1 hora).
            -- La reserva existente empieza antes de que la nueva termine (T_N + 59 min)
            -- AND fecha_hora < DATE_ADD(?, INTERVAL 59 MINUTE) 
            -- Y la reserva existente termina después de que la nueva empiece (T_N)
            -- AND DATE_ADD(fecha_hora, INTERVAL 59 MINUTE) > ?
            -- El check de ejemplo dado era aproximado. Usaremos una aproximación más segura:
            AND fecha_hora BETWEEN DATE_SUB(?, INTERVAL 59 MINUTE) AND DATE_ADD(?, INTERVAL 59 MINUTE)
        ");

        $check_sql->bind_param("iss", $mesa_id, $start_time, $start_time);
        $check_sql->execute();
        $result = $check_sql->get_result();
        $check_sql->close();

        return $result->num_rows == 0; // Devuelve true si no se encontró solapamiento
    }


    // CREATE: Lógica para insertar una nueva reservación
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $mesa_id = $_POST['mesa_id'];
        $fecha_hora = $_POST['fecha_hora'];
        $nombre_cliente = $_POST['nombre_cliente'];
        $telefono_cliente = $_POST['telefono_cliente'];
        $num_personas = (int)$_POST['num_personas'];
        $estado = 'Pendiente';

        // Validar que la fecha/hora no sea en el pasado
        if (strtotime($fecha_hora) < time()) {
            $mensaje = "❌ Error: No puedes crear una reserva en el pasado.";
        }
        else if (check_availability($conn, $mesa_id, $fecha_hora)) {
            // La mesa está disponible
            // NOTA: Tu tabla de reservaciones usa 'mesa_id' (foreign key)
            $stmt = $conn->prepare("INSERT INTO reservaciones (mesa_id, fecha_hora, nombre_cliente, telefono_cliente, num_personas, estado) VALUES (?, ?, ?, ?, ?, ?)");
            // NOTA: El primer tipo debe ser 'i' si mesa_id es INTEGER en el POST.
            $stmt->bind_param("ississ", $mesa_id, $fecha_hora, $nombre_cliente, $telefono_cliente, $num_personas, $estado);

            if ($stmt->execute()) {
                $mensaje = "✅ Reserva para **" . htmlspecialchars($nombre_cliente) . "** creada con éxito.";
            } else {
                $mensaje = "❌ Error al guardar la reserva: " . $stmt->error;
            }
            $stmt->close();
        } else {
            // Mesa no disponible en ese horario
            $mensaje = "❌ Error: La mesa seleccionada ya tiene una reserva confirmada o pendiente en ese rango de tiempo (asumiendo 1 hora de ocupación).";
        }
    }

    // Obtener todas las mesas para el selector
    $mesas_sql = "SELECT mesa_id, numero_mesa, capacidad FROM mesas ORDER BY numero_mesa ASC";
    $mesas_result = $conn->query($mesas_sql);

    // Cierre de conexión
    $conn->close();
} else {
    $mensaje = "❌ Error de conexión a la base de datos. Verifica db_connection.php.";
    $mesas_result = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Reservación</title>
    <style>
        /* Paleta de Colores (consistencia) */
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
            max-width: 550px; 
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
        form input[type="number"], 
        form input[type="datetime-local"], /* Nuevo campo */
        form select {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        form input:focus, 
        form select:focus {
            border-color: var(--color-destacado-fuerte);
            outline: none;
        }
        
        /* Estilo específico para Select */
        form select {
            appearance: none;
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
            background-color: #A3820B;
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
        <h2>📅 Registrar Nueva Reservación</h2>
        <hr>

        <?php if ($mensaje): 
            $clase_mensaje = (strpos($mensaje, '✅') !== false) ? 'mensaje-success' : 'mensaje-error';
        ?>
            <p class="mensaje-info <?php echo $clase_mensaje; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form method="POST" action="crear_reservacion.php">
            <label for="nombre_cliente">Nombre del Cliente:</label>
            <input type="text" id="nombre_cliente" name="nombre_cliente" required>

            <label for="telefono_cliente">Teléfono del Cliente (Opcional):</label>
            <input type="text" id="telefono_cliente" name="telefono_cliente" placeholder="Ej. 5512345678">
            
            <label for="num_personas">Número de Personas:</label>
            <input type="number" id="num_personas" name="num_personas" min="1" required>

            <label for="fecha_hora">Fecha y Hora de la Reserva:</label>
            <input type="datetime-local" id="fecha_hora" name="fecha_hora" required>

            <label for="mesa_id">Mesa Asignada:</label>
            <select id="mesa_id" name="mesa_id" required>
                <option value="" disabled selected>-- Seleccionar Mesa --</option>
                <?php
                if ($mesas_result && $mesas_result->num_rows > 0) {
                    while($mesa = $mesas_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($mesa["mesa_id"]) . "'>Mesa #" . htmlspecialchars($mesa["numero_mesa"]) . " (Cap. " . htmlspecialchars($mesa["capacidad"]) . ")</option>";
                    }
                } else {
                    echo "<option value='' disabled>No hay mesas disponibles</option>";
                }
                ?>
            </select>

            <input type="submit" value="Reservar Mesa">
        </form>
        
        <div class="volver-link">
            <a href="toma_pedido.php">← Volver al Panel de Control</a>
        </div>
    </div>
</body>
</html>