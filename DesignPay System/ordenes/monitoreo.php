<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '2') {
    exit("Acceso denegado");
}
?>

<h2>📡 Monitoreo de Órdenes en Tiempo Real</h2>
<div id="zona-monitoreo">
    <!-- Aquí se insertarán las tarjetas con JavaScript -->
</div>

<script>
function cargarMonitoreo() {
    fetch('ordenes/monitoreo_datos.php')
        .then(res => res.text())
        .then(html => {
            document.getElementById('zona-monitoreo').innerHTML = html;
        })
        .catch(err => {
            document.getElementById('zona-monitoreo').innerHTML = '<p>Error al cargar monitoreo.</p>';
            console.error(err);
        });
}

// Cargar al inicio
cargarMonitoreo();

// Recargar cada 10 segundos
setInterval(cargarMonitoreo, 1000);
</script>
