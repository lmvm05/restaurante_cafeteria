<?php
include 'db_connection.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Menú</title>
    <style>
        /* Paleta de Colores (consistencia) */
        :root {
            --color-cafe-oscuro: #4E342E;
            --color-crema-fondo: #FDF7F0;
            --color-destacado-fuerte: #D4AC0D;
            --color-sombra: rgba(0, 0, 0, 0.1);
        }

        body { 
            font-family: 'Arial', sans-serif; 
            background-color: var(--color-crema-fondo); 
            color: var(--color-cafe-oscuro); 
            margin: 0; 
            padding: 20px; 
        }

        .container {
            max-width: 900px;
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
        .header-links {
            margin-bottom: 20px;
            display: flex;
            gap: 10px;
            align-items: center;
        }
        
        .header-links a {
            color: var(--color-cafe-oscuro);
            text-decoration: none;
            border: 1px solid #ccc;
            padding: 8px 15px;
            border-radius: 5px;
            display: inline-block;
            transition: background-color 0.3s, color 0.3s;
            font-weight: bold;
        }
        .header-links a:hover {
            background-color: var(--color-cafe-oscuro);
            color: white;
        }

        /* TABLA DE PRODUCTOS */
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

        /* Columna de Precio */
        table td:nth-child(4) {
            font-weight: bold;
            text-align: right;
            color: #C0392B; /* Rojo para precios */
        }
        
        /* Columna de Acciones */
        table td:last-child {
            text-align: center;
        }
        
        table td:last-child a {
            margin: 0 5px;
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
    <h2>☕ Menú de la Cafetería</h2>
    <a href="crear_producto.php">Agregar Nuevo Producto</a>
    <hr>
    
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Categoría</th>
                <th>Precio</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $sql = "SELECT producto_id, nombre, categoria, precio FROM productos ORDER BY categoria, nombre";
            $result = $conn->query($sql);

            if ($result->num_rows > 0) {
                // Imprimir cada fila
                while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . $row["producto_id"] . "</td>";
                    echo "<td>" . htmlspecialchars($row["nombre"]) . "</td>";
                    echo "<td>" . htmlspecialchars($row["categoria"]) . "</td>";
                    echo "<td>$" . number_format($row["precio"], 2) . "</td>";
                    echo "<td>";
                    echo "<a href='editar_producto.php?id=" . $row["producto_id"] . "'>Editar</a> | ";
                    echo "<a href='eliminar_producto.php?id=" . $row["producto_id"] . "' onclick='return confirm(\"¿Seguro que desea eliminar?\")'>Eliminar</a>";
                    echo "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>No hay productos en el menú.</td></tr>";
            }
            $conn->close();
            ?>
        </tbody>
    </table>
</body>
</html>