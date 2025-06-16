<?php

// db.php  Nuestro archivo clave para conectar la aplicación a la base de datos.

// Credenciales de la Base de Datos
// Info necesaria para que PHP sepa a dónde y cómo conectarse
$host = 'localhost';      // El servidor de la base de datos (nuestro equipo en desarrollo)
$db = 'finance_focus';    // Nombre de nuestra base de datos
$user = 'root';           // Usuario de la base de datos
$pass = '';               // Contraseña del usuario (vacía en desarrollo, pero con una real en producción)

// Conexión a la Base de Datos (Try-Catch para Manejo de Errores)
// Intentamos conectar usando PDO para una conexión segura y moderna
try {
    // Creamos la instancia PDO para MySQL.
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);

    // Configuramos PDO para que lance excepciones si hay errores en la base de datos
    // Esto nos permite capturar y manejar cualquier problema de forma controlada
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    // Si la conexión falla, capturamos el error y mostramos un mensaje
    // En producción, esto se registraría en un log en lugar de mostrarse directamente
    echo "¡Error al conectar a la base de datos! " . $e->getMessage();
}

?>