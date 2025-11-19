<?php
include 'db_connection.php';

$reservacion_id = $_GET['id'] ?? 0;

if ($reservacion_id > 0) {
    // DELETE: Eliminar el registro
    $stmt = $conn->prepare("DELETE FROM reservaciones WHERE reservacion_id = ?");
    $stmt->bind_param("i", $reservacion_id);

    if ($stmt->execute()) {
        // Mensaje de éxito (solo se puede pasar con sesión o URL query si fuera necesario)
    } else {
        // Mensaje de error
    }
    $stmt->close();
}

$conn->close();

// Redireccionar al listado de reservaciones
header("Location: reservaciones.php");
exit();
?>