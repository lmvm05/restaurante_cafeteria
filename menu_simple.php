<?php
// Incluye la conexión a la base de datos
include 'db_connection.php'; 

// Obtener todos los productos y agruparlos por categoría
$sql = "SELECT nombre, descripcion, precio, categoria FROM productos ORDER BY categoria, nombre";
$result = $conn->query($sql);

$menu_por_categoria = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $categoria = $row['categoria'];
        // Agrupar los productos
        $menu_por_categoria[$categoria][] = $row;
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Menú - Cafetería Digital</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
     <link rel="stylesheet" href="css/estilos.css"> 
    <style>
        body {
            font-family: Georgia, serif; /* Fuente elegante tipo serif para menú */
            background-color: #f7f3f0; /* Fondo crema */
            color: #3e2723; /* Texto café oscuro */
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 700px;
            margin: 80px auto;
            padding: 40px;
            background: white;
            border: 1px solid #c9c9c9;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .header {
            text-align: center;
            margin-bottom: 40px;
        }
        .header h1 {
            font-family: 'Times New Roman', serif;
            font-size: 2.5em;
            color: #4e342e; /* Café muy oscuro */
            border-bottom: 3px double #bcaaa4;
            padding-bottom: 10px;
            margin-bottom: 5px;
        }
        .header p {
            font-style: italic;
            color: #795548;
        }
        .category-section {
            margin-bottom: 30px;
        }
        .category-section h2 {
            font-size: 1.8em;
            color: #6d4c41;
            margin-top: 0;
            padding: 5px 0;
            border-left: 5px solid #ffcc80; /* Barra lateral de color crema/naranja */
            padding-left: 10px;
            margin-bottom: 15px;
        }
        .menu-item {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 10px 0;
            border-bottom: 1px dashed #e0e0e0;
        }
        .item-details {
            flex-grow: 1;
            padding-right: 15px;
        }
        .item-name {
            font-weight: bold;
            font-size: 1.1em;
        }
        .item-description {
            font-size: 0.9em;
            color: #795548;
            margin-top: 3px;
        }
        .item-price {
            font-weight: bold;
            color: #4e342e;
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
        <div class="header">
            <h1>Menú de la Casa</h1>
            <p>Sabores únicos para tu día</p>
        </div>
        
        <?php if (empty($menu_por_categoria)): ?>
            <p style="text-align: center;">Nuestro menú está en preparación. ¡Vuelve pronto!</p>
        <?php else: ?>
            
            <?php foreach ($menu_por_categoria as $categoria => $items): ?>
                <div class="category-section">
                    <h2><?php echo htmlspecialchars($categoria); ?></h2>
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item">
                            <div class="item-details">
                                <span class="item-name"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                <div class="item-description"><?php echo htmlspecialchars($item['descripcion']); ?></div>
                            </div>
                            <span class="item-price">$<?php echo number_format($item['precio'], 2); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endforeach; ?>
            
        <?php endif; ?>

    </div>
    <footer>
        <p>&copy; 2025 ¡¡El despertar!!. Todos los derechos reservados.</p>
    </footer>
</body>
</html>