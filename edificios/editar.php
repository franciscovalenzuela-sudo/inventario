<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;
$mensaje = '';

// Obtener datos del edificio
$query = "SELECT * FROM " . DB_PREFIX . "edificios WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$edificio = $stmt->fetch();

if (!$edificio) {
    header("Location: listar.php");
    exit();
}

if ($_POST) {
    $nombre = $_POST['nombre'];
    $ubicacion = $_POST['ubicacion'];
    $descripcion = $_POST['descripcion'];
    $estado = $_POST['estado'];
    
    try {
        $query = "UPDATE " . DB_PREFIX . "edificios 
                  SET nombre = ?, ubicacion = ?, descripcion = ?, estado = ? 
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$nombre, $ubicacion, $descripcion, $estado, $id]);
        
        $mensaje = mostrarAlerta('success', 'Edificio actualizado correctamente');
        
        // Actualizar datos locales
        $edificio['nombre'] = $nombre;
        $edificio['ubicacion'] = $ubicacion;
        $edificio['descripcion'] = $descripcion;
        $edificio['estado'] = $estado;
        
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al actualizar edificio: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Editar Edificio</h2>
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
                    <input type="text" class="form-control" name="nombre" value="<?php echo $edificio['nombre']; ?>" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Ubicación *</label>
                    <input type="text" class="form-control" name="ubicacion" value="<?php echo $edificio['ubicacion']; ?>" required>
                </div>
                <div class="col-12">
                    <label class="form-label">Descripción</label>
                    <textarea class="form-control" name="descripcion" rows="3"><?php echo $edificio['descripcion']; ?></textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="activo" <?php echo $edificio['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $edificio['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Actualizar Edificio
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>