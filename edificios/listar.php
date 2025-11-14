<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM " . DB_PREFIX . "edificios ORDER BY nombre";
$stmt = $db->prepare($query);
$stmt->execute();
$edificios = $stmt->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i>Gestión de Edificios</h2>
    <a href="agregar.php" class="btn btn-primary">
        <i class="fas fa-plus me-2"></i>Nuevo Edificio
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Ubicación</th>
                        <th>Descripción</th>
                        <th>Estado</th>
                        <th>Fecha Creación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($edificios as $edificio): ?>
                    <tr>
                        <td>
                            <strong><?php echo $edificio['nombre']; ?></strong>
                        </td>
                        <td><?php echo $edificio['ubicacion']; ?></td>
                        <td><?php echo $edificio['descripcion']; ?></td>
                        <td><?php echo obtenerEstadoBadge($edificio['estado']); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($edificio['fecha_creacion'])); ?></td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="editar.php?id=<?php echo $edificio['id']; ?>" class="btn btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>