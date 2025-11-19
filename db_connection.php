<?php
// db_connection.php (Versión no modificada, para referencia)

// Configuración de la base de datos
$servername = "localhost";
$username = "root"; // Cambia esto por tu usuario de MySQL
$password = ""; // Cambia esto por tu contraseña de MySQL
$dbname = "cafeteria_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Conexión fallida: " . $conn->connect_error);
}

// Opcional: Establecer el juego de caracteres a UTF8
$conn->set_charset("utf8");

// La conexión queda abierta en la variable $conn
?>