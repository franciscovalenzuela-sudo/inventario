<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Mostrar mensajes de éxito/error
if (isset($_SESSION['success'])) {
    echo mostrarAlerta('success', $_SESSION['success']);
    unset($_SESSION['success']);
}

if (isset($_SESSION['error'])) {
    echo mostrarAlerta('danger', $_SESSION['error']);
    unset($_SESSION['error']);
}

// Solo administradores pueden gestionar usuarios
if (!canManageUsers()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM " . DB_PREFIX . "usuarios ORDER BY fecha_creacion DESC";
$stmt = $db->prepare($query);
$stmt->execute();
$usuarios = $stmt->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-users me-2"></i>Gestión de Usuarios</h2>
    <?php if(canManageUsers()): ?>
    <a href="agregar.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nuevo Usuario
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Usuario</th>
                        <th>Nombre Completo</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <?php if(canManageUsers()): ?>
                        <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($usuarios as $usuario): ?>
                    <tr>
                        <td>
                            <strong><?php echo $usuario['usuario']; ?></strong>
                            <?php if($usuario['id'] == $_SESSION['usuario_id']): ?>
                                <span class="badge bg-info">Tú</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $usuario['nombre_completo']; ?></td>
                        <td><?php echo $usuario['email']; ?></td>
                        <td>
                            <?php 
                            $color_rol = [
                                'admin' => 'danger',
                                'encargado' => 'warning', 
                                'usuario' => 'info'
                            ];
                            $color = $color_rol[$usuario['rol']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $color; ?>">
                                <?php echo ucfirst($usuario['rol']); ?>
                            </span>
                        </td>
                        <td><?php echo obtenerEstadoBadge($usuario['estado']); ?></td>
                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['fecha_creacion'])); ?></td>
                        <?php if(canManageUsers()): ?>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="editar.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if($usuario['id'] != $_SESSION['usuario_id']): ?>
                                <a href="eliminar.php?id=<?php echo $usuario['id']; ?>" 
                                   class="btn btn-danger" 
                                   onclick="return confirm('¿Estás seguro de eliminar este usuario?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>