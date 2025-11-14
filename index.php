<?php
include_once "config.php";
include_once "database/connection.php";

// Redirigir a login si no está autenticado
if (!isset($_SESSION["usuario_id"])) {
    header("Location: usuarios/login.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

// Obtener estadísticas
$total_items = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "inventario")->fetchColumn();
$total_edificios = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "edificios WHERE estado = 'activo'")->fetchColumn();
$items_activos = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "inventario WHERE estado_uso = 'disponible'")->fetchColumn();
$valor_total = $db->query("SELECT SUM(valor) FROM " . DB_PREFIX . "inventario")->fetchColumn() ?? 0;

// Items recientes
$items_recientes = $db->query("SELECT i.*, e.nombre as edificio 
                              FROM " . DB_PREFIX . "inventario i 
                              LEFT JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id 
                              ORDER BY i.fecha_creacion DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Sistema de Inventarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .dashboard-card { transition: transform 0.2s; }
        .dashboard-card:hover { transform: translateY(-5px); }
        .stat-number { font-size: 2rem; font-weight: bold; }
    </style>
</head>
<body>
    <?php include_once "includes/header.php"; ?>

    <div class="container-fluid mt-4">
        <div class="row">
            <div class="col-12">
                <h1 class="mb-4">Dashboard del Sistema</h1>
                
                <!-- Estadísticas -->
                <div class="row mb-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Total Items</div>
                                        <div class="stat-number text-gray-800"><?php echo $total_items; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-boxes fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                            Edificios Activos</div>
                                        <div class="stat-number text-gray-800"><?php echo $total_edificios; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-building fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card border-left-info shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                            Items Disponibles</div>
                                        <div class="stat-number text-gray-800"><?php echo $items_activos; ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card dashboard-card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Valor Total</div>
                                        <div class="stat-number text-gray-800">$<?php echo number_format($valor_total, 2); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Acciones Rápidas -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-primary text-white">
                                <h5 class="mb-0"><i class="fas fa-bolt me-2"></i>Acciones Rápidas</h5>
                            </div>
                            <div class="card-body">
                                <div class="d-grid gap-2">
                                    <a href="inventario/agregar.php" class="btn btn-success btn-lg text-start">
                                        <i class="fas fa-plus-circle me-2"></i>Agregar Nuevo Item
                                    </a>
                                    <a href="inventario/listar.php" class="btn btn-primary btn-lg text-start">
                                        <i class="fas fa-list me-2"></i>Ver Todo el Inventario
                                    </a>
                                    <a href="edificios/listar.php" class="btn btn-info btn-lg text-start">
                                        <i class="fas fa-building me-2"></i>Gestionar Edificios
                                    </a>
                                    <?php if(isAdmin()): ?>
                                    <a href="usuarios/listar.php" class="btn btn-warning btn-lg text-start">
                                        <i class="fas fa-users me-2"></i>Administrar Usuarios
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Items Recientes -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Items Agregados Recientemente</h5>
                            </div>
                            <div class="card-body">
                                <?php if(count($items_recientes) > 0): ?>
                                    <div class="list-group">
                                        <?php foreach($items_recientes as $item): ?>
                                            <div class="list-group-item">
                                                <div class="d-flex w-100 justify-content-between">
                                                    <h6 class="mb-1"><?php echo $item['nombre']; ?></h6>
                                                    <small><?php echo date('d/m/Y', strtotime($item['fecha_creacion'])); ?></small>
                                                </div>
                                                <p class="mb-1">
                                                    <span class="badge bg-secondary"><?php echo $item['codigo_completo']; ?></span>
                                                    <span class="badge bg-info"><?php echo $item['edificio']; ?></span>
                                                </p>
                                                <small class="text-muted">$<?php echo number_format($item['valor'], 2); ?></small>
                                            </div>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <p class="text-muted">No hay items en el inventario.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sistema de Códigos -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header bg-dark text-white">
                                <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Sistema de Códigos</h5>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h6>Estructura de Códigos:</h6>
                                        <ul class="list-unstyled">
                                            <li><span class="badge bg-primary">1.xxx.xxx</span> - Bienes Pasivos</li>
                                            <li><span class="badge bg-success">2.xxx.xxx</span> - Bienes Activos</li>
                                            <li><span class="badge bg-info">1.101.xxx</span> - Mobiliario</li>
                                            <li><span class="badge bg-warning">1.102.xxx</span> - Equipos Computación</li>
                                        </ul>
                                    </div>
                                    <div class="col-md-6">
                                        <h6>Ejemplos:</h6>
                                        <ul class="list-unstyled">
                                            <li><code>1.101.1011</code> - Sillas de Oficina</li>
                                            <li><code>1.101.1012</code> - Escritorios</li>
                                            <li><code>1.102.1021</code> - Computadoras</li>
                                            <li><code>1.102.1022</code> - Impresoras</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include_once "includes/footer.php"; ?>
</body>
</html>