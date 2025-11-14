<?php
include_once "../config.php";
include_once "../database/connection.php";

header('Content-Type: application/json');

$categoria_id = $_GET['categoria_id'] ?? 0;

$database = new Database();
$db = $database->getConnection();

$query = "SELECT * FROM " . DB_PREFIX . "subcategorias 
          WHERE categoria_principal_id = ? AND estado = 'activo' 
          ORDER BY codigo";
$stmt = $db->prepare($query);
$stmt->execute([$categoria_id]);
$subcategorias = $stmt->fetchAll();

echo json_encode($subcategorias);
?>