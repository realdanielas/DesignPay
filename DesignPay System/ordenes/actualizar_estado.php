<?php
include("../conexion.php");
session_start();

$id_vendedor = $_SESSION['id_usuario'];
$accion = $_GET['accion'] ?? '';

// Obtener orden activa o pausada
$orden = $conn->query("SELECT * FROM ordenes_en_tiempo_real 
                       WHERE id_vendedor = $id_vendedor 
                       AND estado IN ('activa', 'pausada') LIMIT 1")->fetch_assoc();

if (!$orden) {
    echo "No hay orden";
    exit();
}

// Al pausar
if ($accion === 'pausar' && $orden['estado'] === 'activa') {
    $inicio = strtotime($orden['inicio']);
    $ahora = time();
    $acumulado = floor($ahora - $inicio) + intval($orden['segundos_acumulados']);

    $conn->query("UPDATE ordenes_en_tiempo_real 
                  SET estado = 'pausada', segundos_acumulados = $acumulado 
                  WHERE id = {$orden['id']}");
    echo "ok";
    exit();
}

// Al reanudar
if ($accion === 'reanudar' && $orden['estado'] === 'pausada') {
    $ahora = date("Y-m-d H:i:s");
    $conn->query("UPDATE ordenes_en_tiempo_real 
                  SET estado = 'activa', inicio = '$ahora' 
                  WHERE id = {$orden['id']}");
    echo "ok";
    exit();
}
?>
