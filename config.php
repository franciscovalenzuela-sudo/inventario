<?php
/**
 * Configuración del Sistema de Inventarios
 */

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_inventarios');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PREFIX', 'inv_');

// Configuración de la aplicación
define('SITE_NAME', 'Sistema de Inventarios');
define('SITE_URL', 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']));

// Roles del sistema
define('ROL_ADMIN', 'admin');
define('ROL_ENCARGADO', 'encargado');
define('ROL_USUARIO', 'usuario');

// Agregar después de las definiciones de roles
define('MODULO_PROVEEDORES', true);


// Iniciar sesión
session_start();

// Función para verificar autenticación
function requireAuth() {
    if (!isset($_SESSION['usuario_id'])) {
        header('Location: ' . SITE_URL . '/usuarios/login.php');
        exit();
    }
}

// Función para verificar si es admin
function isAdmin() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === ROL_ADMIN;
}

// Función para verificar si es encargado
function isEncargado() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === ROL_ENCARGADO;
}

// Función para verificar si es usuario común
function isUsuario() {
    return isset($_SESSION['usuario_rol']) && $_SESSION['usuario_rol'] === ROL_USUARIO;
}

// Función para verificar permisos de escritura (admin y encargado)
function canWrite() {
    return isAdmin() || isEncargado();
}

// Función para verificar permisos de administración (solo admin)
function canManageUsers() {
    return isAdmin();
}

// Función para generar código completo
function generarCodigoCompleto($categoria_principal, $subcategoria, $tipo_objeto) {
    return $categoria_principal . '.' . $subcategoria . '.' . $tipo_objeto;
}

// Función para conexión a la base de datos
function getDatabaseConnection() {
    static $conn = null;
    if ($conn === null) {
        try {
            $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            error_log("Error de conexión: " . $exception->getMessage());
            return false;
        }
    }
    return $conn;
}

// Función para mostrar alertas
function mostrarAlerta($tipo, $mensaje) {
    return '<div class="alert alert-' . $tipo . ' alert-dismissible fade show" role="alert">
        ' . $mensaje . '
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>';
}
?>