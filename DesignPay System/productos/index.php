<?php
include("../verificar_admin.php");
include("../conexion.php");

$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT p.*, c.nombre AS nombre_categoria 
        FROM productos p 
        JOIN categorias c ON p.id_categoria = c.id";
if ($busqueda !== '') {
    $sql .= " WHERE p.nombre LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeBusqueda = "%$busqueda%";
    $stmt->bind_param("s", $likeBusqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query($sql);
}
?>

<h2>📦 Gestión de Productos</h2>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <button class="btn btn-success" onclick="cargarContenido('productos/agregar.php')">➕ Agregar Producto</button>

    <form id="formBuscar" onsubmit="buscarProductos(event)" style="max-width: 300px;">
        <div class="input-group">
            <input type="text" id="inputBuscar" class="form-control" placeholder="Buscar producto..." value="<?= htmlspecialchars($busqueda) ?>">
            <button type="submit" class="btn btn-primary">🔍 Buscar</button>
        </div>
    </form>
</div>

<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Material</th>
            <th>Precio</th>
            <th>Cantidad</th>
            <th>Stock</th>
            <th>Categoría</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($resultado->num_rows > 0): ?>
        <?php while($p = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $p['id'] ?></td>
                <td><?= htmlspecialchars($p['nombre']) ?></td>
                <td><?= htmlspecialchars($p['material']) ?></td>
                <td>$<?= number_format($p['precio'], 2) ?></td>
                <td><?= $p['cantidad'] ?></td>
                <td><?= $p['stock'] ?></td>
                <td><?= htmlspecialchars($p['nombre_categoria']) ?></td>
                <td>
                    <button class="btn btn-warning btn-sm me-1" onclick="cargarContenido('productos/editar.php?id=<?= $p['id'] ?>')">✏️ Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="confirmarEliminarProducto(<?= $p['id'] ?>)">🗑️ Eliminar</button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="text-center">No se encontraron productos.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function buscarProductos(e) {
    e.preventDefault();
    const busqueda = document.getElementById('inputBuscar').value.trim();
    cargarContenido('productos/index.php?busqueda=' + encodeURIComponent(busqueda));
}

function confirmarEliminarProducto(id) {
    Swal.fire({
        title: '¿Eliminar producto?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('productos/eliminar.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: 'id=' + encodeURIComponent(id)
            })
            .then(res => res.text())
            .then(res => {
                if (res.trim() === 'ok') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'Producto eliminado correctamente.',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        cargarContenido('productos/index.php');
                    });
                } else {
                    Swal.fire('Error', res, 'error');
                }
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudo eliminar.', 'error');
            });
        }
    });
}
</script>
