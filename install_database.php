<?php
// Instalador de Base de Datos - Sistema de Inventarios
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_POST['install'] ?? false) {
    installDatabase();
} else {
    showInstallerForm();
}

function showInstallerForm() {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Instalador - Sistema de Inventarios</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="bg-light">
        <div class="container py-5">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card shadow">
                        <div class="card-header bg-primary text-white text-center">
                            <h3 class="mb-0">Instalador de Base de Datos</h3>
                        </div>
                        <div class="card-body">
                            <form method="POST">
                                <div class="mb-3">
                                    <label class="form-label">Host de MySQL</label>
                                    <input type="text" class="form-control" name="db_host" value="localhost" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Usuario MySQL</label>
                                    <input type="text" class="form-control" name="db_user" value="root" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Contraseña MySQL</label>
                                    <input type="password" class="form-control" name="db_pass">
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Nombre de Base de Datos</label>
                                    <input type="text" class="form-control" name="db_name" value="sistema_inventarios" required>
                                </div>
                                <button type="submit" name="install" value="1" class="btn btn-primary w-100">
                                    Instalar Base de Datos
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function installDatabase() {
    $db_host = $_POST['db_host'];
    $db_user = $_POST['db_user'];
    $db_pass = $_POST['db_pass'];
    $db_name = $_POST['db_name'];
    
    try {
        // Conectar al servidor MySQL
        $pdo = new PDO("mysql:host=$db_host", $db_user, $db_pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

	// Insertar usuario administrador
   	 $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
  	  $pdo->exec("INSERT INTO inv_usuarios (usuario, password, nombre_completo, email, rol) VALUES
        ('admin', '$admin_pass', 'Administrador Principal', 'admin@empresa.com', 'admin'),
        ('encargado1', '$admin_pass', 'Encargado de Inventario', 'encargado@empresa.com', 'encargado'),
        ('usuario1', '$admin_pass', 'Usuario Consulta', 'usuario@empresa.com', 'usuario')");
    
   	 // ... (resto del código igual)
        
        // Crear base de datos
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `$db_name`");
        
        // Crear tablas
        $pdo->exec("CREATE TABLE inv_usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            usuario VARCHAR(50) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            nombre_completo VARCHAR(100),
            email VARCHAR(100),
            rol ENUM('admin', 'usuario') DEFAULT 'usuario',
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE inv_edificios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            ubicacion VARCHAR(200),
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        
        $pdo->exec("CREATE TABLE inv_categorias_principales (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo INT UNIQUE NOT NULL,
            nombre VARCHAR(50) NOT NULL,
            descripcion TEXT,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo'
        )");
        
        $pdo->exec("CREATE TABLE inv_subcategorias (
            id INT AUTO_INCREMENT PRIMARY KEY,
            categoria_principal_id INT,
            codigo INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            FOREIGN KEY (categoria_principal_id) REFERENCES inv_categorias_principales(id)
        )");
        
        $pdo->exec("CREATE TABLE inv_tipos_objeto (
            id INT AUTO_INCREMENT PRIMARY KEY,
            subcategoria_id INT,
            codigo INT NOT NULL,
            nombre VARCHAR(100) NOT NULL,
            descripcion TEXT,
            estado ENUM('activo', 'inactivo') DEFAULT 'activo',
            FOREIGN KEY (subcategoria_id) REFERENCES inv_subcategorias(id)
        )");
        
        $pdo->exec("CREATE TABLE inv_inventario (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_completo VARCHAR(50) UNIQUE NOT NULL,
            edificio_id INT,
            categoria_principal_id INT,
            subcategoria_id INT,
            tipo_objeto_id INT,
            nombre VARCHAR(200) NOT NULL,
            descripcion TEXT,
            marca VARCHAR(100),
            modelo VARCHAR(100),
            numero_serie VARCHAR(100),
            estado_fisico ENUM('excelente', 'bueno', 'regular', 'malo', 'obsoleto') DEFAULT 'bueno',
            estado_uso ENUM('disponible', 'en_uso', 'mantenimiento', 'baja') DEFAULT 'disponible',
            valor DECIMAL(15,2),
            fecha_adquisicion DATE,
            fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (edificio_id) REFERENCES inv_edificios(id),
            FOREIGN KEY (categoria_principal_id) REFERENCES inv_categorias_principales(id),
            FOREIGN KEY (subcategoria_id) REFERENCES inv_subcategorias(id),
            FOREIGN KEY (tipo_objeto_id) REFERENCES inv_tipos_objeto(id)
        )");
        
        // Insertar datos iniciales
        $pdo->exec("INSERT INTO inv_categorias_principales (codigo, nombre, descripcion) VALUES
            (1, 'Bienes Pasivos', 'Activos que no generan ingresos directos'),
            (2, 'Bienes Activos', 'Activos que generan ingresos')");
        
        $pdo->exec("INSERT INTO inv_edificios (nombre, descripcion, ubicacion) VALUES
            ('Edificio Principal', 'Sede central de la empresa', 'Av. Principal #123'),
            ('Edificio Anexo', 'Edificio secundario', 'Calle Secundaria #456')");
        
        $pdo->exec("INSERT INTO inv_subcategorias (categoria_principal_id, codigo, nombre, descripcion) VALUES
            (1, 101, 'Mobiliario', 'Muebles y equipamiento de oficina'),
            (1, 102, 'Equipos Computación', 'Equipos de computación y tecnología')");
        
        $pdo->exec("INSERT INTO inv_tipos_objeto (subcategoria_id, codigo, nombre, descripcion) VALUES
            (1, 1011, 'Sillas Oficina', 'Sillas ergonómicas para oficina'),
            (1, 1012, 'Escritorios', 'Escritorios de trabajo'),
            (2, 1021, 'Computadoras', 'Equipos de computo personales')");
        
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $pdo->exec("INSERT INTO inv_usuarios (usuario, password, nombre_completo, email, rol) VALUES
            ('admin', '$admin_pass', 'Administrador Principal', 'admin@empresa.com', 'admin')");
        
        // Crear archivo config.php
        $config_content = "<?php
define('DB_HOST', '$db_host');
define('DB_NAME', '$db_name');
define('DB_USER', '$db_user');
define('DB_PASS', '$db_pass');
define('DB_PREFIX', 'inv_');
define('SITE_NAME', 'Sistema de Inventarios');
session_start();
?>";
        
        file_put_contents('config.php', $config_content);
        
        echo "<div class='alert alert-success'>Base de datos instalada correctamente. Usuario: admin, Contraseña: admin123</div>";
        
    } catch (PDOException $e) {
        echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}
?>