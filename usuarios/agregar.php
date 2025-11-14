<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Solo administradores pueden agregar usuarios
if (!canManageUsers()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

if ($_POST) {
    $usuario = $_POST['usuario'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $nombre_completo = $_POST['nombre_completo'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    
    try {
        $query = "INSERT INTO " . DB_PREFIX . "usuarios 
                  (usuario, password, nombre_completo, email, rol) 
                  VALUES (?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$usuario, $password, $nombre_completo, $email, $rol]);
        
        $mensaje = mostrarAlerta('success', 'Usuario creado correctamente');
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al crear usuario: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-plus me-2"></i>Agregar Nuevo Usuario</h2>
    <a href="listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $mensaje; ?>
        
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Usuario *</label>
                    <input type="text" class="form-control" name="usuario" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Contraseña *</label>
                    <input type="password" class="form-control" name="password" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" name="nombre_completo" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Rol *</label>
                    <select class="form-select" name="rol" required>
                        <option value="usuario">Usuario (Solo lectura)</option>
                        <option value="encargado">Encargado (Gestionar inventario)</option>
                        <option value="admin">Administrador (Control total)</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Crear Usuario
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>