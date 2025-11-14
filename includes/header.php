<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .navbar-brand { font-weight: bold; }
        .sidebar { background-color: #2c3e50; min-height: 100vh; }
        .sidebar .nav-link { color: #ecf0f1; padding: 15px 20px; }
        .sidebar .nav-link:hover { background-color: #34495e; }
        .sidebar .nav-link.active { background-color: #3498db; }
        .user-info { color: #ecf0f1; padding: 15px 20px; border-bottom: 1px solid #34495e; }
        .badge-role { font-size: 0.7em; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="user-info">
                    <h5 class="mb-1">🏢 <?php echo SITE_NAME; ?></h5>
                    <small class="text-muted">
                        <?php 
                        if(isset($_SESSION['usuario_nombre'])) {
                            echo $_SESSION['usuario_nombre'];
                            echo ' <span class="badge bg-';
                            if(isAdmin()) echo 'danger';
                            elseif(isEncargado()) echo 'warning';
                            else echo 'info';
                            echo ' badge-role">' . $_SESSION['usuario_rol'] . '</span>';
                        } else {
                            echo 'No autenticado';
                        }
                        ?>
                    </small>
                </div>
                <nav class="nav flex-column">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>" href="../index.php">
                        <i class="fas fa-home me-2"></i>Inicio
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'inventario/') !== false ? 'active' : ''; ?>" href="../inventario/listar.php">
                        <i class="fas fa-boxes me-2"></i>Inventario
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'edificios/') !== false ? 'active' : ''; ?>" href="../edificios/listar.php">
                        <i class="fas fa-building me-2"></i>Edificios
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'categorias/') !== false ? 'active' : ''; ?>" href="../categorias/listar.php">
                        <i class="fas fa-tags me-2"></i>Categorías
                    </a>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'reportes/') !== false ? 'active' : ''; ?>" href="../reportes/generar.php">
                        <i class="fas fa-chart-bar me-2"></i>Reportes
                    </a>
                    <?php if(canManageUsers()): ?>
                    <a class="nav-link <?php echo strpos($_SERVER['PHP_SELF'], 'usuarios/') !== false ? 'active' : ''; ?>" href="../usuarios/listar.php">
                        <i class="fas fa-users me-2"></i>Usuarios
                    </a>
                    <?php endif; ?>
                    <?php if(isset($_SESSION['usuario_id'])): ?>
                    <a class="nav-link text-warning" href="../usuarios/logout.php">
                        <i class="fas fa-sign-out-alt me-2"></i>Cerrar Sesión
                    </a>
                    <?php else: ?>
                    <a class="nav-link text-success" href="../usuarios/login.php">
                        <i class="fas fa-sign-in-alt me-2"></i>Iniciar Sesión
                    </a>
                    <?php endif; ?>
                </nav>
            </div>
            
            <!-- Main content -->
            <div class="col-md-9 col-lg-10 ml-sm-auto px-4">