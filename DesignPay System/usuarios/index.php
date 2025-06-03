<?php
include("../conexion.php");
session_start();

if ($_SESSION['tipo_usuario'] != '2') {
    exit("Acceso denegado");
}

$busqueda = $_GET['buscar'] ?? '';
$sql = "SELECT * FROM usuarios WHERE usuario LIKE ? ORDER BY id ASC";
$stmt = $conn->prepare($sql);
$like = "%$busqueda%";
$stmt->bind_param("s", $like);
$stmt->execute();
$usuarios = $stmt->get_result();
?>

<h2>👥 Gestión de Usuarios</h2>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <button class="btn btn-success" onclick="cargarContenido('usuarios/agregar.php')">➕ Agregar Usuario</button>

  <form id="formBuscar" onsubmit="buscarUsuarios(event)" style="max-width: 300px;">
  <div class="input-group">
    <input type="text" id="busquedaInput" class="form-control" placeholder="Buscar usuario..." value="<?= htmlspecialchars($busqueda) ?>">
    <button type="submit" class="btn btn-primary">🔍 Buscar</button>
  </div>
</form>

</div>

<table class="table table-bordered table-hover">
    <thead class="table-light">
        <tr>
            <th>ID</th>
            <th>Usuario</th>
            <th>Tipo</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php while ($usuario = $usuarios->fetch_assoc()): ?>
        <tr>
            <td><?= $usuario['id'] ?></td>
            <td><?= htmlspecialchars($usuario['usuario']) ?></td>
            <td>
                <span class="badge <?= $usuario['tipo_usuario'] === '1' ? 'bg-primary' : 'bg-success' ?>">
                    <?= $usuario['tipo_usuario'] === '1' ? 'Vendedor' : 'Administrador' ?>
                </span>
            </td>
            <td>
                <button class="btn btn-warning btn-sm" onclick="cargarContenido('usuarios/editar.php?id=<?= $usuario['id'] ?>')">✏️ Editar</button>
                <button class="btn btn-danger btn-sm" onclick="eliminarUsuario(<?= $usuario['id'] ?>)">🗑️ Eliminar</button>
            </td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function buscarUsuarios(e) {
    e.preventDefault();
    const input = document.getElementById('busquedaInput').value.trim();
    cargarContenido('usuarios/index.php?buscar=' + encodeURIComponent(input));
}

function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('usuarios/eliminar.php?id=' + id)
                .then(r => r.text())
                .then(res => {
                    if (res.trim() === 'ok') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Eliminado',
                            text: 'El usuario ha sido eliminado.',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            cargarContenido('usuarios/index.php');
                        });
                    } else {
                        Swal.fire('Error', res, 'error');
                    }
                });
        }
    });
}
</script>
