<?php
session_start();

// 1. Requerir la conexión (aunque no la use directamente, es buena práctica)
require 'db_connection.php'; 

// 2. Seguridad: Verificar que el usuario haya iniciado sesión y sea Administrador
if (!isset($_SESSION['usuario_id']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    // Si no es Administrador, lo redirigimos a la toma de pedido o al login.
    header('Location: login.php'); 
    exit;
}

$nombre_usuario = htmlspecialchars($_SESSION['usuario_nombre']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard de Administración - El Despertar</title>
    <style>
    /* VARIABLES (Opcional, pero útil para mantener consistencia) */
    :root {
        --color-cafe-oscuro: #4E342E;      /* Marrón oscuro para texto y bordes */
        --color-crema-fondo: #FDF7F0;     /* Fondo sutil */
        --color-destacado-claro: #F9E79F; /* Amarillo suave para fondo de ítem */
        --color-destacado-fuerte: #D4AC0D; /* Amarillo/Dorado para hover */
        --color-sombra: rgba(0, 0, 0, 0.1);
    }

    /* GENERAL */
    body { 
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; /* Fuente más moderna */
        background-color: var(--color-crema-fondo); /* Fondo suave */
        color: var(--color-cafe-oscuro); /* Color de texto basado en café */
        margin: 0; 
        padding: 40px; /* Más padding en general */
        min-height: 100vh; /* Asegura que el fondo cubra toda la página */
    }

    /* CONTENEDOR PRINCIPAL */
    .container { 
        max-width: 1000px; /* Un poco más ancho */
        margin: 0 auto; 
        background: white; 
        padding: 40px; /* Más padding interno */
        border-radius: 12px; /* Bordes más suaves */
        box-shadow: 0 8px 16px var(--color-sombra); /* Sombra más profunda */
    }

    /* TÍTULO */
    h1 { 
        color: var(--color-cafe-oscuro); 
        border-bottom: 3px solid var(--color-destacado-fuerte); /* Borde más visible */
        padding-bottom: 15px; 
        margin-bottom: 30px;
        font-size: 2em;
    }

    /* GRILLA DEL MENÚ */
    .menu-grid { 
        display: grid; 
        /* La rejilla se mantiene, es eficiente */
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); 
        gap: 30px; /* Más separación entre ítems */
        margin-top: 30px; 
    }

    /* ÍTEMS DE LA GRILLA (Tarjetas) */
    .menu-item { 
        text-align: center; 
        background-color: var(--color-destacado-claro); /* Color base más suave */
        padding: 25px 15px; /* Más vertical, menos horizontal */
        border-radius: 10px; 
        transition: transform 0.3s ease, background-color 0.3s ease, box-shadow 0.3s ease; /* Transiciones suaves */
        cursor: pointer;
    }
    
    .menu-item:hover { 
        transform: translateY(-8px); /* Efecto de elevación más notable */
        background-color: var(--color-destacado-fuerte); 
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2); /* Sombra al elevarse */
    }

    /* ESTILO DEL ENLACE DENTRO DEL ÍTEM */
    .menu-item a { 
        text-decoration: none; 
        color: var(--color-cafe-oscuro); 
        font-weight: 600; /* Un poco menos negrita */
        font-size: 1.2em; /* Texto ligeramente más grande */
        display: block; 
    }
    
    .menu-item:hover a { /* El texto se pone blanco/claro al hacer hover */
        color: white; 
    }

    /* ESTILO DE LOGOUT */
    .logout { 
        text-align: right; 
        margin-top: 30px; 
        font-size: 0.9em;
    }
    .logout a {
        color: var(--color-cafe-oscuro);
        text-decoration: none;
        padding: 5px 10px;
        border: 1px solid var(--color-cafe-oscuro);
        border-radius: 5px;
        transition: background-color 0.3s;
    }
    .logout a:hover {
        background-color: var(--color-cafe-oscuro);
        color: white;
    }

    /* Responsive básico para pantallas pequeñas */
    @media (max-width: 600px) {
        body {
            padding: 10px;
        }
        .container {
            padding: 20px;
        }
    }
</style>
</head>
<body>
    <div class="container">
        <h1>☕ Panel de Control | El Despertar</h1>
        <p>Bienvenido, **<?php echo $nombre_usuario; ?>** (Administrador). Selecciona una tarea:</p>

        <hr>

        <h2>Gestión y Operaciones</h2>
        <div class="menu-grid">
            
            <div class="menu-item">
                <a href="crear_mesa.php">
                    📋 <br>
                    Gestión de Mesas
                </a>
            </div>

            <div class="menu-item">
                <a href="crear_mesero.php">
                    👨‍🍳 <br>
                    Alta de Personal (Mesero/Admin)
                </a>
            </div>

            <div class="menu-item">
                <a href="crear_producto.php">
                    🥐 <br>
                    Gestión de Productos/Menú
                </a>
            </div>
            
            <div class="menu-item">
                <a href="crear_reservacion.php">
                    📅 <br>
                    Registro de Reservaciones
                </a>
            </div>

            <div class="menu-item">
                <a href="nueva_comanda.php">
                    ✍️ <br>
                    Tomar Nueva Comanda (Operación)
                </a>
            </div>
        </div>
        
        <div class="logout">
            <a href="index.html">Cerrar Sesión</a>
        </div>
    </div>
</body>
</html>