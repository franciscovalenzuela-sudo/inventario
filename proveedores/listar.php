<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Solo admin y encargado pueden gestionar proveedores
if (!canWrite()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$query = "SELECT p.*, 
                 COUNT(i.id) as total_items
          FROM " . DB_PREFIX . "proveedores p
          LEFT JOIN " . DB_PREFIX . "inventario i ON p.id = i.proveedor_id
          GROUP BY p.id
          ORDER BY p.nombre";

$stmt = $db->prepare($query);
$stmt->execute();
$proveedores = $stmt->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-truck me-2"></i>Gestión de Proveedores</h2>
    <a href="agregar.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nuevo Proveedor
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Proveedor</th>
                        <th>Contacto</th>
                        <th>Teléfono/Email</th>
                        <th>RUC</th>
                        <th>Items</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($proveedores as $proveedor): ?>
                    <tr>
                        <td>
                            <strong><?php echo $proveedor['nombre']; ?></strong>
                            <?php if($proveedor['direccion']): ?>
                                <br><small class="text-muted"><?php echo substr($proveedor['direccion'], 0, 50); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo $proveedor['contacto'] ?: '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td>
                            <?php if($proveedor['telefono']): ?>
                                <div><i class="fas fa-phone me-1 text-success"></i> <?php echo $proveedor['telefono']; ?></div>
                            <?php endif; ?>
                            <?php if($proveedor['email']): ?>
                                <div><i class="fas fa-envelope me-1 text-primary"></i> <?php echo $proveedor['email']; ?></div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <code><?php echo $proveedor['ruc'] ?: '-'; ?></code>
                        </td>
                        <td>
                            <span class="badge bg-info"><?php echo $proveedor['total_items']; ?> items</span>
                        </td>
                        <td><?php echo obtenerEstadoBadge($proveedor['estado']); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="editar.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="ver.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if(count($proveedores) === 0): ?>
                <div class="text-center py-4">
                    <i class="fas fa-truck fa-3x text-muted mb-3"></i>
                    <h5>No hay proveedores registrados</h5>
                    <p class="text-muted">Comienza agregando el primer proveedor.</p>
                    <a href="agregar.php" class="btn btn-primary">Agregar Proveedor</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>