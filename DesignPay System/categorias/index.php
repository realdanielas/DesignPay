<?php
include("../verificar_admin.php");
include("../conexion.php");

$busqueda = $_GET['busqueda'] ?? '';

$sql = "SELECT * FROM categorias";
if ($busqueda !== '') {
    $sql .= " WHERE nombre LIKE ?";
    $stmt = $conn->prepare($sql);
    $likeBusqueda = "%$busqueda%";
    $stmt->bind_param("s", $likeBusqueda);
    $stmt->execute();
    $resultado = $stmt->get_result();
} else {
    $resultado = $conn->query($sql);
}
?>

<h2>📂 Gestión de Categorías</h2>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <button class="btn btn-success" onclick="cargarContenido('categorias/agregar.php')">➕ Agregar Categoría</button>

    <form id="formBuscar" onsubmit="buscarCategorias(event)" style="max-width: 300px;">
        <div class="input-group">
            <input type="text" id="inputBuscar" class="form-control" placeholder="Buscar categoría..." value="<?= htmlspecialchars($busqueda) ?>">
            <button type="submit" class="btn btn-primary">🔍 Buscar</button>
        </div>
    </form>
</div>

<table class="table table-bordered table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th style="width: 10%;">ID</th>
            <th style="width: 60%;">Nombre</th>
            <th style="width: 30%;">Acciones</th>
        </tr>
    </thead>
    <tbody>
    <?php if ($resultado->num_rows > 0): ?>
        <?php while($fila = $resultado->fetch_assoc()): ?>
            <tr>
                <td><?= $fila['id'] ?></td>
                <td style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 100%;">
                    <?= htmlspecialchars($fila['nombre']) ?>
                </td>
                <td>
                    <button class="btn btn-warning btn-sm me-1" onclick="cargarContenido('categorias/editar.php?id=<?= $fila['id'] ?>')">✏️ Editar</button>
                    <button class="btn btn-danger btn-sm" onclick="confirmarEliminar(<?= $fila['id'] ?>)">🗑️ Eliminar</button>
                </td>
            </tr>
        <?php endwhile; ?>
    <?php else: ?>
        <tr>
            <td colspan="3" class="text-center">No se encontraron categorías.</td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>



<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function buscarCategorias(e) {
    e.preventDefault();
    const busqueda = document.getElementById('inputBuscar').value.trim();
    cargarContenido('categorias/index.php?busqueda=' + encodeURIComponent(busqueda));
}

function confirmarEliminar(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`categorias/eliminar.php?id=${id}`)
                .then(res => res.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminada',
                            text: 'Categoría eliminada correctamente.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            cargarContenido('categorias/index.php');
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
