<?php
include 'db_connection.php';
$mensaje = "";
$mesero_data = [];
$mesero_id = $_GET['id'] ?? 0;

// UPDATE: Lógica para manejar el formulario de edición
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['guardar_edicion'])) {
    $mesero_id_post = $_POST['mesero_id'];
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $puesto = $_POST['puesto'];

    $stmt = $conn->prepare("UPDATE meseros SET nombre = ?, apellido = ?, puesto = ? WHERE mesero_id = ?");
    $stmt->bind_param("sssi", $nombre, $apellido, $puesto, $mesero_id_post);

    if ($stmt->execute()) {
        $mensaje = "✅ Personal **" . htmlspecialchars($nombre) . "** actualizado con éxito.";
    } else {
        $mensaje = "❌ Error al actualizar: " . $stmt->error;
    }
    $stmt->close();
    $mesero_id = $mesero_id_post; // Para recargar los datos actualizados
}

// READ: Obtener los datos actuales del mesero (para llenar el formulario)
if ($mesero_id > 0) {
    $stmt_read = $conn->prepare("SELECT nombre, apellido, puesto FROM meseros WHERE mesero_id = ?");
    $stmt_read->bind_param("i", $mesero_id);
    $stmt_read->execute();
    $result = $stmt_read->get_result();
    $mesero_data = $result->fetch_assoc();
    $stmt_read->close();

    if (!$mesero_data) {
        die("Personal no encontrado.");
    }
} else {
    die("ID de Personal no válido.");
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Mesero</title>
</head>
<body>
    <h2>✏️ Editar Personal: <?php echo htmlspecialchars($mesero_data['nombre'] . ' ' . $mesero_data['apellido']); ?></h2>
    <p><?php echo $mensaje; ?></p>
    <a href="meseros.php">← Volver al Listado de Personal</a>
    <hr>
    
    <form method="POST" action="editar_mesero.php?id=<?php echo $mesero_id; ?>">
        <input type="hidden" name="mesero_id" value="<?php echo $mesero_id; ?>">

        <label for="nombre">Nombre:</label><br>
        <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($mesero_data['nombre']); ?>" required><br><br>

        <label for="apellido">Apellido:</label><br>
        <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($mesero_data['apellido']); ?>" required><br><br>

        <label for="puesto">Puesto:</label><br>
        <select id="puesto" name="puesto" required>
            <?php 
            $puestos = ['Mesero', 'Barista', 'Cajero', 'Gerente'];
            foreach ($puestos as $p):
                $selected = ($p == $mesero_data['puesto']) ? 'selected' : '';
                echo "<option value='$p' $selected>$p</option>";
            endforeach;
            ?>
        </select><br><br>

        <input type="submit" name="guardar_edicion" value="Guardar Cambios">
    </form>
</body>
</html>