<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '1') {
    exit("Acceso denegado");
}

$id_vendedor = $_SESSION['id_usuario'];

// Eliminar la orden actual activa o pausada
$conn->query("DELETE FROM ordenes_en_tiempo_real 
              WHERE id_vendedor = $id_vendedor 
              AND estado IN ('activa', 'pausada')");

echo "ok";
