<?php
include 'db_connection.php';

// READ: Consulta para obtener todas las reservaciones, uniéndolas con la información de la mesa.
$sql = "SELECT 
            r.reservacion_id, 
            r.fecha_hora, 
            r.nombre_cliente, 
            r.num_personas, 
            r.estado,
            m.numero_mesa,
            m.capacidad
        FROM reservaciones r
        JOIN mesas m ON r.mesa_id = m.mesa_id
        ORDER BY r.fecha_hora ASC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Reservaciones</title>
    <style>
        .estado-Pendiente { color: blue; }
        .estado-Confirmada { color: green; font-weight: bold; }
        .estado-Cancelada { color: gray; text-decoration: line-through; }
        .estado-Completada { color: purple; }
    </style>
</head>
<body>
    <h2>📅 Gestión de Reservaciones</h2>
    <a href="crear_reservacion.php">➕ Agregar Nueva Reserva</a> | 
    <a href="listado_comandas.php">📋 Volver a Comandas</a>
    <hr>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha y Hora</th>
                <th>Mesa</th>
                <th>Capacidad</th>
                <th>Cliente</th>
                <th>Personas</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    $estado_class = "estado-" . $row["estado"];
                    echo "<tr>";
                    echo "<td>" . $row["reservacion_id"] . "</td>";
                    echo "<td>" . date('d/m/Y H:i', strtotime($row["fecha_hora"])) . "</td>";
                    echo "<td>#" . $row["numero_mesa"] . "</td>";
                    echo "<td>" . $row["capacidad"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["nombre_cliente"]) . "</td>";
                    echo "<td>" . $row["num_personas"] . "</td>";
                    echo "<td class='{$estado_class}'>" . $row["estado"] . "</td>";
                    echo "<td>";
                    echo "<a href='editar_reservacion.php?id=" . $row["reservacion_id"] . "'>Editar/Cambiar Estado</a> | ";
                    echo "<a href='eliminar_reservacion.php?id=" . $row["reservacion_id"] . "' onclick='return confirm(\"¿Desea eliminar la reserva de " . htmlspecialchars($row["nombre_cliente"]) . "?\")'>Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No hay reservaciones registradas.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>