<?php
session_start();
include("../conexion.php");

if (!isset($_SESSION['tipo_usuario']) || $_SESSION['tipo_usuario'] != '2') {
    exit("Acceso denegado. Solo administradores.");
}

$id_pedido = isset($_GET['id']) ? intval($_GET['id']) : 0;
$accion = $_GET['accion']; // 'cobrar' o 'entregar'

if ($id_pedido <= 0 || !in_array($accion, ['cobrar', 'entregar'])) {
    exit("Parámetros inválidos");
}

if ($accion === 'cobrar') {
    $sql = "UPDATE pedidos SET estado_pago = 'cobrado' WHERE id = ?";
} elseif ($accion === 'entregar') {
    $sql = "UPDATE pedidos SET estado_entrega = 'entregado' WHERE id = ?";
}

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();

echo "ok";
