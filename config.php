<?php
declare(strict_types=1);

/*
 * Configuración de conexión.
 * IMPORTANTE: reemplaza MYSQL_HOST_AQUI por el host MySQL completo que aparece
 * en el panel de tu hosting (por ejemplo, mysql-tuusuario.alwaysdata.net).
 */
$dbHost = 'mysql-juancxo.alwaysdata.net';
$dbName = 'juancxo_enviosgit';
$dbUser = 'juacxo';
$dbPass = 'juancho1422';

try {
    $pdo = new PDO(
        "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );

    // Crear la tabla automáticamente si todavía no existe.
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS envios (
            id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
            destinatario VARCHAR(150) NOT NULL,
            descripcion TEXT NOT NULL,
            direccion VARCHAR(255) NOT NULL,
            fecha_creacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fecha_actualizacion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
                ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
} catch (PDOException $e) {
    http_response_code(500);
    exit(
        '<h2>No se pudo conectar con la base de datos</h2>' .
        '<p>Verifica el host MySQL, el nombre de la base de datos, el usuario, ' .
        'la contraseña y los permisos. El host que proporcionaste parece estar incompleto.</p>'
    );
}
