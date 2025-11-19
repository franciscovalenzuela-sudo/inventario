<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Solo admin y encargado pueden editar proveedores
if (!canWrite()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$id = $_GET['id'] ?? 0;
$mensaje = '';

// Obtener datos del proveedor
$query = "SELECT * FROM " . DB_PREFIX . "proveedores WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$id]);
$proveedor = $stmt->fetch();

if (!$proveedor) {
    header("Location: listar.php");
    exit();
}

if ($_POST) {
    $nombre = $_POST['nombre'];
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ruc = $_POST['ruc'];
    $estado = $_POST['estado'];
    
    try {
        $query = "UPDATE " . DB_PREFIX . "proveedores 
                  SET nombre = ?, contacto = ?, telefono = ?, email = ?, 
                      direccion = ?, ruc = ?, estado = ?
                  WHERE id = ?";
        $stmt = $db->prepare($query);
        $stmt->execute([$nombre, $contacto, $telefono, $email, $direccion, $ruc, $estado, $id]);
        
        $mensaje = mostrarAlerta('success', 'Proveedor actualizado correctamente');
        
        // Actualizar datos locales
        $proveedor['nombre'] = $nombre;
        $proveedor['contacto'] = $contacto;
        $proveedor['telefono'] = $telefono;
        $proveedor['email'] = $email;
        $proveedor['direccion'] = $direccion;
        $proveedor['ruc'] = $ruc;
        $proveedor['estado'] = $estado;
        
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al actualizar proveedor: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-edit me-2"></i>Editar Proveedor</h2>
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
                    <label class="form-label">Nombre del Proveedor *</label>
                    <input type="text" class="form-control" name="nombre" value="<?php echo $proveedor['nombre']; ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Persona de Contacto</label>
                    <input type="text" class="form-control" name="contacto" value="<?php echo $proveedor['contacto']; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" value="<?php echo $proveedor['telefono']; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $proveedor['email']; ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <textarea class="form-control" name="direccion" rows="3"><?php echo $proveedor['direccion']; ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">RUC/NIT</label>
                    <input type="text" class="form-control" name="ruc" value="<?php echo $proveedor['ruc']; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="activo" <?php echo $proveedor['estado'] == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $proveedor['estado'] == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Actualizar Proveedor
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
                <a href="ver.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-info">
                    <i class="fas fa-eye me-2"></i>Ver Detalles
                </a>
            </div>
        </form>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>