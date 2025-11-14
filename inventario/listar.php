<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

// Filtros
$filtro_edificio = $_GET['edificio'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';

// Construir consulta con filtros
$query = "SELECT i.*, e.nombre as edificio, cp.nombre as categoria, 
                 sc.nombre as subcategoria, tobj.nombre as tipo_objeto
          FROM " . DB_PREFIX . "inventario i
          LEFT JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id
          LEFT JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
          LEFT JOIN " . DB_PREFIX . "subcategorias sc ON i.subcategoria_id = sc.id
          LEFT JOIN " . DB_PREFIX . "tipos_objeto tobj ON i.tipo_objeto_id = tobj.id
          WHERE 1=1";

$params = [];

if ($filtro_edificio) {
    $query .= " AND i.edificio_id = ?";
    $params[] = $filtro_edificio;
}

if ($filtro_estado) {
    $query .= " AND i.estado_uso = ?";
    $params[] = $filtro_estado;
}

if ($filtro_categoria) {
    $query .= " AND i.categoria_principal_id = ?";
    $params[] = $filtro_categoria;
}

$query .= " ORDER BY i.fecha_creacion DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$inventario = $stmt->fetchAll();

// Obtener opciones para filtros
$edificios = obtenerEdificios($db);
$categorias = obtenerCategorias($db);
?>
<?php include_once "../includes/header.php"; ?>


<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-boxes me-2"></i>Inventario General</h2>
    <div>
        <?php if(canWrite()): ?>
        <a href="agregar.php" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Agregar Item
        </a>
        <?php endif; ?>
        <div class="btn-group">
            <button type="button" class="btn btn-success dropdown-toggle" data-bs-toggle="dropdown">
                <i class="fas fa-download me-2"></i>Exportar
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item" href="exportar.php?formato=excel&<?php echo http_build_query($_GET); ?>">
                    <i class="fas fa-file-excel me-2 text-success"></i>Excel (.xls)
                </a></li>
                <li><a class="dropdown-item" href="exportar.php?formato=pdf&<?php echo http_build_query($_GET); ?>">
                    <i class="fas fa-file-pdf me-2 text-danger"></i>PDF (Imprimir)
                </a></li>
                <li><a class="dropdown-item" href="exportar.php?formato=csv&<?php echo http_build_query($_GET); ?>">
                    <i class="fas fa-file-csv me-2 text-info"></i>CSV
                </a></li>
            </ul>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card mb-4">
    <div class="card-header bg-light">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros</h5>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Edificio</label>
                <select name="edificio" class="form-select">
                    <option value="">Todos los edificios</option>
                    <?php foreach ($edificios as $edificio): ?>
                        <option value="<?php echo $edificio['id']; ?>" <?php echo $filtro_edificio == $edificio['id'] ? 'selected' : ''; ?>>
                            <?php echo $edificio['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Estado</label>
                <select name="estado" class="form-select">
                    <option value="">Todos los estados</option>
                    <option value="disponible" <?php echo $filtro_estado == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="en_uso" <?php echo $filtro_estado == 'en_uso' ? 'selected' : ''; ?>>En Uso</option>
                    <option value="mantenimiento" <?php echo $filtro_estado == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas las categorías</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>" <?php echo $filtro_categoria == $categoria['id'] ? 'selected' : ''; ?>>
                            <?php echo $categoria['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
                <a href="listar.php" class="btn btn-secondary">Limpiar</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Marca/Modelo</th>
                        <th>Ubicación</th>
                        <th>Categoría</th>
                        <th>Estado Físico</th>
                        <th>Estado Uso</th>
                        <th>Valor</th>
                        <?php if(canWrite()): ?>
                        <th>Acciones</th>
                        <?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($inventario as $item): ?>
                    <tr>
                        <td>
                            <code class="bg-light p-1 rounded"><?php echo $item['codigo_completo']; ?></code>
                        </td>
                        <td>
                            <strong><?php echo $item['nombre']; ?></strong>
                            <?php if($item['descripcion']): ?>
                                <br><small class="text-muted"><?php echo substr($item['descripcion'], 0, 50); ?>...</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($item['marca'] || $item['modelo']): ?>
                                <small><?php echo $item['marca']; ?> <?php echo $item['modelo']; ?></small>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo $item['edificio']; ?></td>
                        <td><?php echo $item['categoria']; ?></td>
                        <td><?php echo obtenerEstadoBadge($item['estado_fisico']); ?></td>
                        <td><?php echo obtenerEstadoBadge($item['estado_uso']); ?></td>
                        <td>
                            <strong>$<?php echo number_format($item['valor'], 2); ?></strong>
                        </td>
                        <?php if(canWrite()): ?>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="ver.php?id=<?php echo $item['id']; ?>" class="btn btn-info" title="Ver">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="editar.php?id=<?php echo $item['id']; ?>" class="btn btn-warning" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                            </div>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php if(count($inventario) === 0): ?>
                <div class="text-center py-4">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5>No se encontraron items</h5>
                    <p class="text-muted">No hay items que coincidan con los filtros aplicados.</p>
                    <?php if(canWrite()): ?>
                    <a href="agregar.php" class="btn btn-primary">Agregar primer item</a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>