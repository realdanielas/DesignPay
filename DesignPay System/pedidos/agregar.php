<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '1') exit("Solo vendedores pueden crear pedidos");

$id_vendedor = $_SESSION['id_usuario'];
$cantidades = $_POST['cantidad'];
$precios = $_POST['precio'];
$canal = isset($_POST['canal']) && $_POST['canal'] === 'en_linea' ? 'en_linea' : 'presencial';

$total = 0;
$items = [];

foreach ($cantidades as $id_producto => $cantidad) {
    if ($cantidad > 0) {
        // Validar stock
        $res = $conn->query("SELECT stock FROM productos WHERE id = $id_producto");
        $row = $res->fetch_assoc();
        if ($row['stock'] < $cantidad) {
            echo "stock_error";
            exit();
        }

        $precio = $precios[$id_producto];
        $total += $cantidad * $precio;
        $items[] = ['id' => $id_producto, 'cantidad' => $cantidad, 'precio' => $precio];
    }
}

if (empty($items)) {
    echo "sin_items";
    exit();
}

// Insertar pedido
$conn->query("INSERT INTO pedidos (id_vendedor, total, canal) VALUES ($id_vendedor, $total, '$canal')");

$id_pedido = $conn->insert_id;

// Insertar detalle y actualizar stock
foreach ($items as $i) {
    $conn->query("INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario)
                  VALUES ($id_pedido, {$i['id']}, {$i['cantidad']}, {$i['precio']})");
    $conn->query("UPDATE productos SET stock = stock - {$i['cantidad']} WHERE id = {$i['id']}");
}

echo "ok";
