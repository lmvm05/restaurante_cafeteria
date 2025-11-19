<?php
include 'db_connection.php';

// READ: Consulta para obtener todas las mesas
$sql = "SELECT mesa_id, numero_mesa, capacidad, estado FROM mesas ORDER BY numero_mesa ASC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Mesas</title>
    <style>
        .estado-Disponible { color: green; font-weight: bold; }
        .estado-Ocupada { color: red; font-weight: bold; }
        .estado-Sucia { color: orange; font-weight: bold; }
    </style>
</head>
<body>
    <h2>🪑 Gestión de Mesas</h2>
    <a href="crear_mesa.php">➕ Agregar Nueva Mesa</a> | 
    <a href="listado_comandas.php">📋 Volver a Comandas</a>
    <hr>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Número de Mesa</th>
                <th>Capacidad</th>
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
                    echo "<td>" . $row["mesa_id"] . "</td>";
                    echo "<td>" . $row["numero_mesa"] . "</td>";
                    echo "<td>" . $row["capacidad"] . " personas</td>";
                    echo "<td class='{$estado_class}'>" . $row["estado"] . "</td>";
                    echo "<td>";
                    echo "<a href='editar_mesa.php?id=" . $row["mesa_id"] . "'>Editar</a> | ";
                    echo "<a href='eliminar_mesa.php?id=" . $row["mesa_id"] . "' onclick='return confirm(\"¿Desea eliminar la Mesa # " . $row["numero_mesa"] . "?\")'>Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay mesas registradas.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>