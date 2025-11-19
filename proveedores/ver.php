<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Obtener datos del proveedor
$query = "SELECT * FROM " . DB_PREFIX . "proveedores WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$proveedor = $stmt->fetch();

if (!$proveedor) {
    header("Location: listar.php");
    exit();
}

// Obtener items del proveedor
$query_items = "SELECT i.*, e.nombre as edificio, cp.nombre as categoria
                FROM " . DB_PREFIX . "inventario i
                LEFT JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id
                LEFT JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
                WHERE i.proveedor_id = ?
                ORDER BY i.fecha_creacion DESC";
$stmt_items = $db->prepare($query_items);
$stmt_items->execute([$id]);
$items = $stmt_items->fetchAll();

// Estadísticas
$total_items = count($items);
$valor_total = array_sum(array_column($items, 'valor'));
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-truck me-2"></i>Detalles del Proveedor</h2>
    <div>
        <?php if(canWrite()): ?>
        <a href="editar.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Editar
        </a>
        <?php endif; ?>
        <a href="listar.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver
        </a>
    </div>
</div>

<div class="row">
    <!-- Información del Proveedor -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información del Proveedor</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <th width="40%">Nombre:</th>
                        <td><strong><?php echo $proveedor['nombre']; ?></strong></td>
                    </tr>
                    <?php if($proveedor['contacto']): ?>
                    <tr>
                        <th>Contacto:</th>
                        <td><?php echo $proveedor['contacto']; ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($proveedor['telefono']): ?>
                    <tr>
                        <th>Teléfono:</th>
                        <td>
                            <i class="fas fa-phone me-1 text-success"></i>
                            <?php echo $proveedor['telefono']; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if($proveedor['email']): ?>
                    <tr>
                        <th>Email:</th>
                        <td>
                            <i class="fas fa-envelope me-1 text-primary"></i>
                            <a href="mailto:<?php echo $proveedor['email']; ?>"><?php echo $proveedor['email']; ?></a>
                        </td>
                    </tr>
                    <?php endif; ?>
                    <?php if($proveedor['ruc']): ?>
                    <tr>
                        <th>RUC/NIT:</th>
                        <td><code><?php echo $proveedor['ruc']; ?></code></td>
                    </tr>
                    <?php endif; ?>
                    <?php if($proveedor['direccion']): ?>
                    <tr>
                        <th>Dirección:</th>
                        <td><?php echo nl2br($proveedor['direccion']); ?></td>
                    </tr>
                    <?php endif; ?>
                    <tr>
                        <th>Estado:</th>
                        <td><?php echo obtenerEstadoBadge($proveedor['estado']); ?></td>
                    </tr>
                    <tr>
                        <th>Fecha Registro:</th>
                        <td><?php echo date('d/m/Y H:i', strtotime($proveedor['fecha_creacion'])); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Estadísticas -->
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Estadísticas</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-primary mb-0"><?php echo $total_items; ?></h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                    </div>
                    <div class="col-6 mb-3">
                        <div class="border rounded p-3">
                            <h3 class="text-success mb-0">$<?php echo number_format($valor_total, 2); ?></h3>
                            <small class="text-muted">Valor Total</small>
                        </div>
                    </div>
                </div>
                
                <?php if($total_items > 0): ?>
                <div class="mt-3">
                    <h6>Distribución por Estado:</h6>
                    <?php
                    $estados = [];
                    foreach ($items as $item) {
                        $estado = $item['estado_uso'];
                        if (!isset($estados[$estado])) {
                            $estados[$estado] = 0;
                        }
                        $estados[$estado]++;
                    }
                    
                    foreach ($estados as $estado => $cantidad):
                        $porcentaje = round(($cantidad / $total_items) * 100, 1);
                    ?>
                    <div class="mb-2">
                        <div class="d-flex justify-content-between">
                            <span><?php echo ucfirst($estado); ?></span>
                            <span><?php echo $cantidad; ?> (<?php echo $porcentaje; ?>%)</span>
                        </div>
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar" style="width: <?php echo $porcentaje; ?>%"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Items del Proveedor -->
<div class="card">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0">
            <i class="fas fa-boxes me-2"></i>
            Items Proveídos (<?php echo $total_items; ?>)
        </h5>
    </div>
    <div class="card-body">
        <?php if($total_items > 0): ?>
        <div class="table-responsive">
            <table class="table table-sm table-striped">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Edificio</th>
                        <th>Categoría</th>
                        <th>Estado</th>
                        <th>Valor</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($items as $item): ?>
                    <tr>
                        <td><code><?php echo $item['codigo_completo']; ?></code></td>
                        <td><?php echo $item['nombre']; ?></td>
                        <td><?php echo $item['edificio']; ?></td>
                        <td><?php echo $item['categoria']; ?></td>
                        <td><?php echo obtenerEstadoBadge($item['estado_uso']); ?></td>
                        <td>$<?php echo number_format($item['valor'], 2); ?></td>
                        <td><?php echo date('d/m/Y', strtotime($item['fecha_creacion'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        <div class="text-center py-4">
            <i class="fas fa-inbox fa-2x text-muted mb-3"></i>
            <p class="text-muted">Este proveedor no tiene items asociados.</p>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>