<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Solo administradores pueden editar usuarios
if (!canManageUsers()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;
$mensaje = '';

// Obtener datos del usuario
$query = "SELECT * FROM " . DB_PREFIX . "usuarios WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$usuario = $stmt->fetch();

if (!$usuario) {
    header("Location: listar.php");
    exit();
}

// No permitir que un usuario se edite a sí mismo (para evitar bloqueos)
if ($usuario['id'] == $_SESSION['usuario_id']) {
    $mensaje = mostrarAlerta('warning', 'No puedes editar tu propio usuario desde aquí. Usa el perfil de usuario.');
}

if ($_POST && !$mensaje) {
    $usuario_nuevo = $_POST['usuario'];
    $nombre_completo = $_POST['nombre_completo'];
    $email = $_POST['email'];
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];
    
    // Si se proporciona una nueva contraseña, actualizarla
    $password = $_POST['password'];
    $actualizar_password = !empty($password);
    
    try {
        if ($actualizar_password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . DB_PREFIX . "usuarios 
                      SET usuario = ?, nombre_completo = ?, email = ?, rol = ?, estado = ?, password = ?
                      WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$usuario_nuevo, $nombre_completo, $email, $rol, $estado, $password_hash, $id]);
        } else {
            $query = "UPDATE " . DB_PREFIX . "usuarios 
                      SET usuario = ?, nombre_completo = ?, email = ?, rol = ?, estado = ?
                      WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$usuario_nuevo, $nombre_completo, $email, $rol, $estado, $id]);
        }
        
        $mensaje = mostrarAlerta('success', 'Usuario actualizado correctamente' . ($actualizar_password ? ' (contraseña cambiada)' : ''));
        
        // Actualizar datos locales
        $usuario['usuario'] = $usuario_nuevo;
        $usuario['nombre_completo'] = $nombre_completo;
        $usuario['email'] = $email;
        $usuario['rol'] = $rol;
        $usuario['estado'] = $estado;
        
    } catch (PDOException $e) {
        if ($e->getCode() == 23000) { // Error de duplicado
            $mensaje = mostrarAlerta('danger', 'Error: El nombre de usuario ya existe');
        } else {
            $mensaje = mostrarAlerta('danger', 'Error al actualizar usuario: ' . $e->getMessage());
        }
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-user-edit me-2"></i>Editar Usuario</h2>
    <a href="listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Usuarios
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $mensaje; ?>
        
        <?php if ($usuario['id'] == $_SESSION['usuario_id']): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Advertencia:</strong> Estás editando tu propio usuario. Ten cuidado con los cambios que realices.
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Usuario *</label>
                    <input type="text" class="form-control" name="usuario" value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                    <div class="form-text">Nombre de usuario para iniciar sesión</div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Contraseña</label>
                    <input type="password" class="form-control" name="password" placeholder="Dejar vacío para mantener la actual">
                    <div class="form-text">Solo llena si deseas cambiar la contraseña</div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Nombre Completo *</label>
                    <input type="text" class="form-control" name="nombre_completo" value="<?php echo htmlspecialchars($usuario['nombre_completo']); ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Rol *</label>
                    <select class="form-select" name="rol" required>
                        <option value="usuario" <?php echo $usuario['rol'] == 'usuario' ? 'selected' : ''; ?>>Usuario (Solo lectura)</option>
                        <option value="encargado" <?php echo $usuario['rol'] == 'encargado' ? 'selected' : ''; ?>>Encargado (Gestionar inventario)</option>
                        <option value="admin" <?php echo $usuario['rol'] == 'admin' ? 'selected' : ''; ?>>Administrador (Control total)</option>
                    </select>
                    <div class="form-text">
                        <small>
                            <strong>Usuario:</strong> Solo consulta | 
                            <strong>Encargado:</strong> Gestiona inventario | 
                            <strong>Admin:</strong> Control total
                        </small>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="activo" <?php echo $usuario['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $usuario['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            
            <!-- Información del usuario -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Información del Usuario</h6>
                            <div class="row">
                                <div class="col-md-4">
                                    <strong>ID:</strong> <?php echo $usuario['id']; ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Fecha Creación:</strong> <?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?>
                                </div>
                                <div class="col-md-4">
                                    <strong>Último Acceso:</strong> 
                                    <?php 
                                    // Puedes agregar un campo para último acceso si lo necesitas
                                    echo 'No registrado'; 
                                    ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Actualizar Usuario
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                
                <?php if($usuario['id'] != $_SESSION['usuario_id']): ?>
                <button type="button" class="btn btn-outline-warning" onclick="resetearContrasena()">
                    <i class="fas fa-key me-2"></i>Resetear Contraseña
                </button>
                <?php endif; ?>
            </div>
        </form>
    </div>
</div>

<!-- Modal para resetear contraseña -->
<div class="modal fade" id="modalResetPassword" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Resetear Contraseña</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas resetear la contraseña de <strong><?php echo htmlspecialchars($usuario['nombre_completo']); ?></strong>?</p>
                <p>La nueva contraseña será: <code class="bg-light p-1 rounded">123456</code></p>
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    El usuario deberá cambiar su contraseña después del primer acceso.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-warning" onclick="confirmarReset()">Resetear Contraseña</button>
            </div>
        </div>
    </div>
</div>

<script>
function resetearContrasena() {
    var modal = new bootstrap.Modal(document.getElementById('modalResetPassword'));
    modal.show();
}

function confirmarReset() {
    // Crear un formulario temporal para enviar la solicitud de reset
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'reset_password.php';
    
    const inputId = document.createElement('input');
    inputId.type = 'hidden';
    inputId.name = 'id';
    inputId.value = '<?php echo $usuario["id"]; ?>';
    
    form.appendChild(inputId);
    document.body.appendChild(form);
    form.submit();
}
</script>

<?php include_once "../includes/footer.php"; ?>