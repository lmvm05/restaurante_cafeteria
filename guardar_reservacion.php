<?php
// Script para guardar la reservación en la base de datos
include 'db_connection.php';

// 2. Conexión a la BD
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar la conexión
if ($conn->connect_error) {
    $response['message'] = "Fallo la conexión a la Base de Datos: " . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// 3. Recibir y sanitizar los datos del formulario (POST)
// NOTA: Se usa real_escape_string para seguridad básica contra inyección SQL.
$nombre_cliente = $conn->real_escape_string($_POST['nombre'] ?? '');
$telefono       = $conn->real_escape_string($_POST['telefono'] ?? '');

// Combinar Fecha y Hora en un solo campo para tu columna DATETIME 'fecha_hora'
$fecha_reserva  = $conn->real_escape_string($_POST['fecha'] ?? '');
$hora_reserva   = $conn->real_escape_string($_POST['hora'] ?? '');
$fecha_hora     = $fecha_reserva . ' ' . $hora_reserva;

// Usamos el número de personas para el campo 'mesa_id' por simplificación,
// ya que tu tabla no tiene una columna 'num_personas'.
$mesa_id        = (int)($_POST['personas'] ?? 0); 
$estado         = 'Pendiente'; // Estado inicial de la reservación

// 4. Preparar la consulta SQL
// Usamos los nombres de columna de tu tabla: nombre_cliente, telefono, mesa_id, fecha_hora, estado
$sql = "INSERT INTO reservaciones (nombre_cliente, telefono, mesa_id, fecha_hora, estado) 
        VALUES ('$nombre_cliente', '$telefono', $mesa_id, '$fecha_hora', '$estado')";

// 5. Ejecutar la consulta
if ($conn->query($sql) === TRUE) {
    $response['success'] = true;
    $response['message'] = "Reservación guardada con éxito.";
} else {
    $response['message'] = "Error al insertar en la base de datos: " . $conn->error;
}

// 6. Cerrar la conexión
$conn->close();

// 7. Devolver la respuesta al cliente (JavaScript)
echo json_encode($response);
?>