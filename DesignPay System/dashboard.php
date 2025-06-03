<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
$nombre = $_SESSION['usuario'];
$tipo = $_SESSION['tipo_usuario'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .sidebar {
            min-height: 100vh;
            background-color: #343a40;
            color: white;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar a:hover {
            background-color: #495057;
        }
        .sidebar .active {
            background-color: #495057;
            font-weight: bold;
        }
        .main-content {
            padding: 20px;
        }
        .sidebar h4 {
            padding: 20px 15px 10px;
            font-size: 18px;
            border-bottom: 1px solid #495057;
        }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block sidebar">
            <h4>Gestión de Pedidos</h4>
            <p class="px-3">Bienvenido, <?= htmlspecialchars($nombre) ?></p>
            <ul class="nav flex-column">
                <?php if ($tipo == '2'): ?>
                <li><a href="#" onclick="cargarContenido('ordenes/monitoreo.php'); return false;">📡 Monitoreo</a></li>
                <?php endif; ?>
                <li><a href="#" onclick="cargarContenido('ordenes/nueva.php'); return false;">➕ Nueva Orden</a></li>
                <li><a href="#" onclick="cargarContenido('pedidos/index.php'); return false;">📝 Crear Pedidos</a></li>
                <?php if ($tipo == '2'): ?>
                <li><a href="#" onclick="cargarContenido('pedidos/admin.php'); return false;">📄 Consultar Pedidos</a></li>
                <li><a href="#" onclick="cargarContenido('productos/index.php'); return false;">📦 Productos</a></li>
                <li><a href="#" onclick="cargarContenido('categorias/index.php'); return false;">📚 Categorías</a></li>
                <li><a href="#" onclick="cargarContenido('usuarios/index.php'); return false;">👥 Usuarios</a></li>
                <?php endif; ?>
                <li><a href="logout.php">🚪 Cerrar Sesión</a></li>
            </ul>
        </nav>

        <!-- Contenido principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 main-content">
            <h1>Panel de Control</h1>
            <div id="contenido-dinamico" class="mt-4"></div>
        </main>
    </div>
</div>

<!-- Scripts -->
<script src="js/scripts.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<!-- Modal para pedidos (opcional) -->
<div id="modal-overlay" class="modal-overlay" style="display:none;">
    <div class="modal-contenido">
        <span class="modal-cerrar" onclick="cerrarModal()">&times;</span>
        <div id="detalle-pedido-modal"></div>
    </div>
</div>
</body>
</html>
