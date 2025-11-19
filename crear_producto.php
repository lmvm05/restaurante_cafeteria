<?php
include 'db_connection.php';
$mensaje = "";

// Lógica para manejar el formulario (CREATE)
// Asumo que $conn está disponible globalmente después de incluir 'db_connection.php'
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $precio = $_POST['precio'];
    $categoria = $_POST['categoria'];

    // Usando prepared statement
    // NOTA: El tipo "d" es para double/decimal, que es correcto para el precio.
    $stmt = $conn->prepare("INSERT INTO productos (nombre, descripcion, precio, categoria) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $nombre, $descripcion, $precio, $categoria);

    if ($stmt->execute()) {
        $mensaje = "✅ Nuevo producto **" . htmlspecialchars($nombre) . "** creado con éxito.";
    } else {
        $mensaje = "❌ Error: " . $stmt->error;
    }

    $stmt->close();
}

// Cierre de conexión
$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto</title>
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
            max-width: 550px; /* Un poco más ancho para la descripción */
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

        form input[type="text"],
        form input[type="number"], 
        form select,
        form textarea { /* Aplicamos estilos base a todos los campos */
            width: 100%;
            padding: 10px 15px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
            transition: border-color 0.3s;
        }
        
        form textarea {
            resize: vertical; /* Permite redimensionar solo verticalmente */
            min-height: 100px;
        }

        form input:focus, 
        form select:focus,
        form textarea:focus {
            border-color: var(--color-destacado-fuerte);
            outline: none;
        }
        
        /* Estilo específico para Select (consistencia) */
        form select {
            appearance: none;
            background-image: url('data:image/svg+xml;charset=US-ASCII,<svg xmlns="http://www.w3.org/2000/svg" width="292.4" height="292.4"><path fill="%234E342E" d="M287 69.4a17.6 17.6 0 0 0-13.4-6.2H18.8c-5.8 0-11.1 2.4-13.4 6.2s-2.1 9.4 0 14l127.3 127.3c2.2 2.2 5.1 3.4 8.2 3.4s6-.9 8.2-3.4L287 83.4c2.2-4.6 2.4-9.8 0-14z"/></svg>');
            background-repeat: no-repeat;
            background-position: right 10px top 50%;
            background-size: 10px auto;
        }

        input[type="submit"] {
            background-color: var(--color-destacado-fuerte);
            color: white;
            padding: 12px 20px;
            margin-top: 20px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 1.1em;
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

        /* ENLACE VOLVER */
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
        <h2>🥐 Gestión del Menú</h2>
        <hr>

        <?php if ($mensaje): 
            $clase_mensaje = (strpos($mensaje, '✅') !== false) ? 'mensaje-success' : 'mensaje-error';
        ?>
            <p class="mensaje-info <?php echo $clase_mensaje; ?>"><?php echo $mensaje; ?></p>
        <?php endif; ?>

        <form method="POST" action="crear_producto.php">
            <label for="nombre">Nombre del Producto:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label for="descripcion">Descripción:</label>
            <textarea id="descripcion" name="descripcion"></textarea>

            <label for="precio">Precio ($):</label>
            <input type="number" id="precio" name="precio" step="0.01" min="0.01" required>

            <label for="categoria">Categoría:</label>
            <select id="categoria" name="categoria" required>
                <option value="" disabled selected>-- Seleccionar Categoría --</option>
                <option value="Bebidas Calientes">Bebidas Calientes</option>
                <option value="Bebidas Frías">Bebidas Frías</option>
                <option value="Repostería">Repostería</option>
                <option value="Comida Salada">Comida Salada</option>
            </select>

            <input type="submit" value="Guardar Producto">
        </form>
        
        <div class="volver-link">
            <a href="toma_pedido.php">← Volver al Panel de Control</a>
        </div>
    </div>
</body>
</html>