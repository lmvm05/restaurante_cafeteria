<?php
include 'db_connection.php';

$sql = "SELECT c.comanda_id, c.fecha_hora, c.tipo_pedido, c.estado, c.total, m.nombre as mesero_nombre, me.numero_mesa 
        FROM comandas c 
        JOIN meseros m ON c.mesero_id = m.mesero_id
        LEFT JOIN mesas me ON c.mesa_id = me.mesa_id
        ORDER BY c.fecha_hora DESC";
$result = $conn->query($sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Listado de Comandas</title>
   
<?php
include 'db_connection.php';

// Asumo que db_connection.php define y abre $conn
$conn = $conn ?? null;

if ($conn) {
    $sql = "SELECT c.comanda_id, c.fecha_hora, c.tipo_pedido, c.estado, c.total, m.nombre as mesero_nombre, me.numero_mesa 
            FROM comandas c 
            JOIN meseros m ON c.mesero_id = m.mesero_id
            LEFT JOIN mesas me ON c.mesa_id = me.mesa_id
            ORDER BY c.fecha_hora DESC";
    $result = $conn->query($sql);
    
    $conn->close();
} else {
    $result = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listado de Comandas</title>
    <style>
        /* Paleta de Colores (consistencia) */
        :root {
            --color-cafe-oscuro: #4E342E;
            --color-crema-fondo: #FDF7F0;
            --color-destacado-fuerte: #D4AC0D;
            --color-sombra: rgba(0, 0, 0, 0.1);
            --color-success: #28a745;
            --color-pending: #F39C12; /* Naranja/Amarillo para Pendiente */
            --color-completed: #2ECC71; /* Verde para Completado/Cerrado */
        }

        body { 
            font-family: 'Arial', sans-serif; 
            background-color: var(--color-crema-fondo); 
            color: var(--color-cafe-oscuro); 
            margin: 0; 
            padding: 20px; 
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
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
        }

        /* ENLACES DE CABECERA */
        .header-links a {
            color: var(--color-cafe-oscuro);
            text-decoration: none;
            border: 1px solid #ccc;
            padding: 8px 15px;
            border-radius: 5px;
            margin-right: 10px;
            display: inline-block;
            transition: background-color 0.3s, color 0.3s;
        }
        .header-links a:hover {
            background-color: var(--color-cafe-oscuro);
            color: white;
        }
        .header-links {
            margin-bottom: 20px;
        }

        /* TABLA DE COMANDAS */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: #fff;
            box-shadow: 0 2px 4px var(--color-sombra);
            border-radius: 8px;
            overflow: hidden;
        }

        table thead th {
            background-color: var(--color-cafe-oscuro);
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }

        table tbody td {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        table tbody tr:hover {
            background-color: #FDF3E7;
        }

        /* ESTADO DE LA COMANDA */
        .estado {
            font-weight: bold;
            padding: 4px 8px;
            border-radius: 4px;
            display: inline-block;
            text-align: center;
            min-width: 70px;
        }
        .estado.Pendiente {
            background-color: var(--color-pending);
            color: white;
        }
        .estado.Cerrada, .estado.Pagada {
            background-color: var(--color-completed);
            color: white;
        }
        /* Agregar más estados si es necesario (ej. 'En Preparacion') */

        /* Columna de Total */
        table td:nth-child(7) {
            font-weight: bold;
            text-align: right;
            color: #C0392B; /* Rojo para Totales */
        }
        
        /* Columna de Acciones */
        table td:last-child a {
            margin-right: 8px;
            color: var(--color-destacado-fuerte);
            text-decoration: none;
            transition: color 0.3s;
        }
        table td:last-child a:hover {
            color: var(--color-cafe-oscuro);
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h2>📋 Comandas Activas</h2>
    <a href="nueva_comanda.php">➕ Iniciar Nueva Comanda</a>
    <a href="menu.php">🔄 Gestionar Menú</a>
    <hr>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Fecha/Hora</th>
                <th>Mesero</th>
                <th>Mesa</th>
                <th>Tipo</th>
                <th>Estado</th>
                <th>Total</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["comanda_id"] . "</td>";
                    echo "<td>" . $row["fecha_hora"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["mesero_nombre"]) . "</td>";
                    echo "<td>" . ($row["numero_mesa"] ? "Mesa " . $row["numero_mesa"] : "N/A") . "</td>";
                    echo "<td>" . $row["tipo_pedido"] . "</td>";
                    echo "<td>" . $row["estado"] . "</td>";
                    echo "<td>$" . number_format($row["total"], 2) . "</td>";
                    echo "<td>";
                    echo "<a href='agregar_productos.php?comanda_id=" . $row["comanda_id"] . "'>Ver/Editar</a> | ";
                    echo "<a href='cerrar_comanda.php?comanda_id=" . $row["comanda_id"] . "'>Cerrar/Pagar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='8'>No hay comandas activas.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</body>
</html>