<?php
include("../verificar_admin.php");
include("../conexion.php");

$id = intval($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo "ID inválido";
    exit;
}

$stmt = $conn->prepare("DELETE FROM categorias WHERE id = ?");
$stmt->bind_param("i", $id);
if ($stmt->execute()) {
    http_response_code(200);
    echo "ok";
} else {
    http_response_code(500);
    echo "error";
}
?>
