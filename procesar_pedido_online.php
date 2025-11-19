<?php
include 'db_connection.php';
$mensaje = "";

// Lógica de procesamiento (CREATE)
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // 1. Recoger datos del formulario
    $nombre_cliente = $_POST['nombre_cliente'] ?? 'Cliente Online';
    $telefono = $_POST['telefono'] ?? '';
    $tipo_pedido = $_POST['tipo_pedido'] ?? 'Pickup';
    $direccion = ($tipo_pedido == 'Delivery') ? ($_POST['direccion'] ?? 'N/A') : NULL;
    $productos_seleccionados = $_POST['productos'] ?? [];
    $precios_formulario = $_POST['precios'] ?? [];
    
    $total_comanda = 0.00;
    $items_a_insertar = [];
    
    // 2. Filtrar y validar productos seleccionados
    foreach ($productos_seleccionados as $producto_id => $cantidad) {
        $cantidad = (int)$cantidad;
        if ($cantidad > 0) {
            // **IMPORTANTE**: En un sistema real, aquí OBTENDRÍAMOS el precio
            // de la base de datos usando $producto_id, NO del formulario.
            $precio_unitario = (float)($precios_formulario[$producto_id] ?? 0.00); 
            
            $subtotal = $precio_unitario * $cantidad;
            $total_comanda += $subtotal;

            $items_a_insertar[] = [
                'producto_id' => $producto_id,
                'cantidad' => $cantidad,
                'precio_unitario' => $precio_unitario,
                'subtotal' => $subtotal
            ];
        }
    }

    if (empty($items_a_insertar)) {
        $mensaje = "❌ Error: El pedido no tiene productos seleccionados.";
    } else {
        
        // 3. Iniciar la Transacción
        $conn->begin_transaction();
        
        try {
            // Asignamos un mesero de ID 1 (ejemplo) o un ID 0 si lo permites NULL.
            // Para simplificar, asumiremos que existe un mesero con ID 1 o NULL si lo permite la BD.
            $mesero_id_online = 1; // Usar un ID de mesero genérico o NULL
            
            // A. Insertar el Encabezado de la Comanda
            $stmt_comanda = $conn->prepare("INSERT INTO comandas (mesero_id, tipo_pedido, estado, total) VALUES (?, ?, 'Pendiente', ?)");
            $stmt_comanda->bind_param("isd", $mesero_id_online, $tipo_pedido, $total_comanda);
            $stmt_comanda->execute();
            $nueva_comanda_id = $conn->insert_id;
            $stmt_comanda->close();

            // B. Insertar los Detalles de la Comanda
            $stmt_detalle = $conn->prepare("INSERT INTO detalles_comanda (comanda_id, producto_id, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
            
            foreach ($items_a_insertar as $item) {
                $stmt_detalle->bind_param("iiidd", 
                    $nueva_comanda_id, 
                    $item['producto_id'], 
                    $item['cantidad'], 
                    $item['precio_unitario'], 
                    $item['subtotal']
                );
                $stmt_detalle->execute();
            }
            $stmt_detalle->close();
            
            // C. Opcional: Insertar la dirección y teléfono en una nueva tabla de "Pedidos Online"
            // Por ahora, solo lo mostramos en el mensaje de confirmación.

            $conn->commit();
            
            $mensaje = "✅ ¡Pedido #$nueva_comanda_id confirmado! Total: $" . number_format($total_comanda, 2);
            $mensaje .= "<br>Recibirá una notificación cuando esté listo para $tipo_pedido.";
            if ($direccion) {
                 $mensaje .= "<br>Dirección de entrega: " . htmlspecialchars($direccion);
            }

        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "❌ Error al procesar el pedido: " . $e->getMessage();
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Confirmación de Pedido</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div style="max-width: 600px; margin: 50px auto; padding: 20px; border: 1px solid green; background-color: #e8ffe8;">
        <h2>Gracias por tu pedido, <?php echo htmlspecialchars($nombre_cliente); ?>!</h2>
        <p><?php echo $mensaje; ?></p>
        <p>Tu orden será procesada por nuestro equipo.</p>
        <a href="index.html">Volver al Menú Principal</a>
    </div>
</body>
</html>