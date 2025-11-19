<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

// Solo admin y encargado pueden agregar proveedores
if (!canWrite()) {
    header("Location: ../index.php");
    exit();
}

$database = new Database();
$db = $database->getConnection();

$mensaje = '';

if ($_POST) {
    $nombre = $_POST['nombre'];
    $contacto = $_POST['contacto'];
    $telefono = $_POST['telefono'];
    $email = $_POST['email'];
    $direccion = $_POST['direccion'];
    $ruc = $_POST['ruc'];
    $estado = $_POST['estado'];
    
    try {
        $query = "INSERT INTO " . DB_PREFIX . "proveedores 
                  (nombre, contacto, telefono, email, direccion, ruc, estado) 
                  VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $db->prepare($query);
        $stmt->execute([$nombre, $contacto, $telefono, $email, $direccion, $ruc, $estado]);
        
        $mensaje = mostrarAlerta('success', 'Proveedor creado correctamente');
        
        // Limpiar formulario
        $_POST = [];
        
    } catch (PDOException $e) {
        $mensaje = mostrarAlerta('danger', 'Error al crear proveedor: ' . $e->getMessage());
    }
}
?>
<?php include_once "../includes/header.php"; ?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-truck-loading me-2"></i>Agregar Nuevo Proveedor</h2>
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
                    <input type="text" class="form-control" name="nombre" value="<?php echo $_POST['nombre'] ?? ''; ?>" required>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Persona de Contacto</label>
                    <input type="text" class="form-control" name="contacto" value="<?php echo $_POST['contacto'] ?? ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Teléfono</label>
                    <input type="text" class="form-control" name="telefono" value="<?php echo $_POST['telefono'] ?? ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" class="form-control" name="email" value="<?php echo $_POST['email'] ?? ''; ?>">
                </div>
                
                <div class="col-12">
                    <label class="form-label">Dirección</label>
                    <textarea class="form-control" name="direccion" rows="3"><?php echo $_POST['direccion'] ?? ''; ?></textarea>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">RUC/NIT</label>
                    <input type="text" class="form-control" name="ruc" value="<?php echo $_POST['ruc'] ?? ''; ?>">
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Estado *</label>
                    <select class="form-select" name="estado" required>
                        <option value="activo" <?php echo ($_POST['estado'] ?? 'activo') == 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo ($_POST['estado'] ?? '') == 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
            </div>
            
            <div class="mt-4">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Guardar Proveedor
                </button>
                <a href="listar.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php include_once "../includes/footer.php"; ?>