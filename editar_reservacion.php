<?php
include 'db_connection.php';
$mensaje = "";
$reservacion_data = [];
$reservacion_id = $_GET['id'] ?? 0;

// Reutilizar la función de verificación de disponibilidad (simplificada)
function check_availability_edit($conn, $mesa_id, $fecha_hora, $current_reservation_id) {
    $start_time = $fecha_hora;
    
    $check_sql = $conn->prepare("
        SELECT reservacion_id FROM reservaciones 
        WHERE mesa_id = ? 
        AND estado IN ('Pendiente', 'Confirmada')
        AND reservacion_id != ? -- Excluir la reserva que se está editando
        AND fecha_hora BETWEEN DATE_SUB(?, INTERVAL 59 MINUTE) AND DATE_ADD(?, INTERVAL 59 MINUTE)
    ");

    $check_sql->bind_param("iiss", $mesa_id, $current_reservation_id, $start_time, $start_time);
    $check_sql->execute();
    $result = $check_sql->get_result();
    $check_sql->close();

    return $result->num_rows == 0;
}


// UPDATE: Lógica para manejar el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_edicion'])) {
    $reservacion_id_post = $_POST['reservacion_id'];
    $mesa_id = $_POST['mesa_id'];
    $fecha_hora = $_POST['fecha_hora'];
    $nombre_cliente = $_POST['nombre_cliente'];
    $telefono_cliente = $_POST['telefono_cliente'];
    $num_personas = (int)$_POST['num_personas'];
    $estado = $_POST['estado'];

    if (check_availability_edit($conn, $mesa_id, $fecha_hora, $reservacion_id_post)) {
        $stmt = $conn->prepare("UPDATE reservaciones SET mesa_id = ?, fecha_hora = ?, nombre_cliente = ?, telefono_cliente = ?, num_personas = ?, estado = ? WHERE reservacion_id = ?");
        $stmt->bind_param("issisii", $mesa_id, $fecha_hora, $nombre_cliente, $telefono_cliente, $num_personas, $estado, $reservacion_id_post);

        if ($stmt->execute()) {
            $mensaje = "✅ Reserva de **" . htmlspecialchars($nombre_cliente) . "** actualizada con éxito.";
        } else {
            $mensaje = "❌ Error al actualizar: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $mensaje = "❌ Error: Hay un conflicto de horario con otra reserva. No se pudo guardar.";
    }
    
    $reservacion_id = $reservacion_id_post; // Para recargar los datos actualizados
}

// READ: Obtener los datos actuales de la reserva (para llenar el formulario)
if ($reservacion_id > 0) {
    $stmt_read = $conn->prepare("SELECT mesa_id, fecha_hora, nombre_cliente, telefono_cliente, num_personas, estado FROM reservaciones WHERE reservacion_id = ?");
    $stmt_read->bind_param("i", $reservacion_id);
    $stmt_read->execute();
    $result = $stmt_read->get_result();
    $reservacion_data = $result->fetch_assoc();
    $stmt_read->close();

    if (!$reservacion_data) {
        die("Reservación no encontrada.");
    }
} else {
    die("ID de Reservación no válido.");
}

// Obtener todas las mesas para el selector
$mesas_sql = "SELECT mesa_id, numero_mesa, capacidad FROM mesas ORDER BY numero_mesa ASC";
$mesas_result = $conn->query($mesas_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Reservación</title>
</head>
<body>
    <h2>✏️ Editar Reservación de: <?php echo htmlspecialchars($reservacion_data['nombre_cliente']); ?></h2>
    <p style='color: blue; font-weight: bold;'><?php echo $mensaje; ?></p>
    <a href="reservaciones.php">← Volver al Listado de Reservaciones</a>
    <hr>
    
    <form method="POST" action="editar_reservacion.php?id=<?php echo $reservacion_id; ?>">
        <input type="hidden" name="reservacion_id" value="<?php echo $reservacion_id; ?>">

        <label for="nombre_cliente">Nombre del Cliente:</label><br>
        <input type="text" id="nombre_cliente" name="nombre_cliente" value="<?php echo htmlspecialchars($reservacion_data['nombre_cliente']); ?>" required><br><br>

        <label for="telefono_cliente">Teléfono del Cliente:</label><br>
        <input type="text" id="telefono_cliente" name="telefono_cliente" value="<?php echo htmlspecialchars($reservacion_data['telefono_cliente']); ?>" placeholder="Opcional"><br><br>

        <label for="num_personas">Número de Personas:</label><br>
        <input type="number" id="num_personas" name="num_personas" value="<?php echo htmlspecialchars($reservacion_data['num_personas']); ?>" min="1" required><br><br>

        <label for="fecha_hora">Fecha y Hora (YYY-MM-DD HH:MM):</label><br>
        <?php $formatted_datetime = date('Y-m-d\TH:i', strtotime($reservacion_data['fecha_hora'])); ?>
        <input type="datetime-local" id="fecha_hora" name="fecha_hora" value="<?php echo $formatted_datetime; ?>" required><br><br>

        <label for="mesa_id">Mesa Asignada:</label><br>
        <select id="mesa_id" name="mesa_id" required>
            <?php
            if ($mesas_result->num_rows > 0) {
                while($mesa = $mesas_result->fetch_assoc()) {
                    $selected = ($mesa["mesa_id"] == $reservacion_data["mesa_id"]) ? 'selected' : '';
                    echo "<option value='" . $mesa["mesa_id"] . "' $selected>Mesa #" . $mesa["numero_mesa"] . " (Cap. " . $mesa["capacidad"] . ")</option>";
                }
            }
            ?>
        </select><br><br>
        
        <label for="estado">Estado de la Reserva:</label><br>
        <select id="estado" name="estado" required>
            <?php 
            $estados = ['Pendiente', 'Confirmada', 'Cancelada', 'Completada'];
            foreach ($estados as $e):
                $selected = ($e == $reservacion_data['estado']) ? 'selected' : '';
                echo "<option value='$e' $selected>$e</option>";
            endforeach;
            ?>
        </select><br><br>

        <input type="submit" name="guardar_edicion" value="Guardar Cambios">
    </form>
</body>
</html>