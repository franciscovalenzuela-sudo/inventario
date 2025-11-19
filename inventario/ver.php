<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;

// Obtener datos completos del item
$query = "SELECT i.*, e.nombre as edificio, e.ubicacion as edificio_ubicacion,
                 cp.nombre as categoria, cp.codigo as categoria_codigo,
                 sc.nombre as subcategoria, sc.codigo as subcategoria_codigo,
                 tobj.nombre as tipo_objeto, tobj.codigo as tipo_objeto_codigo
          FROM " . DB_PREFIX . "inventario i
          LEFT JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id
          LEFT JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
          LEFT JOIN " . DB_PREFIX . "subcategorias sc ON i.subcategoria_id = sc.id
          LEFT JOIN " . DB_PREFIX . "tipos_objeto tobj ON i.tipo_objeto_id = tobj.id
          LEFT JOIN " . DB_PREFIX . "proveedores p ON i.proveedor_id = p.id
          WHERE i.id = ?";

$stmt = $db->prepare($query);
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: listar.php");
    exit();
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-eye me-2"></i>Detalles del Item</h2>
    <div>
        <a href="editar.php?id=<?php echo $item['id']; ?>" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>Editar
        </a>
        <a href="listar.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Volver al Inventario
        </a>
    </div>
</div>

<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="card mb-4">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Información General</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>Nombre del Item</h6>
                        <p class="fs-5 fw-bold"><?php echo $item['nombre']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <h6>Código del Sistema</h6>
                        <p><span class="badge bg-primary fs-6"><?php echo $item['codigo_completo']; ?></span></p>
                    </div>
                </div>
                
                <?php if($item['descripcion']): ?>
                <div class="row mt-3">
                    <div class="col-12">
                        <h6>Descripción</h6>
                        <p><?php echo nl2br($item['descripcion']); ?></p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Información Técnica -->
        <div class="card mb-4">
            <div class="card-header bg-info text-white">
                <h5 class="mb-0">Información Técnica</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php if($item['marca']): ?>
                    <div class="col-md-4">
                        <h6>Marca</h6>
                        <p><?php echo $item['marca']; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($item['modelo']): ?>
                    <div class="col-md-4">
                        <h6>Modelo</h6>
                        <p><?php echo $item['modelo']; ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <?php if($item['numero_serie']): ?>
                    <div class="col-md-4">
                        <h6>Número de Serie</h6>
                        <p><code><?php echo $item['numero_serie']; ?></code></p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Información Secundaria -->
    <div class="col-md-4">
        <!-- Estado y Ubicación -->
        <div class="card mb-4">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0">Estado y Ubicación</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Estado Físico</h6>
                    <?php echo obtenerEstadoBadge($item['estado_fisico']); ?>
                </div>
                
                <div class="mb-3">
                    <h6>Estado de Uso</h6>
                    <?php echo obtenerEstadoBadge($item['estado_uso']); ?>
                </div>
                
                <div class="mb-3">
                    <h6>Ubicación</h6>
                    <p class="mb-1"><strong><?php echo $item['edificio']; ?></strong></p>
                    <small class="text-muted"><?php echo $item['edificio_ubicacion']; ?></small>
                </div>
            </div>
        </div>

       <!-- Información del Proveedor -->
    <?php if($item['proveedor_nombre']): ?>
    <div class="card mb-4">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fas fa-truck me-2"></i>Información del Proveedor</h5>
        </div>
        <div class="card-body">
            <h6><?php echo $item['proveedor_nombre']; ?></h6>
            <?php if($item['proveedor_contacto']): ?>
                <p class="mb-1"><small>Contacto: <?php echo $item['proveedor_contacto']; ?></small></p>
            <?php endif; ?>
            <?php if($item['proveedor_telefono']): ?>
                <p class="mb-1"><small><i class="fas fa-phone me-1"></i> <?php echo $item['proveedor_telefono']; ?></small></p>
            <?php endif; ?>
            <?php if($item['proveedor_email']): ?>
                <p class="mb-0"><small><i class="fas fa-envelope me-1"></i> <?php echo $item['proveedor_email']; ?></small></p>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

        <!-- Valor y Fechas -->
        <div class="card mb-4">
            <div class="card-header bg-warning text-white">
                <h5 class="mb-0">Valor y Fechas</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6>Valor</h6>
                    <p class="fs-4 fw-bold text-success">$<?php echo number_format($item['valor'], 2); ?></p>
                </div>
                
                <?php if($item['fecha_adquisicion']): ?>
                <div class="mb-3">
                    <h6>Fecha de Adquisición</h6>
                    <p><?php echo date('d/m/Y', strtotime($item['fecha_adquisicion'])); ?></p>
                </div>
                <?php endif; ?>
                
                <div class="mb-3">
                    <h6>Fecha de Registro</h6>
                    <p><?php echo date('d/m/Y H:i', strtotime($item['fecha_creacion'])); ?></p>
                </div>
                
                <?php if($item['fecha_actualizacion'] != $item['fecha_creacion']): ?>
                <div class="mb-3">
                    <h6>Última Actualización</h6>
                    <p><?php echo date('d/m/Y H:i', strtotime($item['fecha_actualizacion'])); ?></p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Sistema de Códigos -->
        <div class="card">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">Clasificación</h5>
            </div>
            <div class="card-body">
                <div class="mb-2">
                    <small class="text-muted">Categoría Principal</small>
                    <p class="mb-0"><?php echo $item['categoria_codigo']; ?> - <?php echo $item['categoria']; ?></p>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">Subcategoría</small>
                    <p class="mb-0"><?php echo $item['subcategoria_codigo']; ?> - <?php echo $item['subcategoria']; ?></p>
                </div>
                
                <div class="mb-2">
                    <small class="text-muted">Tipo de Objeto</small>
                    <p class="mb-0"><?php echo $item['tipo_objeto_codigo']; ?> - <?php echo $item['tipo_objeto']; ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>