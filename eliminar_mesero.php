<?php
include 'db_connection.php';

$mesero_id = $_GET['id'] ?? 0;

if ($mesero_id > 0) {
    // Es muy importante en una aplicación real verificar si el mesero tiene comandas activas
    // o pendientes antes de eliminarlo, o configurar la llave foránea con ON DELETE CASCADE.
    
    // DELETE: Eliminar el registro
    $stmt = $conn->prepare("DELETE FROM meseros WHERE mesero_id = ?");
    $stmt->bind_param("i", $mesero_id);

    if ($stmt->execute()) {
        $mensaje = "Mesero eliminado con éxito.";
    } else {
        // En caso de que falle por una restricción de llave foránea (si el mesero aún tiene comandas)
        $mensaje = "Error al eliminar. Verifique que el mesero no tenga comandas asignadas. " . $stmt->error;
    }
    $stmt->close();
} else {
    $mensaje = "ID de Mesero no válido.";
}

$conn->close();

// Redireccionar al listado de meseros
// Nota: Puedes pasar el mensaje a través de una variable de sesión para mostrarlo en meseros.php.
header("Location: meseros.php");
exit();
?>