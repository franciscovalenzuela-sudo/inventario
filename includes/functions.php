<?php
// Funciones auxiliares del sistema

function obtenerEstadoBadge($estado) {
    $colores = [
        'activo' => 'success',
        'inactivo' => 'secondary',
        'disponible' => 'success',
        'en_uso' => 'primary',
        'mantenimiento' => 'warning',
        'baja' => 'danger',
        'excelente' => 'success',
        'bueno' => 'info',
        'regular' => 'warning',
        'malo' => 'danger',
        'obsoleto' => 'dark'
    ];
    
    $color = $colores[$estado] ?? 'secondary';
    return '<span class="badge bg-' . $color . '">' . ucfirst($estado) . '</span>';
}

function obtenerEdificios($db) {
    $query = "SELECT * FROM " . DB_PREFIX . "edificios WHERE estado = 'activo' ORDER BY nombre";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerCategorias($db) {
    $query = "SELECT * FROM " . DB_PREFIX . "categorias_principales WHERE estado = 'activo' ORDER BY codigo";
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}

function obtenerSubcategorias($db, $categoria_id = null) {
    $query = "SELECT * FROM " . DB_PREFIX . "subcategorias WHERE estado = 'activo'";
    if ($categoria_id) {
        $query .= " AND categoria_principal_id = ?";
    }
    $query .= " ORDER BY codigo";
    
    $stmt = $db->prepare($query);
    if ($categoria_id) {
        $stmt->execute([$categoria_id]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function obtenerTiposObjeto($db, $subcategoria_id = null) {
    $query = "SELECT * FROM " . DB_PREFIX . "tipos_objeto WHERE estado = 'activo'";
    if ($subcategoria_id) {
        $query .= " AND subcategoria_id = ?";
    }
    $query .= " ORDER BY codigo";
    
    $stmt = $db->prepare($query);
    if ($subcategoria_id) {
        $stmt->execute([$subcategoria_id]);
    } else {
        $stmt->execute();
    }
    return $stmt->fetchAll();
}

function registrarMovimiento($db, $inventario_id, $tipo, $descripcion) {
    $query = "INSERT INTO " . DB_PREFIX . "movimientos_inventario 
              (inventario_id, tipo_movimiento, descripcion, usuario_responsable) 
              VALUES (?, ?, ?, ?)";
    $stmt = $db->prepare($query);
    $stmt->execute([
        $inventario_id,
        $tipo,
        $descripcion,
        $_SESSION['usuario_nombre'] ?? 'Sistema'
    ]);
}
?>