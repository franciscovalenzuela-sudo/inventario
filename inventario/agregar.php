<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

// Obtener opciones para dropdowns
$edificios = obtenerEdificios($db);
$categorias = obtenerCategorias($db);
$proveedores = obtenerProveedores($db); // Nueva línea


if ($_POST) {
    $codigo_completo = $_POST['categoria_principal'] . '.' . $_POST['subcategoria'] . '.' . $_POST['tipo_objeto'];
    $nombre = $_POST['nombre'];
    $edificio_id = $_POST['edificio_id'];
    $categoria_principal_id = $_POST['categoria_principal'];
    $subcategoria_id = $_POST['subcategoria'];
    $tipo_objeto_id = $_POST['tipo_objeto'];
    $descripcion = $_POST['descripcion'];
    $marca = $_POST['marca'];
    $modelo = $_POST['modelo'];
    $numero_serie = $_POST['numero_serie'];
    $estado_fisico = $_POST['estado_fisico'];
    $estado_uso = $_POST['estado_uso'];
    $valor = $_POST['valor'];
    $fecha_adquisicion = $_POST['fecha_adquisicion'];
    
    try {
        $query = "INSERT INTO " . DB_PREFIX . "inventario 
                  (codigo_completo, edificio_id, categoria_principal_id, subcategoria_id, tipo_objeto_id,
                   nombre, descripcion, marca, modelo, numero_serie, estado_fisico, estado_uso, valor, fecha_adquisicion) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $db->prepare($query);
        $stmt->execute([
            $codigo_completo, $edificio_id, $categoria_principal_id, $subcategoria_id, $tipo_objeto_id,
            $nombre, $descripcion, $marca, $modelo, $numero_serie, $estado_fisico, $estado_uso, $valor, $fecha_adquisicion
        ]);
        
        $mensaje = mostrarAlerta('success', 'Item agregado correctamente al inventario');
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al agregar item: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-plus-circle me-2"></i>Agregar Item al Inventario</h2>
    <a href="listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver al Inventario
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $mensaje; ?>
        
        <form method="POST" id="formItem">
            <div class="row g-3">
                <!-- Información Básica -->
                <div class="col-12">
                    <h5 class="border-bottom pb-2">Información Básica</h5>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Nombre del Item *</label>
                    <input type="text" class="form-control" name="nombre" value="<?php echo $_POST['nombre'] ?? ''; ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Edificio *</label>
                    <select name="edificio_id" class="form-select" required>
                        <option value="">Seleccionar Edificio</option>
                        <?php foreach ($edificios as $edificio): ?>
                            <option value="<?php echo $edificio['id']; ?>" <?php echo ($_POST['edificio_id'] ?? '') == $edificio['id'] ? 'selected' : ''; ?>>
                                <?php echo $edificio['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
                </div>
                
                <!-- Sistema de Códigos -->
                <div class="col-12 mt-4">
                    <h5 class="border-bottom pb-2">Sistema de Códigos</h5>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Categoría Principal *</label>
                    <select name="categoria_principal" class="form-select" id="categoriaPrincipal" required>
                        <option value="">Seleccionar Categoría</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['id']; ?>" data-codigo="<?php echo $categoria['codigo']; ?>" 
                                <?php echo ($_POST['categoria_principal'] ?? '') == $categoria['id'] ? 'selected' : ''; ?>>
                                <?php echo $categoria['codigo']; ?> - <?php echo $categoria['nombre']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Subcategoría *</label>
                    <select name="subcategoria" class="form-select" id="subcategoriaSelect" required>
                        <option value="">Primero selecciona categoría</option>
                    </select>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Tipo de Objeto *</label>
                    <select name="tipo_objeto" class="form-select" id="tipoObjetoSelect" required>
                        <option value="">Primero selecciona subcategoría</option>
                    </select>
                </div>
                
                <div class="col-12">
                    <div class="alert alert-info">
                        <strong>Código generado:</strong> 
                        <span id="codigoGenerado" class="badge bg-primary fs-6">-</span>
                    </div>
                </div>
                
                <!-- Información Técnica -->
                <div class="col-12 mt-4">
                    <h5 class="border-bottom pb-2">Información Técnica</h5>
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Marca</label>
                    <input type="text" class="form-control" name="marca" value="<?php echo $_POST['marca'] ?? ''; ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Modelo</label>
                    <input type="text" class="form-control" name="modelo" value="<?php echo $_POST['modelo'] ?? ''; ?>">
                </div>
                
                <div class="col-md-4">
                    <label class="form-label">Número de Serie</label>
                    <input type="text" class="form-control" name="numero_serie" value="<?php echo $_POST['numero_serie'] ?? ''; ?>">
                </div>
                
                <!-- Estado y Valor -->
                <div class="col-12 mt-4">
                    <h5 class="border-bottom pb-2">Estado y Valor</h5>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado Físico *</label>
                    <select name="estado_fisico" class="form-select" required>
                        <option value="excelente">Excelente</option>
                        <option value="bueno" selected>Bueno</option>
                        <option value="regular">Regular</option>
                        <option value="malo">Malo</option>
                        <option value="obsoleto">Obsoleto</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Estado de Uso *</label>
                    <select name="estado_uso" class="form-select" required>
                        <option value="disponible" selected>Disponible</option>
                        <option value="en_uso">En Uso</option>
                        <option value="mantenimiento">Mantenimiento</option>
                        <option value="baja">Baja</option>
                    </select>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Valor *</label>
                    <input type="number" step="0.01" class="form-control" name="valor" value="<?php echo $_POST['valor'] ?? ''; ?>" required>
                </div>
                
                <div class="col-md-3">
                    <label class="form-label">Fecha Adquisición</label>
                    <input type="date" class="form-control" name="fecha_adquisicion" value="<?php echo $_POST['fecha_adquisicion'] ?? ''; ?>">
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success btn-lg">
                    <i class="fas fa-save me-2"></i>Guardar Item
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<script>
// Actualizar subcategorías cuando cambia la categoría principal
document.getElementById('categoriaPrincipal').addEventListener('change', function() {
    const categoriaId = this.value;
    const subcategoriaSelect = document.getElementById('subcategoriaSelect');
    const tipoObjetoSelect = document.getElementById('tipoObjetoSelect');
    
    if (categoriaId) {
        fetch(`../ajax/get_subcategorias.php?categoria_id=${categoriaId}`)
            .then(response => response.json())
            .then(data => {
                subcategoriaSelect.innerHTML = '<option value="">Seleccionar Subcategoría</option>';
                tipoObjetoSelect.innerHTML = '<option value="">Primero selecciona subcategoría</option>';
                
                data.forEach(subcat => {
                    subcategoriaSelect.innerHTML += `<option value="${subcat.id}" data-codigo="${subcat.codigo}">${subcat.codigo} - ${subcat.nombre}</option>`;
                });
                
                actualizarCodigo();
            });
    } else {
        subcategoriaSelect.innerHTML = '<option value="">Primero selecciona categoría</option>';
        tipoObjetoSelect.innerHTML = '<option value="">Primero selecciona subcategoría</option>';
        actualizarCodigo();
    }
});

// Actualizar tipos cuando cambia la subcategoría
document.getElementById('subcategoriaSelect').addEventListener('change', function() {
    const subcategoriaId = this.value;
    const tipoObjetoSelect = document.getElementById('tipoObjetoSelect');
    
    if (subcategoriaId) {
        fetch(`../ajax/get_tipos_objeto.php?subcategoria_id=${subcategoriaId}`)
            .then(response => response.json())
            .then(data => {
                tipoObjetoSelect.innerHTML = '<option value="">Seleccionar Tipo</option>';
                
                data.forEach(tipo => {
                    tipoObjetoSelect.innerHTML += `<option value="${tipo.id}" data-codigo="${tipo.codigo}">${tipo.codigo} - ${tipo.nombre}</option>`;
                });
                
                actualizarCodigo();
            });
    } else {
        tipoObjetoSelect.innerHTML = '<option value="">Primero selecciona subcategoría</option>';
        actualizarCodigo();
    }
});

// Actualizar código cuando cambia cualquier select
document.getElementById('tipoObjetoSelect').addEventListener('change', actualizarCodigo);

function actualizarCodigo() {
    const categoriaSelect = document.getElementById('categoriaPrincipal');
    const subcategoriaSelect = document.getElementById('subcategoriaSelect');
    const tipoObjetoSelect = document.getElementById('tipoObjetoSelect');
    const codigoGenerado = document.getElementById('codigoGenerado');
    
    const categoriaOption = categoriaSelect.options[categoriaSelect.selectedIndex];
    const subcategoriaOption = subcategoriaSelect.options[subcategoriaSelect.selectedIndex];
    const tipoObjetoOption = tipoObjetoSelect.options[tipoObjetoSelect.selectedIndex];
    
    let codigo = '-';
    
    if (categoriaOption && categoriaOption.dataset.codigo && 
        subcategoriaOption && subcategoriaOption.dataset.codigo &&
        tipoObjetoOption && tipoObjetoOption.dataset.codigo) {
        
        codigo = `${categoriaOption.dataset.codigo}.${subcategoriaOption.dataset.codigo}.${tipoObjetoOption.dataset.codigo}`;
    }
    
    codigoGenerado.textContent = codigo;
}
</script>

<?php include_once "../includes/footer.php"; ?>