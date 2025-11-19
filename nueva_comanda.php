<?php
include 'db_connection.php';
$mensaje = "";

// Asumo que db_connection.php define y abre $conn
$conn = $conn ?? null;

if ($conn) {
    // 1. Obtener listado de Meseros
    // NOTA: Usar "mesero_id" de la tabla 'meseros'
    $meseros_sql = "SELECT mesero_id, nombre, apellido FROM meseros ORDER BY nombre ASC";
    $meseros_result = $conn->query($meseros_sql);

    // 2. Obtener listado de Mesas disponibles
    // NOTA: Usar "mesa_id" de la tabla 'mesas'
    $mesas_sql = "SELECT mesa_id, numero_mesa FROM mesas WHERE estado = 'Disponible' ORDER BY numero_mesa ASC";
    $mesas_result = $conn->query($mesas_sql);

    // Lógica para crear la Comanda (solo el encabezado)
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['iniciar_comanda'])) {
        $mesero_id = (int)$_POST['mesero_id'];
        $tipo_pedido = $_POST['tipo_pedido'];
        // Si no es 'En Sitio', mesa_id será NULL.
        $mesa_id = ($tipo_pedido == 'En Sitio' && !empty($_POST['mesa_id'])) ? (int)$_POST['mesa_id'] : NULL;

        // Validaciones previas
        if ($tipo_pedido == 'En Sitio' && !$mesa_id) {
            $mensaje = "❌ Error: Debe seleccionar una mesa para pedidos 'En Sitio'.";
        } else {
            // Iniciar la transacción para asegurar consistencia
            $conn->begin_transaction();
            
            try {
                // A. Insertar el Encabezado de la Comanda
                // NOTA: La tabla comandas usa 'mesero_id' y 'mesa_id'
                $stmt_comanda = $conn->prepare("INSERT INTO comandas (mesero_id, mesa_id, tipo_pedido, estado) VALUES (?, ?, ?, 'Pendiente')");
                $stmt_comanda->bind_param("iis", $mesero_id, $mesa_id, $tipo_pedido);
                $stmt_comanda->execute();
                $nueva_comanda_id = $conn->insert_id;
                $stmt_comanda->close();

                // B. Si es 'En Sitio', actualizar el estado de la Mesa a 'Ocupada'
                // NOTA: La tabla mesas usa 'mesa_id'
                if ($tipo_pedido == 'En Sitio' && $mesa_id) {
                    $stmt_mesa = $conn->prepare("UPDATE mesas SET estado = 'Ocupada' WHERE mesa_id = ?");
                    $stmt_mesa->bind_param("i", $mesa_id);
                    $stmt_mesa->execute();
                    $stmt_mesa->close();
                }

                $conn->commit();
                // Redirigir a la siguiente fase: agregar productos
                header("Location: agregar_productos.php?comanda_id=" . $nueva_comanda_id);
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $mensaje = "❌ Error al iniciar la comanda: " . $e->getMessage();
            }
        }
    }

    $conn->close();
} else {
    $mensaje = "❌ Error de conexión a la base de datos. Verifica db_connection.php.";
    $meseros_result = null;
    $mesas_result = null;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Nueva Comanda</title>
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
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        .container {
            width: 100%;
            max-width: 450px; /* Formulario más compacto, ideal para punto de venta */
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
            text-align: center;
        }

        /* ESTILOS DEL FORMULARIO */
        form label {
            display: block;
            margin-top: 15px;
            margin-bottom: 5px;
            font-weight: bold;
            color: var(--color-cafe-oscuro);
        }

        form select {
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
            /* Estilo Select (flecha custom) */
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="292.4" height="292.4"><path fill="%234E342E" d="M287 69.4a17.6 17.6 0 0 0-13.4-6.2H18.8c-5.8 0-11.1 2.4-13.4 6.2s-2.1 9.4 0 14l127.3 127.3c2.2 2.2 5.1 3.4 8.2 3.4s6-.9 8.2-3.4L287 83.4c2.2-4.6 2.4-9.8 0-14z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 10px auto;
        }

        form select:focus {
            border-color: var(--color-destacado-fuerte);
            outline: none;
        }

        input[type="submit"] {
            background-color: var(--color-destacado-fuerte);
            color: white;
            padding: 12px 20px;
            margin-top: 25px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
            font-weight: bold;
            transition: background-color 0.3s ease;
        }

        input[type="submit"]:hover {
            background-color: #A3820B;
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

        /* ENLACE VOLVER (Dashboard) */
        .volver-link {
            display: block;
            text-align: center;
            margin-top: 20px;
        }
        .volver-link a {
            color: var(--color-cafe-oscuro);
            text-decoration: none;
            border: 1px solid #ccc;
            padding: 8px 15px;
            border-radius: 5px;
            transition: background-color 0.3s, color 0.3s;
        }
        .volver-link a:hover {
            background-color: var(--color-cafe-oscuro);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>📝 Iniciar Nueva Comanda / Pedido</h2>
        <hr>

        <?php if ($mensaje): 
            $clase_mensaje = (strpos($mensaje, '✅') !== false) ? 'mensaje-success' : 'mensaje-error';
        ?>
            <p class="mensaje-info <?php echo $clase_mensaje; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>
        
        <form method="POST" action="nueva_comanda.php">
            
            <label for="tipo_pedido">Tipo de Pedido:</label>
            <select id="tipo_pedido" name="tipo_pedido" onchange="toggleMesa(this.value)" required>
                <option value="En Sitio" selected>En Sitio (Para Consumir Aquí)</option>
                <option value="Pickup">Para Llevar (Pickup)</option>
                <option value="Delivery">A Domicilio (Delivery)</option>
            </select>

            <div id="mesa_selector">
                <label for="mesa_id">Mesa Asignada:</label>
                <select id="mesa_id" name="mesa_id">
                    <option value="" disabled selected>-- Seleccionar Mesa --</option>
                    <?php 
                    if ($mesas_result && $mesas_result->num_rows > 0) {
                        // IMPORTANTE: Reiniciar el puntero del resultado si se usa dos veces
                        $mesas_result->data_seek(0);
                        while($mesa = $mesas_result->fetch_assoc()) {
                            echo "<option value='" . htmlspecialchars($mesa["mesa_id"]) . "'>Mesa " . htmlspecialchars($mesa["numero_mesa"]) . "</option>";
                        }
                    } else {
                        echo "<option value=''>No hay mesas disponibles</option>";
                    }
                    ?>
                </select>
            </div>

            <label for="mesero_id">Mesero / Barista:</label>
            <select id="mesero_id" name="mesero_id" required>
                <option value="" disabled selected>-- Seleccionar Mesero --</option>
                <?php 
                if ($meseros_result && $meseros_result->num_rows > 0) {
                    while($mesero = $meseros_result->fetch_assoc()) {
                        echo "<option value='" . htmlspecialchars($mesero["mesero_id"]) . "'>" . htmlspecialchars($mesero["nombre"] . " " . $mesero["apellido"]) . "</option>";
                    }
                } else {
                    echo "<option value=''>Cargue personal primero</option>";
                }
                ?>
            </select>

            <input type="submit" name="iniciar_comanda" value="Iniciar Comanda">
        </form>
        
        <div class="volver-link">
            <a href="toma_pedido.php">← Volver al Panel de Control</a>
        </div>
    </div>

    <script>
        function toggleMesa(tipo) {
            const selector = document.getElementById('mesa_selector');
            const inputMesa = document.getElementById('mesa_id');
            const placeholderOption = inputMesa.querySelector('option[disabled][selected]');

            if (tipo === 'En Sitio') {
                selector.style.display = 'block';
                inputMesa.setAttribute('required', 'required');
                // Si la mesa_id no existe o solo existe el placeholder, asegúrate de que esté seleccionado el placeholder.
                if (placeholderOption) {
                    placeholderOption.selected = true;
                }
            } else {
                selector.style.display = 'none';
                inputMesa.removeAttribute('required');
                // Deseleccionar cualquier mesa para que el valor enviado sea vacío/NULL
                inputMesa.value = ''; 
            }
        }
        
        // Inicializar el estado al cargar
        document.addEventListener('DOMContentLoaded', () => {
            const selectTipo = document.getElementById('tipo_pedido');
            toggleMesa(selectTipo.value);
        });
    </script>
</body>
</html>