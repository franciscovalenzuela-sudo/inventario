<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Solo administradores pueden resetear contraseñas
if (!canManageUsers()) {
    header("Location: ../index.php");
    exit();
}

$id = $_POST['id'] ?? 0;

if ($id) {
    $database = new Database();
    $db = $database->getConnection();
    
    // Verificar que el usuario existe
    $query = "SELECT * FROM " . DB_PREFIX . "usuarios WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    $usuario = $stmt->fetch();
    
    if ($usuario) {
        // No permitir resetear la propia contraseña
        if ($usuario['id'] == $_SESSION['usuario_id']) {
            $_SESSION['error'] = 'No puedes resetear tu propia contraseña';
            header("Location: listar.php");
            exit();
        }
        
        // Resetear a contraseña por defecto
        $password_hash = password_hash('123456', PASSWORD_DEFAULT);
        
        $query = "UPDATE " . DB_PREFIX . "usuarios SET password = ? WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if ($stmt->execute([$password_hash, $id])) {
            $_SESSION['success'] = 'Contraseña reseteada correctamente para: ' . $usuario['nombre_completo'] . ' (Nueva contraseña: 123456)';
        } else {
            $_SESSION['error'] = 'Error al resetear la contraseña';
        }
    } else {
        $_SESSION['error'] = 'Usuario no encontrado';
    }
} else {
    $_SESSION['error'] = 'ID de usuario no proporcionado';
}

header("Location: listar.php");
exit();
?>