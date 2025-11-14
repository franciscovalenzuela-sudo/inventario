<?php
include_once "../config.php";
include_once "../database/connection.php";

if ($_POST) {
    $database = new Database();
    $db = $database->getConnection();
    
    $usuario = $_POST["usuario"];
    $password = $_POST["password"];
    
    $query = "SELECT * FROM " . DB_PREFIX . "usuarios WHERE usuario = ? AND estado = 'activo'";
    $stmt = $db->prepare($query);
    $stmt->execute([$usuario]);
    
    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (password_verify($password, $user["password"])) {
            $_SESSION["usuario_id"] = $user["id"];
            $_SESSION["usuario_nombre"] = $user["nombre_completo"];
            $_SESSION["usuario_rol"] = $user["rol"];
            header("Location: ../index.php");
            exit();
        }
    }
    $error = "Usuario o contraseña incorrectos";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema de Inventarios</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 100vh; }
        .login-card { background: white; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
    </style>
</head>
<body>
    <div class="container h-100">
        <div class="row h-100 justify-content-center align-items-center">
            <div class="col-md-4">
                <div class="login-card p-4">
                    <div class="text-center mb-4">
                        <h3 class="text-primary">🏢 Sistema de Inventarios</h3>
                        <p class="text-muted">Iniciar Sesión</p>
                    </div>
                    
                    <?php if(isset($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label">Usuario</label>
                            <input type="text" class="form-control" name="usuario" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Iniciar Sesión</button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            Usuario demo: <strong>admin</strong> / Contraseña: <strong>admin123</strong>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>