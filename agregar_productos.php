<?php
include 'db_connection.php';

$comanda_id = $_GET['comanda_id'] ?? 0;
if (!$comanda_id) {
    die("ID de Comanda no especificado.");
}

// 1. Obtener la información de la Comanda
$comanda_info = $conn->query("SELECT c.*, m.nombre as nombre_mesero, me.numero_mesa FROM comandas c 
    JOIN meseros m ON c.mesero_id = m.mesero_id 
    LEFT JOIN mesas me ON c.mesa_id = me.mesa_id
    WHERE c.comanda_id = $comanda_id")->fetch_assoc();

if (!$comanda_info) {
    die("Comanda no encontrada.");
}

// 2. Obtener el listado de Productos (Menú)
$productos_sql = "SELECT producto_id, nombre, precio FROM productos ORDER BY nombre";
$productos_result = $conn->query($productos_sql);

// Lógica para agregar un producto al detalle (CREATE DETALLE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['agregar_producto'])) {
    $producto_id = $_POST['producto_id'];
    $cantidad = (int)$_POST['cantidad'];
    
    // Obtener el precio actual del producto
    $precio_sql = $conn->prepare("SELECT precio FROM productos WHERE producto_id = ?");
    $precio_sql->bind_param("i", $producto_id);
    $precio_sql->execute();
    $precio_result = $precio_sql->get_result();
    $producto_precio = $precio_result->fetch_assoc()['precio'];
    $precio_sql->close();

    $subtotal = $producto_precio * $cantidad;
    
    // Insertar el detalle
    $stmt_detalle = $conn->prepare("INSERT INTO detalles_comanda (comanda_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    $stmt_detalle->bind_param("iiidd", $comanda_id, $producto_id, $cantidad, $producto_precio, $subtotal);
    
    if ($stmt_detalle->execute()) {
        // Actualizar el total de la comanda (Buena práctica en una app real)
        $conn->query("UPDATE comandas SET total = (SELECT SUM(subtotal) FROM detalles_comanda WHERE comanda_id = $comanda_id) WHERE comanda_id = $comanda_id");
        $comanda_info['total'] = $comanda_info['total'] + $subtotal; // Actualizar la variable localmente
        $mensaje = "✅ Producto agregado con éxito.";
    } else {
        $mensaje = "❌ Error al agregar producto: " . $stmt_detalle->error;
    }
    $stmt_detalle->close();
}

// 3. Obtener los detalles actuales de la comanda
$detalles_sql = "SELECT dc.*, p.nombre as nombre_producto FROM detalles_comanda dc JOIN productos p ON dc.producto_id = p.producto_id WHERE dc.comanda_id = $comanda_id";
$detalles_result = $conn->query($detalles_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
    <style>
        /* Paleta de Colores (consistencia) */
        :root {
            --color-cafe-oscuro: #4E342E;
            --color-crema-fondo: #FDF7F0;
            --color-destacado-fuerte: #D4AC0D;
            --color-sombra: rgba(0, 0, 0, 0.1);
            --color-error: #dc3545;
            --color-success: #28a745;
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

        h2, h3, h4 { 
            color: var(--color-cafe-oscuro); 
        }
        
        h2 { 
            border-bottom: 2px solid var(--color-destacado-fuerte); 
            padding-bottom: 10px; 
            margin-bottom: 15px;
        }
        
        /* HEADER DE LA COMANDA */
        .comanda-header {
            background-color: #F9E79F;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 1.1em;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .comanda-header span {
            font-weight: bold;
            margin-right: 15px;
        }

        .comanda-total {
            background-color: var(--color-cafe-oscuro);
            color: white;
            padding: 10px 15px;
            border-radius: 6px;
            font-size: 1.5em;
            font-weight: bold;
        }

        /* SECCIÓN AGREGAR PRODUCTO */
        .add-product-section {
            display: flex;
            gap: 20px;
            align-items: flex-end;
            margin-bottom: 30px;
            padding: 20px;
            border: 1px solid #eee;
            border-radius: 8px;
        }
        
        /* Ajuste para el formulario original sin divs de layout, usamos flex */
        form {
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            padding: 20px 0;
            margin-bottom: 20px;
            border-top: 1px solid #eee;
        }

        form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
            width: 100%; /* Para que la etiqueta ocupe su propia línea */
        }
        
        form select {
            flex-grow: 3; /* Hace que el selector de producto sea más ancho */
            min-width: 250px;
        }
        
        form input[type="number"] {
            flex-grow: 1;
            min-width: 80px;
            max-width: 150px;
        }
        
        form input[type="submit"] {
            /* Resto de estilos en el bloque principal */
            flex-grow: 1;
            min-width: 150px;
            height: 40px; /* Asegura que se alinee con los campos de arriba */
            margin: 0;
            padding: 10px 20px;
            margin-bottom: 10px; /* Ya que el bloque tiene margin-bottom */
        }


        form select,
        form input[type="number"] {
            padding: 10px 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }

        form select:focus,
        form input[type="number"]:focus {
            border-color: var(--color-destacado-fuerte);
            outline: none;
        }
        
        /* Botón de añadir */
        input[type="submit"] {
            background-color: var(--color-destacado-fuerte);
            color: var(--color-cafe-oscuro);
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #F7DC6F;
        }

        /* TABLA DE DETALLES */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background-color: #fff;
            box-shadow: 0 2px 4px var(--color-sombra);
            border-radius: 8px;
            overflow: hidden;
        }

        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }

        table thead th {
            background-color: var(--color-cafe-oscuro);
            color: white;
            font-weight: bold;
            text-transform: uppercase;
        }

        table tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        
        /* Columna de precios y subtotales a la derecha */
        table td:nth-child(3),
        table td:nth-child(4) {
            text-align: right;
            font-weight: bold;
        }
        
        /* MENSAJES DE ESTADO */
        .mensaje-info {
            padding: 10px;
            border-radius: 6px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .mensaje-success {
            background-color: #d4edda;
            color: var(--color-success);
        }
        .mensaje-error {
            background-color: #f8d7da;
            color: var(--color-error);
        }
        
        /* ENLACE VOLVER */
        .volver-link {
            display: block;
            text-align: right;
            margin-top: 30px;
        }
        .volver-link a {
            color: var(--color-cafe-oscuro);
            text-decoration: none;
            border: 2px solid var(--color-cafe-oscuro);
            padding: 10px 20px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
            font-weight: bold;
        }
        .volver-link a:hover {
            background-color: var(--color-cafe-oscuro);
            color: white;
        }
    </style>
<head>
    <meta charset="UTF-8">
    <title>Comanda #<?php echo $comanda_id; ?></title>
</head>
<body>
    <h2>📝 Detalles de Comanda #<?php echo $comanda_id; ?></h2>
    
    <p>
        **Tipo:** <?php echo $comanda_info['tipo_pedido']; ?> | 
        <?php if ($comanda_info['mesa_id']) echo "**Mesa:** " . $comanda_info['numero_mesa'] . " | "; ?>
        **Mesero:** <?php echo $comanda_info['nombre_mesero']; ?> | 
        **Estado:** <?php echo $comanda_info['estado']; ?>
    </p>

    <h3>Total Actual: $<?php echo number_format($comanda_info['total'], 2); ?></h3>
    <hr>
    <?php if (isset($mensaje)) echo "<p style='color: blue;'>$mensaje</p>"; ?>

    <h4>➕ Agregar Producto</h4>
    <form method="POST" action="agregar_productos.php?comanda_id=<?php echo $comanda_id; ?>">
        <label for="producto_id">Producto:</label>
        <select id="producto_id" name="producto_id" required>
            <?php 
            if ($productos_result && $productos_result->num_rows > 0) {
                while($prod = $productos_result->fetch_assoc()) {
                    echo "<option value='" . $prod["producto_id"] . "' data-precio='" . $prod["precio"] . "'>" . htmlspecialchars($prod["nombre"]) . " ($" . number_format($prod["precio"], 2) . ")</option>";
                }
            } else {
                 echo "<option value=''>Cargue productos en el menú primero</option>";
            }
            ?>
        </select>
        
        <label for="cantidad">Cantidad:</label>
        <input type="number" id="cantidad" name="cantidad" min="1" value="1" required>
        
        <input type="submit" name="agregar_producto" value="Añadir a Comanda">
    </form>

    <hr>
    <h4>📋 Productos en la Comanda</h4>
    <table>
        <thead>
            <tr>
                <th>Producto</th>
                <th>Cant.</th>
                <th>P. Unitario</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php
            if ($detalles_result && $detalles_result->num_rows > 0) {
                while($detalle = $detalles_result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($detalle["nombre_producto"]) . "</td>";
                    echo "<td>" . $detalle["cantidad"] . "</td>";
                    echo "<td>$" . number_format($detalle["precio_unitario"], 2) . "</td>";
                    echo "<td>$" . number_format($detalle["subtotal"], 2) . "</td>";
                    // Aquí puedes agregar un enlace/botón para ELIMINAR un ítem
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>Esta comanda no tiene productos.</td></tr>";
            }
            ?>
        </tbody>
    </table>
    <br>
    <a href="listado_comandas.php">Finalizar y Volver al Listado</a>
</body>
</html>