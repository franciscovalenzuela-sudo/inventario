<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

// Filtros (mismos que en listar.php)
$filtro_edificio = $_GET['edificio'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_categoria = $_GET['categoria'] ?? '';
$formato = $_GET['formato'] ?? 'excel';

// Construir consulta con filtros
$query = "SELECT i.*, e.nombre as edificio, cp.nombre as categoria, 
                 sc.nombre as subcategoria, tobj.nombre as tipo_objeto
          FROM " . DB_PREFIX . "inventario i
          LEFT JOIN " . DB_PREFIX . "edificios e ON i.edificio_id = e.id
          LEFT JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
          LEFT JOIN " . DB_PREFIX . "subcategorias sc ON i.subcategoria_id = sc.id
          LEFT JOIN " . DB_PREFIX . "tipos_objeto tobj ON i.tipo_objeto_id = tobj.id
          WHERE 1=1";

$params = [];

if ($filtro_edificio) {
    $query .= " AND i.edificio_id = ?";
    $params[] = $filtro_edificio;
}

if ($filtro_estado) {
    $query .= " AND i.estado_uso = ?";
    $params[] = $filtro_estado;
}

if ($filtro_categoria) {
    $query .= " AND i.categoria_principal_id = ?";
    $params[] = $filtro_categoria;
}

$query .= " ORDER BY i.fecha_creacion DESC";

$stmt = $db->prepare($query);
$stmt->execute($params);
$inventario = $stmt->fetchAll();

if ($formato === 'excel') {
    exportarExcel($inventario);
} elseif ($formato === 'pdf') {
    exportarPDF($inventario);
} elseif ($formato === 'csv') {
    exportarCSV($inventario);
}

function exportarExcel($datos) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment;filename="inventario_' . date('Y-m-d') . '.xls"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');
    
    echo "<html>";
    echo "<head>";
    echo "<meta charset='UTF-8'>";
    echo "<style>";
    echo "table { border-collapse: collapse; width: 100%; }";
    echo "th, td { border: 1px solid #000; padding: 8px; text-align: left; }";
    echo "th { background-color: #f2f2f2; font-weight: bold; }";
    echo ".number { text-align: right; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    
    echo "<h2>Reporte de Inventario - " . date('d/m/Y') . "</h2>";
    
    echo "<table>";
    echo "<tr>
            <th>Código</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Marca</th>
            <th>Modelo</th>
            <th>Número Serie</th>
            <th>Edificio</th>
            <th>Categoría</th>
            <th>Subcategoría</th>
            <th>Tipo</th>
            <th>Estado Físico</th>
            <th>Estado Uso</th>
            <th>Valor</th>
            <th>Fecha Adquisición</th>
            <th>Fecha Registro</th>
          </tr>";
    
    $total_valor = 0;
    foreach ($datos as $item) {
        $total_valor += $item['valor'];
        echo "<tr>";
        echo "<td>" . htmlspecialchars($item['codigo_completo'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['nombre'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['descripcion'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['marca'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['modelo'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['numero_serie'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['edificio'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['categoria'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['subcategoria'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['tipo_objeto'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['estado_fisico'] ?? '') . "</td>";
        echo "<td>" . htmlspecialchars($item['estado_uso'] ?? '') . "</td>";
        echo "<td class='number'>$" . number_format($item['valor'] ?? 0, 2) . "</td>";
        echo "<td>" . ($item['fecha_adquisicion'] ?? '') . "</td>";
        echo "<td>" . ($item['fecha_creacion'] ?? '') . "</td>";
        echo "</tr>";
    }
    
    // Fila de total
    echo "<tr style='font-weight:bold; background-color:#e9ecef;'>";
    echo "<td colspan='11'></td>";
    echo "<td>TOTAL</td>";
    echo "<td class='number'>$" . number_format($total_valor, 2) . "</td>";
    echo "<td colspan='2'></td>";
    echo "</tr>";
    
    echo "</table>";
    
    echo "<p><strong>Total de items:</strong> " . count($datos) . "</p>";
    echo "<p><strong>Valor total del inventario:</strong> $" . number_format($total_valor, 2) . "</p>";
    echo "<p><em>Generado el: " . date('d/m/Y H:i') . "</em></p>";
    
    echo "</body>";
    echo "</html>";
    exit;
}

function exportarPDF($datos) {
    // PDF simple usando HTML con estilos para impresión
    header('Content-Type: text/html; charset=utf-8');
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte de Inventario - PDF</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #2c3e50; }
            .header p { margin: 5px 0; color: #7f8c8d; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 12px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #34495e; color: white; font-weight: bold; }
            tr:nth-child(even) { background-color: #f8f9fa; }
            .total-row { background-color: #e9ecef !important; font-weight: bold; }
            .number { text-align: right; }
            .summary { margin-top: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 5px; }
            .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #7f8c8d; border-top: 1px solid #ddd; padding-top: 10px; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Reporte de Inventario</h1>
            <p>Sistema de Gestión de Inventarios</p>
            <p>Generado el: ' . date('d/m/Y H:i') . '</p>
        </div>
        
        <div class="no-print" style="margin-bottom: 20px;">
            <button onclick="window.print()" style="padding: 10px 20px; background: #3498db; color: white; border: none; border-radius: 5px; cursor: pointer;">
                🖨️ Imprimir / Guardar como PDF
            </button>
            <button onclick="window.close()" style="padding: 10px 20px; background: #e74c3c; color: white; border: none; border-radius: 5px; cursor: pointer;">
                ❌ Cerrar
            </button>
        </div>

        <table>
            <thead>
                <tr>
                    <th width="80">Código</th>
                    <th width="120">Nombre</th>
                    <th width="80">Edificio</th>
                    <th width="80">Categoría</th>
                    <th width="60">Estado</th>
                    <th width="60">Valor</th>
                    <th width="80">Fecha Reg.</th>
                </tr>
            </thead>
            <tbody>';
    
    $total_valor = 0;
    foreach ($datos as $item) {
        $total_valor += $item['valor'];
        $html .= '
                <tr>
                    <td><strong>' . htmlspecialchars($item['codigo_completo']) . '</strong></td>
                    <td>' . htmlspecialchars(substr($item['nombre'], 0, 30)) . '</td>
                    <td>' . htmlspecialchars($item['edificio']) . '</td>
                    <td>' . htmlspecialchars($item['categoria']) . '</td>
                    <td>' . htmlspecialchars($item['estado_uso']) . '</td>
                    <td class="number">$' . number_format($item['valor'], 2) . '</td>
                    <td>' . date('d/m/Y', strtotime($item['fecha_creacion'])) . '</td>
                </tr>';
    }
    
    $html .= '
                <tr class="total-row">
                    <td colspan="4"><strong>TOTAL GENERAL</strong></td>
                    <td><strong>' . count($datos) . ' items</strong></td>
                    <td class="number"><strong>$' . number_format($total_valor, 2) . '</strong></td>
                    <td></td>
                </tr>
            </tbody>
        </table>

        <div class="summary">
            <h3>Resumen del Reporte</h3>
            <p><strong>Total de items en el inventario:</strong> ' . count($datos) . '</p>
            <p><strong>Valor total del inventario:</strong> $' . number_format($total_valor, 2) . '</p>';
    
    // Agregar estadísticas por estado
    $estados = [];
    foreach ($datos as $item) {
        $estado = $item['estado_uso'];
        if (!isset($estados[$estado])) {
            $estados[$estado] = 0;
        }
        $estados[$estado]++;
    }
    
    if (!empty($estados)) {
        $html .= '<p><strong>Distribución por estado:</strong></p><ul>';
        foreach ($estados as $estado => $cantidad) {
            $porcentaje = round(($cantidad / count($datos)) * 100, 1);
            $html .= '<li>' . ucfirst($estado) . ': ' . $cantidad . ' items (' . $porcentaje . '%)</li>';
        }
        $html .= '</ul>';
    }
    
    $html .= '
        </div>

        <div class="footer">
            <p>Sistema de Inventarios - Generado automáticamente</p>
            <p>Página 1 de 1</p>
        </div>

        <script>
            // Auto-impresión al cargar (opcional)
            window.onload = function() {
                // Descomenta la siguiente línea para auto-imprimir
                // window.print();
            };
        </script>
    </body>
    </html>';
    
    echo $html;
    exit;
}

function exportarCSV($datos) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="inventario_' . date('Y-m-d') . '.csv"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Encabezados
    fputcsv($output, [
        'Código', 'Nombre', 'Descripción', 'Marca', 'Modelo', 'Número Serie',
        'Edificio', 'Categoría', 'Subcategoría', 'Tipo', 
        'Estado Físico', 'Estado Uso', 'Valor', 'Fecha Adquisición', 'Fecha Registro'
    ], ';');
    
    $total_valor = 0;
    foreach ($datos as $item) {
        $total_valor += $item['valor'];
        fputcsv($output, [
            $item['codigo_completo'] ?? '',
            $item['nombre'] ?? '',
            $item['descripcion'] ?? '',
            $item['marca'] ?? '',
            $item['modelo'] ?? '',
            $item['numero_serie'] ?? '',
            $item['edificio'] ?? '',
            $item['categoria'] ?? '',
            $item['subcategoria'] ?? '',
            $item['tipo_objeto'] ?? '',
            $item['estado_fisico'] ?? '',
            $item['estado_uso'] ?? '',
            $item['valor'] ?? '',
            $item['fecha_adquisicion'] ?? '',
            $item['fecha_creacion'] ?? ''
        ], ';');
    }
    
    // Fila de total
    fputcsv($output, ['', '', '', '', '', '', '', '', '', '', '', 'TOTAL', $total_valor, '', ''], ';');
    
    fclose($output);
    exit;
}
?>