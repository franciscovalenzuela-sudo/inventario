<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

// Filtros unificados
$filtro_edificio = $_GET['edificio'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$filtro_proveedor = $_GET['proveedor'] ?? '';
$filtro_estado_fisico = $_GET['estado_fisico'] ?? '';
$filtro_busqueda = $_GET['busqueda'] ?? '';

// Construir consulta con filtros
$query = "SELECT i.*, e.nombre as edificio, cp.nombre as categoria, 
                 sc.nombre as subcategoria, tobj.nombre as tipo_objeto,
                 p.nombre as proveedor_nombre
          FROM " . DB_PREFIX . "inventario i
          LEFT JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id
          LEFT JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
          LEFT JOIN " . DB_PREFIX . "subcategorias sc ON i.subcategoria_id = sc.id
          LEFT JOIN " . DB_PREFIX . "tipos_objeto tobj ON i.tipo_objeto_id = tobj.id
          LEFT JOIN " . DB_PREFIX . "proveedores p ON i.proveedor_id = p.id
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

if ($filtro_proveedor) {
    if ($filtro_proveedor === 'null') {
        $query .= " AND i.proveedor_id IS NULL";
    } else {
        $query .= " AND i.proveedor_id = ?";
        $params[] = $filtro_proveedor;
    }
}

if ($filtro_estado_fisico) {
    $query .= " AND i.estado_fisico = ?";
    $params[] = $filtro_estado_fisico;
}

if ($filtro_busqueda) {
    $query .= " AND (i.nombre LIKE ? OR i.descripcion LIKE ? OR i.marca LIKE ? OR i.modelo LIKE ? OR i.codigo_completo LIKE ?)";
    $search_term = "%$filtro_busqueda%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY i.fecha_creacion DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$inventario = $stmt->fetchAll();

// Obtener opciones para filtros
$edificios = obtenerEdificios($db);
$categorias = obtenerCategorias($db);
$proveedores = obtenerProveedores($db);

// Contadores para mostrar en los filtros
$total_items = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "inventario")->fetchColumn();
$items_filtrados = count($inventario);
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

<!-- Filtros Unificados -->
<div class="card mb-4">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0"><i class="fas fa-filter me-2"></i>Filtros de Búsqueda</h5>
        <span class="badge bg-light text-dark">
            <?php echo $items_filtrados; ?> de <?php echo $total_items; ?> items
        </span>
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3">
            <!-- Fila 1: Búsqueda rápida -->
            <div class="col-12">
                <label class="form-label">Búsqueda Rápida</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="busqueda" value="<?php echo htmlspecialchars($filtro_busqueda); ?>" 
                           placeholder="Buscar por nombre, descripción, marca, modelo o código...">
                </div>
            </div>

            <!-- Fila 2: Filtros principales -->
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
            
            <div class="col-md-2">
                <label class="form-label">Estado Uso</label>
                <select name="estado" class="form-select">
                    <option value="">Todos</option>
                    <option value="disponible" <?php echo $filtro_estado == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                    <option value="en_uso" <?php echo $filtro_estado == 'en_uso' ? 'selected' : ''; ?>>En Uso</option>
                    <option value="mantenimiento" <?php echo $filtro_estado == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                    <option value="baja" <?php echo $filtro_estado == 'baja' ? 'selected' : ''; ?>>Baja</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Estado Físico</label>
                <select name="estado_fisico" class="form-select">
                    <option value="">Todos</option>
                    <option value="excelente" <?php echo $filtro_estado_fisico == 'excelente' ? 'selected' : ''; ?>>Excelente</option>
                    <option value="bueno" <?php echo $filtro_estado_fisico == 'bueno' ? 'selected' : ''; ?>>Bueno</option>
                    <option value="regular" <?php echo $filtro_estado_fisico == 'regular' ? 'selected' : ''; ?>>Regular</option>
                    <option value="malo" <?php echo $filtro_estado_fisico == 'malo' ? 'selected' : ''; ?>>Malo</option>
                    <option value="obsoleto" <?php echo $filtro_estado_fisico == 'obsoleto' ? 'selected' : ''; ?>>Obsoleto</option>
                </select>
            </div>
            
            <div class="col-md-2">
                <label class="form-label">Categoría</label>
                <select name="categoria" class="form-select">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $categoria): ?>
                        <option value="<?php echo $categoria['id']; ?>" <?php echo $filtro_categoria == $categoria['id'] ? 'selected' : ''; ?>>
                            <?php echo $categoria['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label class="form-label">Proveedor</label>
                <select name="proveedor" class="form-select">
                    <option value="">Todos los proveedores</option>
                    <option value="null" <?php echo $filtro_proveedor === 'null' ? 'selected' : ''; ?>>Sin proveedor</option>
                    <?php foreach ($proveedores as $proveedor): ?>
                        <option value="<?php echo $proveedor['id']; ?>" <?php echo $filtro_proveedor == $proveedor['id'] ? 'selected' : ''; ?>>
                            <?php echo $proveedor['nombre']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fila 3: Botones de acción -->
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search me-2"></i>Aplicar Filtros
                        </button>
                        <a href="listar.php" class="btn btn-outline-secondary">
                            <i class="fas fa-times me-2"></i>Limpiar
                        </a>
                    </div>
                    
                    <?php if($filtro_edificio || $filtro_estado || $filtro_categoria || $filtro_proveedor || $filtro_estado_fisico || $filtro_busqueda): ?>
                    <div class="text-muted">
                        <small>
                            <i class="fas fa-info-circle me-1"></i>
                            Filtros activos: 
                            <?php
                            $filtros_activos = [];
                            if ($filtro_edificio) $filtros_activos[] = 'Edificio';
                            if ($filtro_estado) $filtros_activos[] = 'Estado';
                            if ($filtro_categoria) $filtros_activos[] = 'Categoría';
                            if ($filtro_proveedor) $filtros_activos[] = 'Proveedor';
                            if ($filtro_estado_fisico) $filtros_activos[] = 'Estado Físico';
                            if ($filtro_busqueda) $filtros_activos[] = 'Búsqueda';
                            echo implode(', ', $filtros_activos);
                            ?>
                        </small>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Resultados -->
<div class="card">
    <div class="card-body">
        <?php if(count($inventario) > 0): ?>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th width="100">Código</th>
                        <th width="200">Nombre</th>
                        <th width="120">Marca/Modelo</th>
                        <th width="100">Ubicación</th>
                        <th width="100">Categoría</th>
                        <th width="120">Proveedor</th>
                        <th width="80">Estado Físico</th>
                        <th width="80">Estado Uso</th>
                        <th width="100">Valor</th>
                        <?php if(canWrite()): ?>
                        <th width="100">Acciones</th>
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
                        <td>
                            <?php if($item['proveedor_nombre']): ?>
                                <span class="badge bg-info"><?php echo $item['proveedor_nombre']; ?></span>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
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
        </div>
        
        <!-- Resumen de resultados -->
        <div class="mt-3 p-3 bg-light rounded">
            <div class="row text-center">
                <div class="col-md-3">
                    <strong>Total Items:</strong> <?php echo $items_filtrados; ?>
                </div>
                <div class="col-md-3">
                    <strong>Valor Total:</strong> $<?php echo number_format(array_sum(array_column($inventario, 'valor')), 2); ?>
                </div>
                <div class="col-md-3">
                    <strong>Disponibles:</strong> 
                    <?php 
                    $disponibles = array_filter($inventario, function($item) {
                        return $item['estado_uso'] === 'disponible';
                    });
                    echo count($disponibles);
                    ?>
                </div>
                <div class="col-md-3">
                    <strong>En Uso:</strong> 
                    <?php 
                    $en_uso = array_filter($inventario, function($item) {
                        return $item['estado_uso'] === 'en_uso';
                    });
                    echo count($en_uso);
                    ?>
                </div>
            </div>
        </div>
        
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-inbox fa-4x text-muted mb-3"></i>
                <h4>No se encontraron items</h4>
                <p class="text-muted mb-4">
                    <?php if($filtro_edificio || $filtro_estado || $filtro_categoria || $filtro_proveedor || $filtro_estado_fisico || $filtro_busqueda): ?>
                    No hay items que coincidan con los filtros aplicados.
                    <?php else: ?>
                    No hay items en el inventario.
                    <?php endif; ?>
                </p>
                <?php if(canWrite()): ?>
                <a href="agregar.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Agregar Primer Item
                </a>
                <?php endif; ?>
                <?php if($filtro_edificio || $filtro_estado || $filtro_categoria || $filtro_proveedor || $filtro_estado_fisico || $filtro_busqueda): ?>
                <a href="listar.php" class="btn btn-outline-secondary">
                    <i class="fas fa-times me-2"></i>Limpiar Filtros
                </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>