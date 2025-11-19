<?php
include 'db_connection.php';
$mensaje = "";
$mesa_data = [];
$mesa_id = $_GET['id'] ?? 0;

// UPDATE: Lógica para manejar el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_edicion'])) {
    $mesa_id_post = $_POST['mesa_id'];
    $numero_mesa = (int)$_POST['numero_mesa'];
    $capacidad = (int)$_POST['capacidad'];
    $estado = $_POST['estado'];
    $area = $_POST['area'];

    $stmt = $conn->prepare("UPDATE mesas SET numero_mesa = ?, capacidad = ?, estado = ?, area = ? WHERE mesa_id = ?");
    $stmt->bind_param("iissi", $numero_mesa, $capacidad, $estado, $area, $mesa_id_post);

    if ($stmt->execute()) {
        $mensaje = "✅ Mesa **#" . htmlspecialchars($numero_mesa) . "** actualizada con éxito.";
    } else {
         if ($conn->errno == 1062) { 
             $mensaje = "❌ Error: El número de mesa " . htmlspecialchars($numero_mesa) . " ya existe.";
        } else {
             $mensaje = "❌ Error al actualizar: " . $stmt->error;
        }
    }
    $stmt->close();
    $mesa_id = $mesa_id_post; // Para recargar los datos actualizados
}

// READ: Obtener los datos actuales de la mesa (para llenar el formulario)
if ($mesa_id > 0) {
    $stmt_read = $conn->prepare("SELECT numero_mesa, capacidad, estado, area FROM mesas WHERE mesa_id = ?");
    $stmt_read->bind_param("i", $mesa_id);
    $stmt_read->execute();
    $result = $stmt_read->get_result();
    $mesa_data = $result->fetch_assoc();
    $stmt_read->close();

    if (!$mesa_data) {
        die("Mesa no encontrada.");
    }
} else {
    die("ID de Mesa no válido.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Mesa</title>
</head>
<body>
    <h2>✏️ Editar Mesa #<?php echo htmlspecialchars($mesa_data['numero_mesa']); ?></h2>
    <p><?php echo $mensaje; ?></p>
    <a href="mesas.php">← Volver al Listado de Mesas</a>
    <hr>
    
    <form method="POST" action="editar_mesa.php?id=<?php echo $mesa_id; ?>">
        <input type="hidden" name="mesa_id" value="<?php echo $mesa_id; ?>">

        <label for="numero_mesa">Número de Mesa:</label><br>
        <input type="number" id="numero_mesa" name="numero_mesa" value="<?php echo htmlspecialchars($mesa_data['numero_mesa']); ?>" min="1" required><br><br>

        <label for="capacidad">Capacidad (Personas):</label><br>
        <input type="number" id="capacidad" name="capacidad" value="<?php echo htmlspecialchars($mesa_data['capacidad']); ?>" min="1" required><br><br>
        
        <label for="area">Área:</label><br>
        <input type="text" id="area" name="area" value="<?php echo htmlspecialchars($mesa_data['area']); ?>" required><br><br>

        <label for="estado">Estado:</label><br>
        <select id="estado" name="estado" required>
            <?php 
            $estados = ['Disponible', 'Ocupada', 'Sucia', 'Mantenimiento'];
            foreach ($estados as $e):
                $selected = ($e == $mesa_data['estado']) ? 'selected' : '';
                echo "<option value='$e' $selected>$e</option>";
            endforeach;
            ?>
        </select><br><br>

        <input type="submit" name="guardar_edicion" value="Guardar Cambios">
    </form>
</body>
</html>