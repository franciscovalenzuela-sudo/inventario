<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;
$mensaje = '';

// Obtener datos del item
$query = "SELECT * FROM " . DB_PREFIX . "inventario WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$item = $stmt->fetch();

if (!$item) {
    header("Location: listar.php");
    exit();
}

// Obtener opciones para dropdowns
$edificios = obtenerEdificios($db);
$categorias = obtenerCategorias($db);
$subcategorias = obtenerSubcategorias($db, $item['categoria_principal_id']);
$tipos_objeto = obtenerTiposObjeto($db, $item['subcategoria_id']);

if ($_POST) {
    $nombre = $_POST['nombre'];
    $edificio_id = $_POST['edificio_id'];
    $descripcion = $_POST['descripcion'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numero_serie = $_POST['numero_serie'];
    $estado_fisico = $_POST['estado_fisico'];
    $estado_uso = $_POST['estado_uso'];
    $valor = $_POST['valor'];
    $fecha_adquisicion = $_POST['fecha_adquisicion'];
    
    try {
        $query = "UPDATE " . DB_PREFIX . "inventario 
                  SET nombre = ?, edificio_id = ?, descripcion = ?, marca = ?, modelo = ?, 
                      numero_serie = ?, estado_fisico = ?, estado_uso = ?, valor = ?, fecha_adquisicion = ?
                  WHERE id = ?";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $nombre, $edificio_id, $descripcion, $marca, $modelo, $numero_serie,
            $estado_fisico, $estado_uso, $valor, $fecha_adquisicion, $id
        ]);
        
        $mensaje = mostrarAlerta('success', 'Item actualizado correctamente');
        
        // Actualizar datos locales
        $item['nombre'] = $nombre;
        $item['edificio_id'] = $edificio_id;
        $item['descripcion'] = $descripcion;
        $item['marca'] = $marca;
        $item['modelo'] = $modelo;
        $item['numero_serie'] = $numero_serie;
        $item['estado_fisico'] = $estado_fisico;
        $item['estado_uso'] = $estado_uso;
        $item['valor'] = $valor;
        $item['fecha_adquisicion'] = $fecha_adquisicion;
        
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al actualizar item: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Editar Item del Inventario</h2>
    <a href="listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Inventario
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $mensaje; ?>
        
        <div class="alert alert-info mb-4">
            <strong>Código del item:</strong> 
            <span class="badge bg-primary fs-6"><?php echo $item['codigo_completo']; ?></span>
            <br>
            <small>El código no puede ser modificado una vez creado el item.</small>
        </div>
        
        <form method="POST">
            <div class="row g-3">
                <!-- Información Básica -->
                <div class="col-12">
                    <h5 class="border-bottom pb-2">Información Básica</h5>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Nombre del Item *</label>
                    <input type="text" class="form-control" name="nombre" value="<?php echo $item['nombre']; ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Edificio *</label>
                    <select name="edificio_id" class="form-select" required>
                        <option value="">Seleccionar Edificio</option>
                        <?php foreach ($edificios as $edificio): ?>
                            <option value="<?php echo $edificio['id']; ?>" <?php echo $item['edificio_id'] == $edificio['id'] ? 'selected' : ''; ?>>
                                <?php echo $edificio['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"><?php echo $item['descripcion']; ?></textarea>
                </div>
                
                <!-- Información Técnica -->
                <div class="col-12 mt-4">
                    <h5 class="border-bottom pb-2">Información Técnica</h5>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-control" name="marca" value="<?php echo $item['marca']; ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-control" name="modelo" value="<?php echo $item['modelo']; ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Número de Serie</label>
                    <input type="text" class="form-control" name="numero_serie" value="<?php echo $item['numero_serie']; ?>">
                </div>
                
                <!-- Estado y Valor -->
                <div class="col-12 mt-4">
                    <h5 class="border-bottom pb-2">Estado y Valor</h5>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado Físico *</label>
                    <select name="estado_fisico" class="form-select" required>
                        <option value="excelente" <?php echo $item['estado_fisico'] == 'excelente' ? 'selected' : ''; ?>>Excelente</option>
                        <option value="bueno" <?php echo $item['estado_fisico'] == 'bueno' ? 'selected' : ''; ?>>Bueno</option>
                        <option value="regular" <?php echo $item['estado_fisico'] == 'regular' ? 'selected' : ''; ?>>Regular</option>
                        <option value="malo" <?php echo $item['estado_fisico'] == 'malo' ? 'selected' : ''; ?>>Malo</option>
                        <option value="obsoleto" <?php echo $item['estado_fisico'] == 'obsoleto' ? 'selected' : ''; ?>>Obsoleto</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado de Uso *</label>
                    <select name="estado_uso" class="form-select" required>
                        <option value="disponible" <?php echo $item['estado_uso'] == 'disponible' ? 'selected' : ''; ?>>Disponible</option>
                        <option value="en_uso" <?php echo $item['estado_uso'] == 'en_uso' ? 'selected' : ''; ?>>En Uso</option>
                        <option value="mantenimiento" <?php echo $item['estado_uso'] == 'mantenimiento' ? 'selected' : ''; ?>>Mantenimiento</option>
                        <option value="baja" <?php echo $item['estado_uso'] == 'baja' ? 'selected' : ''; ?>>Baja</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Valor *</label>
                    <input type="number" step="0.01" class="form-control" name="valor" value="<?php echo $item['valor']; ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha Adquisición</label>
                    <input type="date" class="form-control" name="fecha_adquisicion" value="<?php echo $item['fecha_adquisicion']; ?>">
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Actualizar Item
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                <a href="ver.php?id=<?php echo $item['id']; ?>" class="btn btn-info">
                    <i class="fas fa-eye me-2"></i>Ver Detalles
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>