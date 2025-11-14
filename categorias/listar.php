<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT cp.*, 
                 COUNT(DISTINCT sc.id) as total_subcategorias,
                 COUNT(DISTINCT tobj.id) as total_tipos
          FROM " . DB_PREFIX . "categorias_principales cp
          LEFT JOIN " . DB_PREFIX . "subcategorias sc ON cp.id = sc.categoria_principal_id
          LEFT JOIN " . DB_PREFIX . "tipos_objeto tobj ON sc.id = tobj.subcategoria_id
          GROUP BY cp.id
          ORDER BY cp.codigo";

$stmt = $db->prepare($query);
$stmt->execute();
$categorias = $stmt->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-tags me-2"></i>Sistema de Categorías</h2>
    <a href="subcategorias.php" class="btn btn-info">
        <i class="fas fa-sitemap me-2"></i>Ver Subcategorías
    </a>
</div>

<div class="row">
    <?php foreach ($categorias as $categoria): ?>
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header bg-<?php echo $categoria['codigo'] == 1 ? 'info' : 'success'; ?> text-white">
                <h5 class="mb-0">
                    <i class="fas fa-folder me-2"></i>
                    <?php echo $categoria['codigo']; ?> - <?php echo $categoria['nombre']; ?>
                </h5>
            </div>
            <div class="card-body">
                <p class="card-text"><?php echo $categoria['descripcion']; ?></p>
                <div class="row text-center">
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h4 class="text-primary mb-0"><?php echo $categoria['total_subcategorias']; ?></h4>
                            <small class="text-muted">Subcategorías</small>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="border rounded p-2">
                            <h4 class="text-success mb-0"><?php echo $categoria['total_tipos']; ?></h4>
                            <small class="text-muted">Tipos</small>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <small class="text-muted">
                    Estado: <?php echo obtenerEstadoBadge($categoria['estado']); ?>
                </small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="card mt-4">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Información del Sistema de Códigos</h5>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Estructura Principal:</h6>
                <ul class="list-unstyled">
                    <li><span class="badge bg-primary">1.xxx.xxx</span> - Bienes Pasivos</li>
                    <li><span class="badge bg-success">2.xxx.xxx</span> - Bienes Activos</li>
                </ul>
            </div>
            <div class="col-md-6">
                <h6>Subcategorías Ejemplo:</h6>
                <ul class="list-unstyled">
                    <li><code>1.101</code> - Mobiliario</li>
                    <li><code>1.102</code> - Equipos Computación</li>
                    <li><code>1.103</code> - Herramientas</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>