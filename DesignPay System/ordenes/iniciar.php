<?php
include("../conexion.php");
session_start();

header('Content-Type: text/plain'); // ← asegura texto plano sin HTML

if ($_SESSION['tipo_usuario'] != '1') exit("Acceso denegado");

$id_vendedor = $_SESSION['id_usuario'];
$canal = $_POST['canal'] ?? '';
$tipo = $_POST['tipo_impresion'] ?? '';

if (!$canal || !$tipo) {
    echo "Faltan datos requeridos";
    exit();
}

$inicio = date('Y-m-d H:i:s');

// Verificar si ya tiene una orden activa
$verifica = $conn->query("SELECT id FROM ordenes_en_tiempo_real 
                          WHERE id_vendedor = $id_vendedor AND estado = 'activa'");
if ($verifica->num_rows > 0) {
    echo "Ya tienes una orden activa";
    exit();
}

// Insertar nueva orden
$stmt = $conn->prepare("INSERT INTO ordenes_en_tiempo_real (id_vendedor, canal, tipo_impresion, inicio) VALUES (?, ?, ?, ?)");
$stmt->bind_param("isss", $id_vendedor, $canal, $tipo, $inicio);

if ($stmt->execute()) {
    echo "ok"; // ← sin espacios ni HTML
} else {
    echo "Error en la base de datos";
}
?>
