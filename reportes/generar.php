<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas para reportes
$total_items = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "inventario")->fetchColumn();
$total_edificios = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "edificios WHERE estado = 'activo'")->fetchColumn();
$valor_total = $db->query("SELECT SUM(valor) FROM " . DB_PREFIX . "inventario")->fetchColumn() ?? 0;

// Items por categoría
$items_por_categoria = $db->query("
    SELECT cp.nombre, COUNT(i.id) as total, SUM(i.valor) as valor_total
    FROM " . DB_PREFIX . "inventario i
    JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
    GROUP BY cp.id, cp.nombre
")->fetchAll();

// Items por estado
$items_por_estado = $db->query("
    SELECT estado_uso, COUNT(*) as total
    FROM " . DB_PREFIX . "inventario
    GROUP BY estado_uso
")->fetchAll();

// Items por edificio
$items_por_edificio = $db->query("
    SELECT e.nombre, COUNT(i.id) as total, SUM(i.valor) as valor_total
    FROM " . DB_PREFIX . "inventario i
    JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id
    GROUP BY e.id, e.nombre
")->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-chart-bar me-2"></i>Reportes y Estadísticas</h2>
    <div class="btn-group">
        <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
            <i class="fas fa-download me-2"></i>Exportar Reporte
        </button>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="exportar_reporte.php?tipo=general&formato=excel">
                <i class="fas fa-file-excel me-2"></i>Reporte General (Excel)
            </a></li>
            <li><a class="dropdown-item" href="exportar_reporte.php?tipo=general&formato=pdf">
                <i class="fas fa-file-pdf me-2"></i>Reporte General (PDF)
            </a></li>
        </ul>
    </div>
</div>

<div class="row">
    <!-- Estadísticas Principales -->
    <div class="col-md-3 mb-4">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo $total_items; ?></h4>
                        <p class="mb-0">Total Items</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-boxes fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-success">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo $total_edificios; ?></h4>
                        <p class="mb-0">Edificios</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-building fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-warning">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0">$<?php echo number_format($valor_total, 2); ?></h4>
                        <p class="mb-0">Valor Total</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-dollar-sign fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-3 mb-4">
        <div class="card text-white bg-info">
            <div class="card-body">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="mb-0"><?php echo count($items_por_categoria); ?></h4>
                        <p class="mb-0">Categorías</p>
                    </div>
                    <div class="align-self-center">
                        <i class="fas fa-tags fa-2x"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Items por Categoría -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-chart-pie me-2"></i>Items por Categoría</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Categoría</th>
                                <th>Cantidad</th>
                                <th>Valor Total</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_por_categoria as $categoria): ?>
                            <tr>
                                <td><?php echo $categoria['nombre']; ?></td>
                                <td><?php echo $categoria['total']; ?></td>
                                <td>$<?php echo number_format($categoria['valor_total'], 2); ?></td>
                                <td>
                                    <?php 
                                    $porcentaje = $total_items > 0 ? ($categoria['total'] / $total_items) * 100 : 0;
                                    echo number_format($porcentaje, 1) . '%';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Items por Estado -->
    <div class="col-md-6 mb-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Items por Estado de Uso</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>%</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_por_estado as $estado): ?>
                            <tr>
                                <td><?php echo obtenerEstadoBadge($estado['estado_uso']); ?></td>
                                <td><?php echo $estado['total']; ?></td>
                                <td>
                                    <?php 
                                    $porcentaje = $total_items > 0 ? ($estado['total'] / $total_items) * 100 : 0;
                                    echo number_format($porcentaje, 1) . '%';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Items por Edificio -->
    <div class="col-12 mb-4">
        <div class="card">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0"><i class="fas fa-building me-2"></i>Distribución por Edificio</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Edificio</th>
                                <th>Cantidad</th>
                                <th>Valor Total</th>
                                <th>% Cantidad</th>
                                <th>% Valor</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($items_por_edificio as $edificio): ?>
                            <tr>
                                <td><?php echo $edificio['nombre']; ?></td>
                                <td><?php echo $edificio['total']; ?></td>
                                <td>$<?php echo number_format($edificio['valor_total'], 2); ?></td>
                                <td>
                                    <?php 
                                    $porcentaje_cant = $total_items > 0 ? ($edificio['total'] / $total_items) * 100 : 0;
                                    echo number_format($porcentaje_cant, 1) . '%';
                                    ?>
                                </td>
                                <td>
                                    <?php 
                                    $porcentaje_valor = $valor_total > 0 ? ($edificio['valor_total'] / $valor_total) * 100 : 0;
                                    echo number_format($porcentaje_valor, 1) . '%';
                                    ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header bg-warning">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Sistema</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Permisos por Rol:</h6>
                <ul>
                    <li><strong>Administrador:</strong> Control total del sistema</li>
                    <li><strong>Encargado:</strong> Gestionar inventario (agregar, editar)</li>
                    <li><strong>Usuario:</strong> Solo consulta y exportación de datos</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Funcionalidades de Reportes:</h6>
                <ul>
                    <li>Exportar a Excel (formato tabular completo)</li>
                    <li>Exportar a PDF (resumen ejecutivo)</li>
                    <li>Filtros avanzados por categorías y estados</li>
                    <li>Estadísticas en tiempo real</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>