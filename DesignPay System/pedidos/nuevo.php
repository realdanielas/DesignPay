<?php
include("../conexion.php");
session_start();
if ($_SESSION['tipo_usuario'] != '1') exit("Acceso denegado");

$productos = $conn->query("SELECT * FROM productos WHERE stock > 0");
?>
<h2>Nuevo Pedido</h2>

<form id="formPedido" onsubmit="crearPedido(event)">
    <!-- ✅ Aquí sí se enviará con POST -->
    <label>Canal del Pedido:</label>
    <select name="canal">
        <option value="presencial" selected>Presencial</option>
        <option value="en_linea">En Línea (WhatsApp, Facebook, etc.)</option>
    </select>
    <br><br>

    <table border="1">
        <tr><th>Producto</th><th>Precio</th><th>Stock</th><th>Cantidad</th></tr>
        <?php while($p = $productos->fetch_assoc()): ?>
        <tr>
            <td><?= $p['nombre'] ?></td>
            <td>$<?= $p['precio'] ?></td>
            <td><?= $p['stock'] ?></td>
            <td>
                <input type="number" name="cantidad[<?= $p['id'] ?>]" min="0" max="<?= $p['stock'] ?>" value="0">
                <input type="hidden" name="precio[<?= $p['id'] ?>]" value="<?= $p['precio'] ?>">
            </td>
        </tr>
        <?php endwhile; ?>
    </table>

    <br>
    <button type="submit">Registrar Pedido</button>
</form>


