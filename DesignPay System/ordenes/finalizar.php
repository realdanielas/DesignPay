<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '1') {
    exit("Acceso denegado");
}

$id_vendedor = $_SESSION['id_usuario'];

// Buscar la orden activa
$orden = $conn->query("SELECT * FROM ordenes_en_tiempo_real WHERE id_vendedor = $id_vendedor AND estado = 'activa'")->fetch_assoc();

if (!$orden) {
    echo "No hay orden activa";
    exit();
}

$inicio = strtotime($orden['inicio']);
$fin = time();
$minutos_totales = floor(($fin - $inicio) / 60);
$gratis = $orden['tipo_impresion'] == 'laser' ? 7 : 15;
$excedente = max(0, $minutos_totales - $gratis);
$monto = $excedente * 0.15;

// Marcar como finalizada
$fecha_fin = date('Y-m-d H:i:s', $fin);
$stmt = $conn->prepare("UPDATE ordenes_en_tiempo_real SET fin = ?, estado = 'finalizada', minutos_excedidos = ?, monto_cobrado = ? WHERE id = ?");
$stmt->bind_param("sidi", $fecha_fin, $excedente, $monto, $orden['id']);
$stmt->execute();

// En el futuro podrías pasar esta orden al módulo de pedidos o imprimirla
echo "ok";
?>
