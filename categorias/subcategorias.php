<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$query = "SELECT sc.*, cp.nombre as categoria_nombre, cp.codigo as categoria_codigo,
                 COUNT(tobj.id) as total_tipos
          FROM " . DB_PREFIX . "subcategorias sc
          JOIN " . DB_PREFIX . "categorias_principales cp ON sc.categoria_principal_id = cp.id
          LEFT JOIN " . DB_PREFIX . "tipos_objeto tobj ON sc.id = tobj.subcategoria_id
          GROUP BY sc.id
          ORDER BY cp.codigo, sc.codigo";

$stmt = $db->prepare($query);
$stmt->execute();
$subcategorias = $stmt->fetchAll();
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-sitemap me-2"></i>Subcategorías y Tipos</h2>
    <a href="listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver a Categorías
    </a>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Subcategoría</th>
                        <th>Categoría Padre</th>
                        <th>Descripción</th>
                        <th>Total Tipos</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($subcategorias as $subcat): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary"><?php echo $subcat['categoria_codigo'] . '.' . $subcat['codigo']; ?></span>
                        </td>
                        <td>
                            <strong><?php echo $subcat['nombre']; ?></strong>
                        </td>
                        <td><?php echo $subcat['categoria_nombre']; ?></td>
                        <td><?php echo $subcat['descripcion']; ?></td>
                        <td>
                            <span class="badge bg-info"><?php echo $subcat['total_tipos']; ?> tipos</span>
                        </td>
                        <td><?php echo obtenerEstadoBadge($subcat['estado']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="card mt-4">
    <div class="card-header bg-info text-white">
        <h5 class="mb-0"><i class="fas fa-list me-2"></i>Tipos de Objeto Disponibles</h5>
    </div>
    <div class="card-body">
        <?php
        $query_tipos = "SELECT tobj.*, sc.nombre as subcategoria_nombre, sc.codigo as subcategoria_codigo,
                               cp.codigo as categoria_codigo
                        FROM " . DB_PREFIX . "tipos_objeto tobj
                        JOIN " . DB_PREFIX . "subcategorias sc ON tobj.subcategoria_id = sc.id
                        JOIN " . DB_PREFIX . "categorias_principales cp ON sc.categoria_principal_id = cp.id
                        ORDER BY cp.codigo, sc.codigo, tobj.codigo";
        $stmt_tipos = $db->prepare($query_tipos);
        $stmt_tipos->execute();
        $tipos = $stmt_tipos->fetchAll();
        ?>
        
        <div class="row">
            <?php foreach ($tipos as $tipo): ?>
            <div class="col-md-6 mb-2">
                <div class="border rounded p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <strong class="text-primary"><?php echo $tipo['categoria_codigo'] . '.' . $tipo['subcategoria_codigo'] . '.' . $tipo['codigo']; ?></strong>
                            - <?php echo $tipo['nombre']; ?>
                        </div>
                        <span class="badge bg-secondary"><?php echo $tipo['subcategoria_nombre']; ?></span>
                    </div>
                    <?php if($tipo['descripcion']): ?>
                        <small class="text-muted"><?php echo $tipo['descripcion']; ?></small>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>