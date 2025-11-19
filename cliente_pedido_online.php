<?php
include 'db_connection.php';

// Obtener todos los productos para listarlos y permitir la selección
$sql = "SELECT producto_id, nombre, precio, categoria FROM productos ORDER BY categoria, nombre";
$result = $conn->query($sql);

$menu_por_categoria = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $menu_por_categoria[$row['categoria']][] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hacer Pedido Online</title>
    <link rel="stylesheet" href="css/estilos.css"> 
    <style>
    /* ------------------------------ */
    /* 1. AJUSTE BASE Y POSICIONAMIENTO */
    /* ------------------------------ */
    body {
        /* Fuente ligeramente más moderna y legible */
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
        background-color: #f7f5f3; /* Fondo crema muy sutil */
        color: #444444; 
        padding: 0;
        margin: 0;
        /* IMPORTANTE: Asegura que haya espacio debajo del menú fijo. */
        /* Si tu menú fijo tiene 50px de altura, el body necesita un padding superior. */
        padding-top: 70px; /* Se agregó padding superior para bajar la sección principal */
    }
    
    /* ------------------------------ */
    /* 2. LAYOUT Y CONTENEDOR (La Caja principal) */
    /* ------------------------------ */
    .container { 
        max-width: 950px; /* Un poco más ancho */
        margin: 40px auto 70px auto; /* Más margen inferior para respirar */
        background: #ffffff; 
        padding: 40px 50px; /* Más padding horizontal */
        border-radius: 15px; /* Bordes más suaves */
        /* Sombra sutil y elegante */
        box-shadow: 0 10px 30px rgba(0,0,0,0.1); 
        transition: all 0.3s ease;
        border: 1px solid #e0e0e0; /* Borde muy fino para definición */
    }
    
    /* 3. ENCABEZADO */
    .header { 
        background-color: #5d4037; 
        color: white; 
        padding: 25px 15px; /* Más padding vertical */
        text-align: center; 
        margin-bottom: 40px; 
        border-radius: 12px; 
        font-size: 2em; /* Título más grande */
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
    }

    /* 4. SECCIONES DEL MENÚ */
    .category-section {
        margin-bottom: 35px;
        padding-top: 10px;
    }
    
    .category-section h3 { 
        color: #3e2723; /* Marrón más oscuro */
        border-bottom: 2px solid #bcaaa4; 
        padding-bottom: 10px; 
        margin-bottom: 20px;
        font-size: 1.6em;
        font-weight: 600;
    }
    
    /* 5. ÍTEMS DEL MENÚ */
    .menu-item { 
        display: flex; 
        justify-content: space-between; 
        align-items: center; 
        margin-bottom: 15px; 
        padding: 12px 0; 
        border-bottom: 1px solid #f0f0f0; /* Línea separadora muy clara */
        transition: background-color 0.2s;
    }
    
    .menu-item:hover {
        background-color: #fafafa;
        border-radius: 5px;
    }

    .item-details { 
        flex-grow: 1;
        font-size: 1.15em;
        font-weight: 500;
        color: #3e2723; 
    }
    
    .item-input { 
        width: 140px; 
        text-align: right; 
        display: flex;
        align-items: center;
        gap: 15px;
        font-size: 0.9em;
        color: #777;
    }
    
    /* 6. INPUTS DE CANTIDAD */
    input[type="number"] { 
        width: 60px; 
        padding: 8px; 
        border: 1px solid #bcaaa4; 
        border-radius: 8px; /* Bordes más redondos */
        text-align: center;
        font-size: 1em;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    input[type="number"]:focus {
        border-color: #5d4037; 
        box-shadow: 0 0 8px rgba(93, 64, 55, 0.4);
        outline: none;
    }
    
    /* 7. INPUTS DE CLIENTE */
    .client-info {
        border: 2px dashed #d7ccc8; /* Borde doble dashed */
        padding: 25px;
        margin-top: 40px;
        border-radius: 10px;
        background-color: #fcfcfc; /* Fondo ligeramente diferente para destacar */
    }
    
    .client-info label {
        display: block;
        margin-bottom: 5px;
        font-weight: 600;
        color: #3e2723;
    }
    
    .client-info input, .client-info select { 
        width: 100%; 
        padding: 14px; 
        margin-bottom: 18px; 
        border: 1px solid #d7ccc8; 
        border-radius: 8px; 
        box-sizing: border-box; 
    }
    
    /* 8. BOTÓN DE ENVÍO */
    .submit-btn { 
        background-color: #ff9800; 
        color: white; 
        padding: 18px 35px; /* Botón más grande */
        border: none; 
        border-radius: 10px; 
        cursor: pointer; 
        font-size: 1.3em; 
        margin-top: 40px; 
        width: 100%;
        font-weight: 700;
        transition: background-color 0.3s ease, transform 0.1s;
        box-shadow: 0 5px 15px rgba(255, 152, 0, 0.4);
    }

    .submit-btn:hover {
        background-color: #e68900;
        transform: translateY(-3px); /* Pequeño lift al hacer hover */
        box-shadow: 0 8px 18px rgba(255, 152, 0, 0.5);
    }
</style>
</head>
<header>
        <nav class="main-nav">
            <ul>
                <li><a href="index.html">Inicio</a></li> 
                <li><a href="menu_simple.php">Menú</a></li>
                <li><a href="cliente_pedido_online.php"> Hacer Pedido</a></li>
                <li><a href="reservas.html">Reservarciones</a></li>
            </ul>
        </nav>
    </header>
<body>
    
    
    <div class="container">
        
        <form method="POST" action="procesar_pedido_online.php">
            
            <h2>1. Tu Orden</h2>
            <?php foreach ($menu_por_categoria as $categoria => $items): ?>
                <div class="category-section">
                    <h3><?php echo htmlspecialchars($categoria); ?></h3>
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item">
                            <div class="item-details">
                                <strong><?php echo htmlspecialchars($item['nombre']); ?></strong> 
                                ( $<?php echo number_format($item['precio'], 2); ?> )
                            </div>
                            <div class="item-input">
                                <label for="prod_<?php echo $item['producto_id']; ?>">Cantidad:</label>
                                <input type="number" name="productos[<?php echo $item['producto_id']; ?>]" id="prod_<?php echo $item['producto_id']; ?>" min="0" value="0">
                                <input type="hidden" name="precios[<?php echo $item['producto_id']; ?>]" value="<?php echo $item['precio']; ?>">
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>

            <hr>

            <h2>2. Datos y Entrega</h2>
            <div class="client-info">
                <label for="nombre_cliente">Nombre:</label>
                <input type="text" name="nombre_cliente" required placeholder="Tu nombre completo"><br>
                
                <label for="telefono">Teléfono:</label>
                <input type="text" name="telefono" required placeholder="Teléfono de contacto"><br>
                
                <label for="tipo_pedido">Tipo de Servicio:</label>
                <select name="tipo_pedido" id="tipo_pedido" required onchange="toggleAddress(this.value)">
                    <option value="Pickup">Para Recoger en Tienda (Pickup)</option>
                    <option value="Delivery">Entrega a Domicilio (Delivery)</option>
                </select><br>

                <div id="address_field" style="display:none; margin-top: 10px;">
                    <label for="direccion">Dirección de Entrega:</label>
                    <textarea name="direccion" id="direccion" rows="3" placeholder="Calle, número, colonia, referencias"></textarea><br>
                </div>
            </div>

            <input type="submit" class="submit-btn" value="Confirmar Pedido y Pagar">
        </form>
    </div>
    <footer>
        <p>&copy; 2025 ¡¡El despertar!!. Todos los derechos reservados.</p>
    </footer>

    <script>
        function toggleAddress(tipo) {
            const addressField = document.getElementById('address_field');
            const direccionInput = document.getElementById('direccion');
            if (tipo === 'Delivery') {
                addressField.style.display = 'block';
                direccionInput.setAttribute('required', 'required');
            } else {
                addressField.style.display = 'none';
                direccionInput.removeAttribute('required');
            }
        }
        // Asegurar que el estado inicial se maneje correctamente
        document.addEventListener('DOMContentLoaded', () => {
             toggleAddress(document.getElementById('tipo_pedido').value);
        });
    </script>
</body>
</html>