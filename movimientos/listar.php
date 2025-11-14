<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT m.*, i.nombre as item_nombre, i.codigo_completo,
                 eo.nombre as edificio_origen, ed.nombre as edificio_destino
          FROM " . DB_PREFIX . "movimientos_inventario m
          LEFT JOIN " . DB_PREFIX . "inventario i ON m.inventario_id = i.id
          LEFT JOIN " . DB_PREFIX . "edificios eo ON m.edificio_origen_id = eo.id
          LEFT JOIN " . DB_PREFIX . "edificios ed ON m.edificio_destino_id = ed.id
          ORDER BY m.fecha_movimiento DESC";

$stmt = $db->prepare($query);
$stmt->execute();
$movimientos = $stmt->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-exchange-alt me-2"></i>Historial de Movimientos</h2>
    <a href="../inventario/listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Inventario
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Fecha</th>
                        <th>Item</th>
                        <th>Tipo Movimiento</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Descripción</th>
                        <th>Responsable</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($movimientos as $movimiento): ?>
                    <tr>
                        <td>
                            <small><?php echo date('d/m/Y H:i', strtotime($movimiento['fecha_movimiento'])); ?></small>
                        </td>
                        <td>
                            <div>
                                <strong><?php echo $movimiento['item_nombre']; ?></strong>
                                <br>
                                <small class="text-muted"><?php echo $movimiento['codigo_completo']; ?></small>
                            </div>
                        </td>
                        <td>
                            <?php
                            $badge_class = [
                                'traslado' => 'bg-primary',
                                'baja' => 'bg-danger',
                                'mantenimiento' => 'bg-warning',
                                'actualizacion' => 'bg-info'
                            ];
                            $class = $badge_class[$movimiento['tipo_movimiento']] ?? 'bg-secondary';
                            ?>
                            <span class="badge <?php echo $class; ?>">
                                <?php echo ucfirst($movimiento['tipo_movimiento']); ?>
                            </span>
                        </td>
                        <td>
                            <?php echo $movimiento['edificio_origen'] ?: '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td>
                            <?php echo $movimiento['edificio_destino'] ?: '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td>
                            <?php echo $movimiento['descripcion'] ?: '<span class="text-muted">Sin descripción</span>'; ?>
                        </td>
                        <td>
                            <small><?php echo $movimiento['usuario_responsable']; ?></small>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if(count($movimientos) === 0): ?>
                <div class="text-center py-4">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <h5>No hay movimientos registrados</h5>
                    <p class="text-muted">Los movimientos de items aparecerán aquí.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>