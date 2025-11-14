<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

if ($_POST) {
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['estado'];
    
    try {
        $query = "INSERT INTO " . DB_PREFIX . "edificios 
                  (nombre, ubicacion, descripcion, estado) 
                  VALUES (?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$nombre, $ubicacion, $descripcion, $estado]);
        
        $mensaje = mostrarAlerta('success', 'Edificio creado correctamente');
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al crear edificio: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-building me-2"></i>Agregar Nuevo Edificio</h2>
    <a href="listar.php" class="btn btn-secondary">
        <i class="fas fa-arrow-left me-2"></i>Volver
    </a>
</div>

<div class="card">
    <div class="card-body">
        <?php echo $mensaje; ?>
        
        <form method="POST">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nombre del Edificio *</label>
                    <input type="text" class="form-control" name="nombre" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ubicación *</label>
                    <input type="text" class="form-control" name="ubicacion" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="activo">Activo</option>
                        <option value="inactivo">Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Crear Edificio
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>