<?php
include 'db_connection.php';
$mensaje = "";

$comanda_id = $_GET['comanda_id'] ?? 0;
if (!$comanda_id) {
    die("ID de Comanda no especificado.");
}

// Asumo que db_connection.php define y abre $conn
$conn = $conn ?? null;

if (!$conn) {
     die("Error de conexión a la base de datos.");
}

// 1. Obtener información crucial de la Comanda
// Nota: Se ha añadido el JOIN a 'mesas' para obtener el número real.
$comanda_sql = $conn->prepare("
    SELECT c.total, c.mesa_id, c.tipo_pedido, c.estado, me.numero_mesa 
    FROM comandas c 
    LEFT JOIN mesas me ON c.mesa_id = me.mesa_id
    WHERE c.comanda_id = ?
");
$comanda_sql->bind_param("i", $comanda_id);
$comanda_sql->execute();
$comanda_result = $comanda_sql->get_result();
$comanda_info = $comanda_result->fetch_assoc();
$comanda_sql->close();

if (!$comanda_info) {
    die("Comanda no encontrada.");
}

$total = $comanda_info['total'] ?? 0;
$mesa_id = $comanda_info['mesa_id'];
$numero_mesa = $comanda_info['numero_mesa'];
$tipo_pedido = $comanda_info['tipo_pedido'];
$estado_actual = $comanda_info['estado'];

// 2. Procesar el Pago (Lógica de Actualización)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['procesar_pago'])) {
    
    if ($estado_actual == 'Pagada') {
        $mensaje = "Esta comanda ya ha sido pagada.";
    } else {
        
        // Iniciar Transacción
        $conn->begin_transaction();
        $exito = true;

        try {
            // A. Actualizar estado de la Comanda a 'Pagada'
            $stmt_comanda = $conn->prepare("UPDATE comandas SET estado = 'Pagada' WHERE comanda_id = ?");
            $stmt_comanda->bind_param("i", $comanda_id);
            if (!$stmt_comanda->execute()) {
                throw new Exception("Error al actualizar la comanda.");
            }
            $stmt_comanda->close();

            // B. Si es 'En Sitio', liberar la Mesa
            if ($tipo_pedido == 'En Sitio' && $mesa_id) {
                $stmt_mesa = $conn->prepare("UPDATE mesas SET estado = 'Disponible' WHERE mesa_id = ?");
                $stmt_mesa->bind_param("i", $mesa_id);
                if (!$stmt_mesa->execute()) {
                    throw new Exception("Error al liberar la mesa.");
                }
                $stmt_mesa->close();
            }

            // Si todo fue bien, confirmar la transacción
            $conn->commit();
            $mesa_liberada = ($tipo_pedido == 'En Sitio' && $numero_mesa) ? "La mesa $numero_mesa está libre." : "";
            $mensaje = "✅ Pago procesado y Comanda #$comanda_id cerrada con éxito. $mesa_liberada";
            $estado_actual = 'Pagada'; 

        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "❌ Error en la transacción: " . $e->getMessage();
        }
    }
}

