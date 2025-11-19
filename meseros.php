<?php
include 'db_connection.php';

// READ: Consulta para obtener todos los meseros
$sql = "SELECT mesero_id, nombre, apellido, puesto FROM meseros ORDER BY mesero_id DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Meseros</title>
</head>
<body>
    <h2>👥 Gestión de Personal (Meseros)</h2>
    <a href="crear_mesero.php">➕ Agregar Nuevo Mesero</a> | 
    <a href="listado_comandas.php">📋 Volver a Comandas</a>
    <hr>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre Completo</th>
                <th>Puesto</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["mesero_id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["nombre"] . " " . $row["apellido"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["puesto"]) . "</td>";
                    echo "<td>";
                    echo "<a href='editar_mesero.php?id=" . $row["mesero_id"] . "'>Editar</a> | ";
                    echo "<a href='eliminar_mesero.php?id=" . $row["mesero_id"] . "' onclick='return confirm(\"¿Desea eliminar a " . htmlspecialchars($row["nombre"]) . "?\")'>Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No hay personal registrado.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>