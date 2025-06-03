<?php
include("../conexion.php");
session_start();
if ($_SESSION['tipo_usuario'] != '1') exit("Solo vendedores acceden aquí");

$id_vendedor = $_SESSION['id_usuario'];

$res = $conn->query("SELECT * FROM pedidos WHERE id_vendedor = $id_vendedor ORDER BY fecha DESC");
?>
<h2>Mis Pedidos</h2>
<a href="#" onclick="cargarContenido('pedidos/nuevo.php')">+ Nuevo Pedido</a>
<table border="1">
    <tr><th>ID</th><th>Fecha</th><th>Total</th></tr>
    <?php while($p = $res->fetch_assoc()): ?>
    <tr>
        <td><?= $p['id'] ?></td>
        <td><?= $p['fecha'] ?></td>
        <td>$<?= number_format($p['total'], 2) ?></td>
    </tr>
    <?php endwhile; ?>
</table>
