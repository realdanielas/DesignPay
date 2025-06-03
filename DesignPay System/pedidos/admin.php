<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '2') {
    exit("Acceso restringido solo para administradores");
}

$canal = $_GET['canal'] ?? 'todos';
$estado_pago = $_GET['estado_pago'] ?? 'todos';
$estado_entrega = $_GET['estado_entrega'] ?? 'todos';

$sql = "SELECT p.id, p.fecha, p.total, p.canal, p.estado_pago, p.estado_entrega, u.usuario AS vendedor
        FROM pedidos p
        JOIN usuarios u ON p.id_vendedor = u.id
        WHERE 1=1";

if ($canal !== 'todos') $sql .= " AND p.canal = '$canal'";
if ($estado_pago !== 'todos') $sql .= " AND p.estado_pago = '$estado_pago'";
if ($estado_entrega !== 'todos') $sql .= " AND p.estado_entrega = '$estado_entrega'";
$sql .= " ORDER BY p.fecha DESC";

$res = $conn->query($sql);
?>

<h2 class="mb-4">📋 Pedidos Registrados (Administrador)</h2>

<form id="filtrosPedidos" class="row g-3 mb-4" onchange="aplicarFiltros()">
    <div class="col-md-3">
        <label class="form-label">Canal:</label>
        <select name="canal" class="form-select">
            <option value="todos" <?= $canal === 'todos' ? 'selected' : '' ?>>Todos</option>
            <option value="presencial" <?= $canal === 'presencial' ? 'selected' : '' ?>>Presencial</option>
            <option value="en_linea" <?= $canal === 'en_linea' ? 'selected' : '' ?>>En Línea</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Pago:</label>
        <select name="estado_pago" class="form-select">
            <option value="todos" <?= $estado_pago === 'todos' ? 'selected' : '' ?>>Todos</option>
            <option value="pendiente" <?= $estado_pago === 'pendiente' ? 'selected' : '' ?>>Pendiente</option>
            <option value="cobrado" <?= $estado_pago === 'cobrado' ? 'selected' : '' ?>>Cobrado</option>
        </select>
    </div>
    <div class="col-md-3">
        <label class="form-label">Entrega:</label>
        <select name="estado_entrega" class="form-select">
            <option value="todos" <?= $estado_entrega === 'todos' ? 'selected' : '' ?>>Todos</option>
            <option value="no_entregado" <?= $estado_entrega === 'no_entregado' ? 'selected' : '' ?>>No Entregado</option>
            <option value="entregado" <?= $estado_entrega === 'entregado' ? 'selected' : '' ?>>Entregado</option>
        </select>
    </div>
</form>

<div class="table-responsive">
    <table class="table table-bordered align-middle table-hover">
        <thead class="table-dark text-center">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Vendedor</th>
                <th>Canal</th>
                <th>Total</th>
                <th>Pago</th>
                <th>Entrega</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
        <?php while ($p = $res->fetch_assoc()): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= $p['fecha'] ?></td>
                <td><?= htmlspecialchars($p['vendedor']) ?></td>
                <td><?= ucfirst($p['canal']) ?></td>
                <td>$<?= number_format($p['total'], 2) ?></td>
                <td>
                    <?php if ($p['estado_pago'] === 'cobrado'): ?>
                        <span class="badge bg-success">Cobrado</span>
                    <?php else: ?>
                        <span class="badge bg-danger">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td>
                    <?php if ($p['estado_entrega'] === 'entregado'): ?>
                        <span class="badge bg-primary">Entregado</span>
                    <?php else: ?>
                        <span class="badge bg-warning text-dark">No entregado</span>
                    <?php endif; ?>
                </td>
                <td class="text-center">
                    <a href="#" class="btn btn-sm btn-outline-info me-1" onclick="cargarDetallePedido(<?= $p['id'] ?>)">🔍 Ver</a>
                    <?php if ($p['estado_pago'] === 'pendiente'): ?>
                        <button class="btn btn-sm btn-outline-success me-1" onclick="actualizarEstado(<?= $p['id'] ?>, 'cobrar')">💰 Cobrar</button>
                    <?php endif; ?>
                    <?php if ($p['estado_entrega'] === 'no_entregado'): ?>
                        <button class="btn btn-sm btn-outline-primary" onclick="actualizarEstado(<?= $p['id'] ?>, 'entregar')">📦 Entregar</button>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endwhile; ?>
        </tbody>
    </table>
</div>

<!-- Modal para detalle del pedido -->
<div class="modal fade" id="modalDetallePedido" tabindex="-1" aria-labelledby="modalDetalleLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalDetalleLabel">Detalle del Pedido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenido-modal-detalle">
        <!-- Aquí se cargará el contenido del pedido -->
      </div>
    </div>
  </div>
</div>


<!-- SweetAlert2 CDN -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
function aplicarFiltros() {
    const form = document.getElementById('filtrosPedidos');
    const params = new URLSearchParams(new FormData(form)).toString();
    cargarContenido('pedidos/admin.php?' + params);
}

function actualizarEstado(id, accion) {
    let mensaje = accion === 'cobrar' ? '¿Marcar este pedido como COBRADO?' : '¿Marcar como ENTREGADO?';
    Swal.fire({
        title: mensaje,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`pedidos/actualizar_estado.php?id=${id}&accion=${accion}`)
                .then(r => r.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        Swal.fire('✅ Éxito', 'Estado actualizado correctamente.', 'success');
                        setTimeout(() => cargarContenido('pedidos/admin.php'), 1200);
                    } else {
                        Swal.fire('❌ Error', res, 'error');
                    }
                });
        }
    });
}


function cargarDetallePedido(id) {
    fetch('pedidos/detalle.php?id=' + id)
        .then(r => r.text())
        .then(html => {
            document.getElementById('contenido-modal-detalle').innerHTML = html;
            new bootstrap.Modal(document.getElementById('modalDetallePedido')).show();
        });
}

</script>
