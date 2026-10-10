# Gestión de Envíos — PHP + MySQL

Aplicación sencilla para registrar, consultar, buscar, editar y eliminar envíos.

## Campos
- Destinatario
- Descripción
- Dirección

## Instalación

1. Sube `index.php` y `config.php` a una carpeta de tu hosting PHP.
2. Abre `config.php` y reemplaza `MYSQL_HOST_AQUI` por el **host MySQL completo** que muestra tu proveedor.
3. Confirma que el nombre de base de datos, usuario y contraseña coincidan con los datos de tu hosting.
4. Verifica que la base de datos `juancxo_enviosgit` ya exista y que el usuario tenga permisos para crear tablas y modificar registros.
5. Abre la dirección web donde subiste `index.php`.

La aplicación ejecuta `CREATE TABLE IF NOT EXISTS` al conectarse, por lo que crea automáticamente la tabla `envios` si todavía no existe.

## Requisitos
- PHP 8.0 o superior recomendado
- Extensión PDO MySQL habilitada
- MySQL o MariaDB

## Estructura
- `index.php`: interfaz y operaciones CRUD.
- `config.php`: conexión PDO y creación automática de la tabla.

## Importante sobre el host
El host que se recibió estaba incompleto (`mysql-.....`). Debes sustituir `MYSQL_HOST_AQUI` por el valor exacto del panel de hosting. No publiques el archivo `config.php` en un repositorio público con credenciales reales. Si ya compartiste una contraseña real en un lugar público, cámbiala desde el panel del hosting.
