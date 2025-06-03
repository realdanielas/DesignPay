<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '2') {
    exit("Acceso denegado");
}

$id = intval($_GET['id']);

// Obtener info del pedido y vendedor
$pedido = $conn->query("SELECT p.*, u.usuario AS vendedor 
                        FROM pedidos p
                        JOIN usuarios u ON p.id_vendedor = u.id
                        WHERE p.id = $id")->fetch_assoc();

// Obtener detalle de productos
$detalle = $conn->query("SELECT dp.*, pr.nombre 
                         FROM detalle_pedido dp
                         JOIN productos pr ON dp.id_producto = pr.id
                         WHERE dp.id_pedido = $id");
?>

<!-- Agrega esto en el <head> de tu plantilla principal para cargar Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

<div id="detalle-impresion">
    <div style="text-align: center; margin-bottom: 15px;">
        <img src="img/logo.png" alt="Logo" style="max-width: 150px; height: auto;">
    </div>
    <h3>Detalle del Pedido #<?= htmlspecialchars($pedido['id']) ?></h3>
    <p><strong>Vendedor:</strong> <?= htmlspecialchars($pedido['vendedor']) ?></p>
    <p><strong>Fecha:</strong> <?= htmlspecialchars($pedido['fecha']) ?></p>
    <p><strong>Canal:</strong> <?= ucfirst(htmlspecialchars($pedido['canal'])) ?></p>
    <p><strong>Estado de pago:</strong> <?= ucfirst(htmlspecialchars($pedido['estado_pago'])) ?></p>
    <p><strong>Estado de entrega:</strong> <?= ucfirst(htmlspecialchars($pedido['estado_entrega'])) ?></p>

    <table border="1" width="100%" style="margin-top:15px;">
        <tr>
            <th>Producto</th><th>Cantidad</th><th>Precio Unitario</th><th>Subtotal</th>
        </tr>
        <?php 
        $total = 0;
        while($d = $detalle->fetch_assoc()):
            $subtotal = $d['cantidad'] * $d['precio_unitario'];
            $total += $subtotal;
        ?>
        <tr>
            <td><?= htmlspecialchars($d['nombre']) ?></td>
            <td><?= htmlspecialchars($d['cantidad']) ?></td>
            <td>$<?= number_format($d['precio_unitario'], 2) ?></td>
            <td>$<?= number_format($subtotal, 2) ?></td>
        </tr>
        <?php endwhile; ?>
        <tr>
            <td colspan="3" align="right"><strong>Total:</strong></td>
            <td><strong>$<?= number_format($total, 2) ?></strong></td>
        </tr>
    </table>
    <hr style="margin-top: 30px;">
    <p style="font-size: 12px; text-align: center;">
        Descargado el <?= date("Y-m-d H:i:s") ?><br>
        <em>Este documento no reemplaza una factura oficial.</em>
    </p>
    <button class="btn btn-primary btn-no-pdf" onclick="descargarPDF(<?= htmlspecialchars($pedido['id']) ?>)" style="margin-top:15px;">
        <i class="bi bi-printer"></i> Imprimir Detalle
    </button>
</div>
