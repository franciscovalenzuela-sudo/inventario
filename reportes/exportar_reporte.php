<?php
include_once "../config.php";
include_once "../database/connection.php";
include_once "../includes/functions.php";
requireAuth();

$database = new Database();
$db = $database->getConnection();

$tipo = $_GET['tipo'] ?? 'general';
$formato = $_GET['formato'] ?? 'excel';

// Obtener datos para el reporte
$total_items = $db->query("SELECT COUNT(*) FROM " . DB_PREFIX . "inventario")->fetchColumn();
$valor_total = $db->query("SELECT SUM(valor) FROM " . DB_PREFIX . "inventario")->fetchColumn() ?? 0;

$items_por_categoria = $db->query("
    SELECT cp.nombre, COUNT(i.id) as total, SUM(i.valor) as valor_total
    FROM " . DB_PREFIX . "inventario i
    JOIN " . DB_PREFIX . "categorias_principales cp ON i.categoria_principal_id = cp.id
    GROUP BY cp.id, cp.nombre
")->fetchAll();

if ($formato === 'excel') {
    exportarReporteExcel($total_items, $valor_total, $items_por_categoria);
} elseif ($formato === 'pdf') {
    exportarReportePDF($total_items, $valor_total, $items_por_categoria);
} elseif ($formato === 'csv') {
    exportarReporteCSV($total_items, $valor_total, $items_por_categoria);
}

function exportarReporteExcel($total_items, $valor_total, $categorias) {
    header('Content-Type: application/vnd.ms-excel; charset=utf-8');
    header('Content-Disposition: attachment;filename="reporte_estadisticas_' . date('Y-m-d') . '.xls"');
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
    echo ".summary { background-color: #e9ecef; padding: 15px; margin: 20px 0; }";
    echo "</style>";
    echo "</head>";
    echo "<body>";
    
    echo "<h2>Reporte Estadístico de Inventario</h2>";
    echo "<p><strong>Fecha de generación:</strong> " . date('d/m/Y H:i') . "</p>";
    
    echo "<div class='summary'>";
    echo "<h3>Resumen General</h3>";
    echo "<p><strong>Total de items en inventario:</strong> " . $total_items . "</p>";
    echo "<p><strong>Valor total del inventario:</strong> $" . number_format($valor_total, 2) . "</p>";
    echo "</div>";
    
    echo "<h3>Distribución por Categorías</h3>";
    echo "<table>";
    echo "<tr>
            <th>Categoría</th>
            <th>Cantidad</th>
            <th>Valor Total</th>
            <th>% Cantidad</th>
            <th>% Valor</th>
          </tr>";
    
    foreach ($categorias as $categoria) {
        $porcentaje_cant = $total_items > 0 ? ($categoria['total'] / $total_items) * 100 : 0;
        $porcentaje_valor = $valor_total > 0 ? ($categoria['valor_total'] / $valor_total) * 100 : 0;
        
        echo "<tr>";
        echo "<td>" . htmlspecialchars($categoria['nombre']) . "</td>";
        echo "<td class='number'>" . $categoria['total'] . "</td>";
        echo "<td class='number'>$" . number_format($categoria['valor_total'], 2) . "</td>";
        echo "<td class='number'>" . number_format($porcentaje_cant, 1) . "%</td>";
        echo "<td class='number'>" . number_format($porcentaje_valor, 1) . "%</td>";
        echo "</tr>";
    }
    
    echo "<tr style='font-weight:bold; background-color:#f8f9fa;'>";
    echo "<td>TOTAL</td>";
    echo "<td class='number'>" . $total_items . "</td>";
    echo "<td class='number'>$" . number_format($valor_total, 2) . "</td>";
    echo "<td class='number'>100%</td>";
    echo "<td class='number'>100%</td>";
    echo "</tr>";
    
    echo "</table>";
    
    echo "</body>";
    echo "</html>";
    exit;
}

function exportarReportePDF($total_items, $valor_total, $categorias) {
    header('Content-Type: text/html; charset=utf-8');
    
    $html = '<!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Reporte Estadístico - PDF</title>
        <style>
            body { font-family: Arial, sans-serif; margin: 20px; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; margin-bottom: 20px; }
            .header h1 { margin: 0; color: #2c3e50; }
            .summary { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; }
            table { width: 100%; border-collapse: collapse; margin: 20px 0; }
            th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
            th { background-color: #34495e; color: white; }
            .number { text-align: right; }
            .total-row { background-color: #e9ecef; font-weight: bold; }
            .footer { margin-top: 30px; text-align: center; color: #7f8c8d; font-size: 12px; }
            @media print {
                body { margin: 0; }
                .no-print { display: none; }
            }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>Reporte Estadístico de Inventario</h1>
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

        <div class="summary">
            <h3>📊 Resumen General</h3>
            <p><strong>Total de items:</strong> ' . $total_items . '</p>
            <p><strong>Valor total del inventario:</strong> $' . number_format($valor_total, 2) . '</p>
            <p><strong>Valor promedio por item:</strong> $' . ($total_items > 0 ? number_format($valor_total / $total_items, 2) : '0.00') . '</p>
        </div>

        <h3>📈 Distribución por Categorías</h3>
        <table>
            <thead>
                <tr>
                    <th>Categoría</th>
                    <th>Cantidad</th>
                    <th>Valor Total</th>
                    <th>% Cantidad</th>
                    <th>% Valor</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($categorias as $categoria) {
        $porcentaje_cant = $total_items > 0 ? ($categoria['total'] / $total_items) * 100 : 0;
        $porcentaje_valor = $valor_total > 0 ? ($categoria['valor_total'] / $valor_total) * 100 : 0;
        
        $html .= '
                <tr>
                    <td>' . htmlspecialchars($categoria['nombre']) . '</td>
                    <td class="number">' . $categoria['total'] . '</td>
                    <td class="number">$' . number_format($categoria['valor_total'], 2) . '</td>
                    <td class="number">' . number_format($porcentaje_cant, 1) . '%</td>
                    <td class="number">' . number_format($porcentaje_valor, 1) . '%</td>
                </tr>';
    }
    
    $html .= '
                <tr class="total-row">
                    <td><strong>TOTAL</strong></td>
                    <td class="number"><strong>' . $total_items . '</strong></td>
                    <td class="number"><strong>$' . number_format($valor_total, 2) . '</strong></td>
                    <td class="number"><strong>100%</strong></td>
                    <td class="number"><strong>100%</strong></td>
                </tr>
            </tbody>
        </table>

        <div class="footer">
            <p>Sistema de Inventarios - Reporte Estadístico</p>
            <p>Página 1 de 1</p>
        </div>

        <script>
            window.onload = function() {
                // Auto-impresión opcional
                // window.print();
            };
        </script>
    </body>
    </html>';
    
    echo $html;
    exit;
}

function exportarReporteCSV($total_items, $valor_total, $categorias) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment;filename="reporte_estadisticas_' . date('Y-m-d') . '.csv"');
    header('Cache-Control: max-age=0');
    header('Pragma: no-cache');
    
    $output = fopen('php://output', 'w');
    
    // BOM para UTF-8
    fputs($output, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));
    
    // Encabezado del reporte
    fputcsv($output, ['REPORTE ESTADÍSTICO DE INVENTARIO'], ';');
    fputcsv($output, ['Fecha:', date('d/m/Y H:i')], ';');
    fputcsv($output, [], ';');
    
    // Resumen general
    fputcsv($output, ['RESUMEN GENERAL'], ';');
    fputcsv($output, ['Total de items:', $total_items], ';');
    fputcsv($output, ['Valor total:', '$' . number_format($valor_total, 2)], ';');
    fputcsv($output, [], ';');
    
    // Distribución por categorías
    fputcsv($output, ['DISTRIBUCIÓN POR CATEGORÍAS'], ';');
    fputcsv($output, ['Categoría', 'Cantidad', 'Valor Total', '% Cantidad', '% Valor'], ';');
    
    foreach ($categorias as $categoria) {
        $porcentaje_cant = $total_items > 0 ? ($categoria['total'] / $total_items) * 100 : 0;
        $porcentaje_valor = $valor_total > 0 ? ($categoria['valor_total'] / $valor_total) * 100 : 0;
        
        fputcsv($output, [
            $categoria['nombre'],
            $categoria['total'],
            '$' . number_format($categoria['valor_total'], 2),
            number_format($porcentaje_cant, 1) . '%',
            number_format($porcentaje_valor, 1) . '%'
        ], ';');
    }
    
    fputcsv($output, ['TOTAL', $total_items, '$' . number_format($valor_total, 2), '100%', '100%'], ';');
    
    fclose($output);
    exit;
}
?>