// 3. Obtener los detalles de la comanda para el resumen
$detalles_sql = "SELECT dc.*, p.nombre as nombre_producto FROM detalles_comanda dc JOIN productos p ON dc.producto_id = p.producto_id WHERE dc.comanda_id = $comanda_id";
$detalles_result = $conn->query($detalles_sql);

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cerrar Comanda #<?php echo htmlspecialchars($comanda_id); ?></title>
    <style>
        /* Paleta de Colores (refinada) */
        :root {
            --color-cafe-oscuro: #4E342E;
            --color-crema-fondo: #F5F1EB; /* Fondo más sutil */
            --color-principal: #FFFFFF;
            --color-destacado-fuerte: #D4AC0D;
            --color-sombra: rgba(0, 0, 0, 0.15);
            --color-error: #dc3545;
            --color-success: #28a745;
        }

        body { 
            font-family: 'Arial', sans-serif; 
            background-color: var(--color-crema-fondo); 
            color: var(--color-cafe-oscuro); 
            margin: 0; 
            padding: 20px; 
            display: flex;
            justify-content: center;
        }

        .container {
            max-width: 900px; 
            width: 100%;
            background: var(--color-principal);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 5px 20px var(--color-sombra);
        }

        h2 { 
            color: var(--color-cafe-oscuro); 
            border-bottom: 3px solid var(--color-destacado-fuerte); 
            padding-bottom: 15px; 
            margin-bottom: 25px;
            text-align: center;
            font-size: 2em;
        }

        /* Enlace Volver */
        .volver-link {
            display: block;
            margin-bottom: 20px;
            text-align: left;
        }
        .volver-link a {
            color: var(--color-cafe-oscuro);
            text-decoration: none;
            padding: 8px 15px;
            border: 1px solid #ccc;
            border-radius: 5px;
            transition: background-color 0.3s;
        }
        .volver-link a:hover {
            background-color: #eee;
        }

        /* LAYOUT DE DOS COLUMNAS */
        .content-layout {
            display: flex;
            gap: 30px;
        }
        .details-column, .payment-column {
            flex: 1;
            padding: 0 15px;
        }
        .details-column {
            border-right: 1px solid #ddd;
        }
        
        /* TICKET/RESUMEN INFO */
        .info-box {
            background-color: #f7f5f0; /* Color crema suave para fondo */
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            border-left: 4px solid var(--color-destacado-fuerte);
        }
        .info-box p {
            margin: 5px 0;
            line-height: 1.4;
        }
        .info-box strong {
            font-weight: bold;
        }
        
        /* TABLA TIPO TICKET */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table thead th {
            border-bottom: 2px solid var(--color-cafe-oscuro);
            padding: 10px 0;
            text-align: left;
            font-size: 0.95em;
        }

        table tbody td {
            padding: 8px 0;
            border-bottom: 1px dashed #ccc;
            font-size: 0.9em;
        }
        
        /* Columnas alineadas */
        table td:nth-child(2) {
            text-align: center;
            width: 15%;
        }
        table td:nth-child(3) {
            text-align: right;
            font-weight: bold;
            color: #C0392B; /* Rojo para subtotales */
            width: 25%;
        }
        
        /* TOTAL DESTACADO */
        .total-box {
            background-color: var(--color-cafe-oscuro);
            color: white;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
            text-align: center;
            font-size: 2.2em;
            font-weight: bold;
            box-shadow: 0 6px 15px var(--color-sombra);
            letter-spacing: 1px;
        }

        /* FORMULARIO DE PAGO */
        .pago-form h4 {
            font-size: 1.5em;
            color: var(--color-cafe-oscuro);
            margin-top: 0;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .metodo-pago-opciones input[type="radio"] {
            margin-right: 8px;
        }
        .metodo-pago-opciones label {
            margin-right: 20px;
            font-size: 1.1em;
            cursor: pointer;
        }

        /* Botón de Confirmar Pago */
        input[type="submit"] {
            background-color: var(--color-success);
            color: white;
            padding: 15px 20px;
            margin-top: 25px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            width: 100%;
            font-size: 1.2em;
            font-weight: bold;
            box-shadow: 0 4px 8px rgba(40, 167, 69, 0.4);
        }

        input[type="submit"]:hover {
            background-color: #1E8449;
        }
        
        /* Mensajes */
        .mensaje-info {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
            font-size: 1.1em;
        }
        .mensaje-success {
            background-color: #d4edda;
            color: var(--color-success);
            border: 1px solid #c3e6cb;
        }
        .mensaje-error {
            background-color: #f8d7da;
            color: var(--color-error);
            border: 1px solid #f5c6cb;
        }
        
        /* Media Queries para diseño responsive */
        @media (max-width: 768px) {
            .content-layout {
                flex-direction: column;
            }
            .details-column {
                border-right: none;
                border-bottom: 1px solid #ddd;
                padding-bottom: 20px;
                margin-bottom: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>💵 Pago y Cierre de Comanda #<?php echo htmlspecialchars($comanda_id); ?></h2>
        
        <div class="volver-link">
             <a href="listado_comandas.php">← Volver al Listado de Comandas</a>
        </div>
        
        <?php if (isset($mensaje)): 
            $clase_mensaje = (strpos($mensaje, '✅') !== false) ? 'mensaje-success' : 'mensaje-error';
        ?>
            <p class="mensaje-info <?php echo $clase_mensaje; ?>"><?php echo htmlspecialchars($mensaje); ?></p>
        <?php endif; ?>

        <div class="content-layout">
            
            <div class="details-column">
                <h3>Detalles de la Comanda</h3>

                <div class="info-box">
                    <p><strong>Comanda ID:</strong> <?php echo htmlspecialchars($comanda_id); ?></p>
                    <p><strong>Tipo de Pedido:</strong> <?php echo htmlspecialchars($tipo_pedido); ?></p>
                    <?php if ($numero_mesa): ?>
                        <p><strong>Mesa Asignada:</strong> Mesa <?php echo htmlspecialchars($numero_mesa); ?></p>
                    <?php endif; ?>
                    <p><strong>Estado Actual:</strong> 
                        <span style="color: <?php echo ($estado_actual == 'Pagada' ? 'var(--color-success)' : '#D4AC0D'); ?>;">
                            <?php echo htmlspecialchars($estado_actual); ?>
                        </span>
                    </p>
                </div>

                <h4>Productos Solicitados</h4>
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cant.</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($detalles_result && $detalles_result->num_rows > 0) {
                            while($detalle = $detalles_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . htmlspecialchars($detalle["nombre_producto"]) . "</td>";
                                echo "<td>" . htmlspecialchars($detalle["cantidad"]) . "</td>";
                                echo "<td>" . number_format($detalle["subtotal"], 2) . "</td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='3' style='text-align: center; border-bottom: none;'>Esta comanda no tiene productos.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <div class="payment-column">
                <div class="total-box">
                    TOTAL A PAGAR: $<?php echo number_format($total, 2); ?>
                </div>

                <?php if ($estado_actual != 'Pagada'): ?>
                    <div class="pago-form">
                        <h4>Confirmar Pago</h4>
                        <form method="POST" action="cerrar_comanda.php?comanda_id=<?php echo htmlspecialchars($comanda_id); ?>">
                            <p>Seleccione el método de pago:</p>
                            <div class="metodo-pago-opciones">
                                <input type="radio" id="pago-efectivo" name="metodo_pago" value="Efectivo" checked> <label for="pago-efectivo">Efectivo</label>
                                <input type="radio" id="pago-tarjeta" name="metodo_pago" value="Tarjeta"> <label for="pago-tarjeta">Tarjeta</label>
                            </div>
                            
                            <input type="submit" name="procesar_pago" value="💰 Confirmar Pago y Cerrar Comanda">
                        </form>
                    </div>
                <?php else: ?>
                    <p class="mensaje-info mensaje-success" style="margin-top: 30px;">
                        Comanda Pagada y Cerrada.
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>