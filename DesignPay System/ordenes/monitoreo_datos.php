<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '2') {
    exit("Acceso denegado");
}

$ordenes = $conn->query("SELECT o.*, u.usuario AS vendedor_nombre
                         FROM ordenes_en_tiempo_real o
                         JOIN usuarios u ON o.id_vendedor = u.id
                         WHERE o.estado IN ('activa', 'pausada')
                         ORDER BY o.id DESC");

if ($ordenes->num_rows === 0) {
    echo "<p>✅ No hay órdenes activas ni pausadas.</p>";
    exit;
}

echo '<div style="display: flex; flex-wrap: wrap; gap: 20px;">';

while ($orden = $ordenes->fetch_assoc()) {
    $min_gratis = $orden['tipo_impresion'] === 'laser' ? 7 : 15;
    $inicio = strtotime($orden['inicio']);
    $ahora = time();
    $estado = $orden['estado'];

    $segundos_totales = ($estado === 'activa')
        ? ($ahora - $inicio) + intval($orden['segundos_acumulados'])
        : intval($orden['segundos_acumulados']);

    $h = str_pad(floor($segundos_totales / 3600), 2, '0', STR_PAD_LEFT);
    $m = str_pad(floor(($segundos_totales % 3600) / 60), 2, '0', STR_PAD_LEFT);
    $s = str_pad($segundos_totales % 60, 2, '0', STR_PAD_LEFT);

    $minutos_exactos = $segundos_totales / 60;
    $excedente = max(0, floor($minutos_exactos - $min_gratis));
    $monto = $excedente * 0.15;

    echo '
    <div style="border: 1px solid #ccc; border-radius: 8px; padding: 15px; width: 300px;">
        <h3>' . ucfirst($orden['vendedor_nombre']) . ' ' . ($estado === 'pausada' ? '⏸️' : '🟢') . '</h3>
        <p><strong>Tipo:</strong> ' . ucfirst($orden['tipo_impresion']) . '</p>
        <p><strong>Canal:</strong> ' . ucfirst($orden['canal']) . '</p>
        <p><strong>Minutos Gratis:</strong> ' . $min_gratis . '</p>
        <p><strong>Tiempo:</strong> ' . "$h:$m:$s" . '</p>
        <p><strong>Excedente:</strong> ' . $excedente . ' min (' . ($monto > 0 ? '$' . number_format($monto, 2) . ' USD' : 'sin cobro') . ')</p>
        <p><strong>Estado:</strong> ' . ucfirst($estado) . '</p>
    </div>';
}

echo '</div>';